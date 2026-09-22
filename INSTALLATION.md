# Installation (Local Development)

Written for Laravel Herd on Windows, since that's this project's actual dev environment — adjust paths/commands if you're on a different stack (Valet, Docker, WSL).

## Prerequisites

| Tool | Version used | Notes |
|---|---|---|
| PHP | 8.4.25 | via Herd |
| Composer | 2.10.3 | |
| Node.js | 24.21.0 | |
| NPM | 11.19.0 | |
| MariaDB | 11.4.13 | via Herd's bundled MariaDB, or standalone |
| Git | 2.55.0 | |

See `TECHNOLOGY_STACK.md` for the full version matrix and what's deliberately not installed.

## 1. Get the code and install dependencies

```bash
composer install
npm install
```

## 2. Environment file

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set the database credentials (see below). `APP_URL` should already be `http://alieimran-landingpage.test` — adjust if you're using a different local domain.

## 3. Database

Create the database and point `.env` at it:

```sql
CREATE DATABASE alieimran_landingpage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alieimran_landingpage
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

Then run migrations and the default seeders (link categories, homepage section definitions):

```bash
php artisan migrate
php artisan db:seed
```

## 4. Create the Super Admin

V1 supports exactly one Super Admin. This is interactive and asks for name/email/password — it refuses to run if an admin already exists:

```bash
php artisan admin:create
```

## 5. Build frontend assets

```bash
npm run build
```

For active frontend development, `npm run dev` instead (Vite HMR).

## 6. Local domain (Herd-specific)

If Herd doesn't automatically pick up the project folder (it's parked under a shared path but sometimes doesn't detect a new subfolder — see `TROUBLESHOOTING.md`), add a hosts file entry manually:

**Windows** (`C:\Windows\System32\drivers\etc\hosts`, requires Administrator):
```
127.0.0.1 alieimran-landingpage.test
```

## 7. Verify

```bash
php artisan test
```

Should show all tests passing (81 as of the last update to this file). Then visit `http://alieimran-landingpage.test` — you should see the homepage, and `http://alieimran-landingpage.test/admin` should redirect to login.

## What gets seeded vs. what needs real content

The seeders create structural defaults (link categories, homepage section definitions) — no personal content. Profile info, links, social links, and the digital card are empty until set via the admin panel at `/admin`. See `PROJECT_STATUS.md`'s "Seed data" section for what's already been populated on the reference install this documentation set describes.
