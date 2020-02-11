<?php
$repertoire='';
require_once 'inc/init.php';

if (empty($_POST))
	exit ();

// Id de l'auteur de l'annonce
$id = $_POST['id'];

// Enregistrement d'un avis
if (isset($_POST['avis']))
	{
	if (strlen($_POST['avis']) >= 3)
		executerRequete ("INSERT INTO note (avis, note, membre_id1, membre_id2, date_enregistrement)
		                  VALUES (:avis, :note, :membre_id1, :membre_id2, NOW())",
		                 array (':avis' => $_POST['avis'], ':note' => $_POST['note'], ':membre_id1' => $_SESSION['membre']['id'], ':membre_id2' => $_POST['id']));
	else
		{
		echo '<div class="alert alert-danger">Vous devez formuler un avis pour pouvoir mettre une note.</div>';
		exit ();
		}
	}

// Calculer la moyenne des notes de l'auteur de l'annonce et l'afficher
if (!empty($id))
	{
	$resultat = executerRequete ("SELECT count(*) decompte, AVG(note) note
	                             FROM note 
	                             WHERE membre_id2=:auteur", array(':auteur'=>$id));
	$ligne = $resultat->fetch (PDO::FETCH_ASSOC);
	if ($ligne['decompte']>0)
		echo noteEnEtoiles((int)$ligne['note']);
	}

