<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// index.php
// filtre et tri de la liste des annonces à afficher
// délégation de l'affichage de la liste à "liste_annonces.php" via des requêtes ajax
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='';
require_once 'inc/init.php';
$pageCourante = 'index.php';

// Initialiser les filtres et tri dans la session s'il n'y figurent pas encore
// ---------------------------------------------------------------------------
if (!isset($_SESSION['filtre']))              $_SESSION['filtre'] = array();
if (!isset($_SESSION['filtre']['categorie'])) $_SESSION['filtre']['categorie'] = '0';
if (!isset($_SESSION['filtre']['ville']))     $_SESSION['filtre']['ville']     = '0';
if (!isset($_SESSION['filtre']['membre']))    $_SESSION['filtre']['membre']    = '0';
if (!isset($_SESSION['filtre']['prix']))      $_SESSION['filtre']['prix']      = '7';
if (!isset($_SESSION['triAccueil']))          $_SESSION['triAccueil']          = '0';
if (!isset($_SESSION['pageAccueil']))         $_SESSION['pageAccueil']         = '0';
$_SESSION['mots-cles'] = '';

// ---------------------------------------------------------------
// SQL
// ---------------------------------------------------------------


// Liste des catégories, pour la sélection du filtre par catégorie
// ---------------------------------------------------------------
$listeCategories = array ();
$resultat = executerRequete("SELECT id, titre, mots_cles FROM categorie ORDER by id");
if ($resultat)
	$listeCategories = $resultat->fetchAll (PDO::FETCH_ASSOC);

// liste des villes
// ----------------
$listeVilles = array ();
$resultat = executerRequete("SELECT DISTINCT ville FROM annonce ORDER BY ville");
if ($resultat)
	{
	while ($ligne = $resultat->fetch (PDO::FETCH_NUM))
		{
		array_push ($listeVilles, $ligne[0]);
		}
	}

// liste des membres ayant publié au moins une annonce
// ---------------------------------------------------
$listeMembres = array ();
$resultat = executerRequete("SELECT id, pseudo FROM membre where 0 < (SELECT COUNT(id) FROM annonce WHERE membre_id = membre.id) ORDER BY pseudo");
if ($resultat)
	{
	while ($ligne = $resultat->fetch (PDO::FETCH_ASSOC))
		{
		extract ($ligne);
		$listeMembres[$id] = $pseudo;
		}
	}

// -------------------------------------------------------------
// Boîtes de sélection de choix des filtres et du critère de tri
// -------------------------------------------------------------

// Par catégorie
// -------------
$contenu .= '<br>';
$contenu .= '<div class="row">';
$contenu .=     '<div class="col-sm-4">';
$contenu .=         '<div class="form-group">';
$contenu .=             '<label for="categorie" class="col-form-label">Catégorie :</label>';
$contenu .=             '<select class="form-control" id="categorie">';
$contenu .=                 "<option value='0'>Toutes les catégories</option>";
foreach ($listeCategories as $categorie)
	$contenu .=         '<option value="'.$categorie['id'].'" title="'.$categorie['mots_cles'].'"'.(($categorie['id'] == $_SESSION['filtre']['categorie'])?' selected':'').'>'.$categorie['titre'].'</option>';
$contenu .=             '</select>';
$contenu .=         '</div>';

// Par ville
// ---------
$contenu .=         '<div class="form-group">';
$contenu .=             '<label for="ville" class="col-form-label">Ville :</label>';
$contenu .=             '<select class="form-control" id="ville">';
$contenu .=                 "<option value='0'>Toutes les villes</option>";
foreach ($listeVilles as $ville)
	if ($ville === $_SESSION['filtre']['ville'])
		$contenu .=         "<option selected>$ville</option>";
	else
		$contenu .=         "<option>$ville</option>";
$contenu .=             '</select>';
$contenu .=         '</div>';

// Par auteur des annonces
// -----------------------
$contenu .=         '<div class="form-group">';
$contenu .=             '<label for="membre" class="col-form-label">Membre :</label>';
$contenu .=             '<select class="form-control" id="membre">';
$contenu .=                 "<option value='0'>Tous les membres</option>";
foreach ($listeMembres as $id => $pseudo)
	if ($pseudo === $_SESSION['filtre']['membre'])
		$contenu .=         "<option selected>$pseudo</option>";
	else
		$contenu .=         "<option>$pseudo</option>";
$contenu .=             '</select>';
$contenu .=         '</div><br>';

// par prix maximum
// ----------------
$contenu .=         '<div class="form-group">';
$contenu .=             '<label for="range-prix">Prix :</label>';
$contenu .=             '<input type="range" class="custom-range" min="0" max="7" step="0.1" id="range-prix" value="'.($_SESSION['filtre']['prix']??7).'">';
$contenu .=             '<p style="font-size:0.8rem;" id="affichage-prix"></p>';
$contenu .=         '</div>';


// ---------------------------------------------------
// Présentation du résultat de la sélection d'annonces
// ---------------------------------------------------

// Nombre d'annonces sélectionnées
// -------------------------------
$contenu .=         '<p id="nombre-annonces">';
//                      C'est là qu'on va écrire le nombre d'annonces, communiqué par la requête AJAX
$contenu .=         '</p>';
$contenu .=     '</div>'; // col-sm-4

