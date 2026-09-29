# YonoLootZone V15 — New Game Publishing Workflow

V15 adds a dedicated **Generate SEO Files** action to the Game Manager.

## For every new game
1. Open `/admin.html`.
2. Go to **Game Manager → Add Game**.
3. Fill Game Name, Category, Game/Download Link, image and the SEO fields.
4. Write an original Game Description. Add real FAQ answers only.
5. Click **Generate SEO Files**.
6. Upload `<slug>-index.html` as:
   `public_html/games/<slug>/index.html`
7. Add the generated `<url>` entry from `<slug>-sitemap.xml` to `public_html/sitemap.xml`.
8. Use the generated internal-link JSON to add a link from a relevant existing page or post.
9. Click **Save Game** so the admin browser data also contains the new game.
10. In Google Search Console, request indexing for the new URL when appropriate.

## Important
The admin panel uses browser localStorage. It cannot directly write new HTML files into Hostinger's `public_html` folder. V15 therefore generates production-ready files for upload.

Do not publish copied competitor text, fake reviews, fake player counts, fake bonuses, or unsupported claims. Keep game links and terms current.
