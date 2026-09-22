# Alie Imran — Personal Landing Page

The root digital identity and link-hub for **alieimran.com** — a standalone Laravel 13 application. Owns personal identity, the link hub, social links, the digital business card, contact intake, and site-level SEO/analytics/theme. Deliberately does **not** own detailed professional content (projects, photography, blog, CV, Wataniah experience) — that belongs to the separate Portfolio application. See `SRS_COMPLIANCE.md` for the full ownership boundary.

## Documentation

| File | What it's for |
|---|---|
| `SRS_COMPLIANCE.md` | Requirement-by-requirement status against the governing SRS |
| `PROJECT_STATUS.md` | Current snapshot — what's live, known issues, prioritized next steps |
| `DEVELOPMENT_LOG.md` | Chronological record of what was built, in what order, and why |
| `DESIGN_SYSTEM.md` | The cybersecurity/IT visual theme — color tokens, components, conventions |
| `TECHNOLOGY_STACK.md` | Installed package versions vs. baseline, what's deliberately not installed |
| `INSTALLATION.md` | Local development setup |
| `DEPLOYMENT.md` | General production deployment process |
| `CPANEL_DEPLOYMENT.md` | cPanel/SSH-specific deployment steps for this project's actual host |
| `SECURITY.md` | Security posture, hardening applied, known tradeoffs |
| `BACKUP.md` | What to back up and how to restore it |
| `TROUBLESHOOTING.md` | Common local-dev problems and their fixes |

## Quick start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan admin:create
npm run build
```

See `INSTALLATION.md` for the full walkthrough, including MariaDB setup and Herd-specific notes.

## Stack

Laravel 13 · PHP 8.4 · MariaDB 11.4 · Blade · Tailwind CSS 4 · Vite · Pest. Full version matrix in `TECHNOLOGY_STACK.md`.

## Testing

```bash
php artisan test
./vendor/bin/pint
```

81 tests passing as of the last update to this file. Keep both green before committing.

## License

Personal project. Not open-sourced.
