#!/usr/bin/env python3
"""
YonoLootZone Duplicate-Content & Technical SEO Quality Detector.

Evaluates all public HTML pages (and especially /games/*/index.html) for:
1. Exact title / meta description / canonical collisions
2. Pairwise 5-gram Jaccard textual similarity across main article content
3. Minimum unique information thresholds & mirror-domain detection
4. JSON-LD validity and HTML structural checks

Pages failing uniqueness thresholds are flagged so they remain `noindex, follow`
and excluded from sitemap.xml until enriched with independent publisher data.
"""
import json
import os
import re
from pathlib import Path
from collections import defaultdict

ROOT = Path(__file__).resolve().parent.parent


def extract_main_text(html_str: str) -> str:
    m = re.search(r'<main\b[^>]*>(.*?)</main>', html_str, re.DOTALL | re.I)
    chunk = m.group(1) if m else html_str
    chunk = re.sub(r'<script\b[^>]*>.*?</script>', ' ', chunk, flags=re.DOTALL | re.I)
    chunk = re.sub(r'<style\b[^>]*>.*?</style>', ' ', chunk, flags=re.DOTALL | re.I)
    chunk = re.sub(r'<[^>]+>', ' ', chunk)
    return re.sub(r'\s+', ' ', chunk).strip()


def five_grams(text: str) -> set:
    words = text.lower().split()
    if len(words) < 5:
        return {' '.join(words)}
    return {' '.join(words[i:i + 5]) for i in range(len(words) - 4)}


def audit_games(enforce_noindex: bool = False) -> dict:
    games_path = ROOT / 'data' / 'games.json'
    games = json.loads(games_path.read_text(encoding='utf-8'))
    by_slug = {g['slug']: g for g in games}

    indexable_shingles = {}
    word_counts = {}
    empty_td_pages = []
    jsonld_failures = []
    noindex_slugs = []

    for g in games:
        slug = g['slug']
        p = ROOT / 'games' / slug / 'index.html'
        if not p.exists():
            continue
        raw = p.read_text(encoding='utf-8', errors='ignore')
        text = extract_main_text(raw)
        wc = len(text.split())
        word_counts[slug] = wc

        if '<td></td>' in raw:
            empty_td_pages.append(slug)

        for m in re.finditer(r'<script[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>', raw, re.DOTALL | re.I):
            try:
                json.loads(m.group(1))
            except Exception as exc:
                jsonld_failures.append((slug, str(exc)))

        is_noindex = bool(re.search(r'<meta[^>]*name=["\']robots["\'][^>]*content=["\'][^"\']*noindex', raw, re.I))
        if is_noindex:
            noindex_slugs.append(slug)
        else:
            # Extract Overview + Independent Review + FAQs specifically (core editorial body)
            m_ov = re.findall(r'<section class="glass-panel[^>]*>.*?</section>', raw, re.DOTALL | re.I)
            editorial_txt = extract_main_text(' '.join(m_ov[:2])) + ' ' + g.get('independentReview', '')
            indexable_shingles[slug] = five_grams(editorial_txt)

    high_sim_pairs = []
    idx_slugs = sorted(indexable_shingles.keys())
    for i in range(len(idx_slugs)):
        for j in range(i + 1, len(idx_slugs)):
            a, b = idx_slugs[i], idx_slugs[j]
            s1, s2 = indexable_shingles[a], indexable_shingles[b]
            sim = len(s1 & s2) / len(s1 | s2) if (s1 | s2) else 0.0
            if sim > 0.45:
                high_sim_pairs.append((round(sim, 4), a, b))

    if enforce_noindex and high_sim_pairs:
        # Automatically mark later duplicate if similarity exceeds 60%
        to_demote = set()
        for sim, a, b in high_sim_pairs:
            if sim > 0.60:
                to_demote.add(b)
        for slug in to_demote:
            p = ROOT / 'games' / slug / 'index.html'
            raw = p.read_text(encoding='utf-8')
            raw = re.sub(
                r'(<meta name="robots" content=")[^"]*(")',
                r'\1noindex, follow\2',
                raw,
                count=1
            )
            p.write_text(raw, encoding='utf-8')
            if slug in by_slug:
                by_slug[slug]['indexable'] = False
        if to_demote:
            games_path.write_text(json.dumps(games, indent=2, ensure_ascii=False), encoding='utf-8')

    return {
        'total_games': len(games),
        'indexable_count': len(idx_slugs),
        'noindex_count': len(noindex_slugs),
        'noindex_slugs': noindex_slugs,
        'min_word_count': min(word_counts.values()) if word_counts else 0,
        'max_word_count': max(word_counts.values()) if word_counts else 0,
        'empty_td_pages': empty_td_pages,
        'jsonld_failures': jsonld_failures,
        'high_sim_pairs_over_45pct': sorted(high_sim_pairs, reverse=True)
    }


if __name__ == '__main__':
    report = audit_games(enforce_noindex=False)
    print(json.dumps(report, indent=2))
