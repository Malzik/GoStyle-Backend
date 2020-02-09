# GO STYLE
GoStyle est une boutique en ligne de vêtements dédiée à la culture streetwear. Elle a été fondée en 2016 à Paris. Aujourd’hui le groupe comprend 7 boutiques réparties dans toute la France et un site e-commerce basé sur le CMS Prestashop qui permet de visualiser le catalogue client et de réaliser des achats en ligne.
L’affluence quotidienne du site est évaluée à 20 000 utilisateurs uniques avec un taux de conversion de 20% pour l’achat en ligne.

## Pour commencer

Les instructions suivantes vont vous permettre de me configurer et lancer votre environnement de test.

### Prérequis

De quoi avez vous besoin pour installer et lancer le projet.

```
Composer (https://getcomposer.org/Composer-Setup.exe)
L'executable symfony pour lancer le serveur (https://get.symfony.com/cli/setup.exe)
Un moteur de base de données Mysql
git
```

### Installation

Dans cette partie nous allons vous expliquer pas à pas comment paramétrer votre environnement de developpement

Première étape vous devez cloner le projet sur votre machine en tapant la commande suivante dans git

```
git clone https://github.com/Malzik/GoStyle-Backend.git
```

Quand le projet est cloner dans un dossier local, se rendre dans  celui-ci et lancez la commande d'installation de composer

```
composer install
```

Afin de mettre à jour les paquets entrez la commande de mise à jour 

```
composer update
```

Dupliquez le .env.example et le renommer en .env. Modifiez les champs suivants.

```
DATABASE_URL (Lien connexion à la base de données)
JWT_PASSPHRASE (Mot de passe du certificat SSL)
```

Entrez la commande de création de base de données

```
php bin/console doctrine:database:create
```

Entrez la commande de migration de la base

```
php bin/console doctrine:migration:migrate
```

Générez des données avec la commande suivante

```
php bin/console doctrine:fixtures:load
```

Une fois toutes les étapes complétées entrez la commande de lancement du serveur

```
symfony server:start
```

## Lancer les tests

```
php bin/phpunit
```

## Auteurs

* **Birac Lucas** - *Developpeur* - [SepraDC](https://github.com/SepraDC)
* **Heroin Alexis** - *Developpeur* - [Malzik](https://github.com/Malzik)

