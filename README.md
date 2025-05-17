# NutriVie - Site Web de Nutrition et Bien-être

Ce projet a été développé dans le cadre d'un mini-projet scolaire. Il s'agit d'un site web dynamique sur le thème de la nutrition et du bien-être.

## Description

NutriVie est une plateforme interactive permettant aux utilisateurs de s'informer sur la nutrition, d'utiliser des outils de suivi de santé, et d'interagir avec du contenu personnalisé.

Le site comprend :
- Une section d'articles sur la nutrition et le bien-être
- Des outils interactifs (calculateur d'IMC, calculateur de besoins caloriques)
- Un système de suivi personnel de santé
- Un formulaire de contact
- Un panneau d'administration pour gérer le contenu

## Technologies utilisées

- **Front-end** : HTML, CSS, JavaScript
- **Back-end** : PHP
- **Base de données** : MySQL

## Installation

1. Clonez ce dépôt sur votre machine locale ou sur votre serveur.
2. Créez une base de données MySQL sur votre serveur.
3. Configurez les informations de connexion à la base de données dans le fichier `includes/db.php`.
4. Importez le site dans votre environnement de développement local (XAMPP, WAMP, etc.).
5. Accédez au site via votre navigateur web.

La structure de la base de données sera automatiquement créée lors de la première visite sur le site.

## Structure du projet

```
/
├── admin/             # Panneau d'administration
├── css/               # Feuilles de style
├── img/               # Images du site
├── includes/          # Fichiers d'inclusion PHP
├── js/                # Scripts JavaScript
├── pages/             # Pages principales du site
├── index.php          # Page d'accueil
└── README.md          # Ce fichier
```

## Fonctionnalités principales

### Utilisateurs
- Inscription et connexion
- Profil personnel
- Suivi de santé personnalisé

### Articles
- Liste des articles
- Affichage détaillé d'un article
- Recherche d'articles

### Outils
- Calculateur d'IMC
- Calculateur de besoins caloriques
- Conseils nutritionnels

### Administration
- Gestion des utilisateurs
- Gestion des articles
- Gestion des messages de contact

## Création d'un compte administrateur

Par défaut, aucun compte administrateur n'est créé. Pour en créer un, vous devez :

1. Créer un compte utilisateur normal via la page d'inscription.
2. Exécuter la requête SQL suivante sur votre base de données :
   ```sql
   UPDATE utilisateurs SET role = 'admin' WHERE email = 'votre_email@exemple.com';
   ```
   (Remplacez 'votre_email@exemple.com' par l'email de votre compte)

## Contact

Ce site a été créé dans le cadre d'un projet scolaire. Pour toute question, veuillez contacter [votre nom] à [votre email].

## Licence

Ce projet est destiné à des fins éducatives uniquement.
