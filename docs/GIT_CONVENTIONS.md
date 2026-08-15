# Conventions Git du projet VITALIS

## Branches principales

- `main` : version stable du projet
- `develop` : branche principale de développement

## Branches de travail

Pour une nouvelle fonctionnalité :

`feature/SCRUM-XXX-description`

Exemple :

`feature/SCRUM-164-api-connexion`

Pour une correction :

`fix/SCRUM-XXX-description`

Exemple :

`fix/SCRUM-210-correction-login`

## Convention des commits

Chaque commit doit commencer par la clé du ticket Jira.

Format :

`SCRUM-XXX Description du changement`

Exemple :

`SCRUM-164 Ajouter l'API de connexion`

## Pull Requests

Le titre d'une Pull Request doit contenir la clé Jira.

Format :

`SCRUM-XXX - Description`

Exemple :

`SCRUM-164 - Développer l'API de connexion`

## Règles de fusion

- Aucun développement direct sur `main`
- Les branches `feature` sont fusionnées dans `develop`
- `develop` est fusionnée dans `main` après validation
- Une Pull Request doit être revue avant le merge

## Sécurité

Les éléments suivants ne doivent jamais être envoyés dans le dépôt :

- fichier `.env`
- mots de passe
- clés API
- tokens
- secrets
- identifiants PostgreSQL
feature/SCRUM-426-ai-preparation
=======

## Sécurité

Les éléments suivants ne doivent jamais être envoyés dans le dépôt :

- fichier `.env`
- mots de passe
- clés API
- tokens
- secrets
- identifiants PostgreSQL
 main
