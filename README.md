<div>
  <h1 align="center" position="relative">
    <a  href="https://gitlab.cefim-formation.org/ArmandWADJI/ecf-back-end.git">Devjob Back-End 👩🏻‍💻🧑🏽‍💻👨🏿
    </a> 
  </h1> 
</div> 

<img width="1538" alt="Capture d’écran 2023-06-02 à 12 59 00" src="https://github.com/armandwadji/Devjob_Back/assets/90448006/16301845-1238-4a58-b4df-2266d10a8607">


## Description
Devjob est une application qui va vous permettre de créer des offres d'emplois. 
Vous pourrez partager vos offres à la communauté du site, dans le but de recevoir des candidatures.

## Configuration requise

- [php ^8][php]
- [composer ^2.6][composer]
- [symfony ^6][symfony]
- [SGBD][SGBD] : [Lamp][Lamp], [Mamp][Mamp], [Xamp][Xamp]

## Installation
Une fois tous les éléments de la configuration requise installé sur votre pc,
vous pouvez executer cette commande pour installer le projet en local sur votre machine :

```
git clone https://gitlab.cefim-formation.org/ArmandWADJI/ecf-back-end.git
```

De plus l'application utilise un système d'envoie de mail, donc pour simuler un serveur SMTP en local sur votre machine vous devez en installer un. 

Pour ce projet nous utiliserons MailDev qui à été dévélopper en NodeJs. 

Saisisez dans votre terminal la commande suivante:

```
npm install -g maildev
```

lancer le server SMPT tous simplement avec la commande suivante: 

```
maildev
```

Vous devez créer un fichier à la racine de votre arboresence que vous nommerez **.env.dev.local**.

Vous y intégrerez ces lignes, où vous y ajouteerez les informations de connexion à votre base de donnée ainsi que le port de votre serveur SMTP. 

```
DATABASE_URL="mysql://SERVER:PASSWORD@127.0.0.1:3306/devjob?serverVersion=8&charset=utf8mb4"
MAILER_DSN=smtp://localhost:PORTDUSERVEURSMTP
```

Ensuite, ouvrez un autre terminal et installez les dépendances du projet avec la commande suivante :

```
composer install
```

Maintenant il vous faut créer votre DATABASE en saisiant la commande suivante :

```
php bin/console doctrine:database:create
```

Ensuite vous devez faire une migration, dans le but de générer les requêtes SQL, nécéssaires pour créer les tables et liaisons des différentes entités du projet avec la commande suivante :

```
php bin/console make:migration
```

Ne vous inquiétez pas nous avons presque terminer l'installation 😊😊.
Il vous faut maintenant créer tous le schéma de Base de donné avec la commande suivante :

```
php bin/console doctrine:migrations:migrate
```

## Démarrer l'application

Maintenant il vous faut quelques données pour pouvoir manipulé l'application.
Cela tombe bien, nous en avons préparer, pour les ajoutés en Base de donnée, saisisez la commande suivante :

```
php bin/console doctrine:fixtures:load
```

Vous pouvez enfin lancer le projet et le tester à l'aide de la commande :

```
symfony server:start
```

## Connexion en tant qu' administrateur

Pour vous connecter en tant qu'administrateur voici les paramètres de login.


email :
```
admin@devjob.com
```

password :
```
Php1234#
```

## Connexion en tant qu' entreprise

Pour vous connecter en tant qu'entreprise voici les paramètres de login.


email :
```
sélectionné un email dans la table User de votre base de donnée!
```

password :
```
password
```

<p align="right">Back to top :
  <a href="#top">
    ☝
  </a>
</p>

<h1 align="center">Bon Code 🖥 💻 📱</h1>

<!-- prettier-ignore-start -->
[php]: https://www.php.net/downloads
[composer]: https://getcomposer.org/download/
[symfony]: https://symfony.com/doc/current/setup.html
[SGBD]: #
[Lamp]: https://ubuntu.com/server/docs/lamp-applications
[Mamp]: https://www.mamp.info/en/downloads/
[Xamp]: https://www.apachefriends.org/fr/download.html
<!-- prettier-ignore-end -->
