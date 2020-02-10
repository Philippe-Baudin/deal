<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// liste_commentaires.php
// Affichage de la liste de commentaires d'une annonce demandée via une requête AJAX
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='';
require_once 'inc/init.php';

if (empty($_POST) || !isset($_POST['id']))
	exit ();

// La consignes envoyées par la requête AJAX
$id = $_POST['id'];
$date = $_POST['date']??'';

// Constituer la requête et le tableeau de marqueurs selectionnant les commentaires à afficher
$requete = "SELECT pseudo, commentaire, a.date_enregistrement date_lue
            FROM commentaire a, membre
            WHERE a.annonce_id = :id AND membre.id=a.membre_id";
$marqueurs = array (':id' => $id);
if ($date)
	{
	$requete .= " AND a.date_enregistrement >= :date";
	$marqueurs [':date'] = $date;
	}
$requete.= " ORDER BY date_lue";

// Récupérer les commentaires à afficher
$resultat = executerRequete ($requete, $marqueurs);

if ($resultat->rowCount() == 0)
	{
	echo '<p>Aucun membre n\'a laissé de commentaire</h5>';
	exit ();
	}

// Afficher les commentaires
while ($ligne = $resultat->fetch (PDO::FETCH_ASSOC))
	{
	extract ($ligne);
	echo '<h6>Commentaire de '.$pseudo.', daté du '.$date_lue.'</h6>';
	echo '<p style="margin-left:50px;">'.$commentaire.'<p>';
	}
if ($date)
	{
	echo '<a href="" onclick="suiteCommentaires('.$id.');return false">Afficher tous les commentaires ...</a>';
	}
else
	{
	$resultat = executerRequete ("SELECT MAX(date_enregistrement) FROM commentaire WHERE annonce_id = :id AND membre_id = :moi",
	                             array (':id' => $id, ':moi' => $_SESSION['membre']['id']));
	if ($resultat->rowCount() == 1)
		{
		$date = $resultat->fetch(PDO::FETCH_NUM)[0];
		if ($date)
			echo '<a href="" onclick="suiteCommentaires('.$id.',\''.$date.'\');return false">Afficher moins de commentaires ...</a>';
		}
	}

