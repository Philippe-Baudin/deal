<?php
require_once 'inc/init.php';
$message = '';


/*
// 3. Internaute déjà connecté : on le redirige vers son profil
if (estConnecte())
	{
	header ('location:profil.php'); // on redirige vers le profil
	exit (); // et on quitte le script
	}
*/

// 1. Traitement du formulaire de connexion
if ($_POST && isset ($_POST['pseudo']) && isset ($_POST['mdp'])) // si le formulaire a été envoyé
	{
	// Contrôle du formulaire
	if (empty($_POST['pseudo']) || empty($_POST['mdp'])) // empty vérifie si c'est 0, NULL, FALSE, '' ou non défini donc ça suffit
		$message .= '<div class="alert alert-danger">Les identifiants sont obligatoires</div>';

	// S'il n'y a pas d'erreur sur le formulaire, on vérifie l'existence du couple pseudo, mdp dans la base
	if (empty ($message)) // donc pas d'erreur
		{
		$resultat = executerRequete ("SELECT * FROM membre WHERE pseudo = :pseudo", array (':pseudo' => $_POST['pseudo']));
		if ($resultat->rowCount() == 1) // donc le pseudo existe : vérifier le mot de passe
			{
			$membre = $resultat->fetch (PDO::FETCH_ASSOC);
			if (password_verify($_POST['mdp'], $membre['mdp'])) // On est obligé d'utiliser password_verify() : password_hash() retourne une clé différente à chaque appel (salage)
				{
				$_SESSION['membre'] = $membre; // on met l'array $membre dans la session
				$_SESSION['tri'] = 'date_enregistrement';
				header ('location:'); // pseudo et mdp étant corrects, on redirige l'internaute vers la page profil
				}
			else // mauvais mot de passe
				{
				$message .= '<div class="alert alert-danger">Erreur sur les identifiants</div>';
				}
			}
		else // le pseudo n'existe pas dans la base
			{
			$message .= '<div class="alert alert-danger">Erreur sur les identifiants</div>';
			}
		} // fin if (empty ($message))
	} // fin du if ($_POST)


// Fenêtre modale de connexion
$contenu .= '<div class="modal fade" id="modaleConnexion" tabindex="-1" role="dialog" aria-labelledby="modaleConnexionTitle" aria-hidden="true">';
$contenu .=   '<div class="modal-dialog modal-dialog-centered modal" role="document">';
$contenu .=     '<div class="modal-content">';
$contenu .=       '<div class="modal-header">';
$contenu .=         '<h5 class="modal-title" id="exampleModalLongTitle">Connexion</h5>';
$contenu .=         '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
$contenu .=           '<span aria-hidden="true">&times;</span>';
$contenu .=         '</button>';
$contenu .=       '</div>';
$contenu .=       '<div class="modal-body">';
$contenu .=         '<form method="post" action="">';
$contenu .=           '<div class="form-group">';
$contenu .=             '<label for="pseudo" class="col-form-label">Pseudo :</label>';
$contenu .=             '<input type="text" class="form-control" id="pseudo" name="pseudo"></textarea>';
$contenu .=           '</div>';
$contenu .=           '<div class="form-group">';
$contenu .=             '<label for="mdp" class="col-form-label">Mot de passe :</label>';
$contenu .=             '<input type="password" class="form-control" id="mdp" name=mdp>';
$contenu .=           '</div>';
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


/*





















require_once 'inc/header.php';
?>
<h1 class="mt-4">Connexion</h1>
<?php
echo $message; // pour la deconnexion
echo $contenu; // pour la connexion
?>

<form method="post" action="">
	<div>
		<input type="hidden" name="connexion" value="connexion">
	</div>
	<div>
		<div><label for="pseudo">Pseudo</label></div>
		<div><input type="text" name="pseudo" id="pseudo"></div>
	</div>
	<div>
		<div><label for="mdp">Mot de passe</label></div>
		<div><input type="password" name="mdp" id="mdp"></div>
	</div>
	<div class="mt-2"><input type="submit" value="Se connecter"></div>
</form>
<?php
require_once 'inc/footer.php';


*/