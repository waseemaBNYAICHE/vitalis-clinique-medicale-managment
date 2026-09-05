#!/bin/sh
# Production entrypoint for the Laravel container.
#
# Unlike the development entrypoint, this one never installs dependencies, never
# writes a .env and never generates an APP_KEY: a key generated at boot would be
# different on every deployment and on every replica, which would invalidate all
# sessions and make previously encrypted values unreadable.
set -e

APP_DIR=/var/www/html
ROLE="${CONTAINER_ROLE:-app}"

cd "$APP_DIR"

log() { echo "[entrypoint][$ROLE] $*"; }

if [ -z "${APP_KEY:-}" ]; then
    log "APP_KEY is not set - refusing to start"
    log "generate one with: php artisan key:generate --show"
    exit 1
fi

mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

log "waiting for database ${DB_HOST:-postgres}:${DB_PORT:-5432}"
i=0
until php -r '
    $h = getenv("DB_HOST") ?: "postgres";
    $p = getenv("DB_PORT") ?: "5432";
    exit(@fsockopen($h, (int) $p, $e, $s, 2) ? 0 : 1);
'; do
    i=$((i + 1))
    if [ "$i" -ge 60 ]; then
        log "database still unreachable after 60 attempts - giving up"
        exit 1
    fi
    sleep 2
done

# Migrations are opt-in: running them automatically on every deployment means a
# schema change can start before the operator is ready for it, and concurrent
# replicas would race each other.
if [ "${RUN_MIGRATIONS:-false}" = "true" ] && [ "$ROLE" = "app" ]; then
    log "running migrations"
    php artisan migrate --force --no-ansi
fi

# Caches are built at boot rather than at build time: they capture the runtime
# environment, which is not known while the image is being built.
if [ "$ROLE" = "app" ]; then
    log "caching configuration, routes and views"
    php artisan config:cache --no-ansi
    php artisan route:cache --no-ansi
    php artisan view:cache --no-ansi
fi

log "ready"

exec "$@"
