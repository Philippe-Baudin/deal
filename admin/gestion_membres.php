<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_membres.php
// affiche la liste des membres avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once '../inc/init.php';

$afficherFormulaire = false;

// Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers connection
	header ('location:../connexion.php');
	exit ();
	}

// Modification d'un membre
if (!empty($_POST))
	{
	$contenu = validerMembre($_POST, !empty($_POST['mdp']));
	if (empty($contenu))
		{
		$requete = executerRequete ("SELECT * FROM membre WHERE id!=:id AND pseudo=:pseudo",array(':id'=>$_POST['id'], ':pseudo'=>$_POST['pseudo']));
		if ($requete->rowCount() == 0)
			{
			$requete = executerRequete ("UPDATE membre SET pseudo=:pseudo, civilite=:civilite, nom=:nom, prenom=:prenom, email=:email, telephone=:telephone, role=:role, date_enregistrement=NOW() WHERE id=:id",
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
			if ($requete && !empty($_POST['mdp']))
				{
				$mdp = password_hash ($_POST['mdp'], PASSWORD_DEFAULT);
				$requete = executerRequete ("UPDATE membre SET mdp=:mdp, date_enregistrement=NOW() WHERE id=:id", array (':id' => $_POST['id'], ':mdp' => $mdp));
				}
			}
		else
			{
			$contenu .= '<div class="alert alert-danger">Le pseudo "'.$_POST['pseudo'].'" existe déjà.</div>';
			}
		}
	if (empty($contenu) && isset($requete))
		$contenu .= '<div class="alert alert-success">Le membre a été enregistré.</div>';
	else
		$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
	}

// Suppression d'un membre
if (isset ($_GET['suppression'])) // Si on a 'suppression' dans l'URL c'est qu'on a cliqué sur "suppression" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("DELETE FROM membre WHERE id = :id", array (':id' => $_GET['suppression']));
	if ($resultat->rowCount() == 1)
		$contenu .= '<div class="alert alert-success">Le membre a bien été supprimé.</div>';
	else
		$contenu .= '<div class="alert alert-danger">Erreur lors de la suppression du membre.</div>';
	}

// Demande de modification d'un membre
else if (isset ($_GET['modification'])) // Si on a 'modification' dans l'URL c'est qu'on a cliqué sur "modification" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("SELECT * FROM membre WHERE id = :id", array (':id' => $_GET['modification']));
	if ($resultat->rowCount() == 1)
		{
		$afficherFormulaire = true;
		$membre_courant = $resultat->fetch (PDO::FETCH_ASSOC);
		}
	}

// Affichage des membres dans le back-office :
$resultat = executerRequete ("SELECT * FROM membre");
$contenu .='<div class="table-responsive">';
$contenu .=   '<table class="table">';
$contenu .=      '<thead class="thead-dark">';
$contenu .=          '<tr>';
$contenu .=              '<th scope="col">Id_membre</th>';
$contenu .=              '<th scope="col">Pseudo</th>';
$contenu .=              '<th scope="col">Civilité</th>';
$contenu .=              '<th scope="col">Nom</th>';
$contenu .=              '<th scope="col">Prénom</th>';
$contenu .=              '<th scope="col">email</th>';
$contenu .=              '<th scope="col">Téléphone</th>';
$contenu .=              '<th scope="col">Statut</th>';
$contenu .=              '<th scope="col">Date d\'enregistrement</th>';
$contenu .=              '<th scope="col">Action</th>';
$contenu .=          '</tr>';
$contenu .=      '</thead>';
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) // pour chaque ligne retournée par la requête
	{
	extract ($ligne);
	$contenu .= '<tr>';
	$contenu .= '<th scope="row">' . $id . '</th>';
	$contenu .= '<td>' . $pseudo . '</td>';
	$contenu .= '<td>' . $civilite . '</td>';
	$contenu .= '<td>' . $nom . '</td>';
	$contenu .= '<td>' . $prenom . '</td>';
	$contenu .= '<td>' . $email . '</td>';
	$contenu .= '<td>' . $telephone . '</td>';
	$contenu .= '<td>' . $role . '</td>';
	$contenu .= '<td>' . $date_enregistrement . '</td>';
	// Là, il y a un petit bout de javaScript fûté : quand on retourne false dans un onclick, ça bloque le lien. Na.
	$contenu .= '<td><a href="?modification='.$ligne['id'].'#formulaire">Modifier</a>'."\n".'<a href="?suppression='.$ligne['id'].'" onclick="return confirm(\'Etes Vous certain de vouloir supprimer ce membre?\')">Supprimer</a></td>';
	$contenu .= '</tr>';
	}
$contenu .=   '</table>';
$contenu .='</div>';

require_once '../inc/header.php';

// Navigation entre les pages d'administration
navigation_admin ('Membres');

 // pour afficher les messages et le tableau des membres
echo $contenu;
if ($afficherFormulaire)
	{
	extract ($membre_courant);
	//3. Formulaire de modification de membres
	?>
	<br>
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
				<select name="civilite" class="form-control">
					<option value="M." selected>M.</option>
					<option value="Mme"<?php if (isset($civilite) && $civilite=='Mme') echo 'selected'; ?>>Mme</option>
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
			//XXX La vraie condition, c'est qu'on n'a pas le droit de supprier le rôle 'admin' au dernier admin
			//XXX C'est donc pas ici que ça se gère, mais en requête de suppremssion ou modif d'un membre
			if ($id == $_SESSION['membre']['id']) :
				?>
				<input type="hidden" name="role" value="admin">
			<?php else: ?>
				<div class="form-group col-md-6">
					<label for="role">Statut</label>
					<select name="role" class="form-control">
						<option value="user" selected>user</option>
						<option value="admin"<?php if (isset($role) && $role=='admin') echo 'selected'; ?>>admin</option>
					</select>
				</div>
			<?php endif ?>
		</div>
		<button type="submit" class="btn btn-primary">Enregistrer</button>
	</form>
	<?php
	}

require_once '../inc/footer.php';
