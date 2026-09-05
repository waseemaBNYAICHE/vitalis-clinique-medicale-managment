# Environnement Docker — VITALIS

Ce document décrit l'environnement Docker **de développement** réellement présent
dans le dépôt. Il ne décrit pas un environnement de production.

---

## 1. Vue d'ensemble

Le fichier [`docker-compose.yml`](../docker-compose.yml) à la racine décrit six
services répartis sur un seul réseau bridge `backend` :

```text
                     hôte
                       │
     :5173 ────────────┼──────────── :8000
       │                              │
┌──────▼──────┐                ┌──────▼──────┐
│  frontend   │                │    nginx    │
│ Vue + Vite  │                │  1.29-alpine│
└─────────────┘                └──────┬──────┘
                                      │ fastcgi :9000
                               ┌──────▼──────┐   ┌─────────────┐
                               │     app     │   │    queue    │
                               │ php-fpm 8.4 │   │ queue:work  │
                               └──────┬──────┘   └──────┬──────┘
                                      │                 │
                        ┌─────────────┴────┬────────────┘
                 ┌──────▼──────┐    ┌──────▼──────┐
                 │  postgres   │    │    redis    │
                 │ 17-alpine   │    │  8-alpine   │
                 └─────────────┘    └─────────────┘
```

Seuls `nginx` et `frontend` publient un port sur l'hôte. **PostgreSQL et Redis
ne sont pas exposés** : ils sont joignables uniquement depuis le réseau interne.

## 2. Services

| Service    | Image / build            | Port hôte | Rôle |
|------------|--------------------------|-----------|------|
| `postgres` | `postgres:17-alpine`     | —         | Base de données |
| `redis`    | `redis:8-alpine`         | —         | Cache, sessions, files d'attente |
| `app`      | build `./backend`        | —         | PHP-FPM, sert l'application Laravel |
| `queue`    | build `./backend`        | —         | Worker `php artisan queue:work` |
| `nginx`    | `nginx:1.29-alpine`      | `8000`    | Front HTTP de l'API Laravel |
| `frontend` | build `./frontend`       | `5173`    | Serveur de développement Vite |

### Dépendances de démarrage

`app` et `queue` attendent que `postgres` et `redis` soient `healthy`
(`condition: service_healthy`) avant de démarrer. `nginx` attend `app`, et
`frontend` attend `nginx`. Cinq services sur six déclarent un `healthcheck`.

### Redémarrage horaire du worker

Le service `queue` lance `queue:work --max-time=3600`. Le worker **sort
volontairement au bout d'une heure** et Docker le relance via
`restart: unless-stopped`. Un redémarrage horaire de ce conteneur est le
comportement attendu, pas un incident.

## 3. Volumes

| Volume                  | Contenu | Persistance |
|-------------------------|---------|-------------|
| `postgres-data`         | Données PostgreSQL | **Données réelles — ne pas supprimer** |
| `redis-data`            | Persistance AOF Redis | Cache, reconstructible |
| `backend-vendor`        | `vendor/` du backend | Reconstructible |
| `frontend-node-modules` | `node_modules/` du frontend | Reconstructible |

`backend/` et `frontend/` sont montés en bind depuis l'hôte pour le rechargement
à chaud. `vendor/` et `node_modules/` sont placés dans des volumes nommés afin
que les dépendances installées dans le conteneur ne soient pas masquées par
celles de l'hôte (ni l'inverse).

> `docker compose down -v` détruit `postgres-data` et donc **toutes les données
> applicatives locales**. Utiliser `docker compose down` sans `-v`.

## 4. Variables d'environnement

Toutes les variables consommées par `docker-compose.yml` ont une valeur par
défaut : `docker compose up -d --build` fonctionne sans aucun fichier `.env`.

Pour surcharger ces valeurs :

```bash
cp .env.example .env
```

Les variables disponibles sont documentées dans [`.env.example`](../.env.example)
(ports hôte, identifiants PostgreSQL, `APP_ENV`, `RUN_MIGRATIONS`, `UID`/`GID`).

Le `.env` du backend est **généré dans le conteneur** au premier démarrage à
partir de `backend/.env.example`, et `APP_KEY` y est générée par
`php artisan key:generate`. Aucun `.env` réel n'est versionné.

## 5. Démarrage

```bash
docker compose up -d --build     # construire et démarrer
docker compose ps                # état et santé des conteneurs
docker compose logs -f app       # suivre les logs d'un service
docker compose down              # arrêter en conservant les données
```

Au premier démarrage, le conteneur `app` installe les dépendances Composer,
crée `.env`, génère `APP_KEY` puis exécute les migrations. Compter quelques
minutes ; `docker compose ps` affiche `healthy` une fois le service prêt.

Points d'entrée une fois la pile démarrée :

| URL | Attendu |
|-----|---------|
| <http://localhost:8000/up>          | Health check natif de Laravel |
| <http://localhost:8000/api/health>  | JSON détaillant l'état de PostgreSQL et Redis |
| <http://localhost:5173/>            | Application Vue |

## 6. Migrations

Le conteneur `app` exécute `php artisan migrate --force` à chaque démarrage
lorsque `RUN_MIGRATIONS=true` (valeur par défaut). Après un `git pull` apportant
de nouvelles migrations, il faut donc redémarrer le service :

```bash
docker compose restart app
docker compose exec app php artisan migrate:status
```

## 7. Limites connues

- L'environnement est **orienté développement** : `APP_DEBUG=true` par défaut,
  Vite tourne en mode `dev`, les dépendances de développement Composer sont
  installées et Redis n'a pas de mot de passe (il n'est pas exposé hors du
  réseau interne).
- Aucune configuration de production n'est fournie par ce fichier compose.
