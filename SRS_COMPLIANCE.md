# SRS Compliance

Traceability against *Software Requirements Specification — Alie Imran Personal Landing Page (Final)*. Status as of 2026-09-22.

Legend: ✅ Done · 🟡 Partial · ⬜ Not started · — Not applicable / process requirement (not a feature)

## 1–3. Architecture & ecosystem boundaries

| § | Requirement | Status | Notes |
|---|---|---|---|
| 2 | Root ≠ Portfolio ≠ Tunang ≠ Kahwin | ✅ | Standalone Laravel app, own DB, no shared models/migrations/sessions |
| 3 | No shared DB/auth/models with sibling apps; no modification of sibling files | ✅ | Nothing outside this project directory has been touched |

## 4–6. Root responsibility & source-of-truth

| § | Requirement | Status | Notes |
|---|---|---|---|
| 4 | Personal landing page | ✅ | `/` — `HomeController` |
| 4 | Basic personal identity | ✅ | `SiteSetting` model/admin |
| 4 | Link hub | ✅ | `Link` + `LinkCategory` |
| 4 | Social media links | ✅ | `SocialLink` |
| 4 | Featured links | ✅ | `links.featured` boolean (see §17 note below) |
| 4 | Portfolio link | ✅ | seeded, `https://www.alieimran.com/portfolio` per §12's own default |
| 4 | Digital business card | ⬜ | `digital_cards` table exists; no route/controller/view |
| 4 | General contact/inquiry | 🟡 | `contact_inquiries` table + model exist; no public form, no admin inbox UI yet — homepage only shows a `mailto:` CTA |
| 4 | Root-level analytics | ⬜ | not started |
| 4 | Root-level SEO | 🟡 | basic `<title>`/description/OG tags on homepage; `seo_metadata` table unused; no sitemap/robots.txt |
| 4 | Root-level theme | 🟡 | theme is implemented (see DESIGN_SYSTEM.md) but hard-coded in CSS, not admin-editable; `theme_settings` table exists but unused |
| 4 | Section visibility | ✅ | `SiteSection` model drives homepage block visibility |
| 4 | Administrative CMS | 🟡 | Links/Social Links/Categories/Site Settings CRUD done; Sections/Theme/Digital Card/Contact-inbox/SEO admin screens not built |
| 4 | Security and hardening | 🟡 | see §51–61 below |
| 4 | Error handling | ⬜ | custom 404/403/419/429/500/503 pages not built; still `APP_DEBUG=true` (local only) |
| 5, 63 | No duplicate professional-content CMS (projects/photography/blog/Wataniah/CV) | ✅ | none built; links table used for teasers instead, as intended |
| 6 | Source-of-truth separation maintained | ✅ | |

## 9–17. Homepage & Link Hub detail

| § | Requirement | Status | Notes |
|---|---|---|---|
| 9 | Configurable homepage sections | 🟡 | sections model exists and drives visibility; admin UI to reorder/edit sections not built yet (order currently fixed by `sort_order` seed values) |
| 10–11 | Profile/Hero, no duplication of Portfolio's detailed bio | ✅ | short bio only, per the SRS's own example content |
| 12 | Portfolio link, configurable | ✅ | via Site Settings `portfolio_url` |
| 13 | Link hub (categories, CRUD, reorder, feature) | ✅ | full CRUD; category slugs auto-unique |
| 14 | Link expiration (start/end date) | ✅ | `Link::visible()` scope + tested |
| 15–16 | Social links, Root as sole authority | ✅ | |
| 17 | Featured links | ✅ (adjusted) | Implemented as `links.featured` boolean rather than a separate `featured_links` table — see DESIGN_SYSTEM.md and the commit "Add admin CRUD..." for rationale (SRS §62 explicitly allows adjusting the recommended schema when simpler) |
| 18 | Selected highlights as lightweight teasers | 🟡 | mechanism exists (links + categories); not yet demonstrated with real project/blog/photography teaser entries |
| 19–27 | Projects/Photography/Blog/Wataniah/CV — Root must NOT own detailed content | ✅ | none built; bio mentions Wataniah only as a one-line personal note, no operational detail (§25 sensitivity respected) |
| 26–27 | CV download link | ⬜ | not seeded/built |
| 28–29 | Event links | ⬜ | not seeded (mechanism supports it via link categories) |

## 30–31. Digital Business Card

| § | Requirement | Status |
|---|---|---|
| 30 | `/card` digital business card | ⬜ not started |
| 31 | QR code (Endroid) | ⬜ not started — package not installed, per stack policy of installing only when needed |

