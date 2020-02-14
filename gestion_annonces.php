<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_annonces.php
// affiche la liste des annonces avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='';
require_once 'inc/init.php';

$afficherFormulaire = false;
$afficherTableau = false;


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

$nombrePages = 1;
$_SESSION['page courante'] = 'gestion_annonce.php';
$tri = $_SESSION['tri'] ?? 'date_enregistrement';
$sens = $_SESSION['sens'] ?? 'ASC';
$_SESSION['tri'] = $tri;
$_SESSION['sens'] = $sens;

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
	$resultat = executerRequete ("SELECT id FROM membre WHERE pseudo=:pseudo", array (':pseudo'=> $pseudo));
	if ($resultat->rowCount() >= 1)
		$membre_id = $resultat->fetch (PDO::FETCH_NUM)[0];
	else			
		$contenu .= '<div class="alert alert-danger">Le membre n\'existe pas.</div>';

	if (!isset ($categorie))
		$contenu .= '<div class="alert alert-danger">La catégorie n\'existe pas.</div>';
	else
		{
		$resultat = executerRequete ("SELECT id FROM categorie WHERE titre=:categorie", array (':categorie' => $categorie));
		if ($resultat->rowCount() >= 1)
			$categorie_id = $resultat->fetch (PDO::FETCH_NUM)[0];
		else			
			$contenu .= '<div class="alert alert-danger">La catégorie n\'existe pas.</div>';
		}

	if (empty($contenu))
		{
		if (empty($id))
			$stringRequete = "INSERT INTO annonce VALUES (:id, :titre, :description_courte, :description_longue, :prix, :photo, :pays, :ville, :adresse, :code_postal, :membre_id, :categorie_id, NOW())";
		else
			$stringRequete = "UPDATE annonce SET titre=:titre, description_courte=:description_courte, description_longue=:description_longue, prix=:prix, photo=:photo, pays=:pays, ville=:ville, adresse=:adresse, code_postal=:code_postal, membre_id=:membre_id, categorie_id=:categorie_id, date_enregistrement=".((isset($date_enregistrement)&& $date_enregistrement != '')?':date_enregistrement':'NOW()')." WHERE id=:id";

		$arrayRequete = array (  ':id' => $id
		                       , ':titre' => $titre
		                       , ':description_courte' => $description_courte
		                       , ':description_longue' => $description_longue
		                       , ':prix' => $prix
		                       , ':photo' => ''
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

		// J'upload la photo APRES avoir inséré l'annonce, de façon à connaître son id qui sert à rendre unique le nom de la photo
		if ($resultat)
			{
			if (empty($id))
				{
				$resultat = executerRequete("SELECT id FROM annonce ORDER BY id DESC LIMIT 1");
				$id = $resultat->fetch(PDO::FETCH_NUM)[0];
				}
			$photoBDD = $_POST['photo_actuelle'] ?? ''; // Par défaut, le champ est la photo actuellement dans l'annonce, ou une string vide s'il n'y en a pas
			if (!empty ($_FILES['photo']['name'])) // si on a un nom de fichier, c'est qu'on est en train de le télécharger
				{
				// Vérifier que le nom est celui d'une image (pour bien faire, il faudrait aussi véridier le contenu ...)
				$extension3 = substr ($_FILES['photo']['name'], -4); // extension de 3 lettres : gif, jpg, png ou bmp
 				$extension4 = substr ($_FILES['photo']['name'], -5); // extension de 4 lettres : jpeg
  				if (strcasecmp($extension3, '.gif') && strcasecmp($extension3, '.jpg') && strcasecmp($extension3, '.png') && strcasecmp($extension3, '.bmp') && strcasecmp($extension4, '.jpeg'))
					$contenu .= '<div class="alert alert-danger">Fichier photo invalide</div>';
				else
					{
					// S'il y a déjà une photo, l'effacer
					$resultat = executerRequete ("SELECT photo from annonce WHERE id=:id", array(':id'=>$id));
					if ($resultat)
						{
						$ancienNom = $resultat->fetch(PDO::FETCH_NUM)[0];
						if (file_exists($ancienNom))
							unlink ($ancienNom);
						}
					// Construire un nom de fichier unique (par construction l'id de l'annonce est unique), recopier la photo et l'enregistrer dans la BDD
					$photoBDD = 'img/ref_' . $id . '_' . $_FILES['photo']['name']; // nom du fichier à utiliser ultérieurement dans des balises <img>
					copy($_FILES['photo']['tmp_name'], $photoBDD);
					executerRequete("UPDATE annonce set photo=:photo WHERE id=:id", array(':photo'=>$photoBDD, ':id'=>$id));
					$_POST['photo']=$photoBDD;
					}
				}
			if (empty($contenu))
				{
				$message  = '<div class="modal fade" id="modaleOK" tabindex="-1" role="dialog" aria-labelledby="modaleOKTitle" aria-hidden="true">';
				$message .=      '<div class="modal-dialog modal-dialog-centered modal" role="document">';
				$message .=          '<div class="modal-content">';
				$message .=              '<div class="modal-header">';
				$message .=                  '<h5 class="modal-title" id="modaleAvisTitle">L\'annonce a été enregistrée</h5>';
				$message .=                  '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
				$message .=                      '<span aria-hidden="true">&times;</span>';
				$message .=                  '</button>';
				$message .=              '</div>';
				$message .=              '<div class="modal-body">';
				$message .=                  '<form method="post" action="fiche_annonce.php?id='.$id.'">';
				$message .=                      '<button type="submit" name="OK" id="OK" class="btn btn-primary">&nbsp; OK &nbsp;</button>';
				$message .=                  '</form>';
				$message .=              '</div>';
				$message .=          '</div>';
				$message .=      '</div>';
				$message .= '</div>';
				$message .= '<script>$("#modaleOK").modal("show")</script>';
				}
			}
		}
	if (!empty($contenu))
		{
		$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
		$afficherFormulaire = true;
		$annonceCourante = $_POST;
		}
	} // if (!empty($_POST))

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
$resultat = executerRequete ("SELECT titre FROM categorie");
$listeCategories = $resultat->fetchAll (PDO::FETCH_NUM);


