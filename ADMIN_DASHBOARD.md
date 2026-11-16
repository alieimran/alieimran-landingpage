# Admin Dashboard — redesign, holidays, Hijri calendar and prayer zone

Everything about the redesigned `/admin` dashboard lives in this file: what it shows, where the data comes from, the rules used for Malaysian holidays (including replacement days), how to configure it, how to deploy it and how to fix it when something looks wrong.

Last updated: 2026-11-17.

---

## 1. What changed

The old dashboard was four counters and a system-status table. It is now organised around three questions an admin has when they open it:

1. **What needs my attention?**
2. **How is the site doing?**
3. **What do I want to do next?**

| # | Section | Purpose |
|---|---|---|
| 1 | Header | Time-of-day greeting, date, live/maintenance status, **View site** and **New link** buttons |
| 2 | Attention strip | Only things that need a decision: new inquiries, dependency advisories, components to update, maintenance mode. Shows a calm "All clear" when there is nothing |
| 3 | KPI tiles | Page views, visitors, link clicks (last 7 days vs the 7 before, with % change and a sparkline) and unread inquiries |
| 4 | Traffic chart | Page views / Visitors / Link clicks over 7, 14 or 30 days, previous period as a dashed line, hover/keyboard tooltip, "View as table" |
| 5 | Inbox | Latest 5 inquiries with unread markers |
| 6 | Top pages, Top referrers, Devices | Last 30 days, ranked bars |
| 7 | Calendar row | **Next public holidays** (national), **State holidays** (up to two states), **Hijri calendar** |
| 8 | Manage your site | Nine shortcuts to the admin areas |
| 9 | Waktu solat | JAKIM prayer times with the next prayer highlighted, and the **Change zone** option |
| 10 | System & security | One-line summary pills; version table and advisory list are collapsed unless something needs attention |

Design follows `DESIGN_SYSTEM.md` (cyber-slate + emerald, mono eyebrow labels, light and dark). Charts use one emerald hue on gray, not a rainbow, with every colour state also carried by text or an icon.

---

## 2. Files

| Area | File |
|---|---|
| Controller | `app/Http/Controllers/Admin/DashboardController.php` |
| Prayer zone endpoint | `app/Http/Controllers/Admin/PrayerZoneController.php` |
| Holiday state endpoint | `app/Http/Controllers/Admin/HolidayStateController.php` |
| Holidays (national, state, replacements) | `app/Services/Holidays.php` |
| Hijri occasions | `app/Services/HijriCalendar.php` |
| Prayer times (JAKIM) | `app/Services/PrayerTimes.php` |
| Zone lookup | `app/Support/PrayerZones.php` |
| Settings / zone list | `config/dashboard.php` |
| Holiday data and rules | `config/holidays.php` |
| Migration | `database/migrations/2026_11_17_000001_add_prayer_zone_to_site_settings.php` |
| Views | `resources/views/admin/dashboard.blade.php`, `dashboard-calendar.blade.php`, `dashboard-prayer.blade.php`, `dashboard-status.blade.php` |
| Components | `resources/views/components/stat-tile.blade.php`, `icon.blade.php`, `analytics-bar.blade.php` (extended) |
| Chart | `resources/js/dashboard-chart.js` (registered in `resources/js/app.js`) |
| Tests | `tests/Feature/Admin/DashboardRedesignTest.php` (plus the existing `DashboardStatusTest.php`) |

Routes (admin only): `PATCH /admin/dashboard/prayer-zone`, `PATCH /admin/dashboard/holiday-state`.

---

## 3. Data sources

| Data | Source | Cache | If unavailable |
|---|---|---|---|
| Prayer times, official Hijri date | JAKIM e-Solat API (`e-solat.gov.my`) | 15 min | "Today's JAKIM times are unavailable" |
| National and regional holidays | Public Malaysia holiday calendar feed (iCalendar), URL in `HOLIDAY_FEED_URL` | 12 h (10 min after a failure) | "Holiday calendar is unavailable right now. It will retry in a few minutes." |
| State fixed-date holidays | `config/holidays.php` (curated by hand) | none | Empty-state message |
| Hijri occasions | Calculated with PHP `intl` (Umm al-Qura), shifted to match JAKIM's Hijri date for today | none | Notice that the `intl` extension is missing |

No personal or site data is sent to any of these services: they are plain `GET` requests made from the server. Nothing is loaded by the visitor's browser, so the CSP is unchanged.

Why a curated state list: the free holiday APIs checked (Nager.Date has no Malaysian data) and the feed itself do not say which state a "regional" holiday belongs to. State assignments therefore live in config where they can be reviewed and edited.

