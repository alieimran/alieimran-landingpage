# Design System

Theme: **modern IT / cybersecurity**, dark-forward, mobile-first, shared identically across the public landing page and the admin panel (per explicit instruction — one visual system, not two). Last updated: 2026-09-22.

## Principles

1. **Mobile-first, desktop-polished.** Every layout is designed at 390px width first, then enhanced (wider max-width, larger type) at `sm:`/`lg:` breakpoints. Verified visually via Playwright screenshots at 390px and 1440px.
2. **One theme, one codebase.** Colors and fonts are defined once as Tailwind v4 `@theme` tokens in `resources/css/app.css`. Shared Blade components (buttons, inputs, nav) consume them, so the public site and `/admin` stay visually consistent without per-page duplication.
3. **Dark mode is the primary experience**, light mode is a fully-supported fallback (respects `prefers-color-scheme`, no manual toggle yet). Both are contrast-checked.
4. **Terminal / HUD motifs, used sparingly.** Monospace labels, `#`/`$` prompt-style section eyebrows, a pulsing status dot, corner-bracket avatar framing. The intent is a technical feel that still reads as a professional recruiter-facing page — not a "hacker movie" pastiche.

## Color tokens

Defined in `resources/css/app.css` under `@theme`. These **override Tailwind's built-in `gray` scale**, so any `bg-gray-900`, `text-gray-400`, `border-gray-700`, etc. anywhere in the app automatically renders in the cyber-slate palette — no per-file class changes needed.

| Token | Hex | Typical use |
|---|---|---|
| `gray-50` | `#f4f7f9` | light-mode page background accents |
| `gray-100` | `#e9eef2` | light-mode card borders |
| `gray-200` | `#d3dce3` | light-mode borders |
| `gray-300` | `#b0bfc9` | light-mode muted/disabled |
| `gray-400` | `#7d8b99` | dark-mode muted text (paired with `gray-600` for light mode — see contrast rule below) |
| `gray-500` | `#5a6a78` | mid-tone, safe on both backgrounds |
| `gray-600` | `#445260` | light-mode muted text |
| `gray-700` | `#2d3a47` | dark-mode borders |
| `gray-800` | `#16202b` | dark-mode surfaces (cards, inputs) |
| `gray-900` | `#0b1116` | dark-mode chrome (nav, headers) |
| `gray-950` | `#05080a` | dark-mode page background |

**Accent:** Tailwind's default `emerald` scale (unmodified), used directly as `emerald-400`/`500`/`600` etc. — *not* achieved by overriding `indigo`. An earlier draft overrode the `indigo` token instead; it was reverted in favor of explicit `emerald-*` classes because silently renaming what "indigo" means in the codebase is a maintenance trap for the next reader. `cyan` and semantic colors (`red` for danger, `green`/`amber` for status) are untouched Tailwind defaults.

### Contrast rule (learned the hard way)

Muted text pairs must read **darker shade in light mode, lighter shade in dark mode** — e.g. `text-gray-600 dark:text-gray-400`, never the reverse. An early pass on the homepage had this backwards (`text-gray-400 dark:text-gray-600`), which failed WCAG contrast in *both* modes simultaneously (~3:1 against white, ~3:1 against near-black). Fixed 2026-09-22; if you add new muted-text utility pairs, keep this ordering.

## Typography

- **Sans (body/headings):** Inter — `--font-sans`
- **Mono (labels, badges, section eyebrows, code-like accents):** JetBrains Mono — `--font-mono`
- Loaded via Bunny Fonts (privacy-friendly Google Fonts mirror, already used by Breeze's scaffolding): `https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500,600`

Usage convention: headings and body copy use the sans stack (`font-sans`, the default); anything that reads as a "system label" — job title, section headers (`# featured`, `$ get_in_touch`), category tags, footer status line, nav badges — uses `font-mono`, usually with `uppercase tracking-widest text-xs`.

## Components

Shared Blade components live in `resources/views/components/`. Restyling one cascades everywhere it's used:

- `primary-button.blade.php` — solid `emerald-600` CTA, used for all main form submits (admin CRUD forms).
- `secondary-button.blade.php` — outlined, neutral gray border.
- `danger-button.blade.php` — red, unchanged from Breeze default (destructive actions stay visually distinct from the accent color).
- `text-input.blade.php` — emerald focus ring/border.
- `nav-link.blade.php` / `responsive-nav-link.blade.php` — emerald active-state underline/highlight.
- `application-logo.blade.php` — custom mark: a rounded-square terminal-prompt icon (`>_`), replacing Breeze's default diamond logo.

## Homepage-specific patterns (`resources/views/home.blade.php`)

- **Status badge:** pulsing-dot pill (`systems online`), mono uppercase, emerald border/text at low opacity.
- **Ambient grid backdrop:** a very low-opacity (`0.07`) CSS grid + radial emerald glow, dark mode only (`hidden dark:block`), purely decorative and `aria-hidden`.
- **Avatar framing:** corner-bracket accents (small L-shaped borders) around the profile photo, HUD-viewfinder style.
- **Tagline as tag chips:** the profile `tagline` field is split on `|` and rendered as individual mono chips (e.g. "Windows Infrastructure", "Technology", "Cloud", "Software") rather than one plain string — this was the actual format given in the SRS's own profile example (§10).
- **Section eyebrows:** `# featured`, `# links`, `# elsewhere`, `$ get_in_touch` — mono, uppercase, low-contrast-but-passing gray with an emerald prefix glyph.
- **Cards:** dark surface, 1px border, hover state brightens the border to emerald and adds a soft emerald glow shadow — no layout shift on hover (border-color transition only... actually shadow added, verify no jank if revisited).

## Admin-specific patterns

- **Top accent bar:** a 2px emerald→cyan→emerald gradient strip at the very top of every admin/auth page — the one purely decorative "brand" element shared by both `layouts/app.blade.php` and `layouts/guest.blade.php`.
- **"ADMIN" badge:** pulsing-dot pill next to the logo in the nav, shown only when `Auth::user()->is_admin`.
- **"View Site" link:** top-right of the admin nav (desktop) and in the mobile menu, opens the public homepage in a new tab — added specifically so changes can be checked without losing the admin session.
- **Login page:** dark card, terminal-prompt logo, "ADMIN CONSOLE" mono label — deliberately distinct from the public homepage's branding so it reads as a separate, restricted surface.

## Accessibility notes

- Semantic HTML (`<nav>`, `<main>`, `<footer>`, `<h1>`/`<h2>` hierarchy) throughout.
- All interactive elements are native `<a>`/`<button>`/form controls — no custom-widget keyboard-trap risk.
- Contrast checked in both color schemes for body/muted text (see contrast rule above); decorative-only elements (hover chevron icons) are intentionally exempted since WCAG 1.4.11 doesn't require full contrast on non-essential decoration next to a fully-legible text label.
- Not yet done: a full automated accessibility audit (e.g. axe-core) — flagged for a future pass once more pages exist.

## Verification method

Visual changes are checked with actual rendered screenshots (Playwright, headless Chromium) at both 390px (mobile) and 1440px (desktop), in both light and dark `colorScheme`, before being reported as complete — not just by reading the generated HTML.
