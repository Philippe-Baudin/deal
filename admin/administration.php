<?php
require_once '../inc/init.php';

//1. Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers connection
	header ('location:../connexion.php');
	exit ();
	}
/*
//7. Suppression d'un produit
if (isset ($_GET['id_produit'])) // Si on a id_produit dans l'URL c'est qu'on a cliqué sur "suppression" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("DELETE FROM produit WHERE id_produit = :id_produit", array (':id_produit' => $_GET['id_produit']));
	if ($resultat->rowCount() == 1)
		$contenu .= '<div class="alert alert-success">Le produit a bien été supprimé.</div>';
	else
		$contenu .= '<div class="alert alert-danger">Erreur lors de la suppression du produit.</div>';
	}
*/
/*
//6. Affichage des produits dans le back-office : (un peu primitif : il ne faudrait pas écrire en dur les entêtes tout en remplissant le tableau dans l'ordre des résultats de la requête)
$resultat = executerRequete ("Select * from produit");
$contenu .='<div>Nombre de produits dans la boutique : ' . $resultat->rowCount() . '</div>';
$contenu .='<div class="table-responsive">';
$contenu .=   '<table class="table">';
$contenu .=      '<tr>';
$contenu .=          '<th>Id_produit</th>';
$contenu .=          '<th>Référence</th>';
$contenu .=          '<th>Catégorie</th>';
$contenu .=          '<th>Titre</th>';
$contenu .=          '<th>Description</th>';
$contenu .=          '<th>Couleur</th>';
$contenu .=          '<th>Taille</th>';
$contenu .=          '<th>Public</th>';
$contenu .=          '<th>Photo</th>';
$contenu .=          '<th>Prix</th>';
$contenu .=          '<th>Stock</th>';
$contenu .=          '<th>Action</th>';
$contenu .=      '</tr>';
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) // pour chaque ligne retournée par la requête
	{
	$contenu .= '<tr>';
	foreach ($ligne as $colonne => $valeur) // Pour chaque colonne dans la ligne de resultat
		{
		if ($colonne == 'photo' && !empty($valeur))
			$contenu .= '<td><img src="../'.$valeur.'" style="width:90px;"></img></td>';
		else if ($colonne == 'prix')
			$contenu .= '<td>' . $valeur . ' €</td>';
		else if ($colonne == 'description')
			$contenu .= '<td>' . substr($valeur, 0, 30) . ' ...</td>'; // Pour éviter d'afficher les descriptions trop longues. Il serait encore mieux de ne mettre les "..." que quand on tronque effectivement (quand la description dépasse les 30 caractères).
		else
			$contenu .= '<td>' . $valeur . '</td>';
		}
	// Là, il y a un petit bout de javaScript fûté : quand on retourne false dans un onclick, ça bloque le lien. Na.
	$contenu .= '<td><a href="formulaire_produit.php?id_produit='.$ligne['id_produit'].'">Modifier</a>|<a href="?id_produit='.$ligne['id_produit'].'" onclick="return confirm(\'Etes Vous certain de vouloir supprimer ce produit?\')">Supprimer</a></td>';
	$contenu .= '</tr>';
	}
$contenu .=   '</table>';
$contenu .='</div>';
*/
require_once '../inc/header.php';

//2. Navigation entre les pages d'administration
?>
<h1 classe="mt-4">Gestion Boutique</h1>
<ul class="nav nav-tabs"> <!-- onglets -->
	<li><a class="nav-link active" href="gestion_annonces.php">Annonces</a></li>
	<li><a class="nav-link" href="gestion_categories.php">Catégories</a></li>
	<li><a class="nav-link" href="gestion_membres.php">Membres</a></li>
	<li><a class="nav-link" href="gestion_commentaires.php">Commentaires</a></li>
	<li><a class="nav-link" href="gestion_notes.php">Notes</a></li>
	<li><a class="nav-link" href="statistiques.php">Statistiques</a></li>
</ul>
<?php
echo $contenu; // pour afficher notamment le tableau des produits



require_once '../inc/footer.php';
