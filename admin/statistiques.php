<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// statistiques.php
// affiche des stat sur la base de données
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once '../inc/init.php';

//1. Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers connection
	header ('location:../connexion.php');
	exit ();
	}

$resultat = executerRequete ("SELECT COUNT(*) FROM annonce");
$nombreAnnonces = $resultat->fetch (PDO::FETCH_NUM)[0];

$resultat = executerRequete ("SELECT COUNT(*) FROM membre");
$nombreMembres = $resultat->fetch (PDO::FETCH_NUM)[0];

$resultat = executerRequete ("SELECT COUNT(*) FROM commentaire");
$nombreCommentaires = $resultat->fetch (PDO::FETCH_NUM)[0];

$resultat = executerRequete ("SELECT COUNT(*) FROM note");
$nombreAvis = $resultat->fetch (PDO::FETCH_NUM)[0];

$contenu .= '<div style="padding:20px; margin:20px;">';
$contenu .= '<div class="row">';
$contenu .= '<div class="col-sm-2">';
$contenu .= '</div>';
$contenu .= '<div class="col-sm">';
$contenu .= "<p>$nombreMembres membres / $nombreAnnonces annonces / $nombreCommentaires commentaires / $nombreAvis avis</p>";
$contenu .= '</div>';
$contenu .= '<div class="col-sm-12">';
$contenu .= '<hr>';
$contenu .= '</div>';
$contenu .= '</div>';
$contenu .= '<div class="row">';
$contenu .= '<div class="col-sm-6">';
$contenu .= '<h4>Membres les mieux notés</h4>';
$resultat=executerRequete("SELECT pseudo, COUNT(note) nombreAvis, AVG(note) moyenne FROM membre LEFT JOIN note ON membre.id=note.membre_id2 GROUP BY membre.id ORDER BY moyenne DESC LIMIT 5");
$table = $resultat->fetchAll (PDO::FETCH_ASSOC);
for ($i=0; $i<5 && $i<$resultat->rowCount(); $i++)
	$contenu .= '<p>'.($i+1).' - '. $table[$i]['pseudo'].' : '.sprintf ("%.1f", $table[$i]['moyenne']/4).' étoiles basé sur '.$table[$i]['nombreAvis'].' avis</p>';
$contenu .= '</div>'; // class="col-sm-6">';

$contenu .= '<div class="col-sm-6">';
$contenu .= '<h4>Membres les plus actifs</h4>';
$resultat=executerRequete("SELECT pseudo, COUNT(annonce.id) nombreAnnonces FROM membre LEFT JOIN annonce ON membre.id=membre_id GROUP BY membre.id ORDER BY nombreAnnonces DESC LIMIT 5");
$table = $resultat->fetchAll (PDO::FETCH_ASSOC);
for ($i=0; $i<5 && $i<$resultat->rowCount(); $i++)
	$contenu .= '<p>'.($i+1).' - '. $table[$i]['pseudo'].' : '.$table[$i]['nombreAnnonces'].' annonces</p>';
$contenu .= '</div>'; // class="col-sm-6">';
$contenu .= '</div>'; // class="row">';

$contenu .= '<div class="row"><hr></div>';

$contenu .= '<div class="row">';
$contenu .= '<div class="col-sm-6">';
$contenu .= '<h4>Annonces les plus anciennes</h4>';
$resultat=executerRequete("SELECT id, titre, DATE_FORMAT(date_enregistrement,'%d/%m/%Y') date FROM annonce ORDER BY date_enregistrement LIMIT 5");
$table = $resultat->fetchAll (PDO::FETCH_ASSOC);
for ($i=0; $i<5 && $i<$resultat->rowCount(); $i++)
	$contenu .= '<p>'.($i+1).' - annonce '. $table[$i]['id'].' : '.$table[$i]['titre'].', postée le '.$table[$i]['date'].'</p>';
$contenu .= '</div>'; // class="col-sm-6">';

$contenu .= '<div class="col-sm-6">';
$contenu .= '<h4>Catégories contenant le plus d\'annonces</h4>';
$resultat=executerRequete("SELECT categorie.titre titre, COUNT(annonce.id) nombreAnnonces FROM categorie LEFT JOIN annonce ON categorie.id = categorie_id GROUP BY (categorie.id) ORDER BY nombreAnnonces DESC LIMIT 5");
$table = $resultat->fetchAll (PDO::FETCH_ASSOC);
for ($i=0; $i<5 && $i<$resultat->rowCount(); $i++)
	$contenu .= '<p>'.($i+1).' - catégorie '. $table[$i]['titre'].' : '.$table[$i]['nombreAnnonces'].' annonce'.(($table[$i]['nombreAnnonces']*1)>1?'s':'').'</p>';
$contenu .= '<hr>';
$contenu .= '</div>'; // class="col-sm-6">';
$contenu .= '</div>'; // class="row">';
$contenu .= '</div>'; // padding



require_once '../inc/header.php';

//2. Navigation entre les pages d'administration
navigation_admin ('Statistiques');

echo $contenu;


require_once '../inc/footer.php';
