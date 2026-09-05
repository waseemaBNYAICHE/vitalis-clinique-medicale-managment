# Déploiement — VITALIS

Ce document décrit l'environnement de déploiement **conteneurisé** fourni par le
dépôt, et les variables qu'il faut lui donner.

> **Aucune infrastructure cible n'est rattachée à ce projet.** Il n'existe ni
> serveur, ni fournisseur cloud, ni registre d'images configuré. Ce qui suit
> décrit une pile de production reproductible et les secrets qu'elle réclame ;
> rien n'a été déployé sur une machine distante.

---

## 1. Ce que contient la pile de production

[`docker-compose.prod.yml`](../docker-compose.prod.yml) définit six services :

| Service    | Image                       | Rôle |
|------------|-----------------------------|------|
| `postgres` | `postgres:17-alpine`        | Base de données, volume `postgres-data` |
| `redis`    | `redis:8-alpine`            | Cache, sessions, files d'attente — **authentifié** |
| `app`      | `backend/Dockerfile.prod`   | PHP-FPM, code figé dans l'image |
| `queue`    | même image que `app`        | Worker `queue:work` |
| `api`      | `nginx:1.29-alpine`         | Front HTTP de l'API |
| `web`      | `frontend/Dockerfile.prod`  | Assets Vue compilés, servis en statique |

Seuls `api` et `web` publient un port. `postgres` et `redis` restent internes au
réseau `backend`.

### Différences avec l'environnement de développement

| | Développement | Production |
|---|---|---|
| Frontend | serveur Vite (`npm run dev`) | build statique servi par nginx |
| Code backend | bind mount depuis l'hôte | figé dans l'image |
| Dépendances Composer | avec les paquets de dev | `--no-dev`, autoloader `classmap-authoritative` |
| Erreurs PHP | affichées dans la réponse | journalisées uniquement (`display_errors = Off`) |
| Opcache | revalide à chaque requête | `validate_timestamps = 0` |
| `APP_DEBUG` | `true` | `false` |
| `APP_KEY` | générée au premier démarrage | **exigée** dans l'environnement |
| Migrations | jouées à chaque démarrage | `RUN_MIGRATIONS=false` par défaut |
| Redis | sans mot de passe | mot de passe obligatoire |

---

## 2. Variables d'environnement

Le modèle complet est dans [`.env.production.example`](../.env.production.example).
Aucune variable de production n'a de valeur par défaut : Compose **refuse de
démarrer** si l'une manque, plutôt que de retomber sur une valeur connue.

### Publiques — pas des secrets

| Variable | Rôle |
|---|---|
| `COMPOSE_PROJECT_NAME` | Préfixe des conteneurs et volumes |
| `APP_URL` | URL publique de l'API |
| `VITE_API_URL` | URL de l'API **inlinée dans le bundle JavaScript** au build : lisible par tout visiteur, ne doit donc jamais contenir de secret |
| `API_PORT`, `WEB_PORT` | Ports publiés sur l'hôte |
| `RUN_MIGRATIONS` | `true` pour appliquer les migrations au démarrage |

### Sensibles — à fournir par un gestionnaire de secrets

| Variable | Rôle | Conséquence d'une fuite ou d'un changement |
|---|---|---|
| `APP_KEY` | Clé de chiffrement Laravel | Une fuite compromet sessions et valeurs chiffrées ; **la changer rend illisibles** les données déjà chiffrées |
| `DB_PASSWORD` | Mot de passe PostgreSQL | Accès complet aux données médicales |
| `REDIS_PASSWORD` | Mot de passe Redis | Accès aux sessions et aux jobs |
| `DB_DATABASE`, `DB_USERNAME` | Identifiants de la base | Peu sensibles isolément, mais doivent correspondre à la base provisionnée |

### Comment les fournir

Par ordre de préférence :

1. Le gestionnaire de secrets de la plateforme cible (secret manager cloud,
   `systemd` credentials, secrets Docker Swarm / Kubernetes).
2. Les **GitHub Actions secrets**, si le déploiement est automatisé depuis CI.
3. En dernier recours, un fichier `.env.production` sur la machine cible, en
   `chmod 600`, hors du dépôt.

`.gitignore` ne laisse passer que les modèles `.env.*.example` ; tout fichier
`.env.production` réel reste ignoré. Vérification :

```bash
git check-ignore -v .env.production   # doit renvoyer une règle
```

---

## 3. Démarrer la pile

```bash
cp .env.production.example .env.production
# renseigner APP_KEY, DB_PASSWORD, REDIS_PASSWORD

docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
docker compose -f docker-compose.prod.yml --env-file .env.production ps
```

Générer une `APP_KEY` :

```bash
docker compose exec app php artisan key:generate --show
```

### Migrations

Elles ne sont pas appliquées automatiquement. Deux possibilités :

```bash
# soit en le demandant explicitement au démarrage
RUN_MIGRATIONS=true docker compose -f docker-compose.prod.yml --env-file .env.production up -d

# soit à la main, en gardant la main sur le moment
docker compose -f docker-compose.prod.yml --env-file .env.production \
  exec app php artisan migrate --force
```

Ce choix est délibéré : migrer automatiquement à chaque déploiement fait partir
un changement de schéma sans que l'opérateur l'ait décidé, et deux réplicas
démarrant ensemble se disputeraient la migration.

