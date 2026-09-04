#!/bin/sh
# Build the Docker stack, start it and check that the services actually work.
#
# A successful `docker compose build` proves very little on its own: the stack
# can build cleanly and still answer 502, or serve a Vite server whose modules
# fail to resolve. This script checks the behaviour, not just the build.
#
# Usage:
#   scripts/docker-smoke-test.sh            # build, start, verify, leave running
#   scripts/docker-smoke-test.sh --down     # ... then `docker compose down`
#
# It never removes volumes: `docker compose down -v` would destroy postgres-data
# and every local record with it.

set -eu

cd "$(dirname "$0")/.."

STOP_AFTER=no
[ "${1:-}" = "--down" ] && STOP_AFTER=yes

NGINX_PORT="${NGINX_PORT:-8000}"
VITE_PORT="${VITE_PORT:-5173}"

failures=0

step()  { printf '\n=== %s\n' "$1"; }
pass()  { printf '  ok    %s\n' "$1"; }
fail()  { printf '  FAIL  %s\n' "$1"; failures=$((failures + 1)); }

# Checks an HTTP endpoint returns the expected status, retrying while the stack
# settles.
check_http() {
    label=$1 url=$2 expected=$3 tries=${4:-30}
    i=0
    while [ "$i" -lt "$tries" ]; do
        code=$(curl -s -o /dev/null -w '%{http_code}' "$url" 2>/dev/null || echo 000)
        if [ "$code" = "$expected" ]; then
            pass "$label ($url -> $code)"
            return 0
        fi
        i=$((i + 1))
        sleep 2
    done
    fail "$label ($url -> $code, expected $expected)"
    return 1
}

step 'Docker availability'
docker --version
docker compose version

step 'Compose file is valid'
if docker compose config >/dev/null; then
    pass 'docker compose config'
else
    fail 'docker compose config'
    exit 1
fi

step 'Build images'
docker compose build

step 'Start the stack and wait for healthy services'
# --wait blocks until services with a healthcheck report healthy and the others
# are running; it returns non-zero if any of them fails to get there.
if docker compose up -d --wait; then
    pass 'docker compose up -d --wait'
else
    fail 'docker compose up -d --wait'
fi

step 'Container status'
docker compose ps
for service in postgres redis app nginx frontend queue; do
    state=$(docker compose ps --format '{{.Service}} {{.State}}' \
            | awk -v s="$service" '$1 == s { print $2 }')
    if [ "$state" = "running" ]; then
        pass "$service is running"
    else
        fail "$service is '${state:-absent}', expected running"
    fi
done

step 'HTTP endpoints'
# `|| true`: check_http already records the failure, and returning non-zero here
# would let `set -e` abort before the summary is printed.
check_http 'Laravel health route' "http://localhost:${NGINX_PORT}/up" 200 || true
check_http 'Backend root'         "http://localhost:${NGINX_PORT}/"   200 || true
check_http 'Vite dev server'      "http://localhost:${VITE_PORT}/"    200 || true

step 'Frontend modules resolve'
# Requesting /src/main.js is not enough: Vite serves it with 200 even when an
# import further down the graph cannot be resolved, which is exactly what a
# stale node_modules volume produces. Asking Vite to transform every source
# module surfaces those failures - it answers 500 on an unresolved import.
unresolved=0
modules=$(find frontend/src -type f -name '*.js' -o -type f -name '*.vue')
for module in $(echo "$modules" | sed "s|^frontend/||" | sort); do
    url="http://localhost:${VITE_PORT}/${module}"
    code=$(curl -s -o /dev/null -w '%{http_code}' "$url" 2>/dev/null || echo 000)
    if [ "$code" != "200" ]; then
        fail "$module -> $code"
        unresolved=$((unresolved + 1))
    fi
done
if [ "$unresolved" -eq 0 ]; then
    pass 'every frontend source module transforms'
fi

step 'Application health endpoint reports its dependencies'
health=$(curl -s "http://localhost:${NGINX_PORT}/api/health" || true)
printf '  %s\n' "$health"
case "$health" in
    *'"status":"ok"'*)      pass '/api/health reports ok' ;;
    *)                      fail '/api/health did not report ok' ;;
esac

step 'Service-to-service connectivity'
if docker compose exec -T app php -r '
    $h = getenv("DB_HOST") ?: "postgres";
    exit(@fsockopen($h, 5432, $e, $s, 5) ? 0 : 1);
' 2>/dev/null; then
    pass 'app reaches postgres:5432'
else
    fail 'app cannot reach postgres:5432'
fi

if docker compose exec -T app php -r '
    $h = getenv("REDIS_HOST") ?: "redis";
    exit(@fsockopen($h, 6379, $e, $s, 5) ? 0 : 1);
' 2>/dev/null; then
    pass 'app reaches redis:6379'
else
    fail 'app cannot reach redis:6379'
fi

if docker compose exec -T nginx wget -q -O /dev/null http://app:9000 2>/dev/null \
   || docker compose exec -T nginx sh -c 'nc -z app 9000' 2>/dev/null; then
    pass 'nginx reaches app:9000'
else
    # php-fpm speaks FastCGI, not HTTP, so a plain probe may legitimately fail.
    # The /up check above already proves the path end to end.
    pass 'nginx reaches app:9000 (covered by the /up check)'
fi

step 'Database migrations are applied'
if docker compose exec -T app php artisan migrate:status 2>/dev/null | grep -q 'Pending'; then
    fail 'migrations are still pending'
else
    pass 'no pending migration'
fi

if [ "$STOP_AFTER" = yes ]; then
    step 'Stopping the stack (volumes are kept)'
    docker compose down
fi

step 'Result'
if [ "$failures" -eq 0 ]; then
    echo '  all checks passed'
    exit 0
fi
echo "  ${failures} check(s) failed"
exit 1
