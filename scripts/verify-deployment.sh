#!/bin/sh
# Verify a deployed VITALIS production stack.
#
# Complements scripts/docker-smoke-test.sh, which targets the development stack.
# This one checks a stack started from docker-compose.prod.yml - locally, or on
# a remote host once one exists.
#
# Usage:
#   scripts/verify-deployment.sh
#       Checks the local production stack: HTTP endpoints plus in-container
#       assertions (production settings, migrations).
#
#   scripts/verify-deployment.sh --remote --api-url https://api.example \
#                                         --web-url https://example
#       HTTP checks only. Use this against a host where Docker is not reachable
#       from where the script runs.
#
# Options:
#   --api-url URL    API base URL              (default http://localhost:8080)
#   --web-url URL    Frontend base URL         (default http://localhost:8081)
#   --env-file FILE  Compose env file          (default .env.production)
#   --remote         Skip every check needing a local Docker daemon
#
# It is read-only: nothing is started, stopped, migrated or deleted.

set -eu

cd "$(dirname "$0")/.."

API_URL=http://localhost:8080
WEB_URL=http://localhost:8081
ENV_FILE=.env.production
REMOTE=no

while [ $# -gt 0 ]; do
    case $1 in
        --api-url)  API_URL=$2; shift 2 ;;
        --web-url)  WEB_URL=$2; shift 2 ;;
        --env-file) ENV_FILE=$2; shift 2 ;;
        --remote)   REMOTE=yes; shift ;;
        -h|--help)  sed -n '2,28p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
        *)          echo "unknown option: $1" >&2; exit 2 ;;
    esac
done

failures=0

step() { printf '\n=== %s\n' "$1"; }
pass() { printf '  ok    %s\n' "$1"; }
fail() { printf '  FAIL  %s\n' "$1"; failures=$((failures + 1)); }

http_code() {
    # curl already prints 000 when it cannot connect, and exits non-zero; using
    # `|| echo 000` on top of that would print 000000.
    _code=$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 "$1" 2>/dev/null) || true
    echo "${_code:-000}"
}

expect_http() {
    label=$1 url=$2 expected=$3
    code=$(http_code "$url")
    if [ "$code" = "$expected" ]; then
        pass "$label ($code)"
    else
        fail "$label ($code, expected $expected)"
    fi
}

echo "API: $API_URL"
echo "Web: $WEB_URL"
[ "$REMOTE" = yes ] && echo "Mode: remote (HTTP checks only)"

step 'API responds'
expect_http 'health route /up' "${API_URL}/up" 200

step 'Application reports its dependencies as healthy'
health=$(curl -s --max-time 10 "${API_URL}/api/health" 2>/dev/null || true)
printf '  %s\n' "${health:-<no response>}"
case "$health" in
    *'"status":"ok"'*)       pass 'database and redis reachable' ;;
    *'"status":"degraded"'*) fail 'a dependency is degraded' ;;
    *)                       fail '/api/health did not answer as expected' ;;
esac

step 'Frontend is served'
expect_http 'index'            "${WEB_URL}/"          200
expect_http 'SPA deep link'    "${WEB_URL}/dashboard" 200

body=$(curl -s --max-time 10 "${WEB_URL}/dashboard" 2>/dev/null || true)
case "$body" in
    *'<div id="app">'*) pass 'deep link falls back to index.html' ;;
    *)                  fail 'deep link does not fall back to index.html' ;;
esac

case "$body" in
    *'/assets/'*) pass 'built assets are referenced' ;;
    *)            fail 'no built asset referenced - is this a production build?' ;;
esac

step 'Debug output is not exposed'
# A production stack must not leak stack traces. Laravel answers 404 as JSON on
# /api/*; with APP_DEBUG=true it would return an HTML debug page instead.
missing=$(curl -s --max-time 10 "${API_URL}/api/route-qui-nexiste-pas" 2>/dev/null || true)
case "$missing" in
    '')
        # An empty body means nothing answered, not that the response is clean.
        fail 'no response to check for debug output' ;;
    *'Whoops'*|*'stack trace'*|*'vendor/laravel'*)
        fail 'the error response leaks debug information' ;;
    *)
        pass 'no debug information in the error response' ;;
esac

if [ "$REMOTE" = yes ]; then
    step 'Result'
    if [ "$failures" -eq 0 ]; then
        echo '  all remote checks passed'
        exit 0
    fi
    echo "  ${failures} check(s) failed"
    exit 1
fi

# ---------------------------------------------------------------- local checks
COMPOSE="docker compose -f docker-compose.prod.yml"
if [ -f "$ENV_FILE" ]; then
    COMPOSE="$COMPOSE --env-file $ENV_FILE"
else
    echo
    echo "note: $ENV_FILE not found; local checks will use compose defaults"
fi

step 'Containers'
$COMPOSE ps
for service in postgres redis app queue api web; do
    state=$($COMPOSE ps --format '{{.Service}} {{.State}}' 2>/dev/null \
            | awk -v s="$service" '$1 == s { print $2 }')
    if [ "$state" = "running" ]; then
        pass "$service is running"
    else
        fail "$service is '${state:-absent}', expected running"
    fi
done

step 'Production settings are in effect'
if $COMPOSE exec -T app php -r 'exit(ini_get("display_errors") ? 1 : 0);' 2>/dev/null; then
    pass 'display_errors is Off'
else
    fail 'display_errors is On'
fi

about=$($COMPOSE exec -T app php artisan about --json 2>/dev/null || true)
case "$about" in
    *'"environment":"production"'*) pass 'APP_ENV is production' ;;
    *)                              fail 'APP_ENV is not production' ;;
esac
case "$about" in
    *'"debug_mode":false'*) pass 'APP_DEBUG is false' ;;
    *)                      fail 'APP_DEBUG is not false' ;;
esac
case "$about" in
    *'"config":true'*) pass 'configuration is cached' ;;
    *)                 fail 'configuration is not cached' ;;
esac

if $COMPOSE exec -T app test -d vendor/phpunit 2>/dev/null; then
    fail 'development dependencies are present in the image'
else
    pass 'no development dependency in the image'
fi

step 'Schema is up to date'
if $COMPOSE exec -T app php artisan migrate:status 2>/dev/null | grep -q Pending; then
    fail 'migrations are pending'
else
    pass 'no pending migration'
fi

step 'Recent errors in the logs'
errors=$($COMPOSE logs --tail=200 2>/dev/null \
         | grep -icE 'PHP Fatal|Uncaught|\[error\]' || true)
if [ "${errors:-0}" -eq 0 ]; then
    pass 'no fatal error in the last 200 log lines'
else
    fail "${errors} error line(s) in the last 200 log lines"
fi

step 'Result'
if [ "$failures" -eq 0 ]; then
    echo '  all checks passed'
    exit 0
fi
echo "  ${failures} check(s) failed"
exit 1
