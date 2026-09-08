#!/usr/bin/env python3
"""Generate the canonical XML sitemap for YonoLootZone.

Includes public HTML pages and all dedicated game pages; excludes admin, 404,
source/control files and duplicate URLs.
"""
from pathlib import Path
from xml.sax.saxutils import escape
from datetime import date
import sys

base=(sys.argv[1] if len(sys.argv)>1 else "https://yonolootzone.com").rstrip("/")
root=Path(__file__).resolve().parent
today=date.today().isoformat()

exclude={"admin.html","404.html"}
urls=[("/","weekly","1.0")]
for page in sorted(root.glob("*.html")):
    if page.name in exclude or page.name=="index.html":
        continue
    urls.append((f"/{page.name}","monthly","0.6"))
for page in sorted((root/"guides").glob("*.html")):
    urls.append((f"/guides/{page.name}","monthly","0.6"))
for page in sorted((root/"games").glob("*/index.html")):
    urls.append((f"/games/{page.parent.name}/","weekly","0.8"))

seen=set(); rows=[]
for path,freq,priority in urls:
    loc=base+path
    if loc in seen: continue
    seen.add(loc)
    rows.append(f"  <url><loc>{escape(loc)}</loc><lastmod>{today}</lastmod><changefreq>{freq}</changefreq><priority>{priority}</priority></url>")

xml='<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'+"\n".join(rows)+'\n</urlset>\n'
(root/"sitemap.xml").write_text(xml,encoding="utf-8")
print(f"Generated {len(rows)} canonical public URLs")
