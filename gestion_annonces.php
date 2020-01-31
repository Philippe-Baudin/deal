<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_annonces.php
// affiche la liste des annonces avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once 'inc/init.php';

$afficherFormulaire = false;
$afficherTableau = false;
$nombrePages = 1;
$_SESSION['page courante'] = 'gestion_annonce.php';
$tri = $_SESSION['tri'] ?? 'date_enregistrement';
$sens = $_SESSION['sens'] ?? 'ASC';
$_SESSION['tri'] = $tri;
$_SESSION['sens'] = $sens;


// 1. Droits d'accès
// Il faut être un membre connecté
// De plus, il faut avoir les droits administrateur pour modifier ou supprimer une annonce
if (!estConnecte())
	{
	// Si l'utilisateur n'est pas connecté, le rediriger vers connection
	header ('location:'.RACINE_SITE.'connexion.php');
	exit ();
	}
if (estAdmin())
	{
	if (isset($_GET['creation']))
		$afficherFormulaire = true;
	else
		$afficherTableau = true;
	}
elseif (isset($_GET['suppression']) || isset($_GET['modification']))
	{
	// Si l'utilisateur voulant faire une modification ou une suppression n'est pas admin, le rediriger vers connection
	header ('location:'.RACINE_SITE.'connexion.php');
	exit ();
	}
else
	{
	// si ce n'est pas un administrateur, lui donner le formulaire de saisie d'une annonce
	$afficherFormulaire = true;
	}

