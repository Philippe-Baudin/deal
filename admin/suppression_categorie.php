<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// suppression_categories.php
// supprime la catégorie donnée dans $_POST et affiche un message donnant le résultat
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='../';
require_once '../inc/init.php';

// Si l'utilisateur n'est pas connecté ou n'est pas admin, ou si $_POST n'est pas réglementaire, ne rien faire
if (!estAdmin() || empty($_POST) || !isset($_POST['id']))
	exit();

$id = $_POST['id'];


// Suppression d'une catégorie
$resultat = executerRequete ("DELETE FROM categorie WHERE id = :id", array (':id' => $id));
if ($resultat->rowCount() == 1)
	echo '<div class="alert alert-success">La catégorie a bien été supprimé.</div>';
else
	echo '<div class="alert alert-danger">Erreur lors de la suppression de la catégorie.</div>';
