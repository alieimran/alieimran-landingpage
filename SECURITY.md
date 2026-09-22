# Security

What's actually implemented, why, and what's a known, deliberate tradeoff rather than an oversight. Cross-references `SRS_COMPLIANCE.md` §51–61 for the full requirement-by-requirement status.

## Authentication & authorization

- **Single Super Admin, no public registration.** The registration route, controller, and view were deleted entirely (not just hidden) early in the project. The only way to create an admin account is `php artisan admin:create`, which refuses to run if a Super Admin already exists.
- **`is_admin` is never mass-assignable.** It's deliberately excluded from `User`'s `#[Fillable(...)]` attribute — the only way to set it is `forceFill()`, used exclusively inside `admin:create`.
- **Every admin route** requires `auth` + `verified` + a custom `admin` middleware (checks `is_admin`), and every admin Form Request also checks `is_admin` in its own `authorize()` method as defense-in-depth — a route-middleware bug wouldn't be the only thing standing between a non-admin and a write action.
- Breeze's default login throttling, CSRF, session regeneration, and secure password hashing (bcrypt) are all in place unmodified.

## Input validation & output safety

- Every admin write path goes through a dedicated Form Request — no controller trusts raw `$request->all()`.
- Blade's default `{{ }}` escaping is used throughout; there is no raw/unescaped output of user-controlled content anywhere in the app.
- Mass assignment protection via explicit `$fillable` on every model.

## File uploads

- MIME type, extension, and actual file content are all validated (Laravel's `image` rule inspects real file content, not just the extension/name).
- **SVG is deliberately excluded** from every upload path that accepts images (link images, profile photo, theme logo, theme favicon) — a directly-navigated SVG file can execute embedded `<script>` content even though an `<img src="...">` reference to the same file can't. Given uploads here are admin-only (not public), the practical risk is narrow, but the exclusion costs nothing and closes it entirely.
- Uploaded files are stored under generated UUID filenames, never the original client filename — removes path-traversal and filename-collision risk in one move.
- Old files are deleted when replaced (link images, profile photo, theme assets), so storage doesn't accumulate orphaned uploads indefinitely.

## Security headers & CSP

Applied globally via the `SecureHeaders` middleware:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy` disabling geolocation/microphone/camera/payment
- `Strict-Transport-Security` when the request is actually served over HTTPS
- `Content-Security-Policy`, generated per-request with a fresh nonce

### CSP design

Rather than broad host allowlisting, `script-src` uses `'strict-dynamic'` plus a per-request nonce: only the nonce'd inline scripts (theme init, GA snippet) and Vite's own generated tags (via Laravel's `Vite::useCspNonce()`) are trusted, and — because `strict-dynamic` is present — they can load further scripts of their own (this is what lets Google's `gtag.js` loader pull in its additional scripts) without the policy needing a wide `googletagmanager.com`-and-friends allowlist that would also work for an attacker's injected script tag pointed at the same trusted-looking domains.

### Known, deliberate CSP exceptions

These are documented here and in the middleware's own doc comment — not silently accepted:

1. **`script-src 'unsafe-eval'`** — Alpine.js (used for the mobile nav, the admin "Manage" dropdown, the settings dropdown, the delete-account confirmation modal, and the theme toggle) evaluates directive expressions via `Function()` internally, which CSP's eval restriction blocks by design. Two ways to remove this: migrate to Alpine's separate CSP-compliant build (requires pre-registering every directive's logic in JS instead of writing it inline — real migration effort, especially for the modal's focus-trap logic), or replace Alpine with hand-written vanilla JS for these few, well-contained interactions. Neither was done as a rushed side-change bundled into an unrelated task; both remain open, tracked in `PROJECT_STATUS.md`.
2. **`style-src 'unsafe-inline'`** — inline `style=""` attributes are used for a handful of computed values (analytics chart bar widths, homepage background gradients) that would otherwise need a `<style>` block generated per-request. CSS injection is a meaningfully weaker attack vector than script injection, which is why this tradeoff is more defensible than the script-src one — but it's still a real relaxation of the policy, not free.

If a future pass wants to tighten either of these, that's legitimate follow-up work — just don't assume they're accidental gaps.

## Rate limiting

- Contact form POST: `throttle:5,1` (5 submissions per minute per IP)
- Login: Breeze's default throttle
- Not yet applied: admin write actions (Links/Social Links/etc. CRUD) have no rate limit beyond normal session auth — acceptable for a single-admin system where the only authenticated actor is trusted, but worth revisiting if that assumption ever changes

## Contact form anti-spam

- CSRF (Blade `@csrf`)
- Server-side validation on every field
- Honeypot field (`website`), visually hidden but present in the DOM and announced to screen readers (`sr-only`, not `display:none`) so it catches bots that fill every field indiscriminately, while staying genuinely accessible to legitimate assistive-technology users. Checked separately from Laravel's validation pipeline, so a bot that trips it gets an ordinary-looking success redirect rather than a validation error that would reveal the trap.

## Privacy-conscious analytics

- No raw IP address is ever stored. `page_views.visitor_hash` is a one-way SHA-256 hash of IP + user-agent + the current date + the app key, which **rotates daily** — the same visitor gets a different hash every day, so no long-term tracking or cross-day profiling is possible from this data even if the database were compromised.
- Bot/crawler traffic is filtered out before being recorded (user-agent pattern match).
- `analytics:prune` command (default 90-day retention, scheduled monthly) keeps the data window bounded rather than accumulating indefinitely — SRS §38's "retention should be limited where practical."
- Country-of-visitor detection was deliberately not implemented, after flagging the tradeoff to the project owner: a third-party geolocation API means sending every visitor's IP to an external company, and a self-hosted GeoIP database needs an account and periodic manual updates. Skipped for now as the privacy-first default.

## Production error handling

- `APP_DEBUG` must be `false` in production (see `DEPLOYMENT.md`) — with it `true`, unhandled exceptions leak stack traces, file paths, and potentially credentials.
- Custom themed error pages exist for 404/403/419/429/500/503, replacing Laravel's defaults.
- Those error pages are **resilient to database failure**: the favicon and theme-color-override partials they include wrap their `ThemeSetting` queries in try/catch. Without this, a database outage — exactly the scenario a 500 page needs to survive — would have made the 500 page itself throw, compounding the failure instead of explaining it. This was a real bug caught during development, not a hypothetical.

## IDOR protection

Every admin resource route requires the `admin` middleware; route-model-bound resources (a link, a social link, a contact inquiry, a section) are only reachable by an authenticated admin, and since V1 has exactly one Super Admin, there's no "another user's resource" scenario to leak across accounts.

## Session security

Laravel/Breeze defaults apply (HttpOnly cookies, session regeneration on login, invalidation on logout). Production-specific hardening (`SESSION_SECURE_COOKIE=true`, which requires HTTPS to actually be live first) is called out in `DEPLOYMENT.md` rather than baked into local `.env`.

## Reporting a vulnerability

This is a personal project without a public disclosure program. If you find something, contact the site owner directly rather than filing a public issue.
