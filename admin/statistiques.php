<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// statistiques.php
// affiche des stat sur la base de données
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once '../inc/init.php';

// Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers l'accueil
	header ('location:../index.php');
	exit ();
	}

// Compter les annonces, les membres, les commentaires et les notes
$resultat = executerRequete ("SELECT COUNT(*) FROM annonce");
$nombreAnnonces = $resultat->fetch (PDO::FETCH_NUM)[0];

$resultat = executerRequete ("SELECT COUNT(*) FROM membre");
$nombreMembres = $resultat->fetch (PDO::FETCH_NUM)[0];

$resultat = executerRequete ("SELECT COUNT(*) FROM commentaire");
$nombreCommentaires = $resultat->fetch (PDO::FETCH_NUM)[0];

$resultat = executerRequete ("SELECT COUNT(*) FROM note");
$nombreAvis = $resultat->fetch (PDO::FETCH_NUM)[0];

// Aller chercher les données à afficher et les stocker dans des tableaux
// Membres les mieux notés
$resultat=executerRequete("SELECT pseudo, COUNT(note) nombreAvis, AVG(note) moyenne FROM membre LEFT JOIN note ON membre.id=note.membre_id2 GROUP BY membre.id ORDER BY moyenne DESC LIMIT 5");
$tableAvis = $resultat->fetchAll (PDO::FETCH_ASSOC);

// Membres les plus actifs
$resultat=executerRequete("SELECT pseudo, COUNT(annonce.id) nombreAnnonces FROM membre LEFT JOIN annonce ON membre.id=membre_id GROUP BY membre.id ORDER BY nombreAnnonces DESC LIMIT 5");
$tableActifs = $resultat->fetchAll (PDO::FETCH_ASSOC);

// Annonces les plus anciennes
$resultat=executerRequete("SELECT id, titre, DATE_FORMAT(date_enregistrement,'%d/%m/%Y') date FROM annonce ORDER BY date_enregistrement LIMIT 5");
$tableAnnonces = $resultat->fetchAll (PDO::FETCH_ASSOC);

// Catégories contenant le plus d'annonces
$resultat=executerRequete("SELECT categorie.titre titre, COUNT(annonce.id) nombreAnnonces FROM categorie LEFT JOIN annonce ON categorie.id = categorie_id GROUP BY (categorie.id) ORDER BY nombreAnnonces DESC LIMIT 5");
$tableCategories = $resultat->fetchAll (PDO::FETCH_ASSOC);

$contenu .= '<div style="padding:20px; margin:20px;">';
$contenu .=     '<div class="row">';
$contenu .=         '<div class="col-sm-2">';
$contenu .=         '</div>';
$contenu .=         '<div class="col-sm">';
$contenu .=             "<p>$nombreMembres membres / $nombreAnnonces annonces / $nombreCommentaires commentaires / $nombreAvis avis</p>";
$contenu .=         '</div>';
$contenu .=         '<div class="col-sm-12">';
$contenu .=             '<hr>';
$contenu .=         '</div>';
$contenu .=     '</div>';

$contenu .=     '<div class="row">';
$contenu .=         '<div class="col-sm-6">';
$contenu .=             '<h4>Membres les mieux notés</h4>';
for ($i=0; $i<5 && $i<sizeof($tableAvis); $i++)
	$contenu .=         '<p>'.($i+1).' - '. $tableAvis[$i]['pseudo'].' : '.noteEnEtoiles($tableAvis[$i]['moyenne']).' (basé sur '.$tableAvis[$i]['nombreAvis'].' avis)</p>';
$contenu .=         '</div>'; // class="col-sm-6">';

$contenu .=         '<div class="col-sm-6">';
$contenu .=             '<h4>Membres les plus actifs</h4>';
for ($i=0; $i<5 && $i<sizeof($tableActifs); $i++)
	$contenu .=         '<p>'.($i+1).' - '. $tableActifs[$i]['pseudo'].' : '.$tableActifs[$i]['nombreAnnonces'].' annonces</p>';
$contenu .=         '</div>'; // class="col-sm-6">';
$contenu .=     '</div>'; // class="row">';

$contenu .=     '<div class="row"><hr></div>';

$contenu .=     '<div class="row">';
$contenu .=         '<div class="col-sm-6">';
$contenu .=             '<h4>Annonces les plus anciennes</h4>';
for ($i=0; $i<5 && $i<sizeof($tableAnnonces); $i++)
	$contenu .=         '<p>'.($i+1).' - annonce '. $tableAnnonces[$i]['id'].' : '.$tableAnnonces[$i]['titre'].', postée le '.$tableAnnonces[$i]['date'].'</p>';
$contenu .=         '</div>'; // class="col-sm-6">';

$contenu .=         '<div class="col-sm-6">';
$contenu .=             '<h4>Catégories contenant le plus d\'annonces</h4>';
for ($i=0; $i<5 && $i<sizeof($tableCategories); $i++)
	$contenu .=         '<p>'.($i+1).' - catégorie '. $tableCategories[$i]['titre'].' : '.$tableCategories[$i]['nombreAnnonces'].' annonce'.(($tableCategories[$i]['nombreAnnonces']*1)>1?'s':'').'</p>';
$contenu .=             '<hr>';
$contenu .=         '</div>'; // class="col-sm-6">';
$contenu .=     '</div>'; // class="row">';
$contenu .= '</div>'; // padding

// Header standard
require_once '../inc/header.php';

// Navigation entre les pages d'administration
navigationAdmin ('Statistiques');

// Affichage des résultats
echo $contenu;

// Footer standard
require_once '../inc/footer.php';
