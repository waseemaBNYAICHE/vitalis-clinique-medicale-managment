\# Stratégie de gestion des branches Git – VITALIS



\## 1. Objectif



Ce document définit la stratégie de gestion des branches Git utilisée dans le projet VITALIS.



L'objectif est de faciliter le travail collaboratif, d'assurer la traçabilité avec les tickets Jira et de protéger les versions stables de l'application.



\---



\## 2. Branches principales



\### main



La branche `main` contient la version stable et validée du projet.



Elle est destinée aux versions prêtes pour la production.



Les développeurs ne doivent pas travailler directement sur cette branche.



Les modifications doivent être intégrées via une Pull Request après validation.



\### develop



La branche `develop` est la branche principale de développement.



Elle permet de regrouper les fonctionnalités terminées avant leur intégration dans `main`.



Les nouvelles branches de développement sont normalement créées à partir de `develop`.



\---



\## 3. Branches de fonctionnalités



Chaque nouvelle fonctionnalité ou tâche Jira doit être développée dans une branche dédiée.



Format :



feature/SCRUM-XXX-description



Exemple :



feature/SCRUM-198-strategie-branches



Création d'une branche :



git switch develop

git pull origin develop

git switch -c feature/SCRUM-XXX-description



\---



\## 4. Branches de correction



Pour corriger un bug, une branche dédiée doit être utilisée.



Format :



fix/SCRUM-XXX-description



Exemple :



fix/SCRUM-210-correction-login



\---



\## 5. Convention des commits



Chaque commit doit contenir la référence du ticket Jira correspondant.



Format :



SCRUM-XXX Description du changement



Exemple :



SCRUM-198 Définir la stratégie des branches



Cela permet d'assurer la traçabilité entre Jira et GitHub.



\---



\## 6. Pull Requests



Une Pull Request doit être créée avant l'intégration d'une branche de travail dans `develop`.



Le titre de la Pull Request doit contenir la référence Jira.



Exemple :



SCRUM-198 - Définir la stratégie des branches



La Pull Request doit être vérifiée avant le merge.



Aucun merge direct dans `main` ne doit être effectué par un développeur sans validation.



\---



\## 7. Workflow Git



Le workflow standard du projet est :



Ticket Jira

→ Création d'une branche depuis develop

→ Développement

→ Tests

→ Commit avec référence Jira

→ Push de la branche

→ Pull Request vers develop

→ Revue du code

→ Merge dans develop

→ Validation

→ Pull Request / Merge vers main



\---



\## 8. Exemple avec SCRUM-198



Pour le ticket Jira SCRUM-198 :



Branche :



feature/SCRUM-198-strategie-branches



Commit :



SCRUM-198 Définir la stratégie des branches



Pull Request :



SCRUM-198 - Définir la stratégie des branches



Branche cible :



develop



\---



\## 9. Règles importantes



\- Ne pas développer directement sur `main`.

\- Éviter de développer directement sur `develop`.

\- Utiliser une branche dédiée pour chaque ticket Jira.

\- Ajouter la clé Jira dans les noms de branches, commits et Pull Requests.

\- Tester les modifications avant de créer une Pull Request.

\- Faire relire la Pull Request avant le merge.

\- Supprimer les branches de travail devenues inutiles après leur intégration.



\---



\## 10. Résumé



La stratégie Git du projet VITALIS repose sur deux branches principales :



main : version stable



develop : intégration des développements



Les développeurs travaillent dans des branches dédiées :



feature/SCRUM-XXX-description



ou :



fix/SCRUM-XXX-description



Le workflow permet de garder un historique clair et d'assurer la traçabilité entre GitHub et Jira.