---

## 4. Prayer zone

The **Waktu solat** card has a **Change zone** control: a select grouped by state with all JAKIM zones (`config/dashboard.php`). Saving it:

- stores the zone in `site_settings.prayer_zone`,
- changes the prayer times shown,
- changes the **first state** for holidays (zone → state, see `state_by_zone_prefix` in `config/holidays.php`).

If nothing has been chosen, `JAKIM_PRAYER_ZONE` from `.env` is used (default `WLY01`). Invalid or unknown codes are rejected server-side.

Examples: **Shah Alam = `SGR01`** (Selangor). Kuala Lumpur/Putrajaya = `WLY01`, Labuan = `WLY02`.

---

## 5. Holidays

### 5.1 National holidays ("Next public holidays")

Taken from the feed, but **only entries named in `config/holidays.php → national`** are kept: Tahun Baru Cina, Hari Raya Aidilfitri (and day 2), Hari Pekerja, Hari Raya Aidiladha, Hari Wesak, Awal Muharram, Hari Keputeraan Yang di-Pertuan Agong, Maulidur Rasul, Hari Kebangsaan, Hari Malaysia, Deepavali, Hari Krismas. Observances (Valentine's Day, Easter Sunday, Christmas Eve, New Year's Eve) are ignored. Dates the feed marks "(tentative)" show an amber **tentative** label.

### 5.2 State holidays (up to two states)

The card shows the zone's state **plus an optional second state**. With Shah Alam you get Selangor; use **Choose states → Also show holidays for → Kuala Lumpur / Putrajaya** to show both. A holiday that applies to both appears **once**, tagged with both states, sorted soonest first.

State rows come from two places:

1. **Fixed-date state holidays** in `config/holidays.php → state`, for example Hari Keputeraan Sultan Selangor (11 Dec) and Hari Wilayah Persekutuan (1 Feb).
2. **Regional feed holidays** mapped to states in `config/holidays.php → regional`:

| Feed name | Shown as | States |
|---|---|---|
| New Year's Day | Tahun Baru | Selangor, KL/Putrajaya, Labuan |
| Thaipusam | Thaipusam | Selangor, KL/Putrajaya, Labuan |
| Second Day of Chinese New Year | Tahun Baru Cina (hari kedua) | Selangor, KL/Putrajaya, Labuan |
| Nuzul Al-Quran | Nuzul Al-Quran | Selangor, KL/Putrajaya, Labuan |
| Isra and Mi'raj | Israk dan Mikraj | Selangor |

> **New Year's Day** was previously missing because the feed labels it only "(regional holiday)" and the first version kept national holidays only. It is now mapped as above.

### 5.3 Replacement holidays (cuti ganti)

When a public holiday falls on the weekly rest day, the next working day is added as its own row, labelled **replacement**, named `Cuti ganti <holiday>`:

| State group | Rest day that triggers a replacement | Weekend skipped |
|---|---|---|
| Most states, KL, Putrajaya, Labuan | Sunday | Saturday and Sunday |
| Kedah, Kelantan, Terengganu | Friday | Friday and Saturday (replacement lands on Sunday) |

Rules the code applies:

- The replacement is the next day that is **not a weekend day and not already a holiday**, so a Sunday holiday followed by a Monday holiday is replaced on Tuesday.
- A holiday on **Saturday is not replaced**.
- Replacements are calculated per state; national replacements use the prayer-zone state's weekend.
- The feed's own replacement entries ("Day off for Thaipusam", "… observed", "… holiday") are **ignored** so replacements are computed in one consistent way.
- A replacement inherits the **tentative** flag of the holiday it replaces.
- Replacements are **estimates until gazetted**. The Government can declare a different day.

Live example (as of 2026-11-17): Chinese New Year day 2 falls on Sunday 7 Feb 2027, so Monday 8 Feb 2027 appears as *Cuti ganti Tahun Baru Cina (hari kedua)*.

---

## 6. Hijri calendar

Shows today's official Hijri date from JAKIM (e.g. *7 Jamadilakhir 1448 H*) and the next four occasions: Awal Muharram, Maulidur Rasul (12 Rabiulawal), Israk dan Mikraj, Nisfu Syaaban, Awal Ramadan, Nuzul Al-Quran, Hari Raya Aidilfitri, Hari Arafah and Hari Raya Aidiladha.

How dates are produced: each occasion's Hijri date is converted to a Gregorian date with PHP `intl`, then shifted by the difference between that calendar and JAKIM's date for today. This tracks Malaysian moon-sighting as closely as a calculation can, but **dates are estimates and can differ by a day** from the announcement by the Keeper of the Rulers' Seal. The card says so.

