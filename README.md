SCRUM-163-configurer-github
# gestion-medicale-project-v2
Application web de gestion d’un cabinet médical développée avec Laravel, Vue.js, Machine Learning et une approche DevSecOps.

## Configuration GitHub
- Dépôt GitHub configuré et connecté à Jira.
- Branche de travail : SCRUM-163-configurer-github.
- Convention de branches :
  - main : version stable
  - develop : intégration
  - feature/SCRUM-163-configurer-github : développement des fonctionnalités
  
=======
# vitalis-clinique-medicale-managment

VITALIS est une application de gestion de clinique médicale développée avec Laravel et Vue.js. Elle intègre des fonctionnalités de Machine Learning, un système de contrôle d’accès basé sur les rôles (RBAC), ainsi qu’une approche DevSecOps assurant un pipeline complet d’intégration, de sécurité et de déploiement continus (CI/CD).

## Organisation du dépôt GitHub

Le dépôt du projet VITALIS est organisé afin de faciliter le travail collaboratif, le suivi des tâches Jira et l’intégration continue.

### Branches principales

- `main` : contient la version stable et validée du projet.
- `develop` : contient les développements intégrés avant leur passage en production.

### Branches de développement

Chaque fonctionnalité ou tâche est développée dans une branche dédiée.

Format utilisé :

`feature/SCRUM-XXX-description`

Exemple :

`feature/SCRUM-164-api-connexion`

Pour les corrections :

`fix/SCRUM-XXX-description`

Exemple :

`fix/SCRUM-210-correction-login`

### Convention des commits

Chaque commit doit contenir la référence du ticket Jira correspondant.

Exemple :

`SCRUM-164 Développer l'API de connexion`

Cette convention permet de synchroniser automatiquement les commits avec Jira.

### Pull Requests

Une Pull Request doit être créée avant de fusionner une branche dans `develop` ou `main`.

Le titre doit également contenir la clé Jira.

Exemple :

`SCRUM-164 - Développer l'API de connexion`

### Workflow Git

Ticket Jira
→ Création de branche
→ Développement
→ Commit
→ Push
→ Pull Request
→ Revue du code
→ Merge dans develop
→ Validation
→ Merge dans main

### Structure générale du dépôt

vitalis/
├── backend/        # Application Laravel
├── frontend/       # Application Vue.js
├── database/       # Scripts et documentation PostgreSQL
├── docker/         # Configuration Docker
├── .github/
│   └── workflows/  # Pipelines CI/CD
├── docs/           # Documentation du projet README.md
└── .gitignore
## Jira Integration Test 
Test de liaison entre Jira & Github.

### Responsabilités GitHub

La répartition détaillée des responsabilités GitHub de l’équipe est disponible dans :

`docs/GITHUB_RESPONSIBILITIES.md`

## Documentation Frontend

Les prérequis nécessaires au développement Frontend sont disponibles dans :

`docs/FRONTEND_PREREQUISITES.md`


## Jira Integration Test
Test de liaison entre Jira et Github.
 develop
