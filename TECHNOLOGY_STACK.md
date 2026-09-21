# Technology Stack

Baseline defined by the SRS (§64–67), checked against what's actually installed in this project. Last verified: 2026-09-22.

## Backend

| Component | SRS Baseline | Installed | Notes |
|---|---|---|---|
| PHP | 8.4.25 | 8.4.25 | via Laravel Herd |
| Laravel | 13.31.0 | 13.32.0 | patch ahead of baseline, no breaking changes |
| Composer | 2.10.3 | 2.10.3 | |
| MariaDB | 11.4.13 | 11.4.13 | local dev via Herd's bundled MariaDB |
| Database driver | — | `mysql` (Laravel's MariaDB-compatible driver) | `utf8mb4` / `utf8mb4_unicode_ci` |

## Frontend

| Component | SRS Baseline | Installed | Notes |
|---|---|---|---|
| Node.js | 24.21.0 | 24.21.0 | |
| NPM | 11.19.0 | 11.19.0 | |
| Vite | 8.x | 7.3.6 (`vite` ^7.0.7) | Breeze 2.4.2 still pins Vite 7; not yet on 8.x |
| Tailwind CSS | 4.x | 4.x (`tailwindcss` ^4.0.0, `@tailwindcss/vite` ^4.0.0) | migrated from a stray v3/v4 mix — see Development Log, 2026-09-22 |
| Blade | — | Laravel default | primary templating |
| Alpine.js | installed | installed (via Breeze) | not yet used for any custom interaction |
| Livewire | 4.4.4 | **not installed** | deliberately deferred — no feature yet needs server-driven interactivity (SRS §67: "use only where useful") |

## Auth & Admin

| Component | Status | Notes |
|---|---|---|
| Laravel Breeze | 2.4.2 installed | Blade stack, dark-mode-capable scaffolding |
| Spatie Permission | **not installed** | V1 has a single Super Admin (`users.is_admin` boolean); SRS §49 explicitly says this package is not required until multi-role is a real requirement |
| Spatie Activitylog | **not installed** | deferred until admin audit logging (SRS §50) is actually built |

## Media & Files

| Component | Status | Notes |
|---|---|---|
| Intervention Image | **not installed** | no image processing need yet — uploads are stored as-is after MIME/extension validation |
| Endroid QR Code | **not installed** | deferred until the Digital Business Card (SRS §30–31) is built |
| GD / WebP / AVIF | available in PHP build | not yet exercised by app code |

## Testing

| Component | SRS Baseline | Installed |
|---|---|---|
| Pest | 4.7.8 | 4.7.8 (`pestphp/pest` ^4.7) |
| Pest Plugin Laravel | — | 4.1 (`pestphp/pest-plugin-laravel` ^4.1) |
| PHPUnit | 12.5.33 | ^12.5.12 (via Pest) |

40 tests passing as of 2026-09-22 (`php artisan test`).

## Tooling

- **Laravel Pint** — code style, run via `./vendor/bin/pint`. Clean on every commit so far.
- **Laravel Herd** — local dev environment (nginx + PHP-FPM + MariaDB), serving `alieimran-landingpage.test` via a `D:\Herd` parked path plus a manual Windows hosts-file entry (Herd's own DNS proxy didn't pick up the new folder automatically — see Development Log).

## Deliberately not installed

Per SRS §67 ("not every installed package must be used"), the following remain uninstalled until a concrete feature needs them:

- **Livewire** — no page yet needs server-driven reactivity beyond what Blade + plain forms handle.
- **Spatie Permission** — single Super Admin model doesn't need role/permission management.
- **Spatie Activitylog** — no admin audit trail built yet.
- **Intervention Image** — no resizing/cropping/format-conversion requirement yet.
- **Endroid QR Code** — Digital Business Card not built yet.

When any of these features gets built, install the corresponding package at that point — don't pre-install speculatively.
