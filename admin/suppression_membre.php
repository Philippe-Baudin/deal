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

$id = $_POST['id'];

// Suppression d'un membre, et répercussions sur les annonces, les commentaires et les notes
$resultat = executerRequete ("UPDATE annonce SET  membre_id = NULL WHERE membre_id = :id", array (':id' => $id));
$resultat = executerRequete ("UPDATE commentaire SET  membre_id = NULL WHERE membre_id = :id", array (':id' => $id));
$resultat = executerRequete ("UPDATE note SET  membre_id1 = NULL WHERE membre_id1 = :id", array (':id' => $id));
$resultat = executerRequete ("DELETE FROM note WHERE membre_id2 = :id", array (':id' => $id));
$resultat = executerRequete ("DELETE FROM membre WHERE id = :id", array (':id' => $id));
if ($resultat->rowCount() == 1)
	echo '<div class="alert alert-success">Le membre a bien été supprimé.</div>';
else
	echo '<div class="alert alert-danger">Erreur lors de la suppression du membre.</div>';

