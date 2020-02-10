<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// recherche.php
// présentation des résultat de la recherche
// TODO des "and" des "ou" et des "non" pour la liste de mots-cles
// TODO aller chercher les infos plus profondemment
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='';
require_once 'inc/init.php';
$pageCourante = 'recherche.php';

// ---------------------------------------------------------------
// Abandonner s'il n'y a rien à traiter
// ---------------------------------------------------------------
if (empty($_POST) || !isset ($_POST['mots-cles']))
	{
	if (isset ($_SESSION['mots-cles']))
		{
		$listeMotsCles = $_SESSION['mots-cles'];
		}
	else
		{
		header ('location:index.php');
		exit ();
		}
	}
else
	{
	// Dans un premier temps, je suppose que ce sont des mots séparés par des espaces,
	$listeMotsCles = array_filter (explode (" ", $_POST['mots-cles']));
	if (empty($listeMotsCles))
		{
		header ('location:index.php');
		exit ();
		}
	$_SESSION['mots-cles'] = $listeMotsCles;
	}
$nombreMotsCles = sizeof ($listeMotsCles);

if (!isset($_SESSION['pageRecherche'])) $_SESSION['pageRecherche'] = '0';
$page = (int) ($_GET['page'] ?? 0);

// ---------------------------------------------------------------
// SQL
// ---------------------------------------------------------------

/*
// Version 1
// liste des catégories dont le titre ou le champ 'mot-clefs' matche les mots-clés donnés
// clause WHERE pour sélectionner les mots-cles
$where = 'WHERE 1';
$marqueurs = array ();
foreach ($listeMotsCles as $indice => $motCle)
	{
	$where .= " AND (titre like :mot_cle_$indice OR mots_cles like :mot_cle_$indice)";
	$marqueurs [":mot_cle_$indice"] = '%'.$motCle.'%';
	}
$resultat = executerRequete ("SELECT id from annonce WHERE membre_id IS NOT NULL AND categorie_id IN (SELECT id FROM categorie $where)", $marqueurs);
$nombreAnnonces = $resultat ? $resultat->rowCount() : 0;
if ($nombreAnnonces > 0)
	{
	$listeId = array ();
	while ($ligne = $resultat->fetch (PDO::FETCH_NUM))
		array_push ($listeId, $ligne[0]);
	}
*/

/*
// Version 2 : approfondir :
selectionner chaque mot clef indépendamment
calculer la note de chaque annonce où le mot-clef apparaît
	+1.3 s'il apparaît dans le titre
	+1.2 s'il apparaît dans le libellé court
	+1.1 s'il apparaît dans le libellé long
	+1   s'il apparaît dans le titre ou les mots-cles de la categorie
creer une liste indicée 0 des id+note pour le premier mot-cle
      une liste indicée 1 des id+note pour le deuxième mot-cle
      etc.
fusionner les listes : seuls survivent ceux qui appartiennent à toutes
trier la liste finale par note décroissante
*/

// 1. Requête SQL
$requete = '';
foreach ($listeMotsCles as $indice => $motCle)
	{
	if ($requete != '')	$requete .= ' UNION ';
	$requete .= "(SELECT $indice indice, id, 1 note from annonce WHERE membre_id IS NOT NULL AND categorie_id IN (SELECT id FROM categorie WHERE titre like :mot_cle_$indice OR mots_cles like :mot_cle_$indice))";
	$requete .= ' UNION ';
	$requete .= "(SELECT $indice indice, id, 1.1 note from annonce WHERE membre_id IS NOT NULL AND description_longue like :mot_cle_$indice)";
	$requete .= ' UNION ';
	$requete .= "(SELECT $indice indice, id, 1.2 note from annonce WHERE membre_id IS NOT NULL AND description_courte like :mot_cle_$indice)";
	$requete .= ' UNION ';
	$requete .= "(SELECT $indice indice, id, 1.3 note from annonce WHERE membre_id IS NOT NULL AND titre like :mot_cle_$indice)";
	$marqueurs [":mot_cle_$indice"] = '%'.$motCle.'%';
	}
