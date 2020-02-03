<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_categories.php
// affiche la liste des catégories avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once '../inc/init.php';

// Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers l'accueil
	header ('location:../index.php');
	exit ();
	}

// Modification ou ajout d'une catégorie
if (!empty($_POST))
	{
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

// Suppression d'une catégorie
if (isset ($_GET['suppression'])) // Si on a 'suppression' dans l'URL c'est qu'on a cliqué sur "suppression" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("DELETE FROM categorie WHERE id = :id", array (':id' => $_GET['suppression']));
	if ($resultat->rowCount() == 1)
		$contenu .= '<div class="alert alert-success">La catégorie a bien été supprimé.</div>';
	else
		$contenu .= '<div class="alert alert-danger">Erreur lors de la suppression de la catégorie.</div>';
	}
// Modification d'une catégorie
else if (isset ($_GET['modification'])) // Si on a 'modification' dans l'URL c'est qu'on a cliqué sur "modification" dans le tableau ci-dessous
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
	$contenu .= '<a href="?suppression='.$ligne['id'].'" ';
	if (isset($nombreAnnonces[$id]))
		$contenu .= 'onclick="alert(\'Il y a '.$nombreAnnonces[$id].' annonces pour cette catégorie. Vous ne pouvez pas la supprimer.\'); return false;" class="lien-noir">'.POUBELLE.'</a>';
	else
		$contenu .= 'onclick="return confirm(\'Etes vous certain de vouloir supprimer cette catégorie ?\')" class="lien-noir">'.POUBELLE.'</a>';
	$contenu .= '</td>';
	$contenu .= '</tr>';
	}
$contenu .=   '</table>';
$contenu .='</div>';
$id = 0;
$titre = '';
$mots_cles = '';

require_once '../inc/header.php';

// Navigation entre les pages d'administration
navigationAdmin ('Catégories');

echo $contenu; // pour afficher notamment le tableau des catégories
isset ($categorieCourante) && extract ($categorieCourante);

// Formulaire de création/modification des catégories
echo '<br>';
if (isset ($_GET['modification']))
	echo '<div><p style="text-align: center;">Modifier la catégorie '.$titre.' (id '.$id.')</p></div>';
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

<!-- Modale de confirmation de la suppression -->
<div class="modal fade" id="modaleConfrmerSuppression" tabindex="-1" role="dialog" aria-labelledby="modaleConfrmerSuppressionTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modaleConfrmerSuppressionTitle">Etes-vous sûr de vouloir supprimer cette categorie ?</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="row">
				<div class="col-sm-2">
					<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="supprimerCategorie();">Oui</button>
				</div>
				<div class="col-sm">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modale d'interdiction de la suppression -->
<div class="modal fade" id="modaleConfrmerSuppression" tabindex="-1" role="dialog" aria-labelledby="modaleConfrmerSuppressionTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modaleConfrmerSuppressionTitle">Vous ne pouvez pas supprimer une catégorie qui contient des annonces.</h5>
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
	
	function supprimerCategorie ()
		{

		}
</script>
<?php
require_once '../inc/footer.php';
