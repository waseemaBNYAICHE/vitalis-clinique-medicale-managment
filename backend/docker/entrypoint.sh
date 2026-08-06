#!/bin/sh
# Prepares the Laravel application before handing control to php-fpm / artisan.
#
# CONTAINER_ROLE=app    -> performs first-run setup (deps, .env, APP_KEY, migrations)
# CONTAINER_ROLE=queue  -> waits until the `app` container has finished that setup
set -e

APP_DIR=/var/www/html
ROLE="${CONTAINER_ROLE:-app}"

cd "$APP_DIR"

log() { echo "[entrypoint][$ROLE] $*"; }

app_is_ready() {
    [ -f vendor/autoload.php ] && [ -f .env ] && grep -q '^APP_KEY=base64:' .env 2>/dev/null
}

ensure_writable_dirs() {
    mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/testing \
        storage/logs \
        bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
    chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
}

if [ "$ROLE" = "app" ]; then
    # A bind mount from a fresh clone has no vendor/ - install on first boot.
    if [ ! -f vendor/autoload.php ]; then
        log "vendor/ missing, running composer install (first boot may take a minute)"
        composer install --no-interaction --prefer-dist --no-progress
    fi

    if [ ! -f .env ]; then
        log "creating .env from .env.example"
        cp .env.example .env
    fi

    # APP_KEY is generated locally and never committed.
    if ! grep -q '^APP_KEY=base64:' .env; then
        log "generating APP_KEY"
        php artisan key:generate --force --no-ansi
    fi

    ensure_writable_dirs

    # Config is read from the compose environment; stale caches would mask it.
    php artisan config:clear --no-ansi >/dev/null 2>&1 || true
    php artisan route:clear  --no-ansi >/dev/null 2>&1 || true

    if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
        log "waiting for database ${DB_HOST:-postgres}:${DB_PORT:-5432}"
        until php -r '
            $h = getenv("DB_HOST") ?: "postgres";
            $p = getenv("DB_PORT") ?: "5432";
            exit(@fsockopen($h, (int) $p, $e, $s, 2) ? 0 : 1);
        '; do
            sleep 1
        done

        log "running migrations"
        php artisan migrate --force --no-ansi
    fi

    log "ready"
else
    log "waiting for the app container to finish setup"
    while ! app_is_ready; do
        sleep 2
    done
    ensure_writable_dirs
    log "ready"
fi

exec "$@"
