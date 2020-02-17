<?php
require_once 'inc/init.php';
$messageInscription = '';


// Traitement du formulaire d'inscription
if ($_POST && isset ($_POST['pseudo']) && isset ($_POST['mdp']) && isset($_POST['nom'])) // si le formulaire a été envoyé
	{
	$messageInscription = validerMembre ($_POST);

	// S'il n'y a pas d'erreur sur le formulaire, on vérifie l'unicité du pseudo avant d'inscrire le membre
	if (empty ($messageInscription)) // donc pas d'erreur
		{
		// Vérifier l'unicité du pseudo en BDD
		$membre = executerRequete ("SELECT * FROM membre WHERE pseudo = :pseudo", array (':pseudo' => $_POST['pseudo']));

		if ($membre && $membre->rowCount())
			{
			// La requête renvoie au moins une ligne : le pseudo existe déjà
			$messageInscription .= '<div class="alert alert-danger">Le pseudo '.$_POST['pseudo'].' n\'est pas disponible. Veuillez en choisir un autre.</div>';
			}
		else
			{
			// hasher le mot de passe
			$mdp = password_hash ($_POST['mdp'], PASSWORD_DEFAULT);

			$succes = executerRequete ("INSERT INTO membre ( pseudo, mdp, nom, prenom, telephone, email, civilite, role,  date_enregistrement)
			                            VALUES             (:pseudo,:mdp,:nom,:prenom,:telephone,:email,:civilite,'user', NOW())",
			                           array ( ':pseudo'       => $_POST['pseudo']
			                                 , ':mdp'          => $mdp // le mot de passe hashé
			                                 , ':nom'          => $_POST['nom']
			                                 , ':prenom'       => $_POST['prenom']
			                                 , ':telephone'    => $_POST['telephone']
			                                 , ':email'        => $_POST['email']
			                                 , ':civilite'     => $_POST['civilite']
			                                 )
			                          );
			if ($succes)
				{
				$messageConnexion .= '<div class="success alert-success">Vous êtes inscrit ! Vous pouvez vous connecter.</div>';
				}
			else
				{
				$messageInscription .= '<div class="alert alert-danger">Ooops !... Erreur lors de l\'enregistrement : veuillez réessayer plus tard ...</div>';
				}
			} // fin du if rowCount()
		} // fin du if (empty ($contenu))
	} // fin du if (!empty($_POST))

// Fenêtre modale d'inscription
if (empty($messageInscription))
	$contenu .= '<div class="modal fade" id="modaleInscription" tabindex="-1" role="dialog" aria-labelledby="modaleInscriptionTitle" aria-hidden="true">';
else
	$contenu .= '<div class="modal" id="modaleInscription" tabindex="-1" role="dialog" aria-labelledby="modaleInscriptionTitle" aria-hidden="true">';
$contenu .=   '<div class="modal-dialog modal-dialog-centered modal-lg" role="document">';
$contenu .=     '<div class="modal-content">';
$contenu .=       '<div class="modal-header">';
$contenu .=         '<h5 class="modal-title" id="modaleInscriptionTitle">Inscription</h5>';
$contenu .=         '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
$contenu .=           '<span aria-hidden="true">&times;</span>';
$contenu .=         '</button>';
$contenu .=       '</div>';
$contenu .=       '<div class="modal-body">';

if (!empty($messageInscription))
	{
	$contenu .=       '<div class="row">';
	$contenu .=         '<div class="col-sm-12">';
	$contenu .=           $messageInscription;
	$contenu .=         '</div>';
	$contenu .=       '</div>';
	}

$contenu .=         '<form method="post" action="#">';
$contenu .=           '<div class="form-group">';
$contenu .=             '<label for="pseudo_inscription" class="col-form-label">Pseudo :</label>';
$contenu .=             '<input type="text" class="form-control" id="pseudo_inscription" name="pseudo" value="'.($_POST['pseudo']??'').'">';
$contenu .=           '</div>';
$contenu .=           '<div class="form-group">';
$contenu .=             '<label for="mdp_inscription" class="col-form-label">Mot de passe :</label>';
$contenu .=             '<input type="password" class="form-control" id="mdp_inscription" name="mdp" value="'.($_POST['mdp']??'').'">';
$contenu .=           '</div>';

$contenu .=           '<div class="row">';
$contenu .=             '<div class="form-group col-sm-2">';
$contenu .=               '<label for="civilite" class="col-form-label">Civilité :</label>';
$contenu .=               '<select name="civilite" id="civilite" class="form-control">';
$contenu .=                 '<option value="M." selected>M.</option>';
$contenu .=                 '<option value="Mme"'.((isset($_POST['civilite'])&&$_POST['civilite']=='Mme')?' selected':'').'>Mme</option>';
$contenu .=               '</select>';
$contenu .=             '</div>';
$contenu .=             '<div class="form-group col-sm-5">';
$contenu .=               '<label for="nom" class="col-form-label">Nom :</label>';
$contenu .=               '<input type="text" class="form-control" id="nom" name="nom" value="'.($_POST['nom']??'').'">';
$contenu .=             '</div>';
$contenu .=             '<div class="form-group col-sm">';
$contenu .=               '<label for="prenom" class="col-form-label">Prénom :</label>';
$contenu .=               '<input type="text" class="form-control" id="prenom" name="prenom" value="'.($_POST['prenom']??'').'">';
$contenu .=             '</div>';
$contenu .=           '</div>';

$contenu .=           '<div class="row">';
$contenu .=             '<div class="form-group col-sm-7">';
$contenu .=               '<label for="email" class="col-form-label">email :</label>';
$contenu .=               '<input type="text" class="form-control" id="email" name="email" value="'.($_POST['email']??'').'">';
$contenu .=             '</div>';
$contenu .=             '<div class="form-group col-sm">';
$contenu .=               '<label for="telephone" class="col-form-label">Téléphone :</label>';
$contenu .=               '<input type="text" class="form-control" id="telephone" name="telephone" value="'.($_POST['telephone']??'').'">';
$contenu .=             '</div>';
$contenu .=           '</div>';

$contenu .=           '<div class="row">';
$contenu .=             '<div class="col-sm-4">';
$contenu .=               '<button type="submit" class="btn btn-primary">M\'inscrire</button>';
$contenu .=             '</div>';
$contenu .=             '<div class="col-sm">';
$contenu .=               '<button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>';
$contenu .=             '</div>';
$contenu .=           '</div>';

$contenu .=         '</form>';
$contenu .=       '</div>';
$contenu .=     '</div>';
$contenu .=   '</div>';
$contenu .= '</div>';

