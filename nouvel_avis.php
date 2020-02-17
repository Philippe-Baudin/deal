<?php
$repertoire='';
require_once 'inc/init.php';

if (empty($_POST))
	exit ();
extract ($_POST);

// Enregistrement d'un avis
$erreur = false;
if (estConnecte() && isset($avis))
	{
	$erreur = true;
	if (!isset ($id))
		echo '<div class="alert alert-danger">Non !</div>';
	elseif (strlen($avis) < 3)
		echo '<div class="alert alert-danger">Il faut déposer un avis d\'au moins 3 caractères pour mettre une note.</div>';
	elseif (!preg_match ("/^[- ²,;:!?.\/€%*+()\"'\n\r&\_a-zA-Z0-9ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝÞàáâãäåæçèéêëìíîïðñòóôõöùúûüýþÿĀāĂăĄąĆćĈĉĊċČčĎďĐđĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħĨĩĪīĬĭĮįİıĲĳĴĵĶķĸĹĺĻļĽľĿŀŁłŃńŅņŇňŊŋŌōŎŏŐőŒœŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŦŧŨũŪūŬŭŮůŰűŲųŴŵŶŷŸŹźŻżŽžſ]*$/u", $avis))
		echo '<div class="alert alert-danger">Il y a des caractères invalides dans l\'avis.</div>';
	else
		{
		executerRequete ("INSERT INTO note (avis, note, membre_id1, membre_id2, date_enregistrement)
		                  VALUES (:avis, :note, :membre_id1, :membre_id2, NOW())",
		                 array (':avis' => $_POST['avis'], ':note' => $_POST['note'], ':membre_id1' => $_SESSION['membre']['id'], ':membre_id2' => $_POST['id']));
		$erreur = false;
		}
	}

// Calculer la moyenne des notes de l'auteur de l'annonce et l'afficher
if (!empty($id) && !$erreur)
	{
	$resultat = executerRequete ("SELECT count(*) decompte, AVG(note) note
	                             FROM note 
	                             WHERE membre_id2=:auteur", array(':auteur'=>$id));
	$ligne = $resultat->fetch (PDO::FETCH_ASSOC);
	if ($ligne['decompte']>0)
		echo noteEnEtoiles($ligne['note']);
	}

