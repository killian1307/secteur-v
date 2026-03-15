# ⚡⚽ Secteur V ⚽⚡

![Bannière Secteur V](assets/img/secteurv-server.png)

[![Static Badge](https://img.shields.io/badge/lang-fr-0000FF)](README.md) [![Static Badge](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://www.php.net/) [![Static Badge](https://img.shields.io/badge/Database-MySQL-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/) [![Static Badge](https://img.shields.io/badge/License-MIT-yellow)](LICENSE)

## 🌐 Accès en Ligne

**Vous pouvez accéder à la plateforme compétitive directement depuis votre navigateur :**

<h3 align="center">
🚀 BÊTA OUVERTE ! Rejoindre le Secteur V : <a href="https://secteur-v.letterk.me" target="_blank">https://secteur-v.letterk.me</a>
</h3>

---

**Secteur V** est la plateforme communautaire et compétitive dédiée aux joueurs d'**Inazuma Eleven: Victory Road**. Elle permet aux joueurs de gérer leur profil, de suivre leur progression via un classement ELO dynamique et d'archiver leur historique de matchs. Conçu pour centraliser la compétition, ce projet offre une expérience fluide connectée directement à Discord.

## Résumé

Le projet répond au besoin de structurer la scène compétitive du jeu. Plutôt que de gérer des classements manuellement et communauté par communauté, **Secteur V** automatise le suivi des scores, offre des profils publics partageables et sécurise l'identité des joueurs via une authentification OAuth2 (Discord).

## Fonctionnalités

* **Authentification Discord :** Connexion sécurisée via OAuth2, récupérant automatiquement l'avatar et le pseudo.
* **Classement ELO (Ladder) :** Système de rang dynamique calculé selon les performances.
* **Profils Joueurs (Dossiers) :** Page publique affichant les statistiques, la bio, le rang actuel et l'historique des matchs.
* **Gestion de Compte :** Interface pour modifier sa bio, ses informations de contact ou supprimer ses données (RGPD).

## Prérequis & Installation

> 📝
> Le projet est hébergé en ligne sur [https://secteur-v.letterk.me](https://secteur-v.letterk.me), aucune installation n'est requise pour les joueurs. Si vous souhaitez déployer votre propre instance, suivez ces étapes.

### Étapes d'installation

1.  **Cloner le dépôt :**
    Téléchargez le code source sur votre serveur ou en local.

2.  **Configuration Serveur :**
    Assurez-vous d'avoir un serveur web compatible (Apache/Nginx) avec **PHP 8.0+** et **MySQL**.

3.  **Base de Données :**
    * Créez une base de données MySQL.
    * Importez la structure SQL (table `users` requise).
    * Configurez le fichier `db.php` avec vos identifiants.

4.  **Configuration Discord :**
    * Créez une application sur le [Discord Developer Portal](https://discord.com/developers/applications).
    * Ajoutez votre URL de redirection (ex: `https://votre-site.com/discord_login.php`).
    * Renseignez le `CLIENT_ID` et `CLIENT_SECRET` dans `discord_login.php`.

## Protocole d'Utilisation

1.  **Inscription :** Connectez-vous via le bouton Discord. Un compte est automatiquement créé.
2.  **Personnalisation :** Accédez à "Modifier mon dossier" pour ajouter une bio et personnaliser votre profil.
3.  **Compétition :** Vous saisissee les résultats des matchs, votre ELO se met à jour automatiquement.
4.  **Partage :** Partagez l'URL de votre profil (ex: `secteur-v.letterk.me/profile?username=VotrePseudo`) à vos amis.

> 💡
> **Astuce :** Le site est entièrement *Responsive*, vous pouvez consulter votre classement depuis votre mobile entre deux matchs.

## Dépendances

Ce projet repose sur les technologies standards du web :

* **PHP 8 :** Langage côté serveur.
* **MySQL :** Stockage des données utilisateurs et matchs.
* **FontAwesome :** Icônes de l'interface.
* **Google Fonts :** Typographies (*Cormorant Garamond* pour le style littéraire).

## Utilisation de l'IA

Le développement de ce projet a été fait en utilisant l'IA Gemini comme outil de réflexion et comme banque de données. Même si le plus gros du site a été développé à l'aide de mes connaissances et de l'IA comme outil seulement, certaines parties plus complexes ont nécessité de la génération de code. Quoi qu'il en soit, le code final a été relu, réécrit, testé et débuggé par mes soins sur plusieurs semaines, afin que même les bouts de codes générés par Gemini soient assimilés et intégrés au projet de manière robuste et stable.

Le projet Secteur V n'est donc **pas** un projet de vibe-coding, mais le résultat du travail sur plusieurs semaines d'un étudiant en informatique amoureux d'Inazuma Eleven (et de php), assisté par IA.

## Licences

**Artwork & Jeu :** Inazuma Eleven est une propriété de © LEVEL-5 Inc. Ce projet est un site communautaire non-officiel.

**MIT License**

Vous êtes libre d'utiliser, de modifier, de distribuer et de vendre ce logiciel, à condition d'inclure l'avis de droit d'auteur original et la licence dans toute copie ou partie substantielle du logiciel.

Pour plus de détails, veuillez consulter le fichier [LICENSE](LICENSE).
