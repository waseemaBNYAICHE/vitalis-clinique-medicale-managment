# 🏥 VITALIS — Système de Gestion de Clinique Médicale

VITALIS est une application web de gestion de clinique médicale permettant de centraliser et simplifier la gestion des patients, rendez-vous, consultations, hospitalisations, examens médicaux, ordonnances, factures et paiements.

Le projet est développé avec **Laravel** pour le Backend et **Vue.js** pour le Frontend. Il intègre également un système de contrôle d'accès basé sur les rôles (**RBAC**), des fonctionnalités d'Intelligence Artificielle / Machine Learning ainsi qu'une approche **DevSecOps** avec Docker et CI/CD.

---

## 📌 Contexte du projet

- Formation : Génie Informatique – TA
- Niveau : 3ème année
- Type : Projet tutoré
- Année universitaire : 2025/2026
- Encadrant : M. GHAILANI

---

## 🎯 Objectifs

Les principaux objectifs de VITALIS sont :

- Centraliser la gestion d'une clinique médicale
- Faciliter la gestion des patients
- Organiser les rendez-vous
- Assurer le suivi des consultations
- Gérer les ordonnances et médicaments
- Gérer les examens médicaux et leurs résultats
- Gérer les hospitalisations et les chambres
- Automatiser la facturation et le suivi des paiements
- Sécuriser l'accès aux données médicales
- Gérer les rôles et permissions avec RBAC
- Intégrer des fonctionnalités d'IA / Machine Learning
- Mettre en place une démarche DevSecOps
- Automatiser les tests et contrôles via CI/CD

---

## ✨ Fonctionnalités principales

### 📊 Tableau de bord

- Statistiques générales
- Nombre de patients
- Nombre de consultations
- Revenus
- Rendez-vous du jour
- Alertes
- Graphiques
- Statistiques mensuelles

### 👥 Gestion des patients

- Ajouter un patient
- Modifier un patient
- Consulter les informations d'un patient
- Rechercher un patient
- Filtrer les patients
- Consulter l'historique médical

### 📅 Gestion des rendez-vous

- Créer un rendez-vous
- Modifier un rendez-vous
- Annuler un rendez-vous
- Consulter les rendez-vous
- Gérer les statuts
- Consulter les créneaux disponibles

### 🩺 Gestion des consultations

- Enregistrer une consultation
- Saisir les constantes vitales
- Saisir le motif de consultation
- Ajouter un diagnostic
- Ajouter un traitement
- Ajouter des observations
- Demander un examen médical

### 💊 Gestion des ordonnances

- Créer une ordonnance
- Ajouter des médicaments
- Définir le dosage
- Définir la fréquence
- Définir la durée du traitement
- Ajouter des instructions
- Imprimer l'ordonnance

### 🧪 Examens médicaux

- Créer une demande d'examen
- Consulter les demandes
- Gérer les priorités
- Gérer les statuts
- Enregistrer les résultats
- Consulter les résultats

### 🏥 Hospitalisations

- Enregistrer une admission
- Consulter les patients hospitalisés
- Gérer les chambres
- Affecter une chambre
- Suivre l'état d'hospitalisation

### 💳 Facturation & Paiements

- Créer une facture
- Consulter les factures
- Enregistrer un paiement
- Calculer le montant payé
- Calculer le reste à payer
- Gérer le statut des factures

### 🔐 Utilisateurs & Accès

- Créer un utilisateur
- Modifier un utilisateur
- Activer / désactiver un utilisateur
- Gérer les rôles
- Gérer les permissions
- Contrôler les accès avec RBAC

---

## 🤖 Intelligence Artificielle

VITALIS prévoit l'intégration de fonctionnalités basées sur l'Intelligence Artificielle / Machine Learning afin d'apporter une assistance supplémentaire dans l'exploitation des données de la clinique.

Les fonctionnalités IA seront documentées selon les modèles effectivement développés et intégrés dans l'application.

---

## 🛠️ Technologies utilisées

### Backend

- Laravel
- PHP
- API REST

### Frontend

- Vue.js
- JavaScript
- HTML5
- CSS3

### Base de données

- PostgreSQL

### DevOps / DevSecOps

- Docker
- Git
- GitHub
- CI/CD
- Jira

---

## 🏗️ Architecture générale

```text
Utilisateur
     │
     ▼
┌───────────────────┐
│    Vue.js         │
│    Frontend       │
└─────────┬─────────┘
          │
          │ API REST
          ▼
┌───────────────────┐
│    Laravel        │
│    Backend        │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│   PostgreSQL      │
│ Base de données   │
└───────────────────┘
```

---

## 📂 Structure du projet

```text
VITALIS/
│
├── backend/
│   └── Application Laravel
│
├── frontend/
│   └── Application Vue.js
│
├── docker/
│   └── Configuration Docker
│
├── docs/
│   └── Documentation du projet
│
├── docker-compose.yml
│
└── README.md
```

La structure exacte peut évoluer selon l'organisation finale du dépôt.

---

## ⚙️ Prérequis

Avant d'installer le projet, vérifier la présence de :

- Git
- PHP
- Composer
- Node.js
- npm
- PostgreSQL
- Docker / Docker Compose

---

## 🚀 Installation

### 1. Cloner le dépôt

```bash
git clone <URL_DU_DEPOT>
cd VITALIS
```

