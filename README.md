#  Projet RSE Website

##  Objectif

Ce projet s’inscrit dans le cadre d’un travail estival visant à :
- Vulgariser la notion de **Responsabilité Sociétale des Entreprises (RSE)** auprès d’un large public.
- Concevoir un **site web administrable** incluant des **vidéos, images et textes** explicatifs.
- Mettre en œuvre et renforcer les compétences techniques acquises durant la formation.

Ce projet témoigne de mon **engagement personnel** et sera **rendu le 1er septembre**, conditionnant la poursuite de ma formation sur le campus.

---

## echnologies utilisées

- **Frontend** : HTML, CSS, JavaScript
- **Backend** : PHP
- **Base de données** : MySQL
- **Outils** : Git, GitHub, VS Code

---

##  Fonctionnalités

- Page d’accueil interactive
- Présentation visuelle des principes de la RSE
- Galerie multimédia (vidéos, infographies, photos)
- Espace administrateur pour la gestion des contenus
- Compatibilité mobile

---

##  Auteur

**Christian Ngono Abanda**  
Étudiant à Ynov Nantes | Développeur Web Junior

## Création d'une clé API sur Google reCAPTCHA Admin pour sécuriser d'avantage intégré dans register.html
- Crée un compte sur Twilio et installer le SDK PHP avec les commandes suivantes " composer require twilio/sdk " 
- " sudo apt-get install php8.1-intl "
- " sudo systemctl restart apache2 "
- " which php "
- " php -m | grep intl " enfin relancer 
- " composer require twilio/sdk "

## Le $sid (Account SID) est l’identifiant unique de ton compte Twilio. Il est obligatoire pour initialiser le client Twilio en PHP et envoyer des SMS. Il fonctionne avec le Auth Token ($token) comme une paire de clés d’accès. le récupérer dans ton Twilio Console.

## Configurer mail() avec Postfix (serveur SMTP local). Configurer Postfix sous Bash
- " sudo apt update "
- " sudo apt install postfix mailutils "
- " sudo dpkg-reconfigure postfix "
## Créer le fichier sasl_passwd
- " sudo nano /etc/postfix/sasl_passwd "
## Étapes pour générer un mot de passe d’application Gmail
## Active la validation en deux étapes
- Avant tout, tu dois activer la validation en deux étapes dans ton compte Google : va sur
- myaccount.google.com/security
- Dans la section “Connexion à Google”, clique sur “Validation en deux étapes”
- Suis les instructions pour l’activer (SMS, application Google Authenticator, etc.)
- mettre dans sasl_passwd " [smtp.gmail.com]:587 christianngonoabanda@gmail.com:mot_de_passe_application " // ce mot_de_passe_application a 16 caractères en lettres minuscules est créé dans :
- https://myaccount.google.com/apppasswords où tu vas :
- Connecte-toi à ton compte Google
- Dans “Sélectionner l’application”, choisis : Mail
- “Sélectionner l’appareil”, choisis : Autre (nom personnalisé) → taper "Postfix WSL"
- Cliquer sur " Générer "
- Google t’affichera un mot de passe de 16 caractères (ex. abcd efgh ijkl mnop)
- Copie ce mot de passe et colle-le dans ton fichier /etc/postfix/sasl_passwd

## Compiler et sécuriser
- " sudo postmap /etc/postfix/sasl_passwd "
- " sudo chown root:root /etc/postfix/sasl_passwd /etc/postfix/sasl_passwd.db "
- " sudo chmod 600 /etc/postfix/sasl_passwd /etc/postfix/sasl_passwd.db "
- " sudo systemctl restart postfix " // Redemarer Postfix
## Utiliser PHPMailer (recommandé pour les projets PHP)
- " composer require phpmailer/phpmailer "