//8. Modification/Creation d'une annonce
if (!empty($_POST))
	{
	extract ($_POST);

	if (!isset ($id) || !is_numeric($id))
		$contenu .= '<div class="alert alert-danger">L\'id est invalide.</div>';

	if (!isset ($description_courte) || strlen($description_courte) < 4 || strlen ($description_courte) > 255)
		$contenu .= '<div class="alert alert-danger">La description courte doit être comprise entre 4 et 255 catactères.</div>';

	if (!isset ($description_longue) || strlen($description_longue) < 10)
		$contenu .= '<div class="alert alert-danger">La description longue doit être comporter au moins 10 catactères.</div>';

	$ok = false;
	if (!isset ($prix) || !isFloat ($prix))
		$contenu .= '<div class="alert alert-danger">Le prix doit être un nombre.</div>';
	else
		$prix = sprintf ("%.02f", $prix);

	if (!isset ($pays) || strlen($pays) < 2 || strlen ($pays) > 100)
		$contenu .= '<div class="alert alert-danger">Le pays n\'existe pas.</div>';

	if (!isset ($ville) || strlen($ville) < 1 || strlen ($ville) > 100)
		$contenu .= '<div class="alert alert-danger">La ville n\'existe pas.</div>';

	if (!isset ($adresse) || strlen($adresse) < 4)
		$contenu .= '<div class="alert alert-danger">L\'adresse est invalide.</div>';

	if (!isset ($code_postal) || !preg_match ('#^[0-9]{5}$#', $code_postal))
		$contenu .= '<div class="alert alert-danger">Le code postal est invalide.</div>';

	if (!isset ($pseudo))
		$pseudo = $_SESSION['membre']['pseudo'];
	if (strlen($pseudo) < 4 || strlen ($pseudo) > 100)
		$contenu .= '<div class="alert alert-danger">Le pseudo du membre doit être compris entre 4 et 20 catactères.</div>';
	else
		{
		$requete = executerRequete ("SELECT id FROM membre WHERE pseudo=:pseudo", array (':pseudo'=> $pseudo));
		if ($requete->rowCount() >= 1)
			$membre_id = $requete->fetch (PDO::FETCH_NUM)[0];
		else			
			$contenu .= '<div class="alert alert-danger">Le membre n\'existe pas.</div>';
		}

	if (!isset ($categorie))
		$contenu .= '<div class="alert alert-danger">La catégorie n\'existe pas.</div>';
	else
		{
		$requete = executerRequete ("SELECT id FROM categorie WHERE titre=:categorie", array (':categorie' => $categorie));
		if ($requete->rowCount() >= 1)
			$categorie_id = $requete->fetch (PDO::FETCH_NUM)[0];
		else			
			$contenu .= '<div class="alert alert-danger">La catégorie n\'existe pas.</div>';
		}

	$photo_bdd = ''; // Par défaut, le champ est une string vide en BDD
	if (isset($_POST['photo_actuelle'])) // Si on est en train de modifier le produit, on remet le chemin de la photo en BDD
		{
		$photo_bdd = $_POST['photo_actuelle'];
		}
	if (!empty ($_FILES['photo']['name'])) // si on a un nom de fichier, c'est qu'on est en train de le télécharger
		{
		// Construire un nom de fichier unique (on suppose que la référence est unique et qu'on l'a vérifié plus haut (voir unicité d'un pseudo dans l'inscription d'un membre))
		$fichier_photo = 'ref' . $_POST['id'] . '_' . $_FILES['photo']['name'];
		$photo_bdd = 'img/' . $fichier_photo; // nom du fichier à utiliser ultérieurement dans des balises <img>
		copy($_FILES['photo']['tmp_name'], $photo_bdd);
		}
	// Note : dans la base de données on n'a enregistré que le path du fichier.

	if (empty($contenu))
		{
		if (empty($id))
			$string_requete = "INSERT INTO annonce VALUES (:id, :titre, :description_courte, :description_longue, :prix, :photo, :pays, :ville, :adresse, :code_postal, :membre_id, :categorie_id, NOW())";
		else
			$string_requete = "UPDATE annonce SET titre=:titre, description_courte=:description_courte, description_longue=:description_longue, prix=:prix, photo=:photo, pays=:pays, ville=:ville, adresse=:adresse, code_postal=:code_postal, membre_id=:membre_id, categorie_id=:categorie_id, date_enregistrement=".((isset($date_enregistrement)&& $date_enregistrement != '')?':date_enregistrement':'NOW()')." WHERE id=:id";

		$array_requete = array ( ':id' => $id
		                       , ':titre' => $titre
		                       , ':description_courte' => $description_courte
		                       , ':description_longue' => $description_longue
		                       , ':prix' => $prix
		                       , ':photo' => $photo_bdd
		                       , ':pays' => $pays
		                       , ':ville' => $ville
		                       , ':adresse' => $adresse
		                       , ':code_postal' => $code_postal
		                       , ':membre_id' => $membre_id
		                       , ':categorie_id' => $categorie_id
		                       );
		if (!empty($id) && isset($date_enregistrement) && $date_enregistrement != '') $array_requete[':date_enregistrement'] = $date_enregistrement;
		//$contenu .= "<p>$string_requete</p>";
		$resultat = executerRequete ($string_requete, $array_requete);
		//debug ($resultat);
		if ($resultat)
			{
			//XXX Mettre le message en modale et redirect vers la fiche annonce
			// $contenu .= '<div class="alert alert-success">L\'annonce a été enregistrée.</div>';
			header ('location:fiche_annonce.php?id='.$id);
			exit ();
			}
		else
			$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
		}
	} // if (!empty($_POST))

// Compter les annonces, pour la pagination
$requete = executerRequete ("SELECT COUNT(id) FROM annonce;");
$nombrePages = ceil ($requete->fetch(PDO::FETCH_NUM)[0]/TAILLE_PAGE);

