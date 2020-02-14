<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_membres.php
// affiche la liste des membres avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='../';
require_once '../inc/init.php';

$afficherFormulaire = false;

// Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers l'accueil
	header ('location:../index.php');
	exit ();
	}

// Compter les annonces de chaque membre
$nombreAnnonces = array ();
$resultat = executerRequete ("SELECT membre_id, COUNT(id) nb from annonce group by membre_id");
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC))
	$nombreAnnonces[$ligne['membre_id']] = $ligne['nb'];

// Traitement de la modification d'un membre
if (!empty($_POST))
	{
	$contenu = validerMembre($_POST, !empty($_POST['mdp']));
	if (empty($contenu))
		{
		$resultat = executerRequete ("SELECT * FROM membre WHERE id!=:id AND pseudo=:pseudo",array(':id'=>$_POST['id'], ':pseudo'=>$_POST['pseudo']));
		if ($resultat->rowCount() == 0)
			{
			$resultat = executerRequete ("UPDATE membre SET pseudo=:pseudo, civilite=:civilite, nom=:nom, prenom=:prenom, email=:email, telephone=:telephone, role=:role, date_enregistrement=NOW() WHERE id=:id",
			                            array (  ':id'                  => $_POST['id']
			                                  ,  ':pseudo'              => $_POST['pseudo']
			                                  ,  ':civilite'            => $_POST['civilite']
			                                  ,  ':nom'                 => $_POST['nom']
			                                  ,  ':prenom'              => $_POST['prenom']
			                                  ,  ':email'               => $_POST['email']
			                                  ,  ':telephone'           => $_POST['telephone']
			                                  ,  ':role'                => $_POST['role']
			                                  )
			                           );
			if ($resultat && !empty($_POST['mdp']))
				{
				$mdp = password_hash ($_POST['mdp'], PASSWORD_DEFAULT);
				$resultat = executerRequete ("UPDATE membre SET mdp=:mdp, date_enregistrement=NOW() WHERE id=:id", array (':id' => $_POST['id'], ':mdp' => $mdp));
				}
			}
		else
			{
			$contenu .= '<div class="alert alert-danger">Le pseudo "'.$_POST['pseudo'].'" existe déjà.</div>';
			}
		}
	if (empty($contenu) && isset($resultat))
		$contenu .= '<div class="alert alert-success">Le membre a été enregistré.</div>';
	else
		{
		$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
		$afficherFormulaire = true;
		$membreCourant = $_POST;
		}
	}

// Demande de modification d'un membre
else if (isset ($_GET['modification'])) // Si on a 'modification' dans l'URL c'est qu'on a cliqué sur "modification" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("SELECT * FROM membre WHERE id = :id", array (':id' => $_GET['modification']));
	if ($resultat->rowCount() == 1)
		{
		$afficherFormulaire = true;
		$membreCourant = $resultat->fetch (PDO::FETCH_ASSOC);
		}
	}

// Affichage du tableau des membres, renseigné en réponse à une requête AJAX pour être trié et paginé
$contenu .= '<div class="table-responsive" id="tableau">';
$contenu .= '</div>';

// Header standard
require_once '../inc/header.php';

// Emplacement du message de retour de suppression d'un membre
echo '<div id="messageSuppression"></div>';

// Modale de confirmation de la supression d'un membre
modaleSuppression ('ce membre', true);

// Navigation entre les pages d'administration
navigationAdmin ('Membres');

// pour afficher les messages et le tableau des membres
echo $contenu;

