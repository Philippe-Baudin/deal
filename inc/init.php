<?php
//phpinfo();
// Connexion à la base de données
$pdo = new PDO( 'mysql:host=localhost;dbname=deal'
              , 'philippe'
              , 'Philippe'
//              , 'root'
//              , ''
              , array ( PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
              	      , PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
              	      )
              );
 if ($pdo == false) die('Connexion impossible : ' . mysql_error());
// Note: il est judicieux de supprimer l'affichage des erreurs SQL quand on passe en production.
// Si on veut le garder uniquement pour les pages admin, il faut deux instances de PDO .

// Créer une session (ou y accéder si elle existe)
// (en local, les sessions sont dans c:\wamp64\tmp sous windows et dans /var/lib/php/sessions sous Ubuntu 19.10)
session_start ();

// Définition du chemin du site :
define('RACINE_SITE', '/evaluation/'); // dossier dans lequel se situe le site (dans localhost). Sert à transformer les chemins relatifs en absolus pour les inclusions à différents niveaux.

// Définition du nombre d'items par page dans le tableau des annonces
define('taillePage', 6);

// Quelques variables pour afficher du contenu html
$contenu = '';
$contenu_gauche = '';
$contenu_droite = '';

// Inclusion des fonctions
require_once 'functions.php';
