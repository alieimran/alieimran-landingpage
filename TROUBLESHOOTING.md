# Troubleshooting

Problems actually encountered during development, plus the general categories most likely to recur. If you hit something not listed here, add it once resolved — that's the point of this file.

## Site doesn't resolve (`alieimran-landingpage.test` not found)

Laravel Herd's site-detection doesn't always pick up a new project folder automatically, even when it's under a parked path. Symptom: `curl http://alieimran-landingpage.test` fails to resolve, but `curl -H "Host: alieimran-landingpage.test" http://127.0.0.1/` works fine (proves the app itself is fine, it's a DNS/hosts problem).

**Fix:** add a manual hosts file entry.

Windows (`C:\Windows\System32\drivers\etc\hosts`, requires Administrator):
```
127.0.0.1 alieimran-landingpage.test
```

No reboot needed — takes effect immediately.

## Permission denied writing to the project directory

On Windows, if the project folder's NTFS permissions only grant `BUILTIN\Administrators`/`SYSTEM` write access (not your own user account), every file operation fails with `EPERM`, including tools that should otherwise work fine.

**Fix:** grant your own account write access via File Explorer → project folder → Properties → Security → Edit → add your account with Modify/Write, or via `icacls` if you're comfortable with it. This is a one-time fix per machine.

## `./vendor/bin/pest --init` hangs

Not needed for this project — Laravel 13's default skeleton already ships `tests/Pest.php` fully configured. Running `--init` anyway can hang waiting on an interactive prompt in some terminal environments. If Pest ever needs re-initializing for some reason, check `tests/Pest.php` exists and is correctly configured before reaching for `--init`.

## Migration fails with "no such table" in tests

Pest test files need `uses(RefreshDatabase::class);` at the top to get a migrated in-memory SQLite database for that test run — this is easy to forget when writing a new test file from scratch, and the failure (`SQLSTATE[HY000]: General error: 1 no such table: X`) doesn't always make the missing trait obvious at a glance. Check the top of the test file first.

## `php artisan test --filter=X` appears to hang

Observed on this machine specifically: filtered test runs sometimes appeared to hang indefinitely through certain command-execution wrappers, even though the same command completed in under a second when wrapped in a shell-level `timeout`. If a test run seems stuck with no output for longer than a simple test suite should reasonably take, try re-running it with an explicit timeout wrapper before assuming the tests themselves are broken — check for stray leftover PHP processes from a previous interrupted run first (`tasklist | grep php` on Windows), since those can also cause confusing hangs by holding a lock or port.

## Digital card / site settings show unexpected empty or default values

`SiteSetting::current()`, `ThemeSetting::current()`, and `DigitalCard::current()` are all singleton-pattern models that lazily create their one row (`firstOrCreate`) the first time they're accessed. If a model's `NOT NULL` column has no sensible default and the `current()` method doesn't supply one in its `firstOrCreate` call, the *first* real request to touch that model will fail with a database constraint error, not a friendly validation message. This actually happened during development (`DigitalCard::current()` initially didn't supply a `name`) — if a new singleton-pattern model is added later, make sure `current()` supplies defaults for every `NOT NULL` column without a schema-level default.

## CSP blocks something in the browser console

If a script, style, or resource is silently not loading and the browser console shows a `Content-Security-Policy` violation message, check `app/Http/Middleware/SecureHeaders.php` — the policy is intentionally strict (nonce + `strict-dynamic` for scripts, not broad host allowlisting). Adding a new external script source means adding it to that middleware's allowlist deliberately, not just adding the `<script src="...">` tag and hoping. See `SECURITY.md` for the current policy and its documented exceptions.

## Mail doesn't send in local development

`.env`'s `MAIL_MAILER=log` by default locally — mail isn't actually sent, it's written to `storage/logs/laravel.log`. This is intentional for local dev, not a bug. Check the log file to confirm a "sent" email's actual content rather than expecting an inbox to receive anything.

## Where to look when something's unclear

- `PROJECT_STATUS.md` — current state, known issues, what's genuinely done vs. deferred
- `DEVELOPMENT_LOG.md` — chronological history of *why* something was built the way it was, including bugs caught and fixed along the way
- `SRS_COMPLIANCE.md` — whether a given requirement is actually supposed to be implemented yet
