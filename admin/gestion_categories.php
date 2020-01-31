<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_categories.php
// affiche la liste des catégories avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once '../inc/init.php';

$afficherFormulaire = false;
//1. Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers connection
	header ('location:../connexion.php');
	exit ();
	}

//8. Modification d'une catégorie
if (!empty($_POST))
	{
	extract ($_POST);
	if (!isset ($titre) || strlen($titre) < 4 || strlen ($titre) > 100)
		$contenu .= '<div class="alert alert-danger">Le titre doit être compris entre 4 et 100 catactères.</div>';

	if (empty($contenu))
		$requete = executerRequete ("REPLACE INTO categorie VALUES (:id, :titre, :mots_cles)", array (':id' => $id, ':titre' => $titre, ':mots_cles' => $mots_cles));
	if (empty($contenu) && $requete)
		$contenu .= '<div class="alert alert-success">La catégorie a été enregistrée.</div>';
	else
		$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
	}

//7. Suppression d'une catégorie
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
		{
		$afficherFormulaire = true;
		$categorie_courante = $resultat->fetch (PDO::FETCH_ASSOC);
		}
	}

// Nombre d'annonces pour chaque categorie
$resultat = executerRequete ("SELECT categorie_id, COUNT(*) nombre FROM annonce GROUP BY categorie_id");
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC))
	{
	$nombreAnnonces[$ligne['categorie_id']] = $ligne['nombre']*1;
	}

//6. Affichage du tableau des catégories : 
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
	$contenu .= '<a href="?modification='.$ligne['id'].'#formulaire" class="liens-noirs">'.MODIFIER.'</a> ';
	$contenu .= '<a href="?suppression='.$ligne['id'].'" ';
	if (isset($nombreAnnonces[$id]))
		$contenu .= 'onclick="alert(\'Il y a '.$nombreAnnonces[$id].' annonces pour cette catégorie. Vous ne pouvez pas la supprimer.\'); return false;" class="liens-noirs">'.POUBELLE.'</a>';
	else
		$contenu .= 'onclick="return confirm(\'Etes vous certain de vouloir supprimer cette catégorie ?\')" class="liens-noirs">'.POUBELLE.'</a>';
	$contenu .= '</td>';
	$contenu .= '</tr>';
	}
$contenu .=   '</table>';
$contenu .='</div>';
$id = 0;
$titre = '';
$mots_cles = '';

require_once '../inc/header.php';

//2. Navigation entre les pages d'administration
navigation_admin ('Catégories');

echo $contenu; // pour afficher notamment le tableau des catégories
isset ($categorie_courante) && extract ($categorie_courante);

//3. Formulaire de création/modification des catégories
?>
<br>
<div class="cadre-formulaire">
	<form id="formulaire" method="post" action="gestion_categories.php">
		<input type="hidden" name="id" value="<?php echo $id ?>"> <!-- hidden => éviter de le modifier par accident. value="0" => lors de l'insertion le SGBD utilisera l'auto-incrémentation -->
		<div class="form-row">
			<div class="form-group col-md-1">
			</div>
			<div class="form-group col-md-3">
				<label for="titre">Titre :</label>
				<input type="text" name="titre" id="titre" class="form-control" value="<?php echo $titre??'' ?>">
			</div>
			<div class="form-group col-md-6">
				<label for="email">Mots-Clés :</label>
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
<?php

require_once '../inc/footer.php';
