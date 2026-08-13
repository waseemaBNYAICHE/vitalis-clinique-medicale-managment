# Prédiction des maladies par Intelligence Artificielle

Ce module est dédié à la préparation et à l'intégration de
l'Intelligence Artificielle dans le système de gestion de la clinique Vitalis.

## Objectif

Le module d'Intelligence Artificielle aura pour objectif de prédire
une maladie probable à partir des informations du patient et de ses symptômes,
puis d'identifier la spécialité médicale appropriée.

## Flux prévu

Informations du patient + Symptômes
→ Modèle de Machine Learning
→ Maladie prédite
→ Spécialité médicale
→ Médecin recommandé
## Environnement Python

Python est installé et sa version a été vérifiée.

- Version Python utilisée : 3.10.11
- Commande de vérification : `python --version`
## Environnement virtuel

Un environnement virtuel Python a été créé pour isoler
les dépendances du module Machine Learning.

- Environnement : `.venv`
- Python : 3.10.11
- Emplacement : `AI/.venv/`
## Bibliothèques Machine Learning

Les bibliothèques nécessaires au module Machine Learning ont été
installées dans l'environnement virtuel.

- pandas : 2.3.3
- numpy : 2.2.6
- scikit-learn : 1.7.2