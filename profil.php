<?php
require_once 'inc/init.php';
$commentaires = '<hr>';
$avis = '';

// Si le visiteur n'est pas connecté, le rediriger vers la page de connexion
if (!estConnecte())
	{
	header ('location:connexion.php');
	exit (); // et on quitte le script
	}
extract ($_SESSION['membre']);

// Modification d'un membre
if (!empty($_POST))
	{
	$contenu = validerMembre($_POST, !empty($_POST['mdp']));
	if (empty($contenu))
		{
		$requete = executerRequete ("SELECT * FROM membre WHERE id!=:id AND pseudo=:pseudo",array(':id'=>$_POST['id'], ':pseudo'=>$_POST['pseudo']));
		if ($requete->rowCount() == 0)
			{
			$requete = executerRequete ("UPDATE membre SET civilite=:civilite, nom=:nom, prenom=:prenom, email=:email, telephone=:telephone WHERE id=:id",
			                            array (  ':id'                  => $_POST['id']
			                                  ,  ':civilite'            => $_POST['civilite']
			                                  ,  ':nom'                 => $_POST['nom']
			                                  ,  ':prenom'              => $_POST['prenom']
			                                  ,  ':email'               => $_POST['email']
			                                  ,  ':telephone'           => $_POST['telephone']
			                                  )
			                           );
			if (!$requete)
				{
				$contenu .=  '<div class="alert alert-danger">Erreur lors de l\'enregistrement du profil.</div>';
				}
			else if (!empty($_POST['mdp']))
				{
				$mdp = password_hash ($_POST['mdp'], PASSWORD_DEFAULT);
				$requete = executerRequete ("UPDATE membre SET mdp=:mdp, date_enregistrement=NOW() WHERE id=:id", array (':id' => $_POST['id'], ':mdp' => $mdp));
				if ($requete === false)
					{
					$contenu .=  '<div class="alert alert-danger">Erreur lors de l\'enregistrement du profil.</div>';
					}
				}
			}
		else
			{
			$contenu .= '<div class="alert alert-danger">Le pseudo "'.$_POST['pseudo'].'" existe déjà.</div>';
			}
		}
	if (empty($contenu) && isset($requete))
		{
		$contenu .= '<div class="alert alert-success">Vos modifications ont été enregistrées.</div>';
		$resultat = executerRequete ("SELECT * FROM membre WHERE pseudo = :pseudo", array (':pseudo' => $_POST['pseudo']));
		$_SESSION['membre'] = $resultat->fetch (PDO::FETCH_ASSOC);
		}
	else
		$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
	}