// Suppression d'une annonce
if (isset ($_GET['suppression'])) // Si on a 'suppression' dans l'URL c'est qu'on a cliqué sur "suppression" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("DELETE FROM commentaire WHERE annonce_id = :id", array (':id' => $_GET['suppression']));
	if ($resultat)
		{
		$resultat = executerRequete ("DELETE FROM annonce WHERE id = :id", array (':id' => $_GET['suppression']));
		if ($resultat->rowCount() == 1)
			$contenu .= '<div class="alert alert-success">L\'annonce a bien été supprimé.</div>';
		else
			$contenu .= '<div class="alert alert-danger">Erreur lors de la suppression de l\'annonce.</div>';
		}
	else
		$contenu .= '<div class="alert alert-danger">Erreur lors de la suppression de l\'annonce.</div>';
	}

// Modification d'une annonce
else if (isset ($_GET['modification'])) // Si on a 'modification' dans l'URL c'est qu'on a cliqué sur "modification" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("SELECT annonce.id id, annonce.titre titre, description_courte, description_longue, prix, photo, pays, ville, adresse, code_postal, pseudo, categorie.titre categorie, annonce.date_enregistrement date_enregistrement FROM annonce, categorie, membre WHERE annonce.id = :id AND membre_id=membre.id AND categorie_id=categorie.id", array (':id' => $_GET['modification']));
	if ($resultat->rowCount() == 1)
		{
		$afficherFormulaire = true;
		$annonce_courante = $resultat->fetch (PDO::FETCH_ASSOC);
		}
	}
if (isset ($_GET['page']))
	{
	$numeroPage = $_GET['page']*1;
	if ($numeroPage > $nombrePages)
		$numeroPage = $nombrePages;
	else if ($numeroPage <= 0)
		$numeroPage = 1;
	}
else
	{
	$numeroPage = 1;
	}
$resultat = executerRequete ("SELECT titre FROM categorie");
$liste_categories = $resultat->fetchAll (PDO::FETCH_NUM);



require_once 'inc/header.php';
echo '<style> .container {padding:0;margin:auto;}</style>';

//2. Navigation entre les pages d'administration
if (!isset($_GET['creation']))
	navigation_admin ('Annonces');

echo $contenu; // pour afficher notamment les messages

//6. Affichage du tableau des annonces :
if ($afficherTableau)
	{
	// Selection de l'ordre et du sens du tri -->
	echo '<div>';
	echo     '<label for="tri">Trier par :&nbsp;</label>';
	echo     '<select name="tri" class="tri">';
	echo         '<option value="id_annonce"'         .(($tri=='id_annonce')?         ' selected':'').'>Id</option>';
	echo         '<option value="titre"'              .(($tri=='titre')?              ' selected':'').'>Titre</option>';
	echo         '<option value="description_courte"' .(($tri=='description_courte')? ' selected':'').'>Description courte</option>';
	echo         '<option value="description_longue"' .(($tri=='description_longue')? ' selected':'').'>Description longue</option>';
	echo         '<option value="prix"'               .(($tri=='prix')?               ' selected':'').'>Prix</option>';
	echo         '<option value="photo"'              .(($tri=='photo')?              ' selected':'').'>Photo</option>';
	echo         '<option value="pays"'               .(($tri=='pays')?               ' selected':'').'>Pays</option>';
	echo         '<option value="ville"'              .(($tri=='ville')?              ' selected':'').'>Ville</option>';
	echo         '<option value="adresse"'            .(($tri=='adresse')?            ' selected':'').'>Adresse</option>';
	echo         '<option value="code_postal"'        .(($tri=='code_postal')?        ' selected':'').'>Code postal</option>';
	echo         '<option value="pseudo"'             .(($tri=='pseudo')?             ' selected':'').'>Membre</option>';
	echo         '<option value="categorie"'          .(($tri=='categorie')?          ' selected':'').'>Catégorie</option>';
	echo         '<option value="date_enregistrement"'.(($tri=='date_enregistrement')?' selected':'').'>Date</option>';
	echo     '</select>';
	echo     '<select name="sens" class="sens">';
	echo         '<option value="ASC" selected>croissant</option>';
	echo         '<option value="DESC"' .(($sens=='DESC')? ' selected':'').'>décroissant</option>';
	echo     '</select>';
	echo '</div>';
	echo '<div id="tableau" class="table-responsive-sm">';
	//       Emplacement du tableau, qui sera rempli via AJAX en fonction du tri choisi ci-dessus -->
	echo '</div>';

	// Pagination
	if ($nombrePages > 1)
		{
		echo '<nav aria-label="Page navigation example">';
		echo '<ul class="pagination">';
		echo '<li class="page-item"><a class="page-link"'.(($numeroPage==1)?'':(' href="?page='.($numeroPage-1))).'">Précédente</a></li>';
		for ($i=1; $i<=$nombrePages; $i++)
			echo '<li class="page-item'.(($i==$numeroPage)?' active':'').'"><a class="page-link" href="?page='.$i.'">'.$i.'</a></li>';
		echo '<li class="page-item"><a class="page-link"'.(($numeroPage==$nombrePages)?'':(' href="?page='.($numeroPage+1))).'">Suivante</a></li>';
		echo '</ul>';
		echo '</nav>';
		} // fin if ($nombrePages > 1)
	} // fin if ($afficherTableau)