These occasions can also appear in the holiday lists (for example Maulidur Rasul is both a Hijri occasion and a national holiday). That is intentional: the Hijri card is a religious calendar, the holiday cards are what is gazetted.

---

## 7. Configuration cheat sheet

| I want to… | Do this |
|---|---|
| Change prayer zone / state | Dashboard → Waktu solat → **Change zone** |
| Show a second state's holidays | Dashboard → State holidays → **Choose states** |
| Add a fixed-date state holiday | `config/holidays.php → state`: `'MY-xx' => [[month, day, 'Name']]` |
| Show a feed holiday in another state | `config/holidays.php → regional`: add the state code to `states` |
| Show another regional feed holiday | Add its exact feed name (without the "(regional holiday)" suffix) to `regional` |
| Treat another feed holiday as national | Add it to `national` |
| Change a state's weekend | `config/holidays.php → weekend_days` |
| Use a different feed | `HOLIDAY_FEED_URL` in `.env` |
| Set the default zone before a choice is made | `JAKIM_PRAYER_ZONE` in `.env` |

State codes: Johor `MY-01`, Kedah `MY-02`, Kelantan `MY-03`, Melaka `MY-04`, Negeri Sembilan `MY-05`, Pahang `MY-06`, Pulau Pinang `MY-07`, Perak `MY-08`, Perlis `MY-09`, Selangor `MY-10`, Terengganu `MY-11`, Sabah `MY-12`, Sarawak `MY-13`, KL/Putrajaya `MY-14`, Labuan `MY-15`.