## 32–35. Contact System

| § | Requirement | Status | Notes |
|---|---|---|---|
| 32 | Public contact form with category field | ⬜ | not built — homepage has a `mailto:` CTA as a placeholder only |
| 33 | Inquiry storage + admin read/reply/archive/delete | 🟡 | `ContactInquiry` model + `markAsRead()` exist; no admin inbox UI, no public form to populate it |
| 34 | Email notification on new inquiry | ⬜ | not built (no form yet) |
| 35 | CSRF/validation/rate-limit/honeypot on contact form | ⬜ | not applicable yet — form doesn't exist |

## 36–43. Analytics & SEO

| § | Requirement | Status |
|---|---|---|
| 36–39 | Root analytics (page views, link clicks, optional GA) | ⬜ GA tag wiring exists (`ga_tracking_id` field renders the gtag snippet if set) but no first-party `page_views`/`analytics_events` tracking |
| 40–41 | Root SEO (title, description, canonical, OG, Twitter card, robots, favicon) | 🟡 title/description/OG partially done; no canonical tag, no Twitter card meta, no favicon, no per-page `seo_metadata` admin UI |
| 42 | `/sitemap.xml` | ⬜ not built |
| 43 | `/robots.txt` | ⬜ not built |

## 44–50. Admin & CMS

| § | Requirement | Status | Notes |
|---|---|---|---|
| 44 | `/admin`, single Super Admin, no public registration, `admin:create` command | ✅ | registration route/controller/view deleted; `php artisan admin:create` implemented with validation |
| 45 | Admin manages profile/sections/links/categories/social/featured/digital-card/contact/theme/SEO/analytics | 🟡 | Profile, Links, Social Links, Categories done; the rest not built |
| 46 | Section management (enable, nav/homepage visibility, sort order) | 🟡 | model + homepage consumption done; no admin CRUD screen for sections yet |
| 47 | Theme management (colors, fonts, logo, favicon, dark mode) | ⬜ | not admin-editable; theme is currently a fixed design system in code |
| 48 | Admin auth security (hashing, throttling, CSRF, session regen, secure logout, cookies) | ✅ | Laravel/Breeze defaults; login route throttled; bcrypt hashing |
| 49 | Single Super Admin, Spatie Permission not required for V1 | ✅ | `users.is_admin` boolean, deliberately no RBAC package |
| 50 | Audit logging | ⬜ | Spatie Activitylog not installed; no admin action log |

## 51–61. Security

| § | Requirement | Status | Notes |
|---|---|---|---|
| 51 | OWASP/defense-in-depth mindset | 🟡 | applied throughout; formal review not done |
| 52 | File upload security | ✅ | MIME+extension allow-list (jpg/jpeg/png/webp, SVG deliberately excluded), Laravel's `image` rule (real file inspection, not just extension), generated UUID filenames, old file cleanup on replace |
| 53 | Security headers (CSP, X-Content-Type-Options, Referrer-Policy, HSTS, frame protection) | ⬜ | no header middleware configured yet |
| 54 | Production error handling, `APP_DEBUG=false`, custom error pages | ⬜ | still local dev config; production `.env` not created |
| 55 | Server-side input validation | ✅ | Form Requests on every admin write path |
| 56 | Output escaping | ✅ | Blade's default `{{ }}` escaping used throughout; no raw HTML output of user content |
| 57 | Mass assignment protection | ✅ | explicit `$fillable` everywhere; `users.is_admin` deliberately excluded from `User`'s fillable list, only settable via `forceFill` in the `admin:create` command |
| 58 | IDOR protection | ✅ | every admin route requires `auth`+`verified`+`admin` middleware; Form Requests also check `is_admin` in `authorize()` as defense-in-depth |
| 59 | Rate limiting | 🟡 | Breeze's default login throttle only; no rate limiting configured for admin write actions or the (not-yet-built) contact form |
| 60 | Session security (secure cookies, HttpOnly, SameSite, regen, lifetime) | 🟡 | Laravel defaults apply; not yet hardened for production HTTPS (`SESSION_ENCRYPT`, secure-cookie flags need setting at deploy time) |
| 61 | Database (MariaDB, utf8mb4, credentials in `.env`, FKs/indexes) | ✅ | `alieimran_landingpage` DB, utf8mb4/utf8mb4_unicode_ci, FK on `links.link_category_id`, indexes on `links`/`contact_inquiries` |

## 62–63. Database design

