# Prérequis Frontend — VITALIS

## Technologies utilisées

Le Frontend du projet VITALIS est développé avec :

- Vue.js
- Vite
- JavaScript
- npm
- Axios
- Vue Router

## Logiciels nécessaires

Avant de lancer le Frontend, le développeur doit disposer de :

- Node.js
- npm
- Git
- Visual Studio Code ou un autre éditeur de code
- Un navigateur web moderne

## Vérification de l'environnement

Vérifier Node.js :

node -v

Vérifier npm :

npm -v

Vérifier Git :

git --version

## Installation du projet Frontend

Après avoir cloné le projet :

cd frontend

Installer les dépendances :

npm install

## Lancement de l'application

Pour lancer le serveur de développement :

npm run dev

L'adresse locale du Frontend sera ensuite affichée dans le terminal.

## Configuration de l'environnement

Créer le fichier :

.env

Exemple :

VITE_API_URL=http://localhost:8000/api

Cette variable permet au Frontend Vue.js de communiquer avec l'API Laravel.

## Dépendances principales

- Vue.js : développement de l'interface
- Vue Router : gestion de la navigation
- Axios : communication avec l'API Laravel
- Vite : serveur de développement et build Frontend

## Vérification finale

L'environnement Frontend est correctement configuré si :

- Node.js fonctionne
- npm fonctionne
- les dépendances sont installées
- npm run dev démarre sans erreur
- l'application Vue.js s'affiche dans le navigateur
- le Frontend peut communiquer avec l'API Laravel