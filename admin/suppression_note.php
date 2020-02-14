<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// suppression_note.php
// supprime la note demandée
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='../';
require_once '../inc/init.php';

// Si l'utilisateur n'est pas connecté ou n'est pas admin, ou si $_POST n'est pas réglementaire, ne rien faire
if (!estAdmin() || empty($_POST) || !isset($_POST['id']))
	exit();

// Suppression d'une note
$resultat = executerRequete ("DELETE FROM note WHERE id = :id", array (':id' => $_POST['id']));
if ($resultat->rowCount() == 1)
	echo '<div class="alert alert-success">La note a bien été supprimé.</div>';
else
	echo '<div class="alert alert-danger">Erreur lors de la suppression de la note.</div>';

