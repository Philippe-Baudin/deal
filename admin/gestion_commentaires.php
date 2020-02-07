<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_commentaires.php
// affiche la liste des commentaires avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once '../inc/init.php';

$afficherFormulaire = false;

// Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers l'accueil
	header ('location:../index.php');
	exit ();
	}

// Traitement de la modification d'un commentaire
if (!empty($_POST))
	{
	extract ($_POST);
	if (!isset ($commentaire) || strlen($commentaire) < 10 )
		$contenu .= '<div class="alert alert-danger">Le commentaire doit comprendre au moins 10 caractères.</div>';

	if (!isset ($pseudo) || strlen($pseudo) < 4 || strlen ($pseudo) > 100)
		$contenu .= '<div class="alert alert-danger">Le pseudo du membre doit être compris entre 4 et 20 caractères.</div>';
	else
		{
		$requete = executerRequete ("SELECT id FROM membre WHERE pseudo=:pseudo", array (':pseudo'=> $pseudo));
		if ($requete->rowCount() >= 1)
			$membre_id = $requete->fetch (PDO::FETCH_NUM)[0];
		else			
			$contenu .= '<div class="alert alert-danger">Le membre n\'existe pas.</div>';
		}

	if (!isset ($annonce) || !is_numeric($annonce))
		$contenu .= '<div class="alert alert-danger">L\'identifiant de l\'annonce doit être un nombre.</div>';
	else
		{
		$requete = executerRequete ("SELECT id FROM annonce WHERE id=:id", array (':id'=> $annonce));
		if ($requete->rowCount() < 1)
			$contenu .= '<div class="alert alert-danger">Il n\'y a pas d\'annonce d\'identifiant '.$annonce.'.</div>';
		}

	if (empty($contenu))
		{
		if (empty($date_enregistrement))
			{
			$requete = executerRequete ("REPLACE INTO commentaire VALUES (:id, :commentaire, :membre, :annonce, NOW())",
			                            array (':id' => $id, ':commentaire' => $commentaire, ':membre' => $membre_id, ':annonce' => $annonce));
			}
		else
			{
			$requete = executerRequete ("REPLACE INTO commentaire VALUES (:id, :commentaire, :membre, :annonce, :date_enregistrement)",
			                            array (':id' => $id, ':commentaire' => $commentaire, ':membre' => $membre_id, ':annonce' => $annonce, ':date_enregistrement' => $date_enregistrement));
			}
		}
	if (empty($contenu) && $requete)
		$contenu .= '<div class="alert alert-success">Le commentaire a été enregistrée.</div>';
	else
		{
		$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
		$afficherFormulaire = true;
		$commentaireCourant = $_POST;
		}
	}

// Suppression d'un commentaire
if (isset ($_GET['suppression'])) // Si on a 'suppression' dans l'URL c'est qu'on a cliqué sur "suppression" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("DELETE FROM commentaire WHERE id = :id", array (':id' => $_GET['suppression']));
	if ($resultat->rowCount() == 1)
		$contenu .= '<div class="alert alert-success">Le commentaire a bien été supprimé.</div>';
	else
		$contenu .= '<div class="alert alert-danger">Erreur lors de la suppression du commentaire.</div>';
	}

// Demande de modification d'un commentaire
else if (isset ($_GET['modification'])) // Si on a 'modification' dans l'URL c'est qu'on a cliqué sur "modification" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("SELECT c.id id, commentaire, m.pseudo pseudo, annonce_id annonce, c.date_enregistrement date_enregistrement
	                              FROM commentaire c, membre m
	                              WHERE c.id = :id AND m.id = membre_id", array (':id' => $_GET['modification']));
	if ($resultat->rowCount() == 1)
		{
		$afficherFormulaire = true;
		$commentaireCourant = $resultat->fetch (PDO::FETCH_ASSOC);
		}
	else
		$contenu .= '<div class="alert alert-danger">Erreur interne.</div>';
	}

