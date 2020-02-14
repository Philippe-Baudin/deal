<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_categories.php
// affiche la liste des catégories avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='../';
require_once '../inc/init.php';

// Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers l'accueil
	header ('location:../index.php');
	exit ();
	}

// Suppression, modification ou ajout d'une catégorie
if (!empty($_POST))
	{
	// Suppression d'une catégorie
	if (isset ($_POST['suppression']))
		{
		$resultat = executerRequete ("DELETE FROM categorie WHERE id = :id", array (':id' => $_POST['suppression']));
		if ($resultat->rowCount() == 1)
			echo '<div class="alert alert-success">La catégorie a bien été supprimé.</div>';
		else
			echo '<div class="alert alert-danger">Erreur lors de la suppression de la catégorie.</div>';
		exit ();
		}
	// Modification ou ajout
	extract ($_POST);
	if (!isset ($titre) || strlen($titre) < 4 || strlen ($titre) > 100)
		$contenu .= '<div class="alert alert-danger">Le titre doit être compris entre 4 et 100 caractères.</div>';

	if (empty($contenu))
		{
		if (empty($id))
			$resultat = executerRequete ("INSERT INTO categorie VALUES (0, :titre, :mots_cles)", array (':titre' => $titre, ':mots_cles' => $mots_cles));
		else
			$resultat = executerRequete ("UPDATE categorie SET titre=:titre, mots_cles=:mots_cles WHERE id=:id", array (':id' => $id, ':titre' => $titre, ':mots_cles' => $mots_cles));
		}
	if (empty($contenu) && $resultat && $resultat->rowCount() == 1)
		$contenu .= '<div class="alert alert-success">La catégorie a été enregistrée.</div>';
	else
		{
		$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
		$categorieCourante = $_POST;
		}
	}

// Modification d'une catégorie
if (isset ($_GET['modification'])) // Si on a 'modification' dans l'URL c'est qu'on a cliqué sur "modification" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("SELECT * FROM categorie WHERE id = :id", array (':id' => $_GET['modification']));
	if ($resultat->rowCount() == 1)
		$categorieCourante = $resultat->fetch (PDO::FETCH_ASSOC);
	}

// Nombre d'annonces pour chaque categorie
$resultat = executerRequete ("SELECT categorie_id, COUNT(*) nombre FROM annonce GROUP BY categorie_id");
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC))
	$nombreAnnonces[$ligne['categorie_id']] = $ligne['nombre']*1;

// Affichage du tableau des catégories : 
$resultat = executerRequete ("SELECT * FROM categorie");
$contenu .='<div class="table-responsive">';
$contenu .=   '<table class="table">';
$contenu .=      '<thead class="thead-dark">';
$contenu .=          '<tr>';
$contenu .=              '<th>Id</th>';
$contenu .=              '<th>Titre</th>';
$contenu .=              '<th>Mots-Clés</th>';
$contenu .=              '<th>Nombre d\'annonces</th>';
$contenu .=              '<th>Action</th>';
$contenu .=          '</tr>';
$contenu .=      '</thead>';
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) // pour chaque ligne retournée par la requête
	{
	extract ($ligne);
	$contenu .= '<tr>';
	$contenu .= '<th scope="row">' . $id . '</th>';
	$contenu .= '<td>' . $titre . '</td>';
	$contenu .= '<td>' . $mots_cles . '</td>';
	$contenu .= '<td>' . 	((isset($nombreAnnonces[$id]))?$nombreAnnonces[$id]:0) . '</td>';
	// Là, il y a un petit bout de javaScript fûté : quand on retourne false dans un onclick, ça bloque le lien. Na.
	$contenu .= '<td>';
	$contenu .= '<a href="?modification='.$ligne['id'].'#formulaire" class="lien-noir">'.MODIFIER.'</a> ';
	if (isset($nombreAnnonces[$id]))
		$contenu .= '<span class="lien-noir refus-suppression" id="suppression_'.$id.'_'.$nombreAnnonces[$id].'">'.POUBELLE.'</span>';
	else
		$contenu .= '<span class="lien-noir demande-suppression" id="suppression_'.$id.'">'.POUBELLE.'</span>';


	$contenu .= '</td>';
	$contenu .= '</tr>';
	}