| § | Requirement | Status |
|---|---|---|
| 62 | Recommended Root schema | ✅ (adjusted, documented) — see §17 note above; `page_views`/`analytics_events`/`activity_logs` deliberately deferred until those features are built |
| 63 | No duplicate content tables (projects, photography, blog, Wataniah, resume) | ✅ none created |

## 64–71. Tech stack, performance, accessibility, responsive

See **TECHNOLOGY_STACK.md** for the full version matrix.

| § | Requirement | Status |
|---|---|---|
| 64–67 | Stack matches baseline, unused packages not installed | ✅ |
| 68 | Performance (minimal JS, optimized images, caching, no N+1) | 🟡 no images to optimize yet; no explicit query caching; `Link::with('category')` avoids N+1 on the admin index |
| 69 | Accessibility | 🟡 semantic HTML + contrast pass done; no automated audit yet |
| 70 | Responsive (mobile-first, works at all sizes) | ✅ verified visually at 390px and 1440px, light and dark |
| 71 | Cross-browser support | 🟡 standard Tailwind output, no vendor-specific hacks; not explicitly tested outside Chromium |

## 72–83. Deployment, backup, documentation

| § | Requirement | Status |
|---|---|---|
| 72–76 | Production deployment (cPanel, source outside web root, hardened config) | ⬜ not started — local dev only so far |
| 77–79 | Testing (functional + security scenarios) | 🟡 40 Pest tests covering auth, admin CRUD, authorization boundaries, link visibility windows, CTA validation; no dedicated CSRF/XSS/SQLi-injection-attempt tests beyond what Laravel's framework guarantees by default |
| 80 | Documentation set | 🟡 this file + PROJECT_STATUS.md + DEVELOPMENT_LOG.md + DESIGN_SYSTEM.md + TECHNOLOGY_STACK.md exist; INSTALLATION.md, DEPLOYMENT.md, CPANEL_DEPLOYMENT.md, SECURITY.md, BACKUP.md, TROUBLESHOOTING.md not yet written |
| 81 | Backup procedure | ⬜ not started |
| 82 | Git security (no secrets committed) | ✅ `.env` gitignored and never committed; `.env.example` has placeholder values only |
| 83 | Deployment checklist | ⬜ not started |

## 84. Definition of Done — running scorecard

| # | Item | Status |
|---|---|---|
| 1–3 | Laravel installed, MariaDB working, homepage responsive | ✅ |
| 4 | Super Admin authenticates securely | ✅ |
| 5–9 | Profile/Links/Categories/Social/Featured manageable | ✅ |
| 10 | Sections enable/disable | 🟡 model + homepage consumption only, no admin UI |
| 11 | Portfolio link works | ✅ |
| 12 | External app links work | ✅ (link mechanism is generic) |
| 13–14 | Digital card + QR | ⬜ |
| 15–16 | Contact inquiries stored + email notification | ⬜ |
| 17 | Root SEO works | 🟡 |
| 18 | Sitemap works | ⬜ |
| 19 | Analytics works where enabled | ⬜ |
| 20 | Security controls implemented | 🟡 |
| 21 | Error handling implemented | ⬜ |
| 22 | Automated tests pass | ✅ 40/40 |
| 23 | Production config hardened | ⬜ |
| 24 | Documentation complete | 🟡 in progress |
| 25 | Deployment tested | ⬜ |
| 26–28 | Portfolio/Tunang/Kahwin remain independent | ✅ (trivially — none exist in this workspace to affect) |
| 29 | No duplicate professional-content CMS | ✅ |

## 85–91. Process requirements

These govern *how* the project is built rather than a feature to check off — followed throughout:

- Inspect actual project files before assuming (§85.1) — done at every phase (e.g. discovering the stray Tailwind v3/v4 mix before touching CSS).
- Flag conflicts/deviations before making them, don't silently redesign (§86) — done for the `featured_links` table consolidation and the `indigo`→`emerald` token decision.
- No destructive actions without approval (§87) — no drops, force-pushes, or deletions of unclear-ownership files have occurred.
- Function ownership matrix (§88) respected — no Portfolio-owned content types built here.

## Summary

**Solid:** architecture boundaries, core CMS (Links/Social/Categories/Settings), admin auth, file upload security, mass-assignment/IDOR protection, testing coverage for what's built, responsive design, the shared design system.

**Biggest gaps to close next:** public Contact form + admin inbox, Digital Business Card + QR, SEO completeness (sitemap/robots/canonical/Twitter card), security headers, production error handling, and the deployment/backup docs. See PROJECT_STATUS.md for the prioritized next-steps list.
