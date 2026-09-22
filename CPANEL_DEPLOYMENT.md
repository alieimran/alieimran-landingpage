# cPanel Deployment

Host-specific steps for this project's actual production environment, per the SRS (§72): **JimatHosting cPanel**, SSH on **port 222**, PHP 8.4.x, MariaDB 11.4.x.

This file assumes `DEPLOYMENT.md`'s general process; it covers only what's specific to cPanel/this host. **Not yet exercised against the real host** — write this up before the first real deploy, then correct anything that turns out wrong in practice, since cPanel configurations vary meaningfully between providers.

## SSH access

```bash
ssh -p 222 <cpanel-username>@<host>
```

Confirm the actual hostname/username with the hosting provider's welcome email or cPanel's "SSH Access" panel before the first deploy — placeholders only, not guessed.

## PHP version

cPanel hosts typically offer multiple PHP versions via "MultiPHP Manager" or `.htaccess`/`.cpanel.yml`. Confirm PHP 8.4.x is selected for this domain specifically — a mismatch here is a common and confusing source of "works locally, breaks in production" bugs (e.g. this app uses PHP 8.4 syntax in places).

```bash
php -v   # after SSH, confirm the CLI PHP version matches what the web server uses — cPanel sometimes differs between the two
```

## Directory layout

Per the SRS's ecosystem architecture (§73, §89), this Root app owns `public_html/` directly; sibling apps live in their own subdirectories:

```
public_html/
├── (this app's public/ contents, or a symlink to them)
├── portfolio/       ← separate app, never touched by this deploy
├── tunang/          ← separate app, never touched by this deploy
└── kahwin/          ← separate app, never touched by this deploy
```

cPanel's typical pattern for keeping Laravel's non-public files out of the web root:

1. Clone/upload the full application to a directory **outside** `public_html/`, e.g. `~/alieimran-landingpage/`.
2. Either symlink `public_html/`'s relevant contents to `~/alieimran-landingpage/public/`, or (if the app must live inside `public_html/` due to hosting constraints) use cPanel's "Document Root" setting for the domain to point directly at the app's `public/` folder instead, leaving the rest of the app one level up.
3. Whichever approach: verify `~/alieimran-landingpage/.env` (or wherever it ends up) is **not** reachable via a browser (`https://www.alieimran.com/.env` must 404, not display).

## Database setup (cPanel)

Create the database and user via cPanel's "MySQL® Databases" tool (works the same for MariaDB):

1. Create database (cPanel usually prefixes it, e.g. `username_alieimran`)
2. Create a dedicated database user — **not** a shared/root account
3. Add that user to the database with all privileges
4. Update `.env` with the prefixed names cPanel actually generated, not the local dev names

## Deploying via git

If the host supports cPanel's "Git Version Control" feature:

1. Point it at this repository, deployment branch `main` (or whatever's designated as production-ready)
2. Set the deployment path to the app root chosen above (outside `public_html/`, per the layout note)
3. After each pull, cPanel's git tool can run a deploy script — use it to run `composer install --no-dev --optimize-autoloader`, migrations, and cache commands automatically (see `DEPLOYMENT.md` for the exact command sequence)

If git isn't available server-side, deploy via SSH manually following `DEPLOYMENT.md`'s build process (build assets locally, push, pull on the server).

## Cron (for scheduled tasks)

This app has one scheduled task: `analytics:prune`, monthly (see `routes/console.php`). Laravel's scheduler needs a single cron entry that fires every minute; cPanel's "Cron Jobs" tool:

```
* * * * * cd /home/<cpanel-username>/alieimran-landingpage && php artisan schedule:run >> /dev/null 2>&1
```

Adjust the path to match wherever the app actually lives on this host.

## SSL/HTTPS

cPanel hosts typically offer free AutoSSL (Let's Encrypt) via "SSL/TLS Status." Confirm it's active for `www.alieimran.com` before setting `SESSION_SECURE_COOKIE=true` in `.env` — enabling that setting before HTTPS is actually serving the site will silently break all sessions (cookies get dropped by browsers when marked Secure over plain HTTP).

## Verification specific to this host

After the first real deployment, confirm and record here (update this file once actually done):

- [ ] Confirmed PHP 8.4.x is what's actually serving requests (not just what's in `php -v` for the SSH session)
- [ ] Confirmed MariaDB version via `SELECT VERSION();`
- [ ] Confirmed `.env` is not web-accessible
- [ ] Confirmed `/portfolio`, `/tunang`, `/kahwin` still resolve correctly and weren't affected by this app's deployment
- [ ] Confirmed the cron entry is actually firing (check `analytics:prune`'s effect after a month, or trigger `php artisan schedule:run` manually once to confirm no errors)
