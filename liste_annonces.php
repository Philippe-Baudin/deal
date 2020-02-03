<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// liste_annonces.php
// Affichage de la liste d'annonces demandées par index.php via une requête AJAX
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once 'inc/init.php';

if (empty($_POST))
	exit ();

//echo 'avant : '; print_r ($_SESSION);

// Les consignes envoyées par la requête AJAX
$filtreCategorie = $_POST['filtreCategorie'] ?? '0';
$filtreMembre    = $_POST['filtreMembre']    ?? '0';
$filtreVille     = $_POST['filtreVille']     ?? '0';
$filtrePrix      = $_POST['filtrePrix']      ?? '0';
$triAccueil      = $_POST['triAccueil']      ?? '0';

// Memoriser les consignes dans la session pour qu'elles survivent à de futurs changement de page
$_SESSION['filtre']['categorie'] = $filtreCategorie;
$_SESSION['filtre']['membre']    = $filtreMembre;
$_SESSION['filtre']['ville']     = $filtreVille;
$_SESSION['filtre']['prix']      = $filtrePrix;
$_SESSION['triAccueil']          = $triAccueil;

//echo 'après : '; print_r ($_SESSION);

// Récupérer la note de chaque membre
$listeNotes = array ();
$resultat = executerRequete("SELECT pseudo, AVG(note) moyenne FROM membre, note WHERE membre.id=note.membre_id2 GROUP BY membre.id");
while ($ligne = $resultat->fetch (PDO::FETCH_ASSOC))
	{
	extract ($ligne);
	$listeNotes[$pseudo] = $moyenne;
	}

// Ecriture de la requête de sélection des annonces à présenter, accompagnée du tableau des marqueurs
// --------------------------------------------------------------------------------------------------

// La liste des champs voulus et des tables.
$marqueurs = array ();
$requete = 'SELECT a.id id, titre, photo, description_longue, prix, pseudo, membre.id auteur
            FROM annonce a
            LEFT JOIN membre ON membre_id = membre.id';

// la clause WHERE
$clauseWhere = ' WHERE membre_id IS NOT NULL';
if ($filtreCategorie)
	{
	$clauseWhere .= ' AND categorie_id = :categorie_id';
	$marqueurs [':categorie_id'] = $filtreCategorie;
	}
if ($filtreVille)
	{
	$clauseWhere .= ' AND ville = :ville';
	$marqueurs [':ville'] = $filtreVille;
	}
if ($filtreMembre)
	{
	$clauseWhere .= ' AND pseudo = :pseudo';
	$marqueurs [':pseudo'] = $filtreMembre;
	}
if ($filtrePrix && $filtrePrix < 1e7)
	{
	$clauseWhere .= ' AND prix <= :prix';
	$marqueurs [':prix'] = $filtrePrix;
	}

// la clause ORDER BY
switch ($triAccueil)
	{
	case 0  : $clauseOrderBy = ' ORDER BY a.date_enregistrement DESC'; break;
	case 1  : $clauseOrderBy = ' ORDER BY a.date_enregistrement'; break;
	case 2  : $clauseOrderBy = ' ORDER BY prix'; break;
	case 3  : $clauseOrderBy = ' ORDER BY prix DESC'; break;
	case 4  : $clauseOrderBy = ' ORDER BY (select COUNT(*) from annonce b where a.membre_id = b.membre_id group by b.membre_id) DESC, a.membre_id'; break;
	default : $clauseOrderBy = ' ORDER BY a.date_enregistrement DESC'; break;
	}

// Exécuter la requête
$resultat = executerRequete ($requete.$clauseWhere.$clauseOrderBy, $marqueurs);
$nombreAnnonces = $resultat ? $resultat->rowCount() : 0;

// Envoyer, en commentaire le nombre d'annonces sélectionnées,
// suivi de suffisamment de blancs pour que le nombre d'annonces puisse augmenter au delà du raisonnable
echo "<!--$nombreAnnonces-->               ";


/*
// Afficher la requête SQL pour debug
echo '<div class="row">';
echo     '<div class="col-sm-12">';
echo         '<p>requete SQL : '.$requete.$clauseWhere.$clauseOrderBy.'<p>';
echo         '<p>'.print_r($marqueurs).'</p>';
echo     '</div>';
echo '</div>';
*/

// Afficher la liste des annonces
while ($ligne = $resultat->fetch (PDO::FETCH_ASSOC))
	{
	extract ($ligne);
	if (strlen ($description_longue) > 250) $description_longue = mb_substr($description_longue, 0, 250, 'UTF-8').'...';
	echo '<div class="row">';
	echo     '<div class="col-sm-12">';
	echo     '<hr></div>';
	if (!empty ($photo))
		{
		echo '<div class="col-sm-4">';
		echo     '<a href="fiche_annonce.php?id='.$id.'">';
		echo         '<img src='.$photo.' style="max-width:100%; max-height:150px">';
		echo     '</a>';
		echo '</div>'; // "col-sm-4"
		}
	echo     '<div class="col-sm">';
	echo         '<a href="fiche_annonce.php?id='.$id.'">';
	echo             "<h3>$titre</h3>";
	echo         '</a>';
	echo         "<p>$description_longue</p>";
	echo     '</div>';
	echo '</div>'; // "row"
	echo '<div class="row">';
	echo     '<div class="col-sm-9">';
	echo         '<div class="row">';
	if (estAdmin())
		echo         '<a href="admin/gestion_membres.php?modification='.$auteur.'#formulaire"><h4>'.$pseudo.'&nbsp;</h4></a>';
	else
		echo         '<h4>'.$pseudo.'&nbsp;</h4>';
	if (isset($listeNotes[$pseudo])) echo '<p>'.noteEnEtoiles($listeNotes[$pseudo]).'</p>';
	echo         '</div>';
	echo     '</div>';
	echo      '<div class="col-sm">';
	echo          "<h4>$prix €</h4>";
	echo      '</div>';
	echo '</div>'; // "row"
	}

