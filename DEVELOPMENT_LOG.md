# Development Log

Reverse-chronological. Each entry is what changed and why — not a restatement of the diff (that's what `git log` is for).

## 2026-09-22 — First-party analytics dashboard (§36–39)

- Requested mid-session: admin-visible graphs, visitor insights, "which country," "is there any interaction."
- Country-of-visitor detection was flagged back to the user before building anything, since every option has a real tradeoff (a third-party geolocation API means sending every visitor's IP off-server; a self-hosted GeoIP database needs a MaxMind account and periodic manual updates). Decision: skip it for now, privacy-first default, matches SRS §38's own data-minimization principle. Revisit only if it turns out to matter.
- `page_views` table (path, referrer, device type, browser, OS, and a **daily-rotating one-way hash** of IP+user-agent — never the raw IP, and the hash changes every day specifically so no visitor can be tracked across days) populated by a `TrackPageView` middleware applied only to the public `/` and `/contact` GET routes. Bot/crawler traffic is filtered out via a user-agent pattern check before it ever gets recorded.
- `analytics_events` table for outbound interactions: public link/social hrefs on the homepage now point at `/go/link/{link}` and `/go/social/{socialLink}` instead of the destination directly — those routes log the click then 302-redirect. Since the redirect target always comes from our own `Link`/`SocialLink` database row (never from user input), this can't become an open redirect.
- Wrote a small dependency-free `UserAgentParser` (browser/OS/device-type/bot detection via `str_contains`/regex) rather than pulling in a package — a personal analytics dashboard doesn't need pixel-perfect UA parsing.
- Admin dashboard at `/admin/analytics`: summary tiles, a 14-day views bar chart, top pages, top referrers, browser/device breakdown, and an interactions panel — all rendered as plain CSS bar charts (width-percentage divs) to avoid pulling in a JS charting library, consistent with the "keep the frontend lightweight" principle. Zero new JS dependencies.
- Added `analytics:prune` (default 90-day retention, scheduled monthly) — SRS §38 explicitly calls for bounded retention, not just anonymization.
- Verified with real simulated traffic (varied user agents, referrers, device types) through actual HTTP requests and browser screenshots, then cleared that simulated data afterward so the live dashboard starts clean.
- 8 new tests (page-view recording, bot exclusion, no-raw-IP assertion, link/social click redirect+logging, admin-only access, prune command) — suite at 65 passing.
- Debugging note: `php artisan test --filter=X` appeared to hang through the harness's command-completion detection on this machine (same root cause noted in the SEO entry below) — resolved the same way, by wrapping the command in `timeout N ...`.

## 2026-09-22 — SEO completeness (§40–43)

- Custom favicon: an SVG (crisp at any size, matches the terminal-prompt logo mark) plus generated PNG (180×180, for `apple-touch-icon`) and a real ICO — built with PHP's GD extension since no image-editing tool was available, including hand-constructing a minimal valid ICO container (6-byte header + 16-byte directory entry wrapping a PNG, the modern Vista+ ICO format) rather than relying on any external conversion service.
- Canonical URL, Open Graph, and Twitter Card meta tags on the homepage, computed from `SiteSetting` with an optional override via `SeoMetadata::forPage('home')` — the first real use of that model, which existed since the foundation phase but was unused until now.
- Fixed a real bug before it shipped: `og:image`/`twitter:image` were rendering as relative paths (`/storage/profile/...`) from `Storage::url()`, which social-media crawlers require to be absolute — wrapped in `url()`.
- Dynamic `/sitemap.xml` (only lists indexable pages — currently just `/`; `/contact` is deliberately excluded since it's marked `noindex`) and a rewritten static `/robots.txt` (disallowing `/admin`, `/login`, `/forgot-password`, `/reset-password`, `/dashboard`, `/profile`, `/contact`, referencing the sitemap) — replacing Laravel's generic default stub.
- Testing note: `/robots.txt` is a static file served directly by the webserver, not a Laravel route, so it can't be meaningfully requested through Pest's HTTP test client (which only dispatches through the router) — that test reads the file from disk instead of making an HTTP request.
- Also hit a false alarm worth recording: `php artisan test --filter=...` appeared to hang indefinitely through the harness's command wrapper on this machine, but the same command wrapped in `timeout N ... ; echo EXIT` returned correctly in under a second — the tests were never actually stuck, something about how the harness detects completion for that specific invocation pattern was the problem, not Laravel/Pest/PHP. Prefixing with `timeout` resolved it for the rest of the session.
- 3 new tests, suite at 57 passing.

## 2026-09-22 — Manual dark/light toggle, system-wide

- Switched Tailwind's dark-mode strategy from media-query-only to class-based (`@custom-variant dark (&:where(.dark, .dark *));`), which is what makes an explicit override possible — every `dark:` utility already in use across the app picked this up automatically, no per-page class rewrites needed.
- Built the toggle as two small pieces: `partials/theme-init.blade.php` (a blocking inline script, first thing in every page's `<head>`, so there's no flash of the wrong theme on load; reads `localStorage` with a `prefers-color-scheme` fallback; exposes `window.__setTheme()`) and `components/theme-toggle.blade.php` (the sun/moon button, just calls that global function — the icon swap itself is pure CSS via `dark:hidden`/`dark:block`, no JS state tracking needed).
- Also keeps the `theme-color` meta tag (mobile browser chrome color) in sync when toggled, not just the `dark` class.
- Added to all 4 root HTML documents: the public homepage and contact page (floating button, since neither has a persistent nav bar), the admin layout (desktop nav + mobile menu), and the login page. Deliberately skipped the plain-HTML contact-notification email template — email clients don't run JS.
- Verified with real browser automation, not just reading markup: launched with `colorScheme: 'dark'`, confirmed default dark render, clicked the toggle, confirmed instant light re-render, reloaded the page and confirmed the choice persisted, then separately confirmed the same toggle carries through from the login page into the authenticated admin dashboard after navigating.
- No test changes needed — this is a pure CSS/client-side mechanism, the existing 54 tests (which check content and authorization, not visual theme) continued to pass unmodified.

## 2026-09-22 — Public Contact form + admin inbox

- Built the full Contact system (SRS §32–35), the top item on the prioritized next-steps list: public form at `/contact`, `ContactController`, `StoreContactInquiryRequest`.
- Security measures required by the SRS, all implemented and tested: CSRF (Blade default), server-side validation, `throttle:5,1` rate limiting on the POST route, and a honeypot field (`website`) — checked separately from Laravel's validation pipeline so a bot that fills it gets an ordinary-looking success redirect instead of a validation error that would reveal the trap.
- Email notification (`NewContactInquiryMail`) sent to whatever `SiteSetting::contact_notification_email` is set to, wrapped in a try/catch so a mail failure (e.g. SMTP misconfigured) never prevents the inquiry from being saved — verified with a test that forces `Mail::to()->send()` to throw and confirms the row still lands in the database.
- Admin inbox at `/admin/contact-inquiries`: filterable list (by status), detail view that auto-marks an inquiry as read on open, a status-update form (new/read/replied/archived), and delete. Added a matching unread-count badge next to "Contact" in the admin nav and linked the dashboard's "New Inquiries" stat card straight to the filtered inbox.
- Loosened the Site Settings CTA URL validation twice in support of this: first to accept `mailto:` links, then to also accept root-relative paths (`/contact`), so the homepage's "Get In Touch" CTA could point at the real form instead of opening the visitor's email client directly. Updated the seeded `secondary_cta_url` accordingly.
- Updated the homepage's contact section to lead with a "Send a Message" button to `/contact`, keeping the direct email as a smaller secondary link underneath rather than the only option.
- 14 new tests (form validation, honeypot, rate limiting, mail-failure resilience, admin CRUD, authorization boundaries) — suite now at 54 passing.
- Verified the whole flow for real: submitted an inquiry as a guest via a real browser session, confirmed the unread badge appeared for the admin, opened it and confirmed it flipped to "read" automatically, screenshotted the result — then cleared the test data afterward.

## 2026-09-22 — Documentation set + seed data + admin polish

- Created this file plus `SRS_COMPLIANCE.md`, `PROJECT_STATUS.md`, `DESIGN_SYSTEM.md`, `TECHNOLOGY_STACK.md` as a standing, git-tracked documentation set (to be updated going forward, not deleted).
- Added an admin "View Site" link (desktop nav + mobile menu) opening the public homepage in a new tab, so changes can be checked without losing the admin session.
- Loosened `primary_cta_url`/`secondary_cta_url` validation from a strict `url` rule to a regex accepting `http(s)://` or `mailto:`, so a "Get In Touch" CTA can point straight to an email client. Added tests covering both the accepted `mailto:` case and a rejected garbage-URL case.
- Seeded site content: some sourced directly from the SRS's own example text (name/job title/tagline/portfolio URL), some inferred from strong contextual clues (location), some explicitly-authorized fabricated placeholders (bio paragraph, LinkedIn/GitHub URLs) — see PROJECT_STATUS.md's "Seed data" section for the exact breakdown of what's real vs. fabricated.
- Verified the seeded homepage visually via Playwright screenshots (mobile + desktop, dark mode) — full page now renders hero, featured portfolio card, social links, and contact section together for the first time.

## 2026-09-22 — Cybersecurity/IT theme overhaul, Tailwind v4 migration

- Discovered the project was actually running Tailwind v3 via PostCSS even though `@tailwindcss/vite` v4 was already an installed-but-unused dependency. Migrated properly to v4: removed `postcss.config.js` and `tailwind.config.js`, added the `@tailwindcss/vite` plugin to `vite.config.js`, moved theme config into `resources/css/app.css` via `@theme` (CSS-first config, the v4 convention).
- Defined a cyber-slate `gray` scale override so the existing `dark:bg-gray-800/900`, `border-gray-700`, etc. utilities used throughout Breeze's scaffolding pick up the new dark theme automatically, without touching every file.
- Considered overriding Tailwind's `indigo` token to redefine the accent color (since Breeze's buttons/focus-rings/links all use `indigo-*`), but reverted that approach — renaming what a color token *means* is confusing for a future reader. Did a project-wide `indigo-*` → `emerald-*` replace instead, so the code stays self-documenting.
- Redesigned the public homepage: terminal-style pulsing-dot status badge, tagline rendered as mono tag chips (split on `|`), ambient low-opacity grid backdrop (dark mode only), corner-bracket avatar framing, OG/theme-color meta tags.
- Restyled the admin layout, navigation, and login page to match: gradient top accent bar, "ADMIN" pulsing badge, new terminal-prompt (`>_`) logo mark replacing Breeze's default diamond icon, "ADMIN CONSOLE" mono branding on the login card.
- Caught and fixed a real bug during visual QA: several muted-text utility pairs were backwards (`text-gray-400 dark:text-gray-600` — the lighter shade in light mode, darker shade in dark mode), which failed contrast in *both* color schemes. This was only caught by actually screenshotting the page, not by reading the generated HTML.
- Verified the whole pass — homepage (light/dark, mobile/desktop) and admin (login, dashboard, links index) — via Playwright screenshots before calling it done, per the project's own rule about not claiming a UI change works without seeing it render.

## 2026-09-22 — Admin CRUD + public homepage (first pass)

- Built admin resource controllers for Links, Social Links, and Link Categories, plus a singleton Site Settings editor — all behind `auth`+`verified`+`admin` middleware, with a matching `authorize()` check in every Form Request as defense-in-depth.
- File uploads (link images, profile photo) restricted to `jpg/jpeg/png/webp` (SVG deliberately excluded — stored SVGs are a live XSS vector if ever served/rendered inline), validated via Laravel's `image` rule (inspects actual file content, not just the extension), stored under generated UUID filenames, with old files cleaned up on replace.
- Built the public homepage (`HomeController` + `home.blade.php`), driven by `SiteSection` visibility flags so any block (hero/featured/links/social/contact) can be toggled off from the database without a code change.
- Seeded default `site_sections` rows (hero, featured_links, links, social_links, contact) via a dedicated seeder.
- Verified the full loop end-to-end via `curl` with a real cookie-jar session: logged in as the seeded admin, created a link through the actual HTTP form, confirmed it appeared on the public homepage, then deleted the test data.
- Removed Breeze's default `welcome.blade.php` and its stock example test (no longer reachable — `/` now serves the real homepage), replacing the lost coverage with a dedicated `HomeTest`.

## 2026-09-22 — Foundation setup

- Found the project folder had no write permission for the working Windows account (only `Administrators`/`SYSTEM` had access) — every file operation failed with `EPERM` until the user granted their own account write access. Flagged rather than worked around silently, since changing filesystem ACLs is a security-relevant action.
- Switched `.env`/`.env.example` from the default SQLite connection to MariaDB (`alieimran_landingpage`, utf8mb4), after confirming DB connectivity with credentials the user provided directly.
- `git init` — the project had no version control at all before this.
- Installed Laravel Breeze (Blade stack, dark mode) and Pest — Pest's `tests/Pest.php` turned out to already ship with Laravel 13's default skeleton, so no separate `pest:install` step was needed (a background `./vendor/bin/pest --init` run hung waiting on an interactive prompt and was killed rather than force-answered blind).
- Designed and migrated the Root CMS schema: `site_settings`, `theme_settings`, `site_sections`, `link_categories`, `links`, `social_links`, `digital_cards`, `contact_inquiries`, `seo_metadata`, plus a non-mass-assignable `is_admin` boolean on `users`. Deliberately did **not** create a separate `featured_links` table (see SRS_COMPLIANCE.md §17 note) or `page_views`/`activity_logs` tables (deferred until Analytics/Audit-logging are actually built).
- Removed public registration entirely (route, controller, view) and added `php artisan admin:create` — an interactive command that refuses to run if a Super Admin already exists, enforcing the SRS's single-admin V1 constraint at the command level, not just in the UI.
- Established the baseline test suite (25 → 27 tests across this phase) and confirmed Pint compliance.

---

*Log convention: newest entry first, one heading per work session/phase, bullets on the "why" not just the "what" — the diff already shows the what.*
