# All-in-one prompt: deploy a new site to alieimran.com (JimatHosting cPanel)

Copy everything below the line into a new AI chat. Fill in the **NEW SITE DETAILS** block first.

---

I need your help deploying a new website to my JimatHosting cPanel account. My main site (alieimran.com) is already deployed on this account. Below is everything we learned from that first deployment. Read all of it before you suggest any commands.

## NEW SITE DETAILS (I fill these in)

- Site name / folder name: `<e.g. kahwin>`
- Public URL: `https://alieimran.com/<e.g. kahwin>`
- Local project folder: `<e.g. D:\Herd\kahwin>`
- GitHub repo (private): `<e.g. https://github.com/alieimran/kahwin>`
- Framework: `<Laravel / plain HTML / other>`
- Needs a database: `<yes / no>`
- Sends email (contact form, RSVP, etc.): `<yes / no>`
- Uses the Laravel scheduler/cron: `<yes / no>`

## How to work with me

- You cannot SSH into the server yourself. I run commands in the cPanel **Terminal** and paste the output back to you. Give me commands in small batches and wait for my output before moving on.
- You can run commands on my local Windows PC (build assets, zip files, `curl` the live site to check it).
- My local PC's SSH/scp password login to the server **fails**. Don't use `scp`. Move files through **cPanel File Manager** (upload a zip, then Extract).
- Before you overwrite, move or delete anything on the server, show me what's there first (`ls -la`) and back it up (`mv x x.bak`).
- **Never touch** anything outside the new site's own folders. That includes `public_html/` root files, `public_html/tunang/`, `public_html/cgi-bin/`, `public_html/home/`, `~/alieimran-landingpage/`, `~/tunang-app/`, or any other existing app.
- Explain things simply. I can read Malay and English.

## Server facts (confirmed)

