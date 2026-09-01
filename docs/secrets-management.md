# Gestion des secrets — SCRUM-478

## Objectif

Le ticket SCRUM-478 (rôle cybersécurité / DevSecOps) vise à **protéger le dépôt contre
l'ajout accidentel de secrets**.

Un secret est une valeur qui donne un accès : mot de passe de base de données, clé
d'application Laravel (`APP_KEY`), jeton d'API, clé privée TLS ou SSH, identifiants
cloud. Une fois poussée sur GitHub, une telle valeur doit être considérée comme
compromise, même si le commit est supprimé ensuite.

Le travail réalisé ici est un **audit** du dépôt suivi d'un **durcissement minimal**
du `.gitignore`.

## Règle principale

> Les secrets réels vivent dans un fichier `.env` **local**, jamais dans Git.

- chaque développeur copie le modèle : `cp .env.example .env`
- il complète ensuite les valeurs sur sa machine
- le fichier `.env` obtenu est ignoré par Git et ne doit jamais être ajouté de force

## Le rôle des fichiers `.env.example`

Les fichiers versionnés sont uniquement des **modèles** :

```
.env.example
backend/.env.example
frontend/.env.example
```

Ils servent de documentation : ils listent **tous les noms de variables** dont un
développeur a besoin pour démarrer le projet. Ils ne contiennent que des valeurs de
développement local ou des champs vides (par exemple `APP_KEY=` ou les clés AWS
laissées vides).

Deux règles à respecter quand on modifie un `.env.example` :

1. ne jamais y coller une valeur réelle de production ;
2. ne pas supprimer une variable utile « pour cacher un secret » — c'est le nom de la
   variable qui est utile aux développeurs, pas sa valeur.

## Clés privées et conteneurs d'identifiants

Ne doivent **jamais** être versionnés :

| Extension | Contenu |
| --- | --- |
| `*.pem`, `*.key` | clés privées TLS / SSH |
| `*.p12`, `*.pfx` | conteneurs PKCS#12 (clé privée + certificat) |
| `*.jks`, `*.keystore` | keystores Java / Android |
| `auth.json` | identifiants Composer privés |

En revanche, un **certificat public** (`*.crt`, `*.cer`) ou une **clé publique**
(`*.pub`) ne contient pas de matériel privé et peut rester versionné si le projet en a
besoin. Le `.gitignore` a donc été écrit pour ne pas les bloquer.

## Secrets en production

En production, les secrets ne doivent pas être déposés dans un fichier du dépôt mais
**injectés par l'environnement** :

- variables d'environnement du serveur ou du conteneur ;
- GitHub Actions : *Settings → Secrets and variables → Actions* ;
- ou un gestionnaire de secrets dédié (Vault, AWS Secrets Manager, Doppler…).

Le pipeline GitHub Actions du projet fonctionne déjà sans aucun secret : il n'installe
que des dépendances publiques.

## `APP_DEBUG` en production

Les fichiers `.env.example` contiennent `APP_DEBUG=true` : c'est correct pour du
**développement local**.

En production, il faut impérativement :

```
APP_ENV=production
APP_DEBUG=false
```

Avec `APP_DEBUG=true`, une erreur Laravel affiche une page de débogage contenant la
trace d'exécution, des extraits de code et une partie de la configuration — y compris
des valeurs d'environnement. C'est une fuite d'information directe.

## Si un secret est committé par erreur

Le retirer du dépôt **ne suffit pas**. L'ordre correct est :

1. **Faire tourner le secret** (rotation) : changer le mot de passe, révoquer et
   régénérer le jeton, régénérer l'`APP_KEY`. C'est l'étape la plus importante.
2. Retirer le fichier du suivi Git : `git rm --cached <fichier>`.
3. Vérifier que le `.gitignore` couvre bien ce fichier.
4. Prévenir l'équipe, et nettoyer l'historique si nécessaire (outil dédié, réécriture
   d'historique) — opération lourde à décider ensemble.

## Limite importante : `.gitignore` n'efface pas l'historique

Le `.gitignore` empêche **d'ajouter** un nouveau fichier sensible. Il n'a **aucun
effet** sur :

- un fichier **déjà suivi** par Git (il faut `git rm --cached`) ;
- un secret **déjà présent dans l'historique** des commits ;
- un secret **écrit en dur dans un fichier source** (ex. un mot de passe dans un `.php`
  ou un `.js`), puisque ce n'est pas un fichier ignoré.

C'est pourquoi la rotation du secret reste la seule vraie remédiation.

## Portée du contrôle

Ces mesures sont un **contrôle de sécurité de base**. Elles ne garantissent **pas** que
le dépôt ne contient aucun secret.

L'audit réalisé pour SCRUM-478 a porté sur :

- la **liste des fichiers suivis** (`git ls-files`) et leurs noms ;
- une recherche de **motifs évidents** de secrets en dur dans les fichiers suivis
  (en-têtes de clés privées, formats de jetons connus, affectations littérales du type
  `password = "..."`).

Il n'a **pas** couvert : l'historique complet des commits, les secrets encodés ou
obfusqués, les valeurs à faible entropie impossibles à distinguer d'une chaîne normale,
ni les fichiers non suivis présents sur les postes des développeurs.

## Vérifications rapides

```bash
# Aucun fichier d'environnement reel ne doit apparaitre (hors *.example)
git ls-files | grep -E '(^|/)\.env($|\.)' | grep -v '\.example$'

# Aucune cle privee ni conteneur d'identifiants ne doit apparaitre
git ls-files | grep -E '\.(pem|key|p12|pfx|jks|keystore)$'

# Verifier qu'un fichier sensible serait bien ignore avant de l'ajouter
git check-ignore -v backend/.env
```

Les deux premières commandes ne doivent **rien** afficher.