### Arrêter

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production down
```

> `down -v` détruirait `postgres-data`, donc l'intégralité des données. À ne
> jamais utiliser sur un environnement qui porte des données réelles.

---

## 4. Automatisation

Deux workflows GitHub Actions, aux rôles distincts :

| Workflow | Déclencheurs | Rôle |
|---|---|---|
| [`ci.yml`](../.github/workflows/ci.yml) | push et PR sur `develop` | Installe les dépendances backend et frontend |
| [`deploy.yml`](../.github/workflows/deploy.yml) | push et PR sur `develop` (chemins Docker / backend / frontend), et `workflow_dispatch` | Construit la pile de production, la démarre et vérifie qu'elle répond |

`deploy.yml` **ne déploie nulle part** : il n'existe aucune cible. Il vérifie ce
qui est vérifiable sans serveur — que les images de production se construisent,
que la pile démarre, que l'API et le frontend répondent, et que les réglages de
production sont bien effectifs (`display_errors` désactivé, `APP_DEBUG` à
`false`, `APP_ENV` à `production`, caches construits, dépendances de
développement absentes, aucune migration en attente).

Il n'utilise **aucun secret de dépôt**. Les mots de passe qu'il génère sont
éphémères, propres au conteneur jetable du runner, et ne protègent rien de réel.
Ses permissions sont réduites à `contents: read` : il ne publie rien et n'écrit
nulle part.

### Pour brancher un déploiement réel

Il manque, dans l'ordre :

1. **Une cible** : serveur, plateforme managée ou cluster, avec sa méthode
   d'accès (SSH, API du fournisseur, `kubectl`).
2. **Un registre d'images**, pour que la cible tire les images plutôt que de les
   reconstruire (GHCR est disponible sans secret supplémentaire pour un dépôt
   GitHub).
3. **Les secrets**, déclarés en *GitHub Actions secrets* et jamais dans le
   dépôt : au minimum `APP_KEY`, `DB_PASSWORD`, `REDIS_PASSWORD`, plus les
   identifiants d'accès à la cible.
4. **Un environnement GitHub protégé** (`environment: production`), pour exiger
   une approbation manuelle avant que le job de déploiement ne démarre.
5. **Une stratégie de retour arrière** : conserver l'image précédente et savoir
   la remettre en service.

Tant que ces éléments n'existent pas, ajouter un job de déploiement reviendrait
à écrire des étapes qui ne peuvent pas s'exécuter.

---

## 5. Vérifier un déploiement

[`scripts/verify-deployment.sh`](../scripts/verify-deployment.sh) contrôle une
pile de production déjà démarrée. Il est **en lecture seule** : il ne démarre,
n'arrête, ne migre et ne supprime rien.

```bash
# pile locale : contrôles HTTP + assertions dans les conteneurs
scripts/verify-deployment.sh

# instance distante : contrôles HTTP uniquement
scripts/verify-deployment.sh --remote   --api-url https://api.example --web-url https://example
```

Il vérifie que l'API répond, que `/api/health` déclare PostgreSQL et Redis
joignables, que le frontend sert bien un build (assets hachés) avec son repli
SPA, qu'aucune trace de débogage ne fuit dans une réponse d'erreur, que les six
conteneurs tournent, que les réglages de production sont effectifs, qu'aucune
migration n'est en attente, et qu'aucune erreur fatale n'apparaît dans les
journaux récents.

Il renvoie 0 si tout passe, 1 sinon — utilisable comme porte de sortie après un
déploiement.

> À ne pas confondre avec
> [`scripts/docker-smoke-test.sh`](../scripts/docker-smoke-test.sh), qui vise la
> pile de **développement** et qui, lui, construit et démarre.

### Piège rencontré : changer un mot de passe sur un volume existant

PostgreSQL n'applique `POSTGRES_PASSWORD` qu'à la **première initialisation** du
volume. Redémarrer la pile avec un nouveau `DB_PASSWORD` sur un
`postgres-data` déjà initialisé laisse l'ancien mot de passe en place, et le
conteneur `app` boucle sur :

```
SQLSTATE[08006] FATAL: password authentication failed for user "vitalis"
```

Changer le mot de passe se fait dans la base, pas dans la variable :

```bash
docker compose -f docker-compose.prod.yml --env-file .env.production   exec postgres psql -U "$DB_USERNAME" -c "ALTER USER \"$DB_USERNAME\" WITH PASSWORD '...';"
```

puis mettre `.env.production` en accord. Supprimer le volume fonctionne aussi,
mais **détruit toutes les données**.

---

## 6. Ce qui n'est pas couvert

Honnêtement, et par ordre d'importance si ce projet devait servir réellement :

- **TLS.** La pile écoute en HTTP. Un terminaison TLS (reverse proxy, load
  balancer, ou certificats montés dans `api`/`web`) est indispensable avant toute
  exposition publique — a fortiori pour des données médicales.
- **Sauvegardes.** Aucune sauvegarde de `postgres-data` n'est configurée.
- **Journalisation et supervision.** Les conteneurs écrivent sur la sortie
  standard ; aucune collecte, aucune alerte.
- **Haute disponibilité.** Un seul réplica par service, aucune stratégie de
  déploiement sans interruption.
- **Durcissement.** Pas de limites de ressources, pas de `read_only`, pas de
  `cap_drop`, php-fpm démarre son maître en root (les workers tournent en
  `www-data`).
- **Rotation des secrets.** Aucun mécanisme prévu.

Cette pile a été construite et démarrée en local pour vérifier qu'elle
fonctionne ; elle n'a été déployée sur aucun serveur.
