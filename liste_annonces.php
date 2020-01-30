<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// index.php
// filtre et tri de la liste des annonces à afficher
// délégation de l'affichage de la liste à "liste_annonce.php" via des requêtes ajax
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once 'inc/init.php';

if (empty($_POST))
	exit ();

$filtreCategorie = $_POST['filtreCategorie'] ?? '0';
$filtreMembre    = $_POST['filtreMembre']    ?? '0';
$filtreVille     = $_POST['filtreVille']     ?? '0';
$filtrePrix      = $_POST['filtrePrix']      ?? '0';
$triAccueil      = $_POST['triAccueil']      ?? '0';

$_SESSION['filtre']['categorie'] = $filtreCategorie;
$_SESSION['filtre']['membre']    = $filtreMembre;
$_SESSION['filtre']['ville']     = $filtreVille;
$_SESSION['filtre']['prix']      = $filtrePrix;
$_SESSION['triAccueil']          = $triAccueil;

// Récupérer la note de chaque membre
$listeNotes = array ();
$resultat = executerRequete("SELECT pseudo, AVG(note) moyenne FROM membre, note WHERE membre.id=note.membre_id2 GROUP BY membre.id");
while ($ligne = $resultat->fetch (PDO::FETCH_ASSOC))
	{
	extract ($ligne);
	$listeNotes[$pseudo] = $moyenne;
	}

$marqueurs = array ();
$requete = 'SELECT a.id id, titre, photo, description_longue, prix, pseudo
            FROM annonce a
            LEFT JOIN membre ON membre_id = membre.id';
$clauseWhere = '';
$premiere = true;
if ($filtreCategorie)
	{
	if ($premiere)
		$clauseWhere .= ' WHERE ';
	else
		$clauseWhere .= ' AND ';
	$clauseWhere .= 'categorie_id = :categorie_id';
	$marqueurs [':categorie_id'] = $filtreCategorie;
	$premiere = false;
	}
if ($filtreVille)
	{
	if ($premiere)
		$clauseWhere .= ' WHERE ';
	else
		$clauseWhere .= ' AND ';
	$clauseWhere .= 'ville = :ville';
	$marqueurs [':ville'] = $filtreVille;
	$premiere = false;
	}
if ($filtreMembre)
	{
	if ($premiere)
		$clauseWhere .= ' WHERE ';
	else
		$clauseWhere .= ' AND ';
	$clauseWhere .= 'pseudo = :pseudo';
	$marqueurs [':pseudo'] = $filtreMembre;
	$premiere = false;
	}
if ($filtrePrix && $filtrePrix < 1e7)
	{
	if ($premiere)
		$clauseWhere .= ' WHERE ';
	else
		$clauseWhere .= ' AND ';
	$clauseWhere .= 'prix <= :prix';
	$marqueurs [':prix'] = $filtrePrix;
	$premiere = false;
	}

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
		echo         '<img src='.$photo.' style="max-width:100%">';
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
	echo             '<h4>'.$pseudo.'&nbsp;</h4>';
	if (isset($listeNotes[$pseudo])) echo '<p>'.noteEnEtoiles($listeNotes[$pseudo]).'</p>';
	echo         '</div>';
	echo     '</div>';
	/*
	echo     '<div class="col-sm-7">';
	echo          '<p> '.(isset ($listeNotes[$pseudo]) ? (' note : '.sprintf ("%.1f", $listeNotes[$pseudo])) : '').'</p>';
	echo      '</div>';
	*/
	echo      '<div class="col-sm">';
	echo          "<h4>$prix €</h4>";
	echo      '</div>';
	echo '</div>'; // "row"
	}