$resultat = executerRequete ($requete, $marqueurs);
$nombreAnnonces = $resultat ? $resultat->rowCount() : 0;
if ($nombreAnnonces > 0)
	{
	$listesAnnonces=array();
	while ($ligne = $resultat->fetch (PDO::FETCH_ASSOC))
		{
		extract ($ligne);
		if (!isset($listesAnnonces[$indice])) $listesAnnonces[$indice] = array();
		$listesAnnonces[$indice][$id]=$note;
		}
	// 2. Fusion des listes
	$listeAnnonces = array();
	foreach ($listesAnnonces[0] as $id => $note)
		{
		for ($i=1; $i<$nombreMotsCles; $i++)
			{
			if (isset ($listesAnnonces[$i][$id]))
				$note += $listesAnnonces[$i][$id];
			else
				{
				$note = 0;
				break;
				}
			}
		if ($note) $listeAnnonces [$id] = $note;
		}
	}


// ---------------------------------------------------
// Présentation du résultat de la sélection d'annonces
// ---------------------------------------------------


$contenu .= '<br>';
$contenu .= '<div class="row">';


// Nombre d'annonces sélectionnées
// -------------------------------
$contenu .=     '<div class="col-sm-12">';
$contenu .=         '<p>';
switch ($nombreAnnonces)
	{
	case '0'  : $contenu .= 'Aucune annonce ne correspond à votre recherche.'; break;
	case '1'  : $contenu .= '1 annonce correspond à votre recherche.'; break;
	default   : $contenu .= $nombreAnnonces.' annonces correspondent à votre recherche.'; break;
	}
$contenu .=         '</p>';
$contenu .=     '</div>'; // col-sm-12
$contenu .= '</div>'; // row

if ($nombreAnnonces > 0)
	{
	// Affichage des annonces sélectionnées
	// ------------------------------------
	$contenu .= '<div class="row">';
	$contenu .=     '<div class="col-sm-11" id="liste-annonces">';

	//                  C'est là que va se loger le retour de la requête AJAX (je fais une requête AJAX pour avoir la même présentation que dans la page accueil)
	//XXX ce serait mieux de faire un include. il faut renseigner $_POST
	//require_once 'liste_annonces.php';
	$contenu .=     '</div>';
	$contenu .= '</div>';
	}

// Header standard
// ---------------
require_once 'inc/header.php';
require_once 'connexion_modale.php';
require_once 'inscription_modale.php';

// Affichage du contenu qu'on vient de construire
// ----------------------------------------------
if (!empty($messageInscription))
	echo '<script>$(document).ready(function(){$("#modaleInscription").modal("show");});</script>';
if (!empty($messageConnexion))
	echo '<script>$(document).ready(function(){$("#modaleConnexion").modal("show");});</script>';
?>
<script>$(".container").css("padding","0").css("margin","30px");</script>
<?php
echo $contenu;
if ($nombreAnnonces > 0)
	{
	?>
	<script>
		$(function(){ // document ready

			<?php
			echo 'let page  = "'.$_SESSION['pageRecherche'].'";';
			?>

			// réception et traitement de la réponse à la requête AJAX
			function reponse (contenu)
				{
				$('#liste-annonces').html(contenu);
				$('.page-item').on('click', 'a', function(e)
					{
					page = e.target.id.replace(/[^0-9]/g, '');
					requeteAjax ();
					});
				}
			function requeteAjax ()
				{
				// Emission de la requête AJAX
				$.post("liste_annonces.php", {
					page : page,
					<?php
						/* Version 1
						for ($i=0; $i<$nombreAnnonces; $i++)
						 	echo 'id_'.$i.' : '.$listeId[$i].',';
						*/
						 // version 2
						 $i=0;
						 foreach ($listeAnnonces as $id => $note)
						 	echo 'id_'.($i++).' : ['.$id.','.$note.'],';
					?>			                               
                	}, reponse, "html");
				}
			requeteAjax();

		}); // document ready
	</script>
	<?php
	} // fin if ($nombreAnnonces > 0)

// Et le footer standard
require_once 'inc/footer.php';

