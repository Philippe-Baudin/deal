<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_annonces.php
// affiche la liste des annonces avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once 'inc/init.php';

$afficherFormulaire = false;
$afficherTableau = false;
$nombrePages = 0;
$_SESSION['page courante'] = 'gestion_annonce.php';
$tri = $_SESSION['tri'] ?? 'date_enregistrement';
$sens = $_SESSION['sens'] ?? 'ASC';
$_SESSION['tri'] = $tri;
$_SESSION['sens'] = $sens;


// Droits d'accès
// Il faut être un membre connecté
// De plus, il faut avoir les droits administrateur pour modifier ou supprimer une annonce
if (!estConnecte())
	{
	// Si l'utilisateur n'est pas connecté, le rediriger vers l'accueil
	header ('location:'.RACINE_SITE.'index.php');
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
	// Si l'utilisateur voulant faire une modification ou une suppression n'est pas admin, le rediriger vers l'accueil
	header ('location:'.RACINE_SITE.'index.php');
	exit ();
	}
else
	{
	// si ce n'est pas un administrateur, lui donner le formulaire de saisie d'une annonce
	$afficherFormulaire = true;
	}

// Modification/Creation d'une annonce
if (!empty($_POST))
	{
	extract ($_POST);

	if (!isset ($id) || !is_numeric($id))
		$contenu .= '<div class="alert alert-danger">L\'id est invalide.</div>';

	if (!isset ($description_courte) || strlen($description_courte) < 4 || strlen ($description_courte) > 255)
		$contenu .= '<div class="alert alert-danger">La description courte doit être comprise entre 4 et 255 caractères.</div>';

	if (!isset ($description_longue) || strlen($description_longue) < 10)
		$contenu .= '<div class="alert alert-danger">La description longue doit être comporter au moins 10 caractères.</div>';

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
		$contenu .= '<div class="alert alert-danger">Le pseudo du membre doit être compris entre 4 et 20 caractères.</div>';
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

	if (empty($contenu))
		{
		$photoBDD = ''; // Par défaut, le champ est une string vide en BDD
		if (empty($id))
			$stringRequete = "INSERT INTO annonce VALUES (:id, :titre, :description_courte, :description_longue, :prix, :photo, :pays, :ville, :adresse, :code_postal, :membre_id, :categorie_id, NOW())";
		else
			$stringRequete = "UPDATE annonce SET titre=:titre, description_courte=:description_courte, description_longue=:description_longue, prix=:prix, photo=:photo, pays=:pays, ville=:ville, adresse=:adresse, code_postal=:code_postal, membre_id=:membre_id, categorie_id=:categorie_id, date_enregistrement=".((isset($date_enregistrement)&& $date_enregistrement != '')?':date_enregistrement':'NOW()')." WHERE id=:id";

		$arrayRequete = array (  ':id' => $id
		                       , ':titre' => $titre
		                       , ':description_courte' => $description_courte
		                       , ':description_longue' => $description_longue
		                       , ':prix' => $prix
		                       , ':photo' => $photoBDD
		                       , ':pays' => $pays
		                       , ':ville' => $ville
		                       , ':adresse' => $adresse
		                       , ':code_postal' => $code_postal
		                       , ':membre_id' => $membre_id
		                       , ':categorie_id' => $categorie_id
		                       );
		if (!empty($id) && isset($date_enregistrement) && $date_enregistrement != '')
			$arrayRequete[':date_enregistrement'] = $date_enregistrement;
		$resultat = executerRequete ($stringRequete, $arrayRequete);
		if ($resultat)
			{
			if (empty($id))
				{
				$resultat = executerRequete("SELECT id FROM annonce ORDER BY id DESC LIMIT 1");
				$id = $resultat->fetch(PDO::FETCH_NUM[0]);
				}
			if (isset($_POST['photo_actuelle'])) // Si on est en train de modifier le produit, on remet le chemin de la photo en BDD
				{
				$photoBDD = $_POST['photo_actuelle'];
				}
			if (!empty ($_FILES['photo']['name'])) // si on a un nom de fichier, c'est qu'on est en train de le télécharger
				{
				$extension = substr ($_FILES['photo']['name'], -4);
				if (strcasecmp($extension, '.gif') && strcasecmp($extension, '.jpg') && strcasecmp($extension, '.png') && strcasecmp($extension, '.bmp'))
					$contenu .= '<div class="alert alert-danger">Fichier photo invalide</div>';
				else
					{
					// Construire un nom de fichier unique (on suppose que la référence est unique et qu'on l'a vérifié plus haut (voir unicité d'un pseudo dans l'inscription d'un membre))
					$fichierPhoto = 'ref' . $id . '_' . $_FILES['photo']['name'];
					$photoBDD = 'img/' . $fichierPhoto; // nom du fichier à utiliser ultérieurement dans des balises <img>
					copy($_FILES['photo']['tmp_name'], $photoBDD);
					}
				}
			if (empty($contenu))
				{
				//XXX Mettre le message en modale et redirect vers la fiche annonce
				// $contenu .= '<div class="alert alert-success">L\'annonce a été enregistrée.</div>';
				executerRequete("UPDATE annonce set photo=:photo WHERE id=:id", array(':photo'=>$photoBDD, ':id'=>$id));
				header ('location:fiche_annonce.php?id='.$id);
				exit ();
				}
			}
		}
	$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
	$afficherFormulaire = true;
	$annonceCourante = $_POST;
	} // if (!empty($_POST))