| Item | Value |
|---|---|
| Host | JimatHosting cPanel, server `nova`, **LiteSpeed** web server (honours `.htaccess`) |
| cPanel username | `alieimra` |
| Home directory | `/home2/alieimra` (it's **home2**, not `home`) |
| SSH | port 222, `alieimra@alieimran.com`, but I use cPanel Terminal instead |
| PHP (web and CLI) | 8.4.12. **8.4 is the newest version on this host, there's no 8.5.** The app must not need PHP 8.5+ |
| PHP extensions | Everything Laravel needs is installed (bcmath, ctype, curl, dom, fileinfo, mbstring, openssl, pdo_mysql, tokenizer, xml, zip, intl, gd, redis, etc.) |
| Database | MariaDB 11.4.8 |
| Composer | Not preinstalled. I installed it myself at `~/bin/composer` (2.10.3), and `~/bin` is already on PATH |
| Git | 2.48.2, works over SSH |
| Node / npm | **npm is not available on the server.** Build frontend assets on my PC |
| SSL | AutoSSL is active. HTTPS works for `alieimran.com` and `www.alieimran.com` |
| Main domain document root | Fixed at `/public_html`. **cPanel won't let me change it** (the Domains → Manage page only offers Rename) |

## Current layout on the server

```
/home2/alieimra/
├── bin/composer
├── .ssh/
│   ├── id_ed25519_github(.pub)   ← deploy key, registered ONLY on repo alieimran/alieimran-landingpage
│   └── config                    ← "Host github.com" uses id_ed25519_github
├── alieimran-landingpage/        ← main Laravel app code (git clone, vendor/, .env, storage/)
├── tunang-app/                   ← tunang app code
└── public_html/                  ← the web root for alieimran.com
    ├── index.php                 ← main app's front controller, paths edited to ../alieimran-landingpage/...
    ├── .htaccess, favicon.ico, robots.txt   ← copies of the app's public/ files
    ├── build   -> /home2/alieimra/alieimran-landingpage/public/build        (symlink)
    ├── storage -> /home2/alieimra/alieimran-landingpage/storage/app/public  (symlink)
    ├── index.html.bak            ← old placeholder page, kept as a backup
    ├── cgi-bin/, home/           ← unknown or system folders, don't touch
    └── tunang/                   ← tunang's public files → https://alieimran.com/tunang/ (works)
```

**Why subfolder sites work:** the main app's `.htaccess` only sends a request to Laravel when the path is *not* a real file or folder. So `alieimran.com/tunang/...` goes straight into the real `public_html/tunang/` folder and never reaches the main app. A new site follows the same pattern:

- **App code** goes in `~/<name>-app/`, outside `public_html` (keeps `.env`, `vendor/` and source private)
- **Public files** go in `~/public_html/<name>/`

**The `build` folder must be a symlink, never a copy.** Laravel reads `manifest.json` (the list of CSS/JS file names) from the *app's* `public/build/`, but the browser downloads the files from the *web* folder. If those are two separate copies, they drift apart after an update: the page asks for a new CSS file that only exists in one of them, gets a 404, and shows up unstyled. This actually happened on the main site. A symlink keeps one copy, and LiteSpeed on this host follows it (confirmed).

Before you start, look at how tunang is set up and use it as the template: `ls -la ~/tunang-app ~/public_html/tunang && cat ~/public_html/tunang/index.php`.

## Deployment steps for a Laravel site (adapt them for other stacks)

### 1. Local checks (my PC)
- `composer.json` must require PHP `^8.4` or lower, not 8.5.
- `.env` must be gitignored and not tracked: `git ls-files .env` should print nothing.
- If the repo has no remote yet: `git remote add origin <repo>` then `git push -u origin main`.
- Run `npm run build`, then zip `public/build` into `build.zip` (`Compress-Archive -Path "public\build" -DestinationPath "public\build.zip" -Force`). cPanel shows a "backslashes" warning when extracting it, which is harmless.
- Tailwind only puts classes it finds in your files into the CSS when you build. **Any change to classes in a Blade view needs a new build**, even if you never touched a CSS file. If the CSS file name hash changes (e.g. `app-CzOrgC4l.css` → `app-CsEIoapR.css`), the build must be uploaded.

### 2. GitHub deploy key (one new key per repo)
GitHub doesn't let you reuse a deploy key on a second repo, and `Host github.com` in `~/.ssh/config` is already taken by the landing page key. So make a new key and use a **host alias**:

```bash
ssh-keygen -t ed25519 -C "alieimra@nova <name> deploy key" -f ~/.ssh/id_ed25519_<name> -N ""
cat ~/.ssh/id_ed25519_<name>.pub
```
I then add it on GitHub under repo → Settings → Deploy keys → Add, read-only.
```bash
cat >> ~/.ssh/config <<'EOF'
Host github-<name>
    HostName github.com
    User git
    IdentityFile ~/.ssh/id_ed25519_<name>
    IdentitiesOnly yes
EOF
ssh -T git@github-<name>          # expect: "Hi alieimran/<repo>! You've successfully authenticated"
cd ~ && git clone git@github-<name>:alieimran/<repo>.git <name>-app
cd ~/<name>-app && composer install --no-dev --optimize-autoloader
```

### 3. Database (cPanel → MySQL® Databases)
- Create the database. cPanel adds the prefix, so it becomes `alieimra_<name>db`.
- Either reuse user `alieimra_dbuser` or create a new user.
- **Add User To Database → tick ALL PRIVILEGES → Make Changes.** Being listed under "Privileged Users" isn't proof the privileges were actually granted.

### 4. `.env` (create it on the server; git never brings it over)
Create it with `nano ~/<name>-app/.env`. Save with Ctrl+O then Enter, and exit with Ctrl+X. You can also use File Manager → + File, named `.env`.

```env
APP_NAME="<Name>"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://alieimran.com/<name>
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alieimra_<name>db
DB_USERNAME=alieimra_dbuser
DB_PASSWORD="<password>"

SESSION_DRIVER=database
SESSION_COOKIE=<name>_session
SESSION_PATH=/<name>
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=mail.alieimran.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=<address@alieimran.com>
MAIL_PASSWORD="<password>"
MAIL_FROM_ADDRESS="<address@alieimran.com>"
MAIL_FROM_NAME="${APP_NAME}"
```
- `DB_HOST=127.0.0.1` is confirmed working on this host.
- **The session cookie name has to be unique.** The main app uses `alie-imran-session` on path `/`, and tunang uses `engagement-invitation-session` on path `/tunang`. If the names clash, the apps log each other out.
- For email, first create the mailbox in cPanel → Email Accounts.

Then run:
```bash
cd ~/<name>-app
php artisan key:generate --force
php artisan config:clear          # ALWAYS do this before migrate. A stale config cache caused "Access denied" last time
php artisan migrate --force
php artisan db:seed --force       # only if the app has seeders meant for production
# plus any app-specific setup command (the landing page used: php artisan admin:create)
```

### 5. Publish the public files into the subfolder
```bash
mkdir -p ~/public_html/<name>
cp -a ~/<name>-app/public/. ~/public_html/<name>/
sed -i \
  -e "s|__DIR__.'/../storage/|__DIR__.'/../../<name>-app/storage/|" \
  -e "s|__DIR__.'/../vendor/|__DIR__.'/../../<name>-app/vendor/|" \
  -e "s|__DIR__.'/../bootstrap/|__DIR__.'/../../<name>-app/bootstrap/|" \
  ~/public_html/<name>/index.php
cat ~/public_html/<name>/index.php          # check all 3 paths now point to ../../<name>-app/
rm -f ~/public_html/<name>/storage
ln -s ~/<name>-app/storage/app/public ~/public_html/<name>/storage
rm -rf ~/public_html/<name>/build
ln -s ~/<name>-app/public/build ~/public_html/<name>/build
ls -la ~/public_html/<name>/          # storage and build should both show "->" arrows
```
- The path is `../../` because the subfolder sits one level deeper than the main app's `public_html/index.php`, which uses `../alieimran-landingpage/`.
- Upload `build.zip` through File Manager into **`~/<name>-app/public/`** (the app folder, *not* `public_html`), Extract it, then delete the zip. The symlink makes it live.
- Copy into `public_html/<name>/` only. **Never copy into `public_html/` itself**, because that would overwrite the main site's `index.php` and `.htaccess`.

### 6. Caches
```bash
cd ~/<name>-app
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
```

### 7. Cron (only if the app uses the scheduler)
Add this in cPanel → Cron Jobs:
```
* * * * * cd /home2/alieimra/<name>-app && php artisan schedule:run >> /dev/null 2>&1
```

### 8. Verify from my PC
```bash
curl -sI https://alieimran.com/<name>/        # expect 200
curl -sI https://alieimran.com/                # main site still 200
curl -sI https://alieimran.com/tunang/         # tunang still 200
curl -sI https://alieimran.com/<name>/.env     # must NOT return the file
curl -sI https://alieimran.com/<name>/does-not-exist   # expect 404 with the app's own error page
# CSS the page asks for must actually load (200), not 404:
curl -s https://alieimran.com/<name>/ | grep -o 'build/assets/[^"]*\.css'
curl -sI https://alieimran.com/<name>/build/assets/<that-file>.css
```
Then I test in a browser: the pages, CSS/JS loading, login, and any forms or emails.

## Plain HTML (non-Laravel) site
Zip the site locally, upload it to `public_html/<name>/` in File Manager, and Extract. No database, Composer or `.env` needed. Then run the step 8 checks.

## Updating a site later (only the changes, never the whole project)

On my PC: commit and `git push`. Git sends only the changes. Then on the server:

| What changed | What to run / upload |
|---|---|
| Blade views or PHP code | `cd ~/<name>-app && git pull && php artisan view:cache` (add `config:cache` + `route:cache` if config or routes changed) |
| CSS classes, JS or Tailwind (including new classes used in a Blade view) | `npm run build` on my PC, zip `public/build`, upload and Extract into **`~/<name>-app/public/`** |
| `composer.json` / `composer.lock` | `composer install --no-dev --optimize-autoloader` |
| A new migration | `php artisan migrate --force` |
| A file in `public/` (e.g. `.htaccess`, `favicon.ico`) | Copy that single file into `public_html/<name>/` by hand |

- **Don't re-run the full `cp -a public/. ...` on an update.** It overwrites the edited `index.php` and replaces the `build` symlink with a copy. If you have to re-copy, redo the `sed` edit and both symlinks from step 5.
- Do the build upload **before** the `git pull`, then run the step 8 CSS check.
- The main site updates the same way. Its app lives in `~/alieimran-landingpage`, it's served from `public_html/` (not a subfolder), and its `index.php` paths use `../alieimran-landingpage/`.

## Mistakes from the first deploy (don't repeat them)
1. A stray `<` pasted from a `<placeholder>` made bash try to read a file. Give commands with the real values filled in.
2. `echo '...' .. ~/.bashrc` used `..` instead of `>>`. Double-check redirections.
3. "Access denied for user" on migrate: check ALL PRIVILEGES in cPanel, run `php artisan config:clear`, and confirm `.env` values with `grep DB_ .env`.
4. The main domain's Document Root can't be changed in cPanel. Work with `public_html` using the steps above.
5. `scp` from my PC failed on password. Use File Manager.
6. When I ran `php artisan admin:create`, I typed my email into the Name field. Check prompts carefully.
7. `public_html/build` was first set up as a *copy*. The first CSS update then broke the login page's styling (404 on the new CSS file). The fix was replacing it with a symlink to the app's `public/build`. Set up the symlink from day one.
