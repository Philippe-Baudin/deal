<?php
$repertoire='';
require_once 'inc/init.php';

if (empty($_POST))
	exit ();

// Enregistrement d'un commentaire
if (isset($_POST['commentaire']))
	{
	if (strlen($_POST['commentaire']) >= 3)
		executerRequete ("INSERT INTO commentaire (commentaire, membre_id, annonce_id, date_enregistrement)
		                  VALUES (:commentaire, :membre_id, :annonce_id, NOW())",
		                  array (':commentaire' => $_POST['commentaire'], ':membre_id' => $_SESSION['membre']['id'], ':annonce_id' => $_POST['id']));
	else
		{
		echo '<div class="alert alert-danger">Ecrivez votre commentaire avant de l\'envoyer&nbsp;...</div>';
		exit ();
		}
	}

// Aller chercher les commentaires sur cette annonce
$commentaires = array();
$id = $_POST['id'];
$resultat = executerRequete ("SELECT commentaire,
                                     pseudo auteur_commentaire,
                                     DATE_FORMAT(commentaire.date_enregistrement,'%d/%m/%Y') date
                              FROM commentaire, membre
                              WHERE membre.id = membre_id AND annonce_id = :id
                              ORDER BY commentaire.date_enregistrement", array(':id'=>$id));

// S'il y a des commentaires, les afficher
if ($resultat->rowCount() > 0)
	{
	echo '<div class="row">';
	echo '<p>Commentaires sur cette annonce :</p>';
	echo '</div>';
	$commentaires = $resultat->fetchAll (PDO::FETCH_ASSOC);
	foreach ($commentaires as $commentaire)
		{
		echo '<div class="row">';
		echo     '<div class="col-sm-3">';
		echo         '<p>de '.$commentaire['auteur_commentaire'].', le '.$commentaire['date'].'</p>';
		echo     '</div>';
		echo     '<div class="col-sm">';
		echo         '<p>'.$commentaire['commentaire'].'</p>';
		echo     '</div>';
		echo '</div>';
		}
	}
else
	{
	echo '<p>Aucun commentaire sur cette annonce</p>';
	}
