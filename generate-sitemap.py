#!/usr/bin/env python3
"""Generate a clean XML sitemap for the Yono Games Directory static site.

Usage:
    python generate-sitemap.py [BASE_URL]

The script discovers public HTML pages and /games/*/index.html pages directly
from the site folder. It intentionally excludes admin.html and other utility
files that should not be indexed.
"""
from pathlib import Path
from xml.sax.saxutils import escape
from datetime import date
import sys

base=(sys.argv[1] if len(sys.argv)>1 else "https://yonolootzone.com").rstrip("/")
root=Path(__file__).resolve().parent
today=date.today().isoformat()

urls=[("/", "weekly", "1.0")]
for name in ["about.html","contact.html","privacy.html","disclaimer.html","telegram.html","editorial-policy.html"]:
    if (root/name).exists():
        urls.append((f"/{name}", "monthly", "0.5"))

for page in sorted((root/"games").glob("*/index.html")):
    slug=page.parent.name
    urls.append((f"/games/{slug}/", "weekly", "0.8"))

seen=set()
rows=[]
for path,freq,priority in urls:
    loc=base+path
    if loc in seen: continue
    seen.add(loc)
    rows.append(f"  <url><loc>{escape(loc)}</loc><lastmod>{today}</lastmod><changefreq>{freq}</changefreq><priority>{priority}</priority></url>")

xml='<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'+"\n".join(rows)+'\n</urlset>\n'
(root/"sitemap.xml").write_text(xml,encoding="utf-8")
print(f"Generated {len(rows)} clean URLs")
