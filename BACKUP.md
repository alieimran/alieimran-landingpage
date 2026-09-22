# Backup & Restore

## What needs backing up

| What | Where | Why |
|---|---|---|
| Database | MariaDB `alieimran_landingpage` | All content: profile, links, social links, contact inquiries, digital card, theme settings, site sections, analytics data |
| Uploaded media | `storage/app/public/` | Link images, profile photo, theme logo/favicon — not in the database, only referenced by path from it |
| `.env` | project root, **not in git** | Database credentials, app key, mail credentials — losing this without a copy means re-deriving every production secret from scratch |
| Application source | git | Already versioned; a git remote (GitHub/GitLab/etc.) is itself a backup of the source, separate from the two items above which git doesn't track |

Application source is the *one* thing on this list that's already durably backed up by normal development practice (commit + push). The other three are not, and need an explicit backup step.

## Database backup

```bash
mysqldump -u <user> -p --single-transaction alieimran_landingpage > backup_$(date +%Y%m%d_%H%M%S).sql
```

`--single-transaction` avoids locking tables during the dump on InnoDB (this app's tables are InnoDB by default under Laravel's migrations) — safe to run against a live database without downtime.

For production (cPanel), either:
- Use cPanel's own "Backup" tool (usually schedulable, stores to the account or a remote destination)
- Or run the same `mysqldump` command via SSH (see `CPANEL_DEPLOYMENT.md` for SSH access details) and transfer the resulting file off-server

**Compress and store off-server** — a backup that lives on the same disk as the data it's backing up doesn't survive that disk failing:

```bash
gzip backup_20260922_120000.sql
scp -P 222 backup_20260922_120000.sql.gz <user>@<host>:~/  # or wherever it actually needs to go
```

## Media backup

```bash
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public/
```

Same principle: move the resulting archive off the server it was taken from.

## `.env` backup

Store production `.env` contents somewhere secure and separate from the application server itself — a password manager, an encrypted secrets store, or at minimum an access-controlled private location. **Never commit it to git**, even to a private repository — see `.gitignore` and `SECURITY.md`'s note on why `APP_DEBUG`/credential exposure matters.

## Restore procedure

### Database

```bash
gunzip backup_20260922_120000.sql.gz
mysql -u <user> -p alieimran_landingpage < backup_20260922_120000.sql
```

This overwrites existing data in that database — confirm you're restoring into the intended database, not accidentally the live one, if testing a restore.

### Media

```bash
tar -xzf storage_backup_20260922.tar.gz -C /path/to/app/
php artisan storage:link   # re-create the public symlink if it's not already present
```

### `.env`

Copy the securely-stored contents back to `.env` in the application root, then:

```bash
php artisan config:clear   # if a cached config existed from before the restore
```

### Full disaster recovery (new server)

1. Deploy the application from git (see `DEPLOYMENT.md` / `CPANEL_DEPLOYMENT.md`)
2. Restore `.env`
3. Restore the database (above)
4. Restore `storage/app/public/` (above)
5. Run `php artisan storage:link`
6. Verify: homepage loads, admin login works, a known link/social-link renders with its image, the digital card page (if enabled) still shows correctly

## Backup schedule

Not yet automated. Recommended minimum once this goes to production: daily database dump, weekly media archive, both retained for at least 30 days and stored somewhere other than the production server itself. cPanel's built-in backup scheduler (if available on this host) is the simplest way to achieve this without custom cron scripting — confirm availability and configure it as part of the first production deployment, and update this file once done.
