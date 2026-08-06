# Laravel + Vue Docker Development Environment

Laravel API behind Nginx, a Vue 3 / Vite dev server, PostgreSQL, and Redis for
cache, sessions and queues — all wired up with Docker Compose.

| Service    | Image / stack            | URL                                              |
| ---------- | ------------------------ | ------------------------------------------------ |
| `nginx`    | nginx 1.29 alpine        | <http://localhost:8000> (Laravel API)            |
| `app`      | PHP 8.4 FPM + Laravel 13 | internal (`app:9000`)                            |
| `queue`    | same image as `app`      | internal — `php artisan queue:work redis`        |
| `frontend` | Node 22 + Vue 3 + Vite 8 | <http://localhost:5173>                          |
| `postgres` | PostgreSQL 17 alpine     | internal only — **not published to the host**    |
| `redis`    | Redis 8 alpine           | internal only — **not published to the host**    |

## Quick start

```bash
docker compose up --build -d
```

That single command is all a new developer needs. On first boot the `app`
container copies `backend/.env.example` to `backend/.env`, generates an
`APP_KEY`, waits for PostgreSQL to become healthy, and runs the migrations.

Then open:

- API — <http://localhost:8000/api/health>
- Frontend — <http://localhost:5173>

To override any default, copy the root env file first (optional):

```bash
cp .env.example .env
```

## Project layout

```
.
├── docker-compose.yml            # service, volume and network definitions
├── .env.example                  # compose-level overrides (ports, DB creds)
├── .gitignore / .dockerignore / .gitattributes
├── docker/
│   └── nginx/
│       ├── default.conf          # server block: PHP-FPM upstream, Laravel rewrites
│       └── upstream.conf         # php-fpm -> app:9000
├── backend/                      # Laravel 13
│   ├── Dockerfile                # PHP 8.4-fpm + pgsql/redis extensions + composer
│   ├── .dockerignore
│   ├── .env.example              # Laravel env template (no secrets)
│   ├── config/cors.php           # allows the Vite origin to call /api/*
│   ├── routes/api.php            # GET /api/health smoke test
│   └── docker/
│       ├── entrypoint.sh         # first-boot setup + migrations
│       ├── php.ini
│       └── www.conf              # php-fpm pool
└── frontend/                     # Vue 3 + Vite
    ├── Dockerfile                # Node 22 alpine
    ├── .dockerignore
    ├── .env.example              # VITE_API_URL
    ├── vite.config.js            # host 0.0.0.0, port 5173, polling watcher
    └── docker/entrypoint.sh      # keeps the node_modules volume in sync
```

## Configuration

Laravel reads its config from the compose `environment:` block, which takes
precedence over `backend/.env`. The defaults are:

| Variable            | Value                       |
| ------------------- | --------------------------- |
| `DB_CONNECTION`     | `pgsql`                     |
| `DB_HOST` / `PORT`  | `postgres` / `5432`         |
| `REDIS_HOST`/`PORT` | `redis` / `6379`            |
| `REDIS_CLIENT`      | `phpredis`                  |
| `CACHE_STORE`       | `redis`                     |
| `SESSION_DRIVER`    | `redis`                     |
| `QUEUE_CONNECTION`  | `redis`                     |
| `VITE_API_URL`      | `http://localhost:8000/api` |

### Secrets

No secrets are committed. `.env` files are git-ignored; only `.env.example`
templates are tracked. `APP_KEY` is generated inside the container on first
boot. The PostgreSQL password defaults to `secret` — a local development
placeholder. Override it in `.env` and use a real secret store outside your
laptop.

### Ports

PostgreSQL and Redis use `expose:` rather than `ports:`, so they are reachable
only from other containers on the `backend` network — never from the host or
the wider network.

## Volumes

| Volume                  | Purpose                                          |
| ----------------------- | ------------------------------------------------ |
| `postgres-data`         | PostgreSQL data directory (survives rebuilds)    |
| `redis-data`            | Redis AOF persistence                            |
| `frontend-node-modules` | `node_modules`, kept out of the host bind mount  |
| `backend-vendor`        | Composer `vendor/`, Linux-native binaries        |

`./backend` and `./frontend` are bind-mounted, so edits on the host apply
immediately — PHP through opcache revalidation, Vue through Vite HMR.

## Health checks

`postgres` (`pg_isready`) and `redis` (`redis-cli ping`) both declare health
checks, and `app` waits on `condition: service_healthy` for both before it
starts. `nginx` and `frontend` have HTTP checks of their own.

```bash
docker compose ps
```

## Common commands

```bash
# lifecycle
docker compose up --build -d          # build + start everything
docker compose ps                     # status and health
docker compose logs -f app            # follow one service
docker compose down                   # stop (volumes kept)
docker compose down -v                # stop and wipe all data

# laravel
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan tinker
docker compose exec app php artisan queue:work redis
docker compose exec app composer require <package>

# database / redis (no host port — go through the container)
docker compose exec postgres psql -U laravel -d laravel
docker compose exec redis redis-cli

# frontend
docker compose exec frontend npm install <package>
docker compose exec frontend npm run build
```

## Verifying the stack

```bash
curl http://localhost:8000/api/health
# {"status":"ok","services":{"database":"ok","redis":"ok"},"timestamp":"..."}
```

The Vue page at <http://localhost:5173> calls that same endpoint and renders the
result, which confirms the browser → Vite → API → PostgreSQL/Redis path end to
end.

## Troubleshooting

- **Port already in use** — set `NGINX_PORT` or `VITE_PORT` in `.env`.
- **Dependencies out of date after pulling** — `docker compose down -v` then
  `docker compose up --build -d` recreates the `vendor` and `node_modules`
  volumes.
- **Migrations did not run** — check `docker compose logs app`; set
  `RUN_MIGRATIONS=false` in `.env` to disable auto-migration.
- **HMR not firing on Windows/WSL** — the Vite watcher already polls
  (`usePolling: true` in `frontend/vite.config.js`); raise `interval` if CPU use
  is high.
- **IDE cannot resolve Laravel classes** — `vendor/` lives in a named volume, so
  the host copy is an empty mount point. Populate one for indexing with
  `docker run --rm -v "$PWD/backend:/app" -w /app composer:2 install`; it is
  git-ignored and containers keep using the volume.