// Affichage du tableau des commentaires : 
$contenu .='<div class="table-responsive" id="tableau">';
$contenu .='</div>';
/*
$id = 0;
$titre = '';
$mots_cles = '';
*/
// Header standard
require_once '../inc/header.php';

// Navigation entre les pages d'administration
navigationAdmin ('Commentaires');

echo $contenu; // pour afficher notamment le tableau des commentaires

if ($afficherFormulaire)
	{
	isset ($commentaireCourant) && extract ($commentaireCourant);

	// Formulaire de création/modification des commentaires
?>
	<div class="cadre-formulaire" id="formulaire">
		<form method="post" action="gestion_commentaires.php">
			<input type="hidden" name="id" value="<?php echo $id??0 ?>"> <!-- hidden => éviter de le modifier par accident. value="0" => lors de l'insertion le SGBD utilisera l'auto-incrémentation -->
			<div class="form-row">
				<div class="form-group col-md-12">
					<label for="commentaire">Commentaire :</label>
					<textarea style="height:20vh" name="commentaire" id="commentaire" class="form-control"><?php echo $commentaire??'' ?></textarea>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-3">
					<label for="pseudo">Membre :</label>
					<input type="text" name="pseudo" id="pseudo" class="form-control" value="<?php echo $pseudo??'' ?>">
				</div>
				<div class="form-group col-md-3">
					<label for="annonce">Annonce :</label>
					<input type="text" name="annonce" id="annonce" class="form-control" value="<?php echo $annonce??'' ?>">
				</div>
				<div class="form-group col-md-6">
					<label for="date_enregistrement">Date enregistrement :</label>
					<input type="text" name="date_enregistrement" id="date_enregistrement" class="form-control" value="<?php echo $date_enregistrement??'' ?>">
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-2">
				</div>
				<div class="form-group col-md-2">
					<button type="submit" class="btn btn-primary">&nbsp; Enregistrer &nbsp;</button>
				</div>
				<div class="form-group col-md">
					<a href="<?php echo RACINE_SITE.'admin/gestion_commentaires.php?page='.$_SESSION["pageCommentaire"]??0?>" class="btn btn-secondary">&nbsp; Annuler &nbsp;</a>
				</div>
			</div>
		</form>
	</div>
	<?php
	} // fin du if ($afficherFormulaire)
	?>
<script>
	$(function(){ // document ready

		// Le tri et le numéro de page
		<?php
			echo 'let tri  = "'.($_SESSION["triCommentaire"]??0).'";';
			echo 'let sens = "'.($_SESSION["sensCommentaire"]??0).'";';
			echo 'let page = "'.($_SESSION["pageCommentaire"]??0).'";';
		?>

		// réception et traitement de la réponse à la requête AJAX
		function reponse (contenu)
			{
			$('#tableau').html(contenu);
			<?php if ($afficherFormulaire) echo 'location.hash = "#formulaire"' ?>

			$('.page-item').on('click', 'a', function(e)
				{
				page = e.target.id.replace(/[^0-9]/g, '');
				requeteAjax ();
				});
			}

		// Lancement de la requête AJAX
		function requeteAjax ()
			{
			// Emission de la requête AJAX
			$.post('table_commentaires.php', { triCommentaire  : tri,
			                                   sensCommentaire : sens,
			                                   pageCommentaire : page,
			                                 }, reponse, 'html');
			}

	    // trier si on clique sur une entête du tableau
		$('#tableau').on('click', 'th.tri', function(e){
			if (tri == e.target.id) sens = ((sens=='ASC')?'DESC':'ASC');
			else { tri = e.target.id; sens='ASC'; }
			requeteAjax();
		});

		// A l'affichage de la page, lancer une première fois la requête AJAX
		requeteAjax ();

	}); // document ready
</script>

<?php
require_once '../inc/footer.php';