require_once 'inc/header.php';

// Emplacement du message de retour de suppression d'une annonce
echo '<div id="messageSuppression"></div>';

// Modale de confirmation de la supression d'une annonce'
modaleSuppression ('cette annonce', false);

// Navigation entre les pages d'administration
if (!isset($_GET['creation']))
	navigationAdmin ('Annonces');

echo $contenu;
if (isset($message)) echo $message;

// Affichage du tableau des annonces :
if ($afficherTableau)
	{
	// Selection de l'ordre et du sens du tri -->
	echo '<div class="row">';
	echo     '<div class="col-sm-4">';
	echo         '<div class="form-group form-inline">';
	echo             '<label for="critere-tri">Trier par :&nbsp;</label>';
	echo             '<select name="tri" class="form-control tri" id="critere-tri">';
	echo                 '<option value="id_annonce"'         .(($tri=='id_annonce')?         ' selected':'').'>Id</option>';
	echo                 '<option value="titre"'              .(($tri=='titre')?              ' selected':'').'>Titre</option>';
	echo                 '<option value="description_courte"' .(($tri=='description_courte')? ' selected':'').'>Description courte</option>';
	echo                 '<option value="description_longue"' .(($tri=='description_longue')? ' selected':'').'>Description longue</option>';
	echo                 '<option value="prix"'               .(($tri=='prix')?               ' selected':'').'>Prix</option>';
	echo                 '<option value="photo"'              .(($tri=='photo')?              ' selected':'').'>Photo</option>';
	echo                 '<option value="pays"'               .(($tri=='pays')?               ' selected':'').'>Pays</option>';
	echo                 '<option value="ville"'              .(($tri=='ville')?              ' selected':'').'>Ville</option>';
	echo                 '<option value="adresse"'            .(($tri=='adresse')?            ' selected':'').'>Adresse</option>';
	echo                 '<option value="code_postal"'        .(($tri=='code_postal')?        ' selected':'').'>Code postal</option>';
	echo                 '<option value="pseudo"'             .(($tri=='pseudo')?             ' selected':'').'>Membre</option>';
	echo                 '<option value="categorie"'          .(($tri=='categorie')?          ' selected':'').'>Catégorie</option>';
	echo                 '<option value="date_enregistrement"'.(($tri=='date_enregistrement')?' selected':'').'>Date</option>';
	echo             '</select>';
	echo             '<select name="sens" class="form-control sens">';
	echo                 '<option value="ASC" selected>croissant</option>';
	echo                 '<option value="DESC"' .(($sens=='DESC')? ' selected':'').'>décroissant</option>';
	echo             '</select>';
	echo         '</div>';
	echo     '</div>';
	echo '</div>';
	echo '<div id="tableau" class="table-responsive-sm" style="margin:10px">';
	//       Emplacement du tableau, qui sera rempli via AJAX en fonction du tri choisi ci-dessus -->
	echo '</div>';

	} // fin if ($afficherTableau)



// Formulaire de création/modification des annonces
if ($afficherFormulaire)
	{
	isset($annonceCourante) && extract ($annonceCourante);
?>
	<div class="cadre-formulaire">
		<form id="formulaire" method="post" action="gestion_annonces.php?page=<?php echo $_SESSION['page']??0?>" enctype=multipart/form-data>
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
						echo '<a href="'.RACINE_SITE.'gestion_annonces.php?page='.($_SESSION['page']??0).'" class="btn btn-secondary">&nbsp; Annuler &nbsp;</a>';
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

		// Le tri et le numéro de page
		<?php
			echo 'let tri  = "'.($_SESSION["tri"]??0).'";';
			echo 'let sens = "'.($_SESSION["sens"]??0).'";';
			echo 'let page = "'.($_SESSION["page"]??0).'";';
		?>
		let cible;

		// clic sur le bouton 'oui' de la fenêtre modale de confirmation de suppression
		$(".ok-suppression").on ('click', function(){
			$.post('admin/suppression_annonce.php', {id:cible},function(reponse){
				$('#modaleSuppression').modal('hide');
				$('#messageSuppression').html(reponse);
				afficherTableau ();
				}, 'html');
			});

		// réception et traitement de la réponse à la requête AJAX d'affichage du tableau
		function reponse (contenu)
			{
			$('#tableau').html(contenu);
			<?php if ($afficherFormulaire) echo 'location.hash = "#formulaire"' ?>

			// clic sur une icône "poubelle" du tableau
			$(".demande-suppression").on ('click', function(e){
				cible = e.currentTarget.id.replace(/[^0-9]/g,'');
				$('#modaleSuppression').modal('show');
				});

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

	}); // document ready
</script>


<?php
require_once 'inc/footer.php';

	