// Formulaire de création/modification des annonces
if ($afficherFormulaire)
	{
	isset($annonce_courante) && extract ($annonce_courante);
?>
	<div class="cadre-formulaire">
		<form id="formulaire" method="post" action="gestion_annonces.php?page=<?php echo $numeroPage ?>" enctype=multipart/form-data>
			<input type="hidden" name="id" value="<?php echo $id??0 ?>">
			<div class="form-row">
				<div class="form-group col-md-4">
					<div><label for="titre">Titre :</label></div>
					<div><input style="width:100%" type="text" name="titre" id="titre" class="form-control" value="<?php echo $titre??'' ?>"></div>
				</div>
				<div class="form-group col-md-8">
					<div><label for="description_courte">Description courte :</label></div>
					<div><input style="width:100%" type="text" name="description_courte" id="description_courte" class="form-control" value="<?php echo $description_courte??'' ?>"></div>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-12">
					<div><label for="description_longue">Description longue :</label></div>
					<div><textarea style="width:100%;height:25vh" type="text" name="description_longue" id="description_longue" class="form-control"><?php echo $description_longue??'' ?></textarea></div>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-2">
					<div><label for="prix">Prix :</label></div>
					<div><input style="width:100%" type="text" name="prix" id="prix" value="<?php echo $prix??'' ?>" class="form-control"></div>
				</div>
				<div class="form-group col-md-6">
					<div><label for="photo">Photo :</label></div>
					<div>
						<?php
							// Upload de la photo
							if (!empty($photo)) 
								{
								echo "<img src=$photo style='max-height:200px;'>";
								echo '<input type="hidden" name="photo_actuelle" value="'.($photo??'').'">';
								}
						?>
						<input type="file" name="photo" id="photo" class="btn btn-secondary"> <!-- NE PAS OUBLIER l'attribut "enctype" dans la balise <form> -->
					</div>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<div><label for="adresse">Adresse :</label></div>
					<div><input style="width:100%" type="text" name="adresse" id="adresse" value="<?php echo $adresse??'' ?>" class="form-control"></div>
				</div>
				<div class="form-group col-md-2">
					<div><label for="code_postal">Code postal :</label></div>
					<div><input style="width:100%" type="text" name="code_postal" id="code_postal" value="<?php echo $code_postal??'' ?>" class="form-control"></div>
				</div>
				<div class="form-group col-md-2">
					<div><label for="ville">Ville :</label></div>
					<div><input style="width:100%" type="text" name="ville" id="ville" value="<?php echo $ville??'' ?>" class="form-control"></div>
				</div>
				<div class="form-group col-md-2">
					<div><label for="pays">Pays :</label></div>
					<div><input style="width:100%" type="text" name="pays" id="pays" value="<?php echo $pays??'' ?>" class="form-control"></div>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<div><label for="categorie">Catégorie :</label></div>
					<div>
						<select name="categorie" class="form-control">
							<?php
							foreach ($liste_categories as $valeur)
								echo '<option value="'.$valeur[0].'"'.(isset($categorie)&&($valeur[0]==$categorie)?' selected':'').'>'.$valeur[0].'</option>';
							?>
						</select>
					</div>
				</div>
			</div>
			<?php if (estAdmin() && !isset($_GET['creation'])): /* seul l'admin peut changer l'auteur et la date d'enregistrement d'une annonce */ ?>
			<div class="form-row">
				<div class="form-group col-md-6">
					<div>
						<div><label for="pseudo">Auteur :</label></div>
						<div><input style="width:100%" type="text" name="pseudo" id="pseudo" value="<?php echo $pseudo??$_SESSION['membre']['pseudo'] ?>" class="form-control"></div>
					</div>
				</div>
				<div class="form-group col-md-6">
					<div>
						<div><label for="date_enregistrement">Date d'enregistrement :</label></div>
						<div><input style="width:100%" type="text" name="date_enregistrement" id="date_enregistrement" value="<?php echo $date_enregistrement??'' ?>" class="form-control"></div>
					</div>
				</div>
			</div>
			<?php endif; ?>
			<div class="form-row">
				<div class="form-group col-md-1">
				</div>
				<div class="form-group col-md-2">
					<button type="submit" class="btn btn-primary">&nbsp; Enregistrer &nbsp;</button>
				</div>
				<div class="form-group col-md-2">
					<?php
					if (estAdmin() && !isset($_GET['creation']))
						echo '<a href="'.RACINE_SITE.'gestion_annonces.php?page='.$numeroPage.'" class="btn btn-secondary">&nbsp; Annuler &nbsp;</a>';
					else
						echo '<a href="'.RACINE_SITE.'index.php" class="btn btn-secondary">&nbsp; Annuler &nbsp;</a>';
					?>
				</div>
			</div>
		</form>
	</div>
<?php

	} // fin if ($afficherFormulaire)

