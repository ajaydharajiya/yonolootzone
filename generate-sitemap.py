#!/usr/bin/env python3
"""Generate sitemap.xml from my-game-backup.json or games JSON exported by the admin."""
import json,sys,html
base=(sys.argv[1] if len(sys.argv)>1 else "https://example.com").rstrip("/")
src=sys.argv[2] if len(sys.argv)>2 else "my-game-backup.json"
with open(src,encoding="utf-8") as f:d=json.load(f)
games=d.get("games",d if isinstance(d,list) else [])
urls=[base+"/"]
for g in games:
    if g.get("slug") and g.get("status",True) is not False:
        urls.append(base+"/#game/"+g["slug"])
body='<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
for u in urls: body+=f" <url><loc>{html.escape(u)}</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>\n"
body+="</urlset>\n"
open("sitemap.xml","w",encoding="utf-8").write(body)
print("Generated",len(urls),"URLs")
