# YonoLootZone V14 — Daily Publishing Workflow

V14 keeps the site static but makes publishing much easier.

## Daily post workflow
1. Open `/admin.html` on your site.
2. Select **Posts & Content** → **New Post**.
3. Choose a type: Article Update, New Game, How-to Guide, or Comparison/List.
4. Add a unique title, meta description, target keyword, excerpt and original article.
5. Add a real featured image and accurate alt text. Add a YouTube URL/Shorts URL or direct MP4 only when useful.
6. For a New Game post, add the game name, current game URL, image and category.
7. Click **Preview** to check the page.
8. Click **Generate Publishing Files**.

V14 generates three files:
- `your-slug.html` — upload to `public_html/posts/`
- `your-slug-sitemap.xml` — copy its `<url>...</url>` block into `sitemap.xml`
- `posts-index.html` — upload/replace `public_html/posts/index.html` so the new article is discoverable from the posts hub

## Video support
- YouTube watch URLs are converted to an embeddable player.
- YouTube Shorts URLs are converted to an embeddable player.
- Direct `.mp4`, `.webm`, and `.ogg` URLs use an HTML video player.

## SEO rules
- Write original, useful content for the searcher.
- Use one clear primary topic per post.
- Do not copy competitor articles.
- Do not publish fake bonuses, player counts, reviews, withdrawals, or guarantees.
- Do not create many near-identical pages only to target keyword variations.
- Keep internal links natural and useful.

## Recommended weekly mix
- 3–5 useful guides/updates
- 1–2 genuine new-game announcements when you actually add games
- 1 comparison/list article when there is enough original information to compare

## Important static-site limitation
The admin panel runs in your browser. It cannot directly write new HTML files into Hostinger's `public_html`. You still upload the generated files. For true one-click publishing, the next architecture step is a small server-backed CMS (PHP/MySQL or Node.js) that writes the pages and sitemap on the server.
