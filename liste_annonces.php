<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// liste_annonces.php
// Affichage de la liste d'annonces demandées par index.php via une requête AJAX
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='';
require_once 'inc/init.php';


if (empty($_POST))
	exit ();


// Le début des requêtes à lancer
$requeteDecompte = "SELECT COUNT(a.id)
                    FROM annonce a
                    LEFT JOIN membre ON membre_id = membre.id";
$clauseOrderBy = ' ORDER BY a.date_enregistrement DESC';
$marqueurs = array ();

// Cas de la page accueil : filtres et tri
if (isset ($_POST['filtreCategorie']))
	{
	$requeteSelection = 'SELECT a.id id, titre, photo, description_longue, prix, pseudo, membre.id auteur
	                     FROM annonce a
	                     LEFT JOIN membre ON membre_id = membre.id';

	// Les consignes envoyées par la requête AJAX
	$filtreCategorie = $_POST['filtreCategorie'] ?? '0';
	$filtreMembre    = $_POST['filtreMembre']    ?? '0';
	$filtreVille     = $_POST['filtreVille']     ?? '0';
	$filtrePrix      = $_POST['filtrePrix']      ?? '0';
	$triAccueil      = $_POST['triAccueil']      ?? '0';
	$pageAccueil     = $_POST['pageAccueil']     ?? '0';

	// Mémoriser les consignes dans la session pour qu'elles survivent à de futurs changement de page
	$_SESSION['filtre']['categorie'] = $filtreCategorie;
	$_SESSION['filtre']['membre']    = $filtreMembre;
	$_SESSION['filtre']['ville']     = $filtreVille;
	$_SESSION['filtre']['prix']      = $filtrePrix;
	$_SESSION['triAccueil']          = $triAccueil;
	$_SESSION['pageAccueil']         = $pageAccueil;


	// Récupérer la note de chaque membre
	$listeNotes = array ();
	$resultat = executerRequete("SELECT pseudo, AVG(note) moyenne FROM membre, note WHERE membre.id=note.membre_id2 GROUP BY membre.id");
	while ($ligne = $resultat->fetch (PDO::FETCH_ASSOC))
		{
		extract ($ligne);
		$listeNotes[$pseudo] = $moyenne;
		}

	// Ecrire les requêtes de sélection des annonces à présenter, accompagnées du tableau des marqueurs
	// ------------------------------------------------------------------------------------------------

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
	if ($filtrePrix && $filtrePrix < 7)
		{
		$clauseWhere .= ' AND prix <= :prix';
		$marqueurs [':prix'] = pow (10, $filtrePrix);
		}

	// la clause ORDER BY
	switch ($triAccueil)
		{
		case 1  : $clauseOrderBy = ' ORDER BY a.date_enregistrement'; break;
		case 2  : $clauseOrderBy = ' ORDER BY prix'; break;
		case 3  : $clauseOrderBy = ' ORDER BY prix DESC'; break;
		case 4  : $clauseOrderBy = ' ORDER BY (select COUNT(*) from annonce b where a.membre_id = b.membre_id group by b.membre_id) DESC, a.membre_id'; break;
		default : break;
		}
	$taillePage = TAILLE_PAGE_ACCUEIL;
	$page = (int) $pageAccueil;
	}
else // donc !isset ($_POST['filtreCategorie']) donc affichage sur la page de recherche
	{
	$page = (int)($_POST['page'] ?? '0');
	$_SESSION['pageRecherche'] = $page;
	$taillePage = TAILLE_PAGE_RECHERCHE;

	$requeteSelection = 'SELECT a.id id, titre, photo, description_longue, prix, pseudo, membre.id auteur, CASE';
	for ($i=0; isset($_POST["id_$i"]); $i++)
		$requeteSelection .= ' WHEN a.id = '.$_POST["id_$i"][0].' THEN '.$_POST["id_$i"][1];
//remplacer les indices par des notes, ce qui impose de formatter $_POST comme un tableau de tableaux [id, note]
//parce que là, c'est carrément absurde de trier deux fois les annonces
	$requeteSelection .= " END as pertinence FROM annonce a LEFT JOIN membre ON membre_id = membre.id";
	$clauseWhere = ' WHERE a.id IN (-1';
	for ($i=0; isset($_POST["id_$i"]); $i++)
		$clauseWhere .= ','.$_POST["id_$i"][0];
	$clauseWhere .= ')';
	$clauseOrderBy = ' ORDER BY pertinence DESC';
	}

// Compter les annonces sélectionnées
$resultat = executerRequete ($requeteDecompte.$clauseWhere, $marqueurs);
$nombreAnnonces = $resultat ? ($resultat->fetch(PDO::FETCH_NUM)[0]) : 0;

// En déduire les annonces à afficher, en fonction du numéro de page
if ($nombreAnnonces > $taillePage)
	{
	$annonceDebut = $taillePage*$page;
	$limite = ' LIMIT '.$annonceDebut.','.$taillePage;
	$nombrePages = ceil ($nombreAnnonces/$taillePage);
	}

$resultat = executerRequete ($requeteSelection.$clauseWhere.$clauseOrderBy.($limite??''), $marqueurs);

// Envoyer, en commentaire le nombre d'annonces sélectionnées,
// suivi de suffisamment de blancs pour que le nombre d'annonces puisse augmenter au delà du raisonnable
echo "<!--$nombreAnnonces-->               ";

// Afficher la requête SQL pour debug
/*
echo '<div class="row">';
echo     '<div class="col-sm-12">';
echo         '<p>requete SQL : '.$requeteSelection.$clauseWhere.$clauseOrderBy.($limite??'').'<p>';
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

// Pagination
if (isset($limite))
	pagination ($page, $nombrePages);