// Compter les annonces, pour la pagination
$requete = executerRequete ("SELECT COUNT(id) FROM annonce;");
$nombrePages = ceil ($requete->fetch(PDO::FETCH_NUM)[0]/TAILLE_PAGE_ANNONCE);

// Suppression d'une annonce
if (isset ($_GET['suppression'])) // Si on a 'suppression' dans l'URL c'est qu'on a cliqué sur "suppression" dans le tableau ci-dessous
	{
	// Récupérer l'URL de la photo et supprimer le fichier
	$resultat = executerRequete ("SELECT photo FROM annonce WHERE annonce_id = :id", array (':id' => $_GET['suppression']));
	if ($resultat && $resultat->rowCount()==1)
		{
		$photo = $resultat->fetch(PDO::FETCH_NUM)[0];
		if (!empty($photo))
			unlink ('../'.$photo);
		}
	$resultat = executerRequete ("DELETE FROM commentaire WHERE annonce_id = :id", array (':id' => $_GET['suppression']));
	$resultat = executerRequete ("DELETE FROM annonce WHERE id = :id", array (':id' => $_GET['suppression']));
	if ($resultat->rowCount() == 1)
		$contenu .= '<div class="alert alert-success">L\'annonce a bien été supprimée.</div>';
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
		$annonceCourante = $resultat->fetch (PDO::FETCH_ASSOC);
		}
	}
if (isset ($_GET['page']))
	{
	$numeroPage = (int)$_GET['page'];
	if ($numeroPage >= $nombrePages)
		$numeroPage = $nombrePages-1;
	else if ($numeroPage <= 0)
		$numeroPage = 0;
	}
else
	{
	$numeroPage = 0;
	}
$resultat = executerRequete ("SELECT titre FROM categorie");
$listeCategories = $resultat->fetchAll (PDO::FETCH_NUM);



require_once 'inc/header.php';
echo '<script>$(".container").css("padding","0").css("margin","0");</script>';
//echo '<style> .container {padding:0;margin:auto;}</style>';

// Navigation entre les pages d'administration
if (!isset($_GET['creation']))
	navigationAdmin ('Annonces');

echo $contenu; // pour afficher notamment les messages

// Affichage du tableau des annonces :
if ($afficherTableau)
	{
	// Selection de l'ordre et du sens du tri -->
	echo '<div>';
	echo     '<label for="critere-tri">Trier par :&nbsp;</label>';
	echo     '<select name="tri" class="tri" id="critere-tri">';
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
	echo '<div id="tableau" class="table-responsive-sm" style="margin:10px">';
	//       Emplacement du tableau, qui sera rempli via AJAX en fonction du tri choisi ci-dessus -->
	echo '</div>';

	// Pagination
	if ($nombrePages > 1)
		{
		echo '<nav aria-label="Page navigation example">';
		echo '<ul class="pagination">';
		if ($numeroPage<=0)
			echo '<li><a class="page-link" onclick="return false" href="">Précédente</a></li>';
		else
			echo '<li class="page-item"><a class="page-link" href="?page='.($numeroPage-1).'">Précédente</a></li>';
		for ($i=0; $i<$nombrePages; $i++)
			echo '<li class="page-item'.(($i==$numeroPage)?' active':'').'"><a class="page-link" href="?page='.$i.'">'.($i+1).'</a></li>';
		if (($numeroPage>=$nombrePages-1))
			echo '<li><a class="page-link" onclick="return false;" href="">Suivante</a></li>';
		else
			echo '<li class="page-item"><a class="page-link" href="?page='.($numeroPage+1).'">Suivante</a></li>';
		echo '</ul>';
		echo '</nav>';
		} // fin if ($nombrePages > 1)
	} // fin if ($afficherTableau)



// Formulaire de création/modification des annonces
if ($afficherFormulaire)
	{
	isset($annonceCourante) && extract ($annonceCourante);
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
					<div><textarea style="width:100%;height:25vh" name="description_longue" id="description_longue" class="form-control"><?php echo $description_longue??'' ?></textarea></div>
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
						<select name="categorie" id="categorie" class="form-control">
							<?php
							foreach ($listeCategories as $valeur)
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

	    // lancer la requete AJAX pour l'affichage initial
	    function requeteAjax()
	    	{
			$.post('table_annonces.php', {tri : tri, sens : sens, page : page}, reponse, 'html');
			}

	    // fonction de réponse à la requête ajax
		function reponse (retour)
			{
			$('#tableau').html(retour);
			<?php if ($afficherFormulaire) echo 'location.hash = "#formulaire"' ?>
			}

		// trier si on clique sur une option du select
		$('select.tri option').click(e=>{
			tri = e.target.value;
			requeteAjax();;
		});

		// trier si on clique sur une option du select
		$('select.sens option').click(e=>{
			sens = e.target.value;
			requeteAjax();;
		});

	    // trier si on clique sur une entête du tableau
		$('#tableau').on('click', 'th.tri', function(e){
			if (tri == e.target.id) sens = ((sens=='ASC')?'DESC':'ASC');
			else tri = e.target.id;
			requeteAjax();;
			selectTri.val(tri);
			selectSens.val(sens);
		});

		requeteAjax();
	}); // document ready
</script>

<?php
require_once 'inc/footer.php';

	
