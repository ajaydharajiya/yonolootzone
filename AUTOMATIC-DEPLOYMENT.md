# YonoLootZone automatic deployment

Recommended production flow:

GitHub -> Hostinger Git deployment -> public_html -> yonolootzone.com

## One-time setup

1. Create a GitHub repository, for example `yonolootzone`.
2. Upload/push this project to the `main` branch.
3. In Hostinger open:
   Websites -> yonolootzone.com -> Dashboard -> Advanced -> Git.
4. Connect GitHub and select the repository and `main` branch.
5. Set the deployment/root directory to `public_html` if Hostinger asks for it.
6. Deploy once.

After that, future pushes to `main` can automatically deploy the website.

## CI

GitHub Actions runs on every push and pull request to `main`.
It checks:
- required website files
- old domain/brand references
- sitemap
- robots.txt

A failed check stops the CI workflow.

## Docker

Docker files are included for local testing or VPS deployment.

Run locally:

    docker compose up --build

Then open:

    http://localhost:8080

Docker is not required for the current static Hostinger web-hosting deployment.