// Choix du critère de tri
// -----------------------
$contenu .=     '<div class="col-sm">';
$contenu .=         '<div class="row">';
$contenu .=             '<div class="col-sm-2">';
$contenu .=             '</div>';
$contenu .=             '<div class="col-sm-8">';
$contenu .=                 '<div class="form-group">';
$contenu .=                     '<select class="form-control" id="tri">';
$contenu .=                         '<option value="0"'.($_SESSION['triAccueil']==='0'?' selected':'').'>Trier par date (de la plus récente à la plus ancienne)</option>';
$contenu .=                         '<option value="1"'.($_SESSION['triAccueil']==='1'?' selected':'').'>Trier par date (de la plus ancienne à la plus récente)</option>';
$contenu .=                         '<option value="2"'.($_SESSION['triAccueil']==='2'?' selected':'').'>Trier par prix (du moins cher au plus cher)</option>';
$contenu .=                         '<option value="3"'.($_SESSION['triAccueil']==='3'?' selected':'').'>Trier par prix (du plus cher au moins cher)</option>';
$contenu .=                         '<option value="4"'.($_SESSION['triAccueil']==='4'?' selected':'').'>Les meilleurs vendeurs en premier</option>';
$contenu .=                     '</select>';
$contenu .=                 '</div>';
$contenu .=             '</div>'; // col-sm-8
$contenu .=         '</div>'; // row

// Affichage des annonces sélectionnées
// ------------------------------------
$contenu .=         '<div class="row">';
$contenu .=             '<div class="col-sm-12" id="liste-annonces">';
//                           C'est là que va se loger le retour de la requête AJAX
$contenu .=             '</div>';
$contenu .=         '</div>';
$contenu .=     '</div>'; // col-sm
$contenu .= '</div>';

// Header standard
// ---------------
require_once 'inc/header.php';
?>
<script>
	$(function(){ // document ready

		// Les filtres et le tri
		<?php
			echo 'let filtreCategorie = "'.$_SESSION['filtre']['categorie'].'";';
			echo 'let filtreVille     = "'.$_SESSION['filtre']['ville'].'";';
			echo 'let filtreMembre    = "'.$_SESSION['filtre']['membre'].'";';
			echo 'let filtrePrix      = "'.$_SESSION['filtre']['prix'].'";';
			echo 'let triAccueil      = "'.$_SESSION['triAccueil'].'";';
			echo 'let pageAccueil     = "'.$_SESSION['pageAccueil'].'";';
		?>
		
		// les différents select
		let categorie = $('select#categorie');
		let ville     = $('select#ville');
		let membre    = $('select#membre');
		let tri       = $('select#tri');


		// réception et traitement de la réponse à la requête AJAX
		function reponse (contenu)
			{
			nombreAnnonces = contenu.substring(0, 20).replace(/[^.0-9]/g, '');
			switch (nombreAnnonces)
				{
				case '0'  : htmlNombreAnnonces = 'Aucune annonce ne correspond à votre sélection.'; break;
				case '1'  : htmlNombreAnnonces = '1 annonce correspond à votre sélection.'; break;
				default   : htmlNombreAnnonces = ''+nombreAnnonces+' annonces correspondent à votre sélection.'; break;
				}
			$('#nombre-annonces').html(htmlNombreAnnonces);
			$('#liste-annonces').html(contenu);

			$('.page-item').on('click', 'a', function(e)
				{
				pageAccueil = e.target.id.replace(/[^0-9]/g, '');
				requeteAjax ();
				});
			}

		function requeteAjax ()
			{
			// Emission de la requête AJAX
			$.post('liste_annonces.php', { filtreCategorie : filtreCategorie,
			                               filtreVille     : filtreVille,
			                               filtreMembre    : filtreMembre,
			                               filtrePrix      : filtrePrix,
			                               triAccueil      : triAccueil,
			                               pageAccueil     : pageAccueil
			                             }, reponse, 'html');
			}

		// listeners sur les différents select
		categorie.change(e=>{
			filtreCategorie = categorie.val();
			pageAccueil = 0;
			requeteAjax ();
		});
		ville.change(_=>{
			filtreVille = ville.val();
			pageAccueil = 0;
			requeteAjax ();
		});
		membre.change(_=>{
			filtreMembre = membre.val();
			pageAccueil = 0;
			requeteAjax ();
		});
		tri.change(_=>{
			triAccueil = tri.val();
			pageAccueil = 0;
			requeteAjax ();
		});

		// Affichage du prix maximum donné par le slider
		let rangePrix = $('#range-prix');
		let affichagePrix = $('#affichage-prix');
		function afficherPrix(prix){
			if (prix === undefined)
				{
				filtrePrix = prix = rangePrix.val();
				prix = Math.pow(10,rangePrix.val());
				prix = Math.round (prix);
				}
			if (rangePrix.val() == 7)
				affichagePrix.html('');
			else
				affichagePrix.html('Prix maximum : '+prix+' €');
		}

		// Quand on déplace le slider, faire bouger l'affichage de la souris sans lancer la requête AJAX
		rangePrix.on('mousemove', function(){
			afficherPrix ();
		});

		// Quand on lache le slider, lancer la requête AJAX
		rangePrix.on('change', function(){
			afficherPrix ();
			pageAccueil = 0;
			requeteAjax ();
		});

		// A l'affichage de la page, afficher le prix maxi et lancer la requête AJAX pour afficher la sélection d'anonces courante
		afficherPrix ();
		rangePrix.val(filtrePrix);
		requeteAjax ();

	}); // document ready
</script>
<?php
require_once 'connexion_modale.php';
require_once 'inscription_modale.php';

// Affichage du contenu qu'on vient de construire
// ----------------------------------------------
if (!empty($messageInscription))
	echo '<script>$(document).ready(function(){$("#modaleInscription").modal("show");});</script>';
if (!empty($messageConnexion))
	echo '<script>$(document).ready(function(){$("#modaleConnexion").modal("show");});</script>';

echo $contenu;

// Et le footer standard
require_once 'inc/footer.php';

