# V16 Server-side CMS

V16 adds a small PHP publishing CMS under `/cms/`.

## Hostinger setup
1. Upload the V16 website files to `public_html`.
2. Make sure the hosting plan has PHP enabled.
3. Open `https://yonolootzone.com/cms/setup.php` once.
4. Create a strong admin password (10+ characters).
5. After setup, use `https://yonolootzone.com/cms/`.
6. Publish a post or game. The CMS writes the public HTML, updates `sitemap.xml`, and refreshes `/posts/index.html`.

The CMS stores its small content index in `cms/data/`. Do not expose or edit these files publicly. The included `.htaccess` denies access to the data folder.

## Important
- Take a backup before first production deployment.
- Do not put passwords into public JavaScript/localStorage.
- Use original, factual content. Do not publish fake bonuses, fake reviews, fake player counts, or copied competitor text.
- The existing static pages remain intact.
