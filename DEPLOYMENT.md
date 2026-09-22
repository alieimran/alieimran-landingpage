# Deployment

General production deployment process. For the actual host's specific steps (cPanel, SSH), see `CPANEL_DEPLOYMENT.md`. This file covers what's true regardless of host.

## Before every deployment

Run through the checklist at the bottom of this file. Don't skip it — several items (an accidentally-committed `.env`, `APP_DEBUG=true` in production, a missed migration) are the kind of mistake that's invisible locally and very visible once live.

## Build process

Per SRS §76, the production server is not assumed to have Node.js/NPM available. Frontend assets are built **locally**, committed or transferred as built artifacts, not built on the server:

```
Local development
  → npm run build              (produces public/build/*)
  → git commit + push
  → SSH to server
  → git pull
  → composer install --no-dev --optimize-autoloader
  → php artisan migrate --force
  → php artisan config:cache
  → php artisan route:cache
  → php artisan view:cache
  → php artisan event:cache
```

`public/build/` is gitignored in local development (see `.gitignore`) — for a git-pull-based deployment, either:
- Build locally and `git add -f public/build` for the deploy commit, or
- Build on the server as a one-time exception if Node happens to be available there, or
- Use a CI step that builds and pushes only the `public/build/` artifacts.

Confirm which approach the actual host supports before relying on it — see `CPANEL_DEPLOYMENT.md`.

## Production `.env`

Differences from local `.env` that **must** be set before going live:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.alieimran.com

DB_HOST=<production DB host>
DB_DATABASE=<production DB name>
DB_USERNAME=<production DB user — not root>
DB_PASSWORD=<strong, unique password>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=<real SMTP host>
MAIL_PORT=587
MAIL_USERNAME=<real SMTP user>
MAIL_PASSWORD=<real SMTP password>
MAIL_FROM_ADDRESS=<real from address>
```

`APP_DEBUG=false` is not optional — with it `true`, unhandled exceptions leak stack traces, file paths, and potentially database credentials to any visitor who triggers an error. This app has custom error pages (404/403/419/429/500/503) specifically so `APP_DEBUG=false` doesn't mean a blank ugly page — see `SECURITY.md`.

`SESSION_SECURE_COOKIE=true` requires the site actually being served over HTTPS — don't set it before HTTPS is live, or every session cookie gets silently dropped.

## Directory structure

Per SRS §74, only Laravel's `public/` directory should be exposed to the web server where the hosting setup allows it — `.env`, `storage/`, `vendor/`, and application source should not be web-accessible. On shared cPanel hosting this usually means the Laravel app root lives *outside* `public_html/`, with only `public/`'s contents (or a symlink structure) actually inside it. See `CPANEL_DEPLOYMENT.md` for how this specific host handles it.

The Root app owns `public_html/` itself; sibling applications (Portfolio, Tunang, Kahwin) live in their own subdirectories (`public_html/portfolio/`, etc.) and must never be touched by this app's deployment process.

## Storage & permissions

```bash
php artisan storage:link
```

`storage/` and `bootstrap/cache/` need to be writable by the web server user. On cPanel this is usually already correct after `git pull` if the repository was cloned as that user — verify rather than assume.

## Post-deploy verification

1. Homepage loads: `curl -I https://www.alieimran.com`
2. Admin login works
3. `/sitemap.xml` and `/robots.txt` return correct content
4. Security headers present: `curl -I https://www.alieimran.com` and check for `Content-Security-Policy`, `X-Frame-Options`, `Strict-Transport-Security`
5. A custom error page renders (e.g. visit a nonexistent path) — confirms `APP_DEBUG=false` didn't break error handling
6. Contact form submits and the notification email arrives
7. `php artisan queue:work` isn't needed (this app doesn't use queued jobs currently) but confirm nothing is silently queued and never processed

## Rollback

```bash
git log --oneline -5        # find the last known-good commit
git checkout <commit>       # or git reset --hard <commit> if certain
composer install --no-dev --optimize-autoloader
php artisan migrate:rollback   # only if the bad deploy included a migration, and only after confirming the rollback is safe
php artisan config:cache
php artisan view:cache
```

`migrate:rollback` is destructive if any migration in the rolled-back batch drops columns or tables with data in them — check the migration's `down()` method before running it against production data, not after.

## Deployment checklist

Adapted from SRS §83:

- [ ] `APP_DEBUG=false`, `APP_ENV=production` set
- [ ] Production database credentials in place, not the dev ones
- [ ] `.env` is not web-accessible and not committed to git
- [ ] Frontend assets built (`npm run build`) and present on the server
- [ ] `composer install --no-dev --optimize-autoloader` run
- [ ] Migrations run (`php artisan migrate --force`)
- [ ] Storage symlink created (`php artisan storage:link`)
- [ ] Super Admin exists (`php artisan admin:create`, once)
- [ ] HTTPS enabled, `SESSION_SECURE_COOKIE=true`
- [ ] Security headers present (`curl -I`)
- [ ] Custom error pages verified (not raw Laravel/PHP errors)
- [ ] Contact form tested end-to-end, including the notification email
- [ ] `/sitemap.xml` and `/robots.txt` verified
- [ ] Config/route/view caches built (`config:cache`, `route:cache`, `view:cache`)
- [ ] Backup taken before deploying (see `BACKUP.md`)
- [ ] Sibling applications (`/portfolio`, `/tunang`, `/kahwin`) verified unaffected
