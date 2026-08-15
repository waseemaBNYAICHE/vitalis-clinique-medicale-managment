#!/bin/sh
# node_modules lives in a named volume, so it can lag behind package.json after a
# dependency change. Reconcile it on every boot (a no-op when already in sync).
set -e

cd /app

if [ ! -d node_modules ] || [ -z "$(ls -A node_modules 2>/dev/null)" ]; then
    echo "[entrypoint] node_modules empty, installing dependencies"
    npm install --no-audit --no-fund
elif [ package.json -nt node_modules ]; then
    echo "[entrypoint] package.json changed, syncing dependencies"
    npm install --no-audit --no-fund
fi

exec "$@"
