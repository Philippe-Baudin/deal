<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// suppression_annonce.php
// supprime l'annonce demandée
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='../';
require_once '../inc/init.php';

// Si l'utilisateur n'est pas connecté ou n'est pas admin, ou si $_POST n'est pas réglementaire, ne rien faire
if (!estAdmin() || empty($_POST) || !isset($_POST['id']))
	exit();

// L'id de l'annonce à supprimer
$id = $_POST['id'];

// Récupérer l'URL de la photo et supprimer le fichier
$resultat = executerRequete ("SELECT photo FROM annonce WHERE id = :id", array (':id' => $id));
if ($resultat && $resultat->rowCount()==1)
	{
	$photo = $resultat->fetch(PDO::FETCH_NUM)[0];
	if (!empty($photo) && file_exists($photo))
		unlink ($photo);
	}

// Suppression des commentaires sur l'annonce
$resultat = executerRequete ("DELETE FROM commentaire WHERE annonce_id = :id", array (':id' => $id));

// Suppression de l'annonce
$resultat = executerRequete ("DELETE FROM annonce WHERE id = :id", array (':id' => $id));
if ($resultat->rowCount() == 1)
	echo '<div class="alert alert-success">L\'annonce a bien été supprimée.</div>';
else
	echo '<div class="alert alert-danger">Erreur lors de la suppression de l\'annonce.</div>';

