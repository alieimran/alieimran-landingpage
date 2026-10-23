# Deployment

General production deployment process. For the actual host's specific steps (cPanel, SSH), see `CPANEL_DEPLOYMENT.md`. This file covers what's true regardless of host.

## Before every deployment

Run through the checklist at the bottom of this file. Don't skip it — several items (an accidentally-committed `.env`, `APP_DEBUG=true` in production, a missed migration) are the kind of mistake that's invisible locally and very visible once live.

## Build process

Per SRS §76, the production server is not assumed to have Node.js/NPM available. Frontend assets are built **locally**, committed or transferred as built artifacts, not built on the server:

```
On the PC
  → npm run build              (produces public/build/*)
  → git add -A && git commit   (public/build IS committed)
  → git push
In cPanel Terminal
  → ~/deploy-landingpage.sh    (symlink to deploy.sh in this repo)
```

`public/build/` is committed to git because the server has no npm. `public_html/build` on the server is a symlink to the app's `public/build`, so the pulled assets go live immediately. **Always run `npm run build` before committing** any change to Blade views, CSS or JS. Tailwind only includes classes it finds at build time, so a new class in a view needs a new build.

[deploy.sh](deploy.sh) does the server side:
- stops if tracked files were edited on the server
- `git pull --ff-only`
- `composer install` (only when `composer.lock` changed)
- `config:clear`, then `migrate --force`
- rebuilds the config, route, view and event caches
- warns if files copied into `public_html` (`.htaccess`, favicons, `robots.txt`) now differ from `public/`, or if the `build`/`storage` symlinks are missing. It never copies them itself: `public_html/index.php` has edited paths and must not be overwritten.

One-time setup on the server:

```bash
chmod +x ~/alieimran-landingpage/deploy.sh
ln -s ~/alieimran-landingpage/deploy.sh ~/deploy-landingpage.sh
```

The first run moves the old hand-uploaded `public/build` to `~/build-manual-backup-<timestamp>`, because git won't pull over untracked files. Delete that folder once the site looks right.

## Production `.env`

### Cloudflare Turnstile on admin login only

This Laravel app uses the existing session login at /login. Turnstile runs before
credential checks, preserving CSRF, password authentication and login rate limiting.
Contact submissions and password reset routes do not use Turnstile.

The deployment APP_URL is https://www.alieimran.com. Verified on 2026-10-24:
the live www login form posts to https://www.alieimran.com/login. The bare domain
also serves login without redirecting, so use the www login URL when enabled.
Siteverify must return the exact hostname www.alieimran.com and action login;
the widget's broader allowed-hostname setting does not relax server validation.

Use the existing **Managed** widget allowing alieimran.com (including www).
Do not modify domain-wide Cloudflare settings or the separate portfolio app.
No DNS move is needed for Turnstile. Defaults in .env.example are:

```env
TURNSTILE_ENABLED=false
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
TURNSTILE_HOSTNAME=www.alieimran.com
```

After the reviewed changes have been committed and pushed, deploy from cPanel Terminal:

```bash
cd /home2/alieimra/alieimran-landingpage
bash ./deploy.sh
nano .env
```

Enter the site key and rotated secret privately in the production .env, then set
TURNSTILE_ENABLED=true and TURNSTILE_HOSTNAME=www.alieimran.com. Keep APP_DEBUG=false
and APP_URL=https://www.alieimran.com. Never commit .env or real keys, or paste secrets
into command-line arguments. Apply the configuration and verify the page:

```bash
php artisan config:cache
curl -I https://www.alieimran.com/login
```

In a browser, confirm the widget loads and a real admin login succeeds at
https://www.alieimran.com/login. A missing/invalid token, wrong hostname/action,
missing configuration or Cloudflare connection failure must block login. Inspect
CSP for challenges.cloudflare.com in frame-src/connect-src; the loader uses the
existing script nonce. Tokens and passwords are excluded from flashed input, and
verification does not log tokens or secrets. No application database migration is
introduced by this feature; deploy.sh retains the normal migration/cache process.

Emergency recovery, in cPanel Terminal:

```bash
cd /home2/alieimra/alieimran-landingpage
nano .env
# Set TURNSTILE_ENABLED=false, save and exit.
php artisan config:cache
```

Existing password authentication and rate limiting remain active when disabled.

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

## Admin dashboard and visitor activity

- `JAKIM_PRAYER_ZONE=WLY01` defaults to Kuala Lumpur/Putrajaya. Set another official
  JAKIM zone in this application's production `.env`, then `php artisan config:cache`.
  Prayer data comes directly from JAKIM e-Solat, uses Asia/Kuala_Lumpur dates and is
  cached for 15 minutes. A provider outage displays unavailable, not invented times.
