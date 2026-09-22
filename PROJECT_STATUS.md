# Project Status

**Snapshot date:** 2026-09-22
**Environment:** local development (Laravel Herd, Windows 11)
**Live local URL:** `http://alieimran-landingpage.test`

For the detailed requirement-by-requirement breakdown, see **SRS_COMPLIANCE.md**. For what was built in what order, see **DEVELOPMENT_LOG.md**. For version numbers, see **TECHNOLOGY_STACK.md**. For the visual theme, see **DESIGN_SYSTEM.md**.

## What's live right now

- **Public homepage** (`/`) — profile/hero, featured links, link hub, social links, contact CTA (links to the real contact form). Cybersecurity/IT dark theme, mobile-first, verified at 390px and 1440px.
- **Manual dark/light toggle** — every page (public and admin) has a sun/moon button that overrides the OS preference and is remembered across visits (`localStorage`). No flash-of-wrong-theme on load.
- **Public contact form** (`/contact`) — name/email/phone/category/subject/message, CSRF, server-side validation, `throttle:5,1` rate limiting, honeypot spam trap, email notification to the admin (mail-failure-safe).
- **Digital Business Card** (`/card`) — hidden (404) until enabled by the admin; shows contact details and a QR code (generated on the fly, points back at `/card` itself) once live.
- **Admin panel** (`/admin`, single Super Admin only):
  - Dashboard with quick counts (Views 7d, Links, Social Links, New Inquiries — each links to its respective screen)
  - "Manage" dropdown in the nav: Links, Social Links, Link Categories, Site Settings, Digital Card, Sections, Theme — grouped there once the flat nav got too wide for eight+ items
  - Contact inbox: list with status filter, detail view (auto-marks read), status updates, delete — with an unread-count badge in the nav
  - Analytics dashboard (`/admin/analytics`): views over the last 14 days, top pages, top referrers, browser/device breakdown, outbound link/social click counts, recent-visits feed — all first-party, no third-party tracker
  - Sections admin (`/admin/sections`): toggle/relabel/reorder the five fixed homepage blocks — edit-only, no create/delete, since section keys map 1:1 to blocks the homepage template actually knows how to render
  - Theme admin (`/admin/theme`): primary accent color (verified working — see Known issues for what's still out of scope), logo upload, favicon upload
  - "View Site" shortcut in the nav to preview the public page in a new tab
- **Analytics tracking:** page views (path, referrer, device/browser/OS, bot traffic excluded) and outbound clicks (`/go/link/{link}`, `/go/social/{socialLink}`) recorded automatically. No raw IP ever stored — only a one-way hash of IP+user-agent+date that rotates daily. `analytics:prune` command (90-day default, scheduled monthly) for bounded retention.
- **SEO:** canonical URLs, Open Graph + Twitter Card meta (falls back to profile data when no `seo_metadata` override is set), dynamic `/sitemap.xml` (indexable pages only), static `/robots.txt`, custom SVG/PNG/ICO favicon (overridable per the Theme admin page above).
- **Security:** global security headers (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, HSTS when served over HTTPS) plus a per-request-nonce Content-Security-Policy. Themed custom error pages for 404/403/419/429/500/503, verified resilient to database failures (they don't depend on a DB query succeeding to render).
- **Auth:** Breeze-based login/logout/password-reset. Public registration is fully removed.
- **Database:** MariaDB (`alieimran_landingpage`), 16 migrations applied.
- **Tests:** 96 Pest tests passing (`php artisan test`), Pint clean.

## Admin access

- URL: `/admin` (redirects through `/login` if not authenticated)
- Account: `alieimran@outlook.com` — password was set interactively, not stored anywhere in this repo or its docs. If it's lost, run `php artisan admin:create` again — it refuses to run while a Super Admin already exists.

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
- **Alpine.js requires `'unsafe-eval'` in the CSP:** the mobile nav, settings dropdown, delete-account confirmation modal, and theme toggle all use Alpine, which evaluates directive expressions via `Function()` — CSP's eval restriction blocks that by design. Migrating to Alpine's separate CSP build (pre-registering directive logic in JS instead of writing it inline) or replacing Alpine with hand-written vanilla JS would close this, but both are real effort — deliberately not done as a rushed side-change. Documented in the `SecureHeaders` middleware's own doc comment.
- **Theme customization is scoped to primary accent color + logo + favicon:** secondary/background/text colors, button style, border radius, and font family are not wired to `theme_settings` yet. Doing so would mean rewriting every page's fixed Tailwind color classes to read from these settings instead — a much larger change than this pass covers. The primary-color override mechanism itself (a layered CSS override, not a rewrite) is real and verified working, so extending it to more colors is additive work, not a redesign.
- **Honeypot stealth isn't airtight against every bot:** the contact form's honeypot check runs in the controller, *after* Laravel's Form Request validation has already run. A bot that fills the honeypot field but also sends otherwise-invalid data (missing a required field, say) gets a real validation-error response rather than the intended stealth "looks like success" response — the spam is still blocked either way (nothing gets stored or emailed), but the response doesn't always hide that a trap exists. Not fixed, since doing so would mean restructuring how the honeypot check relates to the validation pipeline for a cosmetic edge case against unsophisticated bots specifically.

## Bug audit (2026-09-22)

A systematic pass through every custom controller and model found and fixed several real bugs — full details in `DEVELOPMENT_LOG.md`'s corresponding entry:
- Unchecked checkboxes silently failed to save as `false` on 5 different admin forms (Links, Social Links, Site Settings, Digital Card, Sections) — the single most impactful find, since it meant "disabling" something often didn't actually disable it.
- Link category rename was built but unreachable — no UI ever called the working `update()` route.
- `/go/link/{id}` and `/go/social/{id}` redirects worked even for disabled/expired links.
- The admin "Manage" nav dropdown was positioned with a `mt-32` guess instead of proper CSS, and was visibly wrong.

All four are fixed, tested (10 new/updated tests), and verified in a real browser where visual. Nothing outstanding from this pass.

## Prioritized next steps

Roughly in the order they'd unblock the most SRS Definition-of-Done items (§84):

1. ~~**Public Contact form**~~ — done (2026-09-22).
2. ~~**SEO completeness**~~ — done (2026-09-22).
3. ~~**Manual dark/light toggle**~~ — done (2026-09-22), system-wide.
4. ~~**Analytics dashboard + visitor insights**~~ — done (2026-09-22). Country-of-visitor detection deliberately skipped (privacy-first default) — revisit if it turns out to matter.
5. ~~**Security headers + production error handling**~~ — done (2026-09-22): CSP/security headers, themed error pages. Production `.env` profile itself (`APP_DEBUG=false`, hardened session/cookie settings) still belongs to the deployment step below, not done yet.
6. ~~**Digital Business Card + QR**~~ — done (2026-09-22).
7. ~~**Section management admin UI**~~ — done (2026-09-22), edit-only by design.
8. ~~**Theme management admin UI**~~ — done (2026-09-22), scoped to primary color + logo + favicon; see Known issues for what's deferred.
9. ~~**Deployment docs**~~ — done (2026-09-22): README.md, INSTALLATION.md, DEPLOYMENT.md, CPANEL_DEPLOYMENT.md, SECURITY.md, BACKUP.md, TROUBLESHOOTING.md.

Every item from the original four-part request (security headers/error handling, Digital Business Card + QR, Section/Theme admin UIs, deployment docs) is done. What's left is genuinely down to: **the actual production deployment** (docs are ready, host is untested — see `CPANEL_DEPLOYMENT.md`'s own checklist), and the smaller deferred items noted throughout (full theme color customization beyond the primary accent, the two documented CSP exceptions, country analytics). Nothing above is a partial or fake implementation — each has its own commit, tests, and (where visual) a real browser-screenshot verification recorded in `DEVELOPMENT_LOG.md`.