$contenu .=   '</table>';
$contenu .='</div>';
$id = 0;
$titre = '';
$mots_cles = '';

require_once '../inc/header.php';

// Emplacement du message de retour de suppression d'une catégorie
echo '<div id="messageSuppression"></div>';

// Modale de confirmation de la supression d'une catégorie
modaleSuppression ('cette catégorie', false);

// Navigation entre les pages d'administration
navigationAdmin ('Catégories');

echo $contenu; // pour afficher notamment le tableau des catégories
isset ($categorieCourante) && extract ($categorieCourante);

// Formulaire de création/modification des catégories
echo '<br>';
if (isset ($_GET['modification']))
	//echo '<div><p style="text-align: center;">Modifier la catégorie '.$titre.' (id '.$id.')</p></div>';
	echo '<div class="alert alert-success">Modifier la catégorie '.$titre.' (id '.$id.')</div>'
?>
<div class="cadre-formulaire">
	<form id="formulaire" method="post" action="gestion_categories.php">
		<input type="hidden" name="id" value="<?php echo $id??'' ?>"> <!-- hidden => éviter de le modifier par accident. value="0" => lors de l'insertion le SGBD utilisera l'auto-incrémentation -->
		<div class="form-row">
			<div class="form-group col-md-1">
			</div>
			<div class="form-group col-md-3">
				<label for="titre">Titre :</label>
				<input type="text" name="titre" id="titre" class="form-control" value="<?php echo $titre??'' ?>">
			</div>
			<div class="form-group col-md-6">
				<label for="mots_cles">Mots-Clés :</label>
				<input type="text" name="mots_cles" id="mots_cles" class="form-control" value="<?php echo $mots_cles??'' ?>">
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-4">
			</div>
			<div class="form-group col-md-6">
				<button type="submit" class="btn btn-primary">&nbsp; Enregistrer &nbsp;</button>
			</div>
		</div>
	</form>
</div>

<!-- Modale d'interdiction de la suppression -->
<div class="modal" id="modaleRefusSuppression" tabindex="-1" role="dialog" aria-labelledby="modaleRefusSuppressionTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modaleRefusSuppressionTitle">Vous ne pouvez pas supprimer une catégorie qui contient des annonces.</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
			</div>
		</div>
	</div>
</div>

<script>
	$(function(){ // document ready

		let cible;

		// clic sur le bouton 'oui' de la fenêtre modale de confirmation de suppression
		$(".ok-suppression").on ('click', function(){
			$.post('gestion_categories.php', {suppression:cible},function(reponse){
				$('#modaleSuppression').modal('hide');
				console.log (reponse);
				location.reload();
				}, 'html');
			});

		// clic sur une icône "poubelle" du tableau
		$(".demande-suppression").on ('click', function(e){
			cible = e.currentTarget.id.replace(/[^0-9]/g,'');
			$('#modaleSuppression').modal('show');
			});

		// clic sur une icône "poubelle" du tableau
		$(".refus-suppression").on ('click', function(e){
			$('#modaleRefusSuppression').modal('show');
			});
/*
		// réception et traitement de la réponse à la requête AJAX d'affichage du tableau
		function reponse (contenu)
			{
			$('#tableau').html(contenu);
			<?php if ($afficherFormulaire) echo 'location.hash = "#formulaire"' ?>

			// clic sur une des cases de la pagination
			$('.page-item').on('click', 'a', function(e)
				{
				page = e.target.id.replace(/[^0-9]/g, '');
				afficherTableau ();
				});
			}

		// Lancement de la requête AJAX d'affichage du tableau
		function afficherTableau ()
			{
			// arrêter les listener de demande de supression
			$(".demande-suppression").off("click");

			// Emission de la requête AJAX
			$.post('table_annonces.php', { tri : tri, sens : sens, page : page, }, reponse, 'html');
			}

	    // trier si on clique sur une entête du tableau
		$('#tableau').on('click', 'th.tri', function(e){
			if (tri == e.target.id) sens = ((sens=='ASC')?'DESC':'ASC');
			else { tri = e.target.id; sens='ASC'; }
			afficherTableau();
		});

		// A l'affichage de la page, lancer une première fois la requête AJAX
		afficherTableau ();
*/
	}); // document ready
</script>

<?php
require_once '../inc/footer.php';