- `npm run build` records the asset-build Node/npm/Vite versions in
  `resources/build-info.json`. Include this file and `public/build` in a future
  deployment. It does not report Node installed on the cPanel server.
- The admin dashboard detects web PHP, connected MariaDB/MySQL, Laravel, OpenSSL
  and server software. Versions not exposed by the host (including Composer CLI)
  remain unknown. PHP extensions and hosting patches are outside the lifecycle scan.
- Maintained-branch patch/support checks use endoflife.date, cached six hours.
  Green means exact latest patch on a maintained branch. It is not a CVE-free claim.
  Vendor security links cover runtime/database advisories; OSV checks production
  Composer packages and locked npm build dependencies. Missing/incomplete responses
  cannot yield an all-clear. Requests send only product/package versions to these
  providers. This is not a replacement for hosting security monitoring.
- Logout buttons submit the existing CSRF-protected POST form directly, avoiding
  inline onclick handlers blocked by CSP. Successful logout redirects home.

### Visitor IP/location rollout

The new migration adds nullable visitor fields; historical IPs cannot be recovered.
After these changes are reviewed, committed and pushed, deploy normally:

```bash
cd /home2/alieimra/alieimran-landingpage
bash ./deploy.sh
php artisan analytics:locate
php artisan analytics:prune-visitor-details
php artisan schedule:list
```

Ensure the existing cPanel scheduler cron runs every minute (do not duplicate it):

```cron
* * * * * cd /home2/alieimra/alieimran-landingpage && php artisan schedule:run >> /dev/null 2>&1
```

`analytics:locate` runs hourly, at most 20 public IPs per run, using HTTPS ipwho.is
lookups. Failed lookups retry after a day; rate limits stop the batch. Public pages
never wait for geolocation. Locations are approximate and can represent VPN/mobile
exit points, not the visitor's exact physical location. The admin table paginates
25 visits and only shows the last rolling 30 days. IPs/locations older than 30 days
are cleared daily; existing aggregate analytics keep their normal retention.
Update any public analytics/privacy disclosure to describe this collection before
production activation. Backup retention is separate from live-table pruning.

Leave `ANALYTICS_TRUSTED_PROXIES=` empty with direct LiteSpeed hosting. If this app
is later placed behind a reverse proxy, configure only its confirmed IPs/CIDRs and
run `php artisan config:cache`. Do not trust arbitrary X-Forwarded-For or
CF-Connecting-IP values. No domain-wide or sibling-app changes are needed.

## CMS maintenance mode

Open **Admin > Manage > Maintenance** (`/admin/maintenance`). Enter the public
reason and a future expected return time in Malaysia time (UTC+08:00), enable the
checkbox, and save. Changes take effect immediately; no Artisan down/up command
is needed. Preview opens the saved public design in a new tab. The page follows
the site's light/dark theme, accent color, logo, typography and grid background.

Public routes (including contact submissions and redirects) return HTTP 503 with
Retry-After and no-store headers while enabled. Admins can continue viewing the
site, editing content and managing maintenance. Login/account recovery stay
available. The admin layout displays a maintenance banner. The expected return
time is an estimate and does not automatically disable maintenance; if it passes,
visitors see an in-progress update instead of a stale promised return time.
Uncheck the switch and save to reopen the website. Maintenance is off by default.

Deploy through the normal deployment script, which applies the new additive
`site_settings` migration before clearing/rebuilding application caches. This CMS
mode relies on Laravel and the database being available; it does not replace the
host's outage handling or Laravel's CLI maintenance mode for database downtime.
No sibling application or domain-wide setting is changed.

### Maintenance page editor, suggestions and animation

The Maintenance CMS includes reason suggestions and 15-minute, 30-minute, 1-hour,
2-hour, 4-hour and 24-hour duration presets. Presets calculate the return time from
when settings are saved; custom dates use Malaysia time. Editing the date manually
switches back to a custom estimate. Reason suggestions can be edited before saving.

Admins can customize the status label, heading, reason, button text, footer and
message shown after an estimate expires. Visibility switches control the logo,
start time, return time, theme toggle, animated loader and countdown. Text is
escaped, with no arbitrary HTML/scripts. Countdown is shown only when the return
time is visible and in the future. It uses the server's timestamp plus elapsed
browser time to avoid incorrect visitor clocks. Expiry displays the saved delayed
message and never automatically reopens or reloads the site. The spinner respects
reduced-motion preferences. Both the public page and editor support phone, tablet
and desktop layouts; compact admin navigation is used below 1024px.

The additional additive migration stores page customization as JSON. Local
migrations have been applied. Run the normal deployment script when deploying so
both maintenance migrations are applied, and ship the rebuilt CSS/JS assets.