// Formulaire de modification d'un membre
if ($afficherFormulaire)
	{
	extract ($membreCourant);
	// Formulaire de modification de membres
	?>
	<br>
	<div class="cadre-formulaire">
		<form id="formulaire" method="post" action="gestion_membres.php">
			<input type="hidden" name="id" value="<?php echo $id ?>"> <!-- hidden => éviter de le modifier par accident. value="0" => lors de l'insertion le SGBD utilisera l'auto-incrémentation -->
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="pseudo">Pseudo</label>
					<input type="text" name="pseudo" id="pseudo" class="form-control" value="<?php echo $pseudo ?>">
				</div>
				<div class="form-group col-md-6">
					<label for="email">email</label>
					<input type="text" name="email" id="email" class="form-control" value="<?php echo $email ?>">
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="mdp">Mot de passe (inchangé si le champ reste vide)</label>
					<input type="password" name="mdp" id="mdp" class="form-control">
				</div>
				<div class="form-group col-md-6">
					<label for="telephone">Téléphone</label>
					<input type="text" name="telephone" id="telephone" class="form-control" value="<?php echo $telephone ?>">
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="nom">Nom</label>
					<input type="text" name="nom" id="nom" class="form-control" value="<?php echo $nom ?>">
				</div>
				<div class="form-group col-md-6">
					<label for="civilite">Civilité</label>
					<select name="civilite" id="civilite" class="form-control">
						<option value="M."<?php if (isset($civilite) && $civilite=='M.') echo ' selected'; ?>>M.</option>
						<option value="Mme"<?php if (isset($civilite) && $civilite=='Mme') echo ' selected'; ?>>Mme</option>
					</select>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="prenom">Prénom</label>
					<input type="text" name="prenom" id="prenom" class="form-control" value="<?php echo $prenom ?>">
				</div>
				<?php
				// Un admin n'a pas le droit de s'enlever à lui même le rôle admin. Sinon, on risque de ne plus avoir d'admin du tout ...
				//XXX ça ne suffit pas : on peut avoir deux pages ouvertes sur deux admin différents
				//XXX La vraie condition, c'est qu'on n'a pas le droit de supprimer le rôle 'admin' au dernier admin
				//XXX C'est donc pas ici que ça se gère, mais en requête de suppression ou modif d'un membre
				if ($id == $_SESSION['membre']['id']) :
					?>
					<input type="hidden" name="role" value="admin">
				<?php else: ?>
					<div class="form-group col-md-6">
						<label for="role">Statut</label>
						<select name="role" id="role" class="form-control">
							<option value="user"<?php if (isset($role) && $role=='user') echo ' selected'; ?>>user</option>
							<option value="admin"<?php if (isset($role) && $role=='admin') echo ' selected'; ?>>admin</option>
						</select>
					</div>
				<?php endif ?>
			</div>
			<div class="form-row">
				<div class="form-group col-md-2">
				</div>
				<div class="form-group col-md-2">
					<button type="submit" class="btn btn-primary">&nbsp; Enregistrer &nbsp;</button>
				</div>
				<div class="form-group col-md">
					<a href="<?php echo RACINE_SITE.'admin/gestion_membres.php?page='.$_SESSION["pageMembre"]??0?>" class="btn btn-secondary">&nbsp; Annuler &nbsp;</a>
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
		let cible;

		// clic sur le bouton 'oui' de la fenêtre modale de confirmation de suppression
		$(".ok-suppression").on ('click', function(){
			$.post('suppression_membre.php', {id:cible},function(reponse){
				$('#modaleSuppression').modal('hide');
				$('#messageSuppression').html(reponse);
				afficherTableau ();
				}, 'html');
			});

		// Lancement de la requête AJAX d'affichage du tableau
		function afficherTableau ()
			{
			// arrêter les listener de demande de suppression
			$(".demande-suppression").off("click");
			// Emission de la requête AJAX
			$.post('table_membres.php', { triMembre:tri, sensMembre:sens, pageMembre:page }, reponse, 'html');
			}

		// réception et traitement de la réponse à la requête AJAX d'affichage du tableau
		function reponse (contenu)
			{
			$('#tableau').html(contenu);
			<?php if ($afficherFormulaire) echo 'location.hash = "#formulaire"' ?>

			// clic sur une icône "poubelle" du tableau
			$(".demande-suppression").on ('click', function(e){
				let decoupe = e.currentTarget.id.split('_');
				let pseudo = decoupe[0];
				cible = decoupe[1];
				let nombreAnnonces = decoupe[2];
				let complement = '';
				switch (nombreAnnonces)
					{
					case '0' : complement = pseudo+' n\'a déposé aucune annonce.'; break;
					case '1' : complement = pseudo+' a déposé une annonce qui sera inaccessible aux utilisateurs si vous supprimez son compte.'; break;
					default : complement = pseudo+' a déposé '+nombreAnnonces+' annonces qui seront inaccessibles aux utilisateurs si vous supprimez son compte.'; break;
					}
				//$('#complement').html(complement);
				let modaleSuppression = $('#modaleSuppression');
				modaleSuppression.on('show.bs.modal', _=> {$('#complement').text(complement)})
				modaleSuppression.modal('show');
				});

			// clic sur une des cases de la pagination
			$('.page-item').on('click', 'a', function(e)
				{
				page = e.target.id.replace(/[^0-9]/g, '');
				afficherTableau ();
				});
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
require_once '../inc/footer.php';
