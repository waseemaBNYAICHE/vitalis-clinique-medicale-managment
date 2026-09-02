# Pipeline de sécurité — SCRUM-479

## Objectif

Le ticket SCRUM-479 (rôle cybersécurité / DevSecOps) consiste à **automatiser dans
GitHub Actions** les contrôles de sécurité identifiés par les tickets précédents.

Le travail d'analyse a été fait ailleurs ; SCRUM-479 ne le refait pas, il le **rend
automatique** :

| Ticket | Travail réalisé | Automatisé par SCRUM-479 |
| --- | --- | --- |
| SCRUM-476 | Audit des dépendances backend + correction de la vulnérabilité trouvée | job `backend-dependencies` |
| SCRUM-477 | Audit des dépendances frontend | job `frontend-dependencies` |
| SCRUM-478 | Durcissement de la protection des secrets (`.gitignore`, audit du dépôt) | job `secrets-check` |

L'intérêt : sans automatisation, un audit est une photo à un instant donné. Une
dépendance saine aujourd'hui peut devenir vulnérable demain, et un `.env` peut être
ajouté par erreur la semaine prochaine. Le pipeline rejoue donc ces contrôles à chaque
modification du code.

Le workflow est volontairement **séparé** du workflow d'intégration continue :

- `.github/workflows/ci.yml` : installation des dépendances, build — **inchangé**
- `.github/workflows/security.yml` : contrôles de sécurité — objet de SCRUM-479

## Quand le pipeline s'exécute

- **pull request vers `develop`**
- **pull request vers `main`**
- **push vers `develop`**
- **déclenchement manuel** via `workflow_dispatch` (bouton « Run workflow » dans
  l'onglet Actions)

Permissions **minimales** (principe du moindre privilège) :

```yaml
permissions:
  contents: read
```

Le workflow peut uniquement lire le code : il ne peut ni écrire dans le dépôt, ni
publier de paquet, ni modifier les issues. Il n'a besoin d'aucun secret GitHub pour
fonctionner, puisqu'il n'installe que des dépendances publiques.

## Job 1 — `backend-dependencies` (automatise SCRUM-476)

- PHP 8.4, répertoire `backend/`
- `composer validate --no-check-all --no-check-publish` : vérifie que `composer.lock`
  est cohérent avec `composer.json` (détecte un lock oublié ou modifié à la main)
- `composer audit --locked --no-interaction` : compare les versions **exactes du
  `composer.lock` versionné** à la base d'avis de sécurité PHP

`--locked` audite le lock sans installer le dossier `vendor` : c'est plus rapide et
cela garantit qu'on audite bien ce qui est committé.

**Condition d'échec** : au moins un avis de sécurité concerne une dépendance du lock.
La commande retourne alors un code d'erreur et le job passe au rouge.

## Job 2 — `frontend-dependencies` (automatise SCRUM-477)

- Node 22, répertoire `frontend/`
- `npm ci` : installation reproductible à partir de `package-lock.json`
- `npm audit --audit-level=high`

**Condition d'échec** : au moins une vulnérabilité de niveau **high** ou **critical**.
Le seuil évite de bloquer l'équipe sur des alertes mineures tout en interceptant les
problèmes sérieux.

## Job 3 — `secrets-check` (automatise SCRUM-478)

Deux vérifications, basées uniquement sur `git ls-files`, c'est-à-dire sur la **liste
des noms de fichiers versionnés** :

1. **Fichiers d'environnement** : échec si un `.env`, `.env.local`, `.env.production`,
   `backend/.env`… est suivi par Git. Les modèles `*.example` sont autorisés.
2. **Clés privées et conteneurs d'identifiants** : échec si un fichier `*.pem`,
   `*.key`, `*.p12`, `*.pfx`, `*.jks` ou `*.keystore` est suivi. Les certificats
   **publics** (`*.crt`, `*.cer`, `*.pub`) restent autorisés : ils ne contiennent pas
   de matériel privé.

**Aucun contenu de fichier n'est lu ni affiché.** En cas d'échec, le job n'affiche que
le **nom** du fichier fautif, jamais une valeur secrète.

État attendu du dépôt (conforme aujourd'hui) :

```
.env.example
backend/.env.example
frontend/.env.example
```

Ce job complète le `.gitignore` durci en SCRUM-478 : le `.gitignore` prévient l'erreur,
le pipeline la détecte si elle passe quand même (par exemple via un `git add -f`).

## Résultats constatés localement

| Contrôle | Commande | Résultat local |
| --- | --- | --- |
| Backend (après correctif SCRUM-476) | `composer audit` | Aucun avis de sécurité |
| Frontend | `npm audit` | 0 vulnérabilité |
| Secrets (SCRUM-478) | audit du dépôt | Aucun secret réel trouvé |

⚠️ **Ces résultats proviennent de branches de travail distinctes.** Voir la section
suivante.

## Limite actuelle : les correctifs ne sont pas encore fusionnés

Au moment de l'écriture, le correctif de dépendance issu de SCRUM-476 (mise à jour de
`league/commonmark` vers 2.10.0 dans `backend/composer.lock`) **n'est pas encore
fusionné dans `develop`**, et il n'est pas présent sur la branche SCRUM-479.

Conséquence directe, vérifiée en local sur cette branche :

- `composer audit --locked` **échoue** ici, car le `composer.lock` de la branche
  contient encore `league/commonmark` 2.8.3, qui est affecté par des avis de sécurité ;
- `npm audit --audit-level=high` **passe** (0 vulnérabilité) ;
- `secrets-check` **passe** (seuls les trois `.env.example` sont versionnés).

Ce n'est **pas** un défaut du pipeline : le job fait exactement son travail en signalant
une dépendance vulnérable réellement présente. Le job `backend-dependencies` repassera
au vert dès que le correctif SCRUM-476 sera fusionné dans la branche cible.

Le durcissement `.gitignore` de SCRUM-478 n'est lui non plus pas encore fusionné ; le
job `secrets-check` fonctionne malgré tout, car il inspecte les fichiers **suivis** et
ne dépend pas du `.gitignore`.

## Pourquoi c'est une démarche DevSecOps

- les contrôles sont **automatisés** : plus de vérification oubliée
- ils s'exécutent **tôt**, sur la pull request, avant la fusion
- l'échec est **visible** de toute l'équipe dans l'onglet Actions
- ils sont **reproductibles** : mêmes commandes en local et en CI
- le pipeline applique le **moindre privilège** (`contents: read`)

## Limites — à lire

Ces contrôles sont des **contrôles de sécurité de base automatisés**. Ils ne
garantissent **pas** que l'application est sécurisée.

Ils ne couvrent notamment pas :

- les failles logiques de l'application (droits d'accès, contrôle des rôles, IDOR)
- les injections SQL ou XSS dans le code écrit par l'équipe
- l'analyse statique du code (SAST) et les tests d'intrusion
- la sécurité de l'infrastructure, des conteneurs et de la base de données
- les vulnérabilités non encore publiées dans les bases d'avis de sécurité
- les secrets déjà présents dans l'historique Git, ou écrits en dur dans un fichier
  source (le job `secrets-check` regarde des **noms** de fichiers, pas des contenus)

## Reproduire les contrôles en local

```bash
docker compose exec app composer validate --no-check-all --no-check-publish
docker compose exec app composer audit --locked
docker compose exec frontend npm audit --audit-level=high

git ls-files | grep -E '(^|/)\.env($|\.)' | grep -v '\.example$'
git ls-files | grep -E '\.(pem|key|p12|pfx|jks|keystore)$'
```

Les deux dernières commandes ne doivent **rien** afficher.
