﻿<?php
$repertoire='';
require_once 'inc/init.php';
$commentaires = '';
$avis = '';

// Si le visiteur n'est pas connecté, le rediriger vers l'accueil
if (!estConnecte())
	{
	header ('location:index.php');
	exit (); // et on quitte le script
	}
extract ($_SESSION['membre']);

// Modification d'un membre
if (!empty($_POST))
	{
	if (isset ($_POST['message']))
		{
		// email des admin
		$resultat = executerRequete ("SELECT email FROM membre WHERE role='admin'");
		if ($resultat && $resultat->rowCount()>1)
			{
			while ($email = $resultat->fetch(PDO::FETCH_NUM))
				{
				$to = $email[0];
				$objet = 'Message de '.$_SESSION['membre']['pseudo'];
				if (!mail ($to, $objet, str_replace ("\n.", "\n..", $_POST['message'])))
					$contenu .= '<div class="alert alert-success">Erreur lors de l\'envoi du message.</div>';
				}
			}
		}
	else
		{
		$contenu = validerMembre($_POST, !empty($_POST['mdp']));
		if (empty($contenu))
			{
			$resultat = executerRequete ("SELECT * FROM membre WHERE id!=:id AND pseudo=:pseudo",array(':id'=>$_POST['id'], ':pseudo'=>$_POST['pseudo']));
			if ($resultat->rowCount() == 0)
				{
				$resultat = executerRequete ("UPDATE membre SET civilite=:civilite, nom=:nom, prenom=:prenom, email=:email, telephone=:telephone WHERE id=:id",
				                            array (  ':id'                  => $_POST['id']
				                                  ,  ':civilite'            => $_POST['civilite']
				                                  ,  ':nom'                 => $_POST['nom']
				                                  ,  ':prenom'              => $_POST['prenom']
				                                  ,  ':email'               => $_POST['email']
				                                  ,  ':telephone'           => $_POST['telephone']
				                                  )
				                           );
				if (!$resultat)
					{
					$contenu .=  '<div class="alert alert-danger">Erreur lors de l\'enregistrement du profil.</div>';
					}
				else if (!empty($_POST['mdp']))
					{
					$mdp = password_hash ($_POST['mdp'], PASSWORD_DEFAULT);
					$resultat = executerRequete ("UPDATE membre SET mdp=:mdp, date_enregistrement=NOW() WHERE id=:id", array (':id' => $_POST['id'], ':mdp' => $mdp));
					if ($resultat === false)
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
		if (empty($contenu) && isset($resultat))
			{
			$contenu .= '<div class="alert alert-success">Vos modifications ont été enregistrées.</div>';
			$resultat = executerRequete ("SELECT * FROM membre WHERE pseudo = :pseudo", array (':pseudo' => $_POST['pseudo']));
			$_SESSION['membre'] = $resultat->fetch (PDO::FETCH_ASSOC);
			}
		else
			$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
		}
	}


// Commentaires sur les annonces du membre
$decompteAnnonces = executerRequete ("SELECT COUNT(id) FROM annonce WHERE membre_id = :id",  array (':id' => $id));
if ($decompteAnnonces->fetch (PDO::FETCH_NUM)[0] != '0')
	{
	$commentaires .= '<hr>';
	$commentaires .= '<div style="text-align:center"><h4>Commentaires des autres membres sur vos annonces</h4></div>';
	$listeAnnonces = executerRequete ("SELECT id, titre, date_enregistrement date
	                                     FROM annonce
	                                     WHERE membre_id = :id 
	                                     ORDER BY date_enregistrement DESC", array (':id' => $id));
	while ($annonce = $listeAnnonces->fetch(PDO::FETCH_ASSOC))
		{
		$commentaires .= '<div style="background:lightgrey"><h5><a href="fiche_annonce.php?id='.$annonce['id'].'">annonce "'.$annonce['titre'].'", date : '.$annonce['date'].'</a></h5></div>';
		// Afficher les commentaires de chaque annonce
		// S'il existe un commentaire du membre connecté on n'affiche que les commentaires dont la date est > à sa date
		$resultat = executerRequete ("SELECT MAX(date_enregistrement) FROM commentaire WHERE annonce_id = :id AND membre_id = :moi",
		                             array (':id' => $annonce['id'], ':moi' => $_SESSION['membre']['id']));
		$date = '';
		if ($resultat->rowCount() == 1)
			$date = $resultat->fetch(PDO::FETCH_NUM)[0];
		$commentaires .= '<div id="suite_'.$annonce['id'].'">';
		$commentaires .= '<script>suiteCommentaires('.$annonce['id'].($date?(',\''.$date.'\''):'').')</script>';
		$commentaires .= '</div>';
		$commentaires .= '<hr>';
		}
	}
// Avis des autres membres
$resultat = executerRequete ("SELECT AVG(note) note, COUNT(note) nombre
                             FROM note
                             WHERE membre_id2 = :id", array (':id' => $id));
$resultat = $resultat->fetch (PDO::FETCH_ASSOC);
if ($resultat['nombre'] != '0')
	{
	$avis .= '<div style="text-align:center"><h4>Avis des autres membres : '.noteEnEtoiles($resultat['note']).'</h4></div>';
	$resultat = executerRequete ("SELECT avis, pseudo, note
	                             FROM note
	                             LEFT JOIN membre ON membre_id1 = membre.id
	                             WHERE membre_id2 = :id
	                             ORDER BY note.date_enregistrement DESC", array (':id' => $id));
	while ($ligne = $resultat->fetch (PDO::FETCH_ASSOC))
		{
		$avis .= "<h5>$ligne[pseudo] vous mets une note de $ligne[note]</h5>";
		$avis .= "<p>$ligne[avis]</p>";
		}
	} // fin if ($resultat['nombre'] == '0')



require_once 'inc/header.php';

echo $contenu;

// Afficher le profil
?>

<div class="row" style="width:100%;text-align:center;">
	<div class="col-sm-8">
		<h1 class="mt-4">Profil</h1>
	</div>
	<div class="col-sm connected">
		<button type="button" class="btn btn-primary connected mt-4" data-toggle="modal" data-target="#modaleContact">
 			Contacter un administrateur
		</button>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<hr>
	</div>
</div>
<div class="row">
	<div class="col-sm-4">
		<div class="row">
			&nbsp;
		</div>

<?php
extract ($_SESSION['membre']);
echo         '<h3 style="text-align:center;">'.$pseudo.'</h3>';
echo         '<br>';
echo         '<div style="padding:10px;" class="cadre-formulaire">';
echo             '<p>'.$civilite.' '.$prenom.' '.$nom.'</p>';
echo             '<p><span style="font-size:1.4rem;">&#9742;</span> &nbsp; '.$telephone.'</p>';
echo             '<p><span style="font-size:1.4rem;"><i class="far fa-envelope"></i></span> &nbsp; '.$email.'</p>';
echo         '</div>';
if (estAdmin())
//	echo  '<br><p style="text-align:center;">Vous êtes un administrateur.</p>';
	echo  '<div class="row mt-5 justify-content-center">Vous êtes un administrateur.</div>';
echo    '</div>'; // "col-sm-4"
?>

	<div class="col-sm">
		<div class="row">
			<div class="col-sm-12">
				<p style="text-align:center;">Vous pouvez modifier votre profil :</p>
			</div>
		</div>
		<div class="cadre-formulaire">
			<form id="formulaire" method="post" action="#">
				<input type="hidden" name="id" value="<?php echo $id ?>">
				<input type="hidden" name="pseudo" value="<?php echo $pseudo ?>">
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="civilite">Civilité :</label>
						<select name="civilite" id="civilite" class="form-control">
							<option value="M." selected>M.</option>
							<option value="Mme"<?php if (isset($civilite) && $civilite=='Mme') echo 'selected'; ?>>Mme</option>
						</select>
					</div>
					<div class="form-group col-md-6">
						<label for="mdp">Mot de passe (inchangé si le champ reste vide) :</label>
						<input type="password" name="mdp" id="mdp" class="form-control" value="">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="nom">Nom :</label>
						<input type="text" name="nom" id="nom" class="form-control" value="<?php echo $nom ?>">
					</div>
					<div class="form-group col-md-6">
						<label for="email">email :</label>
						<input type="text" name="email" id="email" class="form-control" value="<?php echo $email ?>">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-6">
						<label for="prenom">Prénom :</label>
						<input type="text" name="prenom" id="prenom" class="form-control" value="<?php echo $prenom ?>">
					</div>
					<div class="form-group col-md-6">
						<label for="telephone">Téléphone :</label>
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
		</div> <!-- "cadre-formulaire" -->
	</div> <!-- "col-sm" -->
</div> <!-- "row" -->

<!-- Fenêtre modale pour contacter un administrateur -->
<div class="modal fade" id="modaleContact" tabindex="-1" role="dialog" aria-labelledby="modaleContactTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modaleContactTitle">Contacter un administrateur</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="post" action="#">
					<div class="form-group">
						<label for="message" class="col-form-label">Votre message :</label>
						<textarea class="form-control" id="message" name="message" rows="5"></textarea>
					</div>
					<div class="row">
						<div class="col-sm-2">
							<button type="submit" class="btn btn-primary">Envoyer</button>
						</div>
						<div class="col-sm">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- Afficher les commentaires et les avis -->
<script>
	function suiteCommentaires(id,date)
		{
		if (typeof date === "undefined") commande = {id:id};
		else commande = {id:id, date:date};
		$.post('liste_commentaires.php', commande, function(retour){$('#suite_'+id).html(retour)}, 'html');
		return false;
		}

</script>

<?php

echo $commentaires;
echo $avis;


require_once 'inc/footer.php';