?>
<script>
	$(function(){ // document ready

	    // Tri du tableau en fonction du choix de l'internaute.
	    // Le tri est réalisé dans "table_annonces.php" qui dessine le tableau à afficher.
	    // Le pilotage est fait par une requête AJAX post.

		<?php
			echo 'let tri  = "'.$_SESSION['tri'].'";';
			echo 'let sens = "'.$_SESSION['sens'].'";';
			echo 'let page = "'.$numeroPage.'";';
		?>

		// éviter de faire plusieurs fois les JQuery
		let selectTri = $('select.tri');
		let selectSens = $('select.sens')

	    // par défaut, tri selon la valeur trouvée dans la session
		$.post('table_annonces.php', {tri : tri, sens : sens, page : page}, reponse, 'html');

	    // fonction de réponse à la requête ajax
		function reponse (retour)
			{
			$('#tableau').html(retour);
			}

		// trier si on clique sur une option du select
		$('select.tri option').click(e=>{
			tri = e.target.value;
			$.post('table_annonces.php', {tri : e.target.value, sens : sens, page : page}, reponse, 'html');
		});

		// trier si on clique sur une option du select
		$('select.sens option').click(e=>{
			sens = e.target.value;
			$.post('table_annonces.php', {tri : tri, sens : e.target.value, page : page}, reponse, 'html');
		});

	    // trier si on clique sur une entête du tableau
		$('#tableau').on('click', 'th.tri', function(e){
			if (tri == e.target.id) sens = ((sens=='ASC')?'DESC':'ASC');
			else tri = e.target.id;
			$.post('table_annonces.php', {tri : tri, sens : sens, page : page}, reponse, 'html');
			selectTri.val(tri);
			selectSens.val(sens);
		});

	}); // document ready
</script>

<?php
require_once 'inc/footer.php';

	