// Commentaires sur les annonces du membre
$requeteCompteAnnonces = executerRequete ("SELECT COUNT(id) FROM annonce WHERE membre_id = :id",  array (':id' => $id));
if ($requeteCompteAnnonces->fetch (PDO::FETCH_NUM)[0] != '0')
	{
	$commentaires .= '<h4>Commentaires des autres membres sur vos annonces</h4>';
	$requeteAnnonces = executerRequete ("SELECT id, titre, date_enregistrement date
	                             FROM annonce
	                             WHERE membre_id = :id 
	                             ORDER BY date_enregistrement DESC", array (':id' => $id));
	while ($annonce = $requeteAnnonces->fetch(PDO::FETCH_ASSOC))
		{
//XXX s'il existe un commentaire du membre connecté
//			n'afficher que les commentaires dont la date est > à sa date
		$commentaires .= '<h5>annonce "'.$annonce['titre'].', date : '.$annonce['date'].'"</h5>';
		// Afficher les commentaires de chaque annonce
		$requeteCommentaires = executerRequete ("SELECT pseudo, a.commentaire, a.date_enregistrement date
		                             FROM commentaire a, membre
		                             WHERE a.annonce_id = :id AND membre.id=a.membre_id
		                               AND (NOT EXISTS (SELECT id FROM commentaire b WHERE b.annonce_id=a.annonce_id AND b.membre_id=:moi) OR (a.date_enregistrement >= (SELECT MAX(date_enregistrement) FROM commentaire c WHERE c.annonce_id = a.annonce_id AND c.membre_id = :moi)))
		                             ORDER BY date DESC", array (':id' => $annonce['id'], ':moi' => $_SESSION['membre']['id']));
		if ($requeteCommentaires->rowCount() > 0)
			{
			while ($resultat = $requeteCommentaires->fetch (PDO::FETCH_ASSOC))
				{
				extract ($resultat);
				$commentaires .= '<h6>Commentaire de '.$pseudo.', daté du '.$date.'</h6>';
				$commentaires .= '<p style="margin-left:50px;">'.$commentaire.'<p>';
				}
			}
		else
			$commentaires .= '<p>Aucun membre n\'a laissé de commentaire</h5>';
		$commentaires .= '<hr>';
		}
	}
// Avis des autres membres
$requete = executerRequete ("SELECT AVG(note) note, COUNT(note) nombre
                             FROM note
                             WHERE membre_id2 = :id", array (':id' => $id));
$resultat = $requete->fetch (PDO::FETCH_ASSOC);
if ($resultat['nombre'] != '0')
	{
	$avis .= '<h4>Avis des autres membres (note moyenne : '.sprintf ("%.1f", $resultat['note']).'/20)</h4>';
	$requete = executerRequete ("SELECT avis, pseudo, note
	                             FROM note
	                             LEFT JOIN membre ON membre_id1 = membre.id
	                             WHERE membre_id2 = :id
	                             ORDER BY note.date_enregistrement DESC", array (':id' => $id));
	while ($resultat = $requete->fetch (PDO::FETCH_ASSOC))
		{
		$avis .= "<h5>$resultat[pseudo] vous mets une note de $resultat[note]</h5>";
		$avis .= "<p>$resultat[avis]</p>";
		}
	} // fin if ($resultat['nombre'] == '0')



require_once 'inc/header.php';

echo $contenu;

// Afficher le profil
echo '<div class="row" style="width:100%;text-align:center;">';
echo     '<div class="col-sm-12">';
echo         '<h1 classe="mt-4" style="pading:auto;">Profil</h1>';
echo     '</div>';
echo '</div>';
echo '<div class="row">';
echo     '<div class="col-sm-12">';
echo         '<hr>';
echo     '</div>';
echo '</div>';
echo '<div class="row">';
echo     '<div class="col-sm-4">';

extract ($_SESSION['membre']);
echo         '<p>Pseudo : '     . $pseudo     . '</p>';
echo         '<p>Nom : '        . $civilite   . ' ' . $prenom . ' ' . $nom . '</p>';
echo         '<p>Téléphone : '  . $telephone  . '</p>';
echo         '<p>Email : '      . $email      . '</p>';
if (estAdmin())
	echo     '<p>Vous êtes un administrateur.</p>';
echo    '</div>'; // "col-sm-4"
?>

	<div class="col-sm">
		<form id="formulaire" method="post" action="">
			<input type="hidden" name="id" value="<?php echo $id ?>">
			<input type="hidden" name="pseudo" value="<?php echo $pseudo ?>">
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="civilite">Civilité</label>
					<select name="civilite" id="civilite" class="form-control">
						<option value="M." selected>M.</option>
						<option value="Mme"<?php if (isset($civilite) && $civilite=='Mme') echo 'selected'; ?>>Mme</option>
					</select>
				</div>
				<div class="form-group col-md-6">
					<label for="mdp">Mot de passe (inchangé si le champ reste vide)</label>
					<input type="password" name="mdp" id="mdp" class="form-control">
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="nom">Nom</label>
					<input type="text" name="nom" id="nom" class="form-control" value="<?php echo $nom ?>">
				</div>
				<div class="form-group col-md-6">
					<label for="email">email</label>
					<input type="text" name="email" id="email" class="form-control" value="<?php echo $email ?>">
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-6">
					<label for="prenom">Prénom</label>
					<input type="text" name="prenom" id="prenom" class="form-control" value="<?php echo $prenom ?>">
				</div>
				<div class="form-group col-md-6">
					<label for="telephone">Téléphone</label>
					<input type="text" name="telephone" id="telephone" class="form-control" value="<?php echo $telephone ?>">
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-5">
				</div>
				<div class="form-group col-md-6">
				<button type="submit" class="btn btn-primary">Enregistrer</button>
				</div>
			</div>
		</form>

	</div> <!-- "col-sm" -->
</div> <!-- "row" -->

<?php
// Afficher les commentaires
echo $commentaires;
echo $avis;

require_once 'inc/footer.php';