### 2. Installer le Backend Laravel

```bash
cd backend
composer install
```

Créer le fichier `.env` :

```bash
cp .env.example .env
```

Générer la clé Laravel :

```bash
php artisan key:generate
```

Configurer PostgreSQL dans `.env` :

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=vitalis
DB_USERNAME=postgres
DB_PASSWORD=VOTRE_MOT_DE_PASSE
```

Créer les tables :

```bash
php artisan migrate
```

Si des données de test sont disponibles :

```bash
php artisan db:seed
```

Lancer Laravel :

```bash
php artisan serve
```

### 3. Installer le Frontend Vue.js

```bash
cd frontend
npm install
npm run dev
```

---

## 🐳 Docker

Le projet utilise Docker afin de fournir un environnement de développement reproductible.

Lorsque la configuration Docker est disponible :

```bash
docker compose up -d --build
```

Vérifier les conteneurs :

```bash
docker compose ps
```

Arrêter les conteneurs :

```bash
docker compose down
```

---

## 🔐 Sécurité

VITALIS met en œuvre plusieurs mécanismes de sécurité :

- Authentification des utilisateurs
- Gestion sécurisée des mots de passe
- Validation des données
- Autorisation des utilisateurs
- Gestion des rôles et permissions
- RBAC
- Protection des routes sensibles
- Contrôle des accès aux données médicales
- Gestion sécurisée des variables d'environnement
- Contrôles de sécurité dans le processus DevSecOps

---

## 🔄 CI/CD

Le projet adopte une approche DevSecOps avec un pipeline CI/CD.

Le processus cible est :

```text
Push / Pull Request
        │
        ▼
Installation des dépendances
        │
        ▼
Build
        │
        ▼
Tests Backend
        │
        ▼
Tests Frontend
        │
        ▼
Contrôles qualité / sécurité
        │
        ▼
Docker Build
        │
        ▼
Validation
        │
        ▼
Déploiement
```

---

## 📦 Déploiement

Une pile de production conteneurisée est fournie
([`docker-compose.prod.yml`](docker-compose.prod.yml)) : assets Vue compilés et
servis en statique, dépendances Composer sans paquets de développement, erreurs
journalisées et non affichées, `APP_KEY` exigée depuis l'environnement.

```bash
cp .env.production.example .env.production
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
```

Les variables requises, la distinction entre valeurs publiques et secrètes, et
les points non couverts (TLS, sauvegardes, supervision) sont détaillés dans
[docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

Aucune infrastructure cible n'est rattachée au projet : cette pile a été
construite et démarrée localement, elle n'a été déployée sur aucun serveur.

---

## 🧪 Tests

Le projet prévoit plusieurs niveaux de tests :

- Tests unitaires
- Tests d'intégration
- Tests fonctionnels
- Tests Backend
- Tests Frontend
- Tests des API
- Tests d'authentification et d'autorisation

Pour Laravel :

```bash
php artisan test
```

---

## 🌿 Gestion Git

Le développement est réalisé de manière collaborative avec Git et GitHub.

Exemple de branches :

```text
main
│
└── develop
    │
    ├── feature/patients
    ├── feature/rendez-vous
    ├── feature/consultations
    ├── feature/ordonnances
    ├── feature/examens
    ├── feature/hospitalisations
    └── feature/facturation
```

Les nouvelles fonctionnalités sont intégrées via des Pull Requests après vérification.

---

## 📋 Gestion du projet

Le suivi du projet est effectué avec **Jira**.

Workflow utilisé :

```text
BACKLOG
   ↓
TO DO
   ↓
IN PROGRESS
   ↓
CODE REVIEW
   ↓
TESTING
   ↓
DONE
```

---

## 👥 Équipe

| Membre | Responsabilités |
|---|---|
| **OUASSIMA BNYAICHE** | Chef de projet — Full-Stack / Frontend (80 %) ; Intelligence Artificielle (20 %) |
| **YOUSSRA KAROUK EL BAHI** | Adjoint au chef de projet — Full-Stack / Backend (80 %) ; Qualité Logicielle et Tests (20 %) |
| **HADBAA ZOKRI** | Développement Backend (80 %) ; CI/CD (20 %) |
| **ANASS DIANE** | Infrastructure Docker & Cloud (80 %) ; Développement Backend (20 %) |
| **HAMZA MAGHFOUR** | Sécurité de l'infrastructure (80 %) ; Développement Frontend (20 %) |
| **MOUNIA STITOU** | Intelligence Artificielle (60 %) ; Développement Frontend (20 %) |

---

## 📚 Documentation

La documentation du projet comprend notamment :

- Rapport fonctionnel
- Documentation technique
- Documentation de la base de données
- Documentation Backend
- Documentation Frontend
- Documentation Docker
- Documentation CI/CD
- Documentation de sécurité
- Rapports de tests
- Rapports individuels

---

## 📦 Livrables

Les principaux livrables du projet sont :

- Application VITALIS
- Code source GitHub
- README
- Rapport fonctionnel final
- Rapports techniques individuels
- Rapports de réunion
- Rapport de tests
- Présentation de soutenance
- Vidéo de démonstration

---

## 📄 Licence

Projet académique réalisé dans le cadre du projet tutoré de 3ème année Génie Informatique – TA.

**Année universitaire : 2025/2026**
