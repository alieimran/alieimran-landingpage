# Project Status

**Snapshot date:** 2026-09-22
**Environment:** local development (Laravel Herd, Windows 11)
**Live local URL:** `http://alieimran-landingpage.test`

For the detailed requirement-by-requirement breakdown, see **SRS_COMPLIANCE.md**. For what was built in what order, see **DEVELOPMENT_LOG.md**. For version numbers, see **TECHNOLOGY_STACK.md**. For the visual theme, see **DESIGN_SYSTEM.md**.

## What's live right now

- **Public homepage** (`/`) — profile/hero, featured links, link hub, social links, contact CTA (links to the real contact form). Cybersecurity/IT dark theme, mobile-first, verified at 390px and 1440px.
- **Manual dark/light toggle** — every page (public and admin) has a sun/moon button that overrides the OS preference and is remembered across visits (`localStorage`). No flash-of-wrong-theme on load.
- **Public contact form** (`/contact`) — name/email/phone/category/subject/message, CSRF, server-side validation, `throttle:5,1` rate limiting, honeypot spam trap, email notification to the admin (mail-failure-safe — the inquiry is always saved even if the notification email fails to send).
- **Admin panel** (`/admin`, single Super Admin only):
  - Dashboard with quick counts (Links, Social Links, New Inquiries — each links to its respective screen)
  - Full CRUD: Links, Social Links, Link Categories, Site Settings (singleton profile editor)
  - Contact inbox: list with status filter, detail view (auto-marks read), status updates, delete — with an unread-count badge in the nav
  - "View Site" shortcut in the nav to preview the public page in a new tab
  - Analytics dashboard (`/admin/analytics`): views over the last 14 days, top pages, top referrers, browser/device breakdown, outbound link/social click counts, recent-visits feed — all first-party, no third-party tracker
- **Analytics tracking:** page views (path, referrer, device/browser/OS, bot traffic excluded) and outbound clicks (`/go/link/{link}`, `/go/social/{socialLink}` redirect-and-log routes) recorded automatically. Privacy-conscious by design: no raw IP address is ever stored, only a one-way hash of IP+user-agent+date that rotates daily (so no visitor can be tracked across days); `analytics:prune` command (monthly via the scheduler once server cron is set up) deletes records older than 90 days by default.
- **SEO:** canonical URLs, Open Graph + Twitter Card meta (falls back to profile data when no `seo_metadata` override is set), dynamic `/sitemap.xml` (indexable pages only), static `/robots.txt` (disallows admin/auth routes), custom SVG/PNG/ICO favicon.
- **Auth:** Breeze-based login/logout/password-reset. Public registration is fully removed (route, controller, view, and its test all deleted).
- **Database:** MariaDB (`alieimran_landingpage`), 12 migrations applied, seeded with default link categories, default site sections, and real-ish profile content (see "Seed data" below).
- **Tests:** 65 Pest tests passing (`php artisan test`), Pint clean.

## Admin access

- URL: `/admin` (redirects through `/login` if not authenticated)
- Account: `alieimran@outlook.com` — password was set interactively, not stored anywhere in this repo or its docs. If it's lost, run `php artisan admin:create` again — it refuses to run while a Super Admin already exists, so an existing account would need to be cleared first (not something to script casually — ask before doing that).

## Seed data — what's real vs fabricated

Per an explicit instruction to seed with some fabrication where real data isn't available:

**Sourced directly from the SRS document or the conversation (real):**
- Name, job title ("System Support Analyst"), tagline ("Windows Infrastructure | Technology | Cloud | Software") — the SRS's own example profile content (§10)
- Portfolio link URL (`https://www.alieimran.com/portfolio`) — the SRS's own stated default (§12)
- Admin contact email (`alieimran@outlook.com`) — matches the account used to log into the admin panel

**Inferred from strong contextual clues (reasonable, not verified):**
- Location: "Malaysia" — inferred from "Tunang"/"Kahwin" (Malay wedding-event terminology) and "Askar Wataniah" (Malaysia's territorial army reserve) appearing throughout the SRS

**Fabricated placeholders (need to be replaced with the real thing):**
- Biography paragraph — plausible professional summary, not written by the user
- LinkedIn URL: `https://linkedin.com/in/alieimran`
- GitHub URL: `https://github.com/alieimran`

**Action needed:** verify/correct the fabricated items above via `/admin/site-settings` (biography) and `/admin/social-links` (LinkedIn/GitHub) — or hand over the real values and they'll get updated directly.

## Known issues / environment quirks

- **Vite version:** SRS baseline says Vite 8.x; Breeze 2.4.2's scaffolding still pins `^7.0.7`. Not blocking, just a version note (see TECHNOLOGY_STACK.md).
- **Hosts resolution:** `alieimran-landingpage.test` needed a manual entry in `C:\Windows\System32\drivers\etc\hosts` — Herd's own site-detection didn't pick up the new folder under `D:\Herd` automatically the way it does for the sibling `alieimran-portfolio.test` and `jemputjemput.test` sites. Already fixed; noting it in case a future new site/subdomain hits the same thing.
- **Theme colors/fonts/logo not yet admin-editable:** the `theme_settings` table exists but nothing reads or writes it. The current dark cybersecurity theme is implemented directly in `resources/css/app.css` and Blade components, not driven by the database. (Dark/light *mode itself* is user-toggleable now — see below — this note is specifically about admin-customizable brand colors/fonts, a separate SRS §47 sub-requirement.) If that becomes a priority, it's a distinct piece of work — wiring the DB values into the CSS custom properties at render time.

## Prioritized next steps

Roughly in the order they'd unblock the most SRS Definition-of-Done items (§84):

1. ~~**Public Contact form**~~ — done (2026-09-22): form, validation, rate limiting, honeypot, email notification, admin inbox.
2. ~~**SEO completeness**~~ — done (2026-09-22): canonical URL, Twitter card meta, favicon, `/sitemap.xml`, `/robots.txt`.
3. ~~**Manual dark/light toggle**~~ — done (2026-09-22), system-wide.
4. ~~**Analytics dashboard + visitor insights**~~ — done (2026-09-22): page views, referrers, device/browser breakdown, outbound link-click tracking, admin dashboard with charts. Country-of-visitor detection deliberately skipped for now (privacy-first default, chosen over sending visitor IPs to a third-party API or self-hosting a GeoIP database) — revisit if it turns out to matter.
5. **Security headers + production error handling** (§53–54) — CSP/X-Content-Type-Options/etc. middleware, custom 404/403/419/429/500/503 pages, and a production `.env` profile (`APP_DEBUG=false`, hardened session/cookie settings).
6. **Digital Business Card + QR** (§30–31) — `/card` route, and only then install Endroid QR Code.
7. **Section management admin UI** (§46) — CRUD screen for `SiteSection` (currently DB-only).
8. **Theme management admin UI** (§47 remainder) — colors/fonts/logo still fixed in code; dark/light mode itself is done (see above).
9. **Deployment docs** (§72–83) — INSTALLATION.md, DEPLOYMENT.md, CPANEL_DEPLOYMENT.md, SECURITY.md, BACKUP.md, TROUBLESHOOTING.md, plus the actual cPanel deployment when ready.

None of this is started yet beyond what's listed as "live right now" above — this is a plan, not a claim of partial progress on these specific items.
