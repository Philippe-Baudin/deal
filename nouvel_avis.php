<?php
require_once 'inc/init.php';
//debug ($_POST);
if (empty($_POST))
	exit ();

// Id de l'auteur de l'annonce
$id = $_POST['id'];

// Enregistrement d'un avis
if (isset($_POST['avis']))
	{
	//XXX contrôle sur les valeurs
	if (strlen($_POST['avis']) >= 3)
		executerRequete ("INSERT INTO note (avis, note, membre_id1, membre_id2, date_enregistrement)
		                  VALUES (:avis, :note, :membre_id1, :membre_id2, NOW())",
		                 array (':avis' => $_POST['avis'], ':note' => $_POST['note'], ':membre_id1' => $_SESSION['membre']['id'], ':membre_id2' => $_POST['id']));
	}

// Calculer la moyenne des notes de l'auteur de l'annonce et l'afficher
if (!empty($id))
	{
	$requete = executerRequete ("SELECT count(*) decompte, AVG(note) note
	                             FROM note 
	                             WHERE membre_id2=:auteur", array(':auteur'=>$id));
	$resultat = $requete->fetch (PDO::FETCH_ASSOC);
	if ($resultat['decompte']>0)
		echo noteEnEtoiles((int)$resultat['note']);
	}

