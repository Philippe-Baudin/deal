<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// suppression_commentaire.php
// supprime le commentaire demandé
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='../';
require_once '../inc/init.php';

// Si l'utilisateur n'est pas connecté ou n'est pas admin, ou si $_POST n'est pas réglementaire, ne rien faire
if (!estAdmin() || empty($_POST) || !isset($_POST['id']))
	exit();

// Suppression d'un commentaire
$resultat = executerRequete ("DELETE FROM commentaire WHERE id = :id", array (':id' => $_POST['id']));
if ($resultat->rowCount() == 1)
	echo '<div class="alert alert-success">Le commentaire a bien été supprimé.</div>';
else
	echo '<div class="alert alert-danger">Erreur lors de la suppression du commentaire.</div>';

