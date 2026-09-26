#!/usr/bin/env bash
#
# Deploys the latest main branch on the JimatHosting server.
# Run it in cPanel Terminal:  ~/deploy-landingpage.sh
# (a symlink to this file; see DEPLOYMENT.md)
#
# Before running it, on the PC: npm run build, commit (including
# public/build), git push. The server has no npm, so the built CSS/JS
# come from git; public_html/build is a symlink to public/build, so
# they go live as soon as they're pulled.
#
# Everything runs inside main(), which bash parses in full before
# running, so git pull replacing this file mid-run is harmless.

main() {
    set -euo pipefail

    local APP="$HOME/alieimran-landingpage"
    local WEB="$HOME/public_html"

    cd "$APP"
    echo "==> Deploying $(basename "$APP")"

    if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
        echo "!! Tracked files were edited on the server. Nothing was changed."
        git status --short --untracked-files=no
        exit 1
    fi

    # One-time switch-over: public/build used to be uploaded by hand,
    # untracked. Git refuses to pull over untracked files, so move the
    # old copy aside (it's replaced by git a moment later).
    if [ -d public/build ] && ! git ls-files --error-unmatch public/build/manifest.json >/dev/null 2>&1; then
        local backup="$HOME/build-manual-backup-$(date +%Y%m%d%H%M%S)"
        echo "==> Moving hand-uploaded public/build to $backup"
        mv public/build "$backup"
    fi

    local before
    before=$(git rev-parse HEAD)

    echo "==> git pull"
    git pull --ff-only origin main

    local after
    after=$(git rev-parse HEAD)

    if [ "$before" = "$after" ]; then
        echo "==> Already up to date ($(git log -1 --format='%h %s'))"
    else
        echo "==> Changes pulled:"
        git log --format='    %h %s' "$before..$after"
    fi

    if ! git diff --quiet "$before" "$after" -- composer.lock || [ ! -d vendor ]; then
        echo "==> composer install"
        composer install --no-dev --optimize-autoloader --no-interaction
    fi

    # config:clear first: a stale config cache once made migrate fail
    # with "Access denied".
    php artisan config:clear
    php artisan migrate --force

    echo "==> Rebuilding caches"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    # public_html holds copies of public/ files, not a checkout, so
    # changes to them don't go live by themselves. Warn, don't copy:
    # index.php there has edited paths and must never be overwritten.
    local f
    for f in .htaccess robots.txt favicon.ico favicon.svg favicon-32.png apple-touch-icon.png; do
        if [ -f "public/$f" ] && ! cmp -s "public/$f" "$WEB/$f"; then
            echo "!! public/$f differs from public_html/$f. Copy it by hand if the change should go live."
        fi
    done

    for f in build storage; do
        if [ ! -L "$WEB/$f" ]; then
            echo "!! public_html/$f is not a symlink. It should point into $APP."
        fi
    done

    echo "==> Done: $(git log -1 --format='%h %s')"
}

main "$@"