After editing config on the server run `php artisan config:clear` (or the deploy script's cache step) and, if the feed mapping changed, clear the cache entry: `php artisan cache:forget holidays:feed:v2`.

---

## 8. Deployment checklist

1. On your PC: `npm run build` (this also refreshes `public/build`, which is committed because production has no Node).
2. Make sure `public/hot` does **not** exist (see Troubleshooting).
3. `php artisan test` — everything must pass.
4. Commit, including `public/build`, the new migration, `config/holidays.php`, and the two new controllers.
5. Push, then run `~/deploy-landingpage.sh` in cPanel Terminal.
6. Run the migration: `php artisan migrate --force`. It adds two nullable columns to `site_settings` (`prayer_zone`, `holiday_extra_state`), so it is safe and reversible. Until it runs, the dashboard falls back to `JAKIM_PRAYER_ZONE` and shows no second state; nothing breaks.
7. Open `/admin`, then in **Waktu solat** choose **Change zone → SGR01** and in **State holidays** choose **Kuala Lumpur / Putrajaya** (the choices are stored in the database, so you make them once per environment; your local database already has them).
8. Check that PHP `intl` is enabled on the host (`php -m | grep intl`). If not, the Hijri card shows a notice. Ask the host to enable it.

---

## 9. Tests

`tests/Feature/Admin/DashboardRedesignTest.php` covers:

- feed parsing: gazetted-only filtering, tentative flags, ignoring observances and the feed's own replacement entries
- feed outage and JAKIM outage degrade gracefully (the missing-`intl` notice is not covered by a test)
- prayer-zone change: validation (unknown, empty, injection-like, over-long), admin-only
- zone → state mapping for every configured zone
- two selected states: merged rows, tags, order, clearing the second state, ignoring a duplicate of the first
- regional holidays: New Year / Thaipusam for KL and Selangor, Israk Mikraj for Selangor only, none for other states
- replacements: Sunday → Monday, skipping a holiday Monday, no replacement for Saturday, Kelantan/Kedah/Terengganu Friday rule, dashboard label
- Hijri: ordering, alignment to JAKIM's date, works without JAKIM
- page content, empty states, admin-only access

Run: `php artisan test tests/Feature/Admin/DashboardRedesignTest.php`.

---

## 10. Troubleshooting

| Symptom | Cause and fix |
|---|---|
| Admin or login page unstyled, giant logo | A Vite dev server is running and left `public/hot`. The page then loads CSS from `http://[::1]:5173`, which the HTTPS site's CSP and mixed-content rules block. Stop `npm run dev` and delete `public/hot`. |
| Traffic chart blank | JavaScript did not load: run `npm run build`, check `public/build` is deployed, look at the browser console. |
| "Holiday calendar is unavailable" | The feed could not be fetched. It retries every 10 minutes; check outbound HTTPS from the server. |
| A holiday you expect is missing | It is probably "(regional holiday)" in the feed and not mapped to your state. Add it under `regional` (section 7). |
| A date is one day off | Hijri and "tentative" dates follow moon sighting. Compare with the official announcement. |
| Wrong state holidays | Check the prayer zone, then the second-state option. |
| "Hijri calendar" shows an `intl` notice | The PHP `intl` extension is disabled on the server. |

---

## 11. Known limitations

- State coverage is **curated for Selangor and Kuala Lumpur/Putrajaya/Labuan**. Other states only show the fixed-date holidays listed in `config/holidays.php`; extend them after checking the gazette.
- The holiday feed is a third-party calendar and may lag official announcements. Tentative dates are labelled.
- Replacement days follow the Holidays Act rule as implemented above; the Government may gazette a different day.
- Hijri occasions are calculated estimates, not announcements.
- The traffic chart treats today as a partial day and says so. The first 60 days of page-view history drive the previous-period comparison.

---

## 12. Checklists (`/admin/checklists`)

An admin page for personal lists such as **Work checklist** and **Marriage preparation**. Lists are private by default and can be shared through a personalised link (section 12.1).

- **Lists and items:** create any number of lists; each item has a title, an optional **link** and an optional note. Tick items off, edit or delete them, and use **Uncheck all** to reuse a recurring list. Each list shows its progress (e.g. `4/12`).
- **Starter templates:** *Work checklist*, *Marriage preparation*, *Trip / travel* and *Home* pre-fill sensible items. Edit them in `config/checklists.php` (an item is a string or `[title, url, note]`). The marriage template is generic, so check your own state and religious-office requirements.
- **Links:** only `http://` and `https://` are accepted (anything else, such as `javascript:`, is rejected). Links open in a new tab with `rel="noopener noreferrer"`. All text is escaped.
- **Where it appears:** *Manage → Checklists* in the navigation, and a **Checklists** shortcut on the dashboard showing the number of open items.
- **Data:** tables `checklists` and `checklist_items` (items are removed with their list). Migrations `2026_11_17_100000_create_checklists_tables.php` and `2026_11_17_110000_add_sharing_to_checklists.php`; run `php artisan migrate --force` on deploy.
- **Files:** `app/Http/Controllers/Admin/ChecklistController.php`, `app/Models/Checklist.php`, `app/Models/ChecklistItem.php`, `app/Http/Requests/Admin/*Checklist*Request.php`, `resources/views/admin/checklists/index.blade.php`, `config/checklists.php`.
- **Tests:** `tests/Feature/Admin/ChecklistTest.php`.
- **Not included (by design, to keep it simple):** drag-and-drop ordering, due dates, per-person accounts for shared lists. Items keep the order they were added in.
- **Ticking is instant:** ticking an item updates the page in place and saves in the background (`resources/js/checklists.js`). If JavaScript or the request fails it reverts and falls back to a normal form post, so nothing is lost.

### 12.1 Sharing a checklist with someone (personalised link)

Every checklist always has a **slug**, generated from its title plus a short random suffix (for example `our-wedding-x7k2q`), or chosen by you when creating the list. In **Share** on a list you can:

| Setting | Effect |
|---|---|
| **Personalised link** (required) | The slug in `https://your-site/checklist/<slug>`. Lowercase letters, numbers and single dashes, 3 to 60 characters, unique. Typing `Our Wedding 2027!` becomes `our-wedding-2027`. |
| **Anyone with the link can view** | Publishes a read-only page. Off = the link returns 404 (a private list is indistinguishable from one that does not exist). |
| **...and they can tick items off** | Visitors get real checkboxes and each tick saves instantly. They can **never add, edit or delete** items, change the list or change sharing. |

The admin page shows a **Shared · can tick** or **Shared · view only** badge, the full link, and a **Copy link** button. Anything a visitor ticks is visible to you on the admin page.

Security notes:

- The link is the only protection: **anyone who has it can open it, and tick items if ticking is on.** Share it only with people you trust, and prefer a longer, less guessable slug. Turning sharing off closes the link immediately.
- Visitors can only tick items belonging to the list in the link; ids from other lists return 404. The public toggle is rate limited (120/min), the page is `noindex, nofollow` with `X-Robots-Tag`, links open with `rel="noopener noreferrer nofollow"` and all text is escaped.
- There are no per-person identities: a tick does not record who did it.
- Files: `app/Http/Controllers/PublicChecklistController.php`, `app/Http/Requests/Admin/ChecklistSharingRequest.php`, `resources/views/checklist-public.blade.php`. Migration `2026_11_17_110000_add_sharing_to_checklists.php` adds `slug`, `is_public`, `allow_ticking` and backfills slugs for existing lists (all private).
