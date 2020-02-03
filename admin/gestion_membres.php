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
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers l'accueil
	header ('location:../index.php');
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
		{
		$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
		$afficherFormulaire = true;
		$membreCourant = $_POST;
		}
	}

// Suppression d'un membre
if (isset ($_GET['suppression'])) // Si on a 'suppression' dans l'URL c'est qu'on a cliqué sur "suppression" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("UPDATE annonce SET  membre_id = NULL WHERE membre_id = :id", array (':id' => $_GET['suppression']));
	$resultat = executerRequete ("UPDATE commentaire SET  membre_id = NULL WHERE membre_id = :id", array (':id' => $_GET['suppression']));
	$resultat = executerRequete ("UPDATE note SET  membre_id1 = NULL WHERE membre_id1 = :id", array (':id' => $_GET['suppression']));
	$resultat = executerRequete ("DELETE FROM note WHERE membre_id2 = :id", array (':id' => $_GET['suppression']));
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
		$membreCourant = $resultat->fetch (PDO::FETCH_ASSOC);
		}
	}

// Compter les annonces de chaque membre
$nombreAnnonces = array ();
$resultat = executerRequete ("SELECT membre_id, COUNT(id) nb from annonce group by membre_id");
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC))
	$nombreAnnonces[$ligne['membre_id']] = $ligne['nb'];

// Affichage des membres dans le back-office :
$resultat = executerRequete ("SELECT * FROM membre");
$contenu .='<div class="table-responsive">';
$contenu .=   '<table class="table">';
$contenu .=      '<thead class="thead-dark">';
$contenu .=          '<tr>';
$contenu .=              '<th scope="col">Id</th>';
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
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC))
	{
	extract ($ligne);
	$contenu .= '<tr>';
	$contenu .=     '<th scope="row">' . $id . '</th>';
	$contenu .=     '<td>' . $pseudo . '</td>';
	$contenu .=     '<td>' . $civilite . '</td>';
	$contenu .=     '<td>' . $nom . '</td>';
	$contenu .=     '<td>' . $prenom . '</td>';
	$contenu .=     '<td>' . $email . '</td>';
	$contenu .=     '<td>' . $telephone . '</td>';
	$contenu .=     '<td>' . $role . '</td>';
	$contenu .=     '<td>' . $date_enregistrement . '</td>';
	// Là, il y a un petit bout de javaScript fûté : quand on retourne false dans un onclick, ça bloque le lien. Na.
	$contenu .=     '<td>';
	$contenu .=         '<a href="?modification='.$ligne['id'].'#formulaire" class="lien-noir">'.MODIFIER.'</a>'."\n";
	switch ($nombreAnnonces[$id]??'0')
		{
		case '0':
			$contenu .= '<a href="?suppression='.$ligne['id'].'" onclick="return confirm(\'Etes-vous certain de vouloir supprimer le compte de '.$pseudo.' ?\')" class="lien-noir">'.POUBELLE.'</a>';
			break;
		case '1':
			$contenu .= '<a href="?suppression='.$ligne['id'].'" onclick="return confirm(\''.$pseudo.' a déposé une annonce qui deviendra inaccessible aux utilisateurs si vous supprimez son compte. Etes-vous certain de vouloir supprimer son compte ?\')" class="lien-noir">'.POUBELLE.'</a>';
			break;
		default :
			$contenu .= '<a href="?suppression='.$ligne['id'].'" onclick="return confirm(\''.$pseudo.' a déposé '.($nombreAnnonces[$id]??'0').' annonces. Si vous supprimez son compte, elles deviendront inaccessibles aux utilisateurs. Etes-vous certain de vouloir supprimer son compte ?\')" class="lien-noir">'.POUBELLE.'</a>';
			break;
		}
	$contenu .=     '</td>';
	$contenu .= '</tr>';
	}
$contenu .=   '</table>';
$contenu .='</div>';

require_once '../inc/header.php';

// Navigation entre les pages d'administration
navigationAdmin ('Membres');

// pour afficher les messages et le tableau des membres
echo $contenu;
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
			<button type="submit" class="btn btn-primary">Enregistrer</button>
		</form>
	</div>

	<!-- Modale de confirmation de la suppression d'un membre -->
	<!--
   <div class="modal fade" id="modaleSuppressionMembre" tabindex="-1" role="dialog" aria-labelledby="modaleSuppressionMembreTitle" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
       <div class="modal-content">
         <div class="modal-header">
           <h5 class="modal-title" id="modaleSuppressionMembreLongTitle">Suppression d'un membre</h5>
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body">
			<p>
           <form method="post" action="#">
             <input type="hidden" name="id" value="'.$id.'">
             <div class="form-group">
               <label for="commentaire" class="col-form-label">Postez un commentaire pour poser une question ou obtenir des précisions sur le produit ou le service proposé :</label>
               <textarea class="form-control" id="commentaire" name="commentaire" rows="5"></textarea>
             </div>
             <div class="row">
               <div class="col-sm-2">
                 <button type="submit" class="btn btn-primary">Envoyer</button>
               </div>
               <div class="col-sm-2">
                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
               </div>
             </div>
           </form>
         </div>
       </div>
     </div>
   </div>
-->
	<?php
	}

require_once '../inc/footer.php';
