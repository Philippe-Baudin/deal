<?php
require_once 'inc/init.php';
$messageConnexion = '';

// Traitement du formulaire de connexion
if ($_POST && isset ($_POST['pseudo']) && isset ($_POST['mdp']) && !isset($_POST['nom'])) // si le formulaire a été envoyé
	{
	// Contrôle du formulaire
	if (empty($_POST['pseudo']) || empty($_POST['mdp'])) // empty vérifie si c'est 0, NULL, FALSE, '' ou non défini donc ça suffit
		$messageConnexion .= '<div class="alert alert-danger">Les identifiants sont obligatoires</div>';

	// S'il n'y a pas d'erreur sur le formulaire, on vérifie l'existence du couple pseudo, mdp dans la base
	if (empty ($messageConnexion)) // donc pas d'erreur
		{
		$resultat = executerRequete ("SELECT * FROM membre WHERE pseudo = :pseudo", array (':pseudo' => $_POST['pseudo']));
		if ($resultat->rowCount() == 1) // donc le pseudo existe : vérifier le mot de passe
			{
			$membre = $resultat->fetch (PDO::FETCH_ASSOC);
			if (password_verify($_POST['mdp'], $membre['mdp'])) // On est obligé d'utiliser password_verify() : password_hash() retourne une clé différente à chaque appel (salage)
				{
				$_SESSION = array();
				$_SESSION['membre'] = $membre; // on met l'array $membre dans la session
				$_SESSION['tri'] = 'date_enregistrement';
				$contenu .= '<script>window.location.href = "'.($pageCourante??'fiche_annonce.php?id=40').'"</script>';
				}
			else // mauvais mot de passe
				{
				$messageConnexion .= '<div class="alert alert-danger">Erreur sur les identifiants</div>';
				}
			}
		else // le pseudo n'existe pas dans la base
			{
			$messageConnexion .= '<div class="alert alert-danger">Erreur sur les identifiants</div>';
			}
		} // fin if (empty ($messageConnexion))
	} // fin du if ($_POST)


// Fenêtre modale de connexion
if (empty($messageConnexion))
	$contenu .= '<div class="modal fade" id="modaleConnexion" tabindex="-1" role="dialog" aria-labelledby="modaleConnexionTitle" aria-hidden="true">';
else
	$contenu .= '<div class="modal" id="modaleConnexion" tabindex="-1" role="dialog" aria-labelledby="modaleConnexionTitle" aria-hidden="true">';
$contenu .=   '<div class="modal-dialog modal-dialog-centered modal" role="document">';
$contenu .=     '<div class="modal-content">';
$contenu .=       '<div class="modal-header">';
$contenu .=         '<h5 class="modal-title" id="modaleConnexionTitle">Connexion</h5>';
$contenu .=         '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
$contenu .=           '<span aria-hidden="true">&times;</span>';
$contenu .=         '</button>';
$contenu .=       '</div>';
$contenu .=       '<div class="modal-body">';

if (!empty($messageConnexion))
	{
	$contenu .=       '<div class="row">';
	$contenu .=         '<div class="col-sm-12" style="top-margin:0;bottom-margin:0">';
	$contenu .=           $messageConnexion;
	$contenu .=         '</div>';
	$contenu .=       '</div>';
	}

$contenu .=         '<form method="post" action="#">';
$contenu .=             '<div class="form-group">';
$contenu .=               '<label for="pseudo_connexion" class="col-form-label">Pseudo :</label>';
$contenu .=               '<input type="text" class="form-control" id="pseudo_connexion" name="pseudo">';
$contenu .=             '</div>';
$contenu .=             '<div class="form-group">';
$contenu .=               '<label for="mdp_connexion" class="col-form-label">Mot de passe :</label>';
$contenu .=               '<input type="password" class="form-control" id="mdp_connexion" name="mdp">';
$contenu .=             '</div>';
$contenu .=           '<div class="row">';
$contenu .=             '<div class="col-sm-4">';
$contenu .=               '<button type="submit" class="btn btn-primary">Me connecter</button>';
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

