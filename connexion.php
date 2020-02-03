<?php
require_once 'inc/init.php';
$message = '';

// Deconnexion de l'internaute
//debug ($_GET);
if (isset ($_GET['action']) && $_GET['action'] == 'deconnexion')
	{
	unset ($_SESSION['membre']); // supprime la partie 'membre' de la session (sans toucher à un éventuel panier)
	$message .= '<div class="alert alert-info">Vous êtes déconnecté</div>';
	}

// Internaute déjà connecté : on le redirige vers son profil
if (estConnecte())
	{
	header ('location:profil.php'); // on redirige vers le profil
	exit (); // et on quitte le script
	}

// Traitement du formulaire de connexion
//debug ($_POST);
if ($_POST) // si le formulaire a été envoyé
	{
	// Contrôle du formulaire
	if (empty($_POST['pseudo']) || empty($_POST['mdp'])) // empty vérifie si c'est 0, NULL, FALSE, '' ou non défini donc ça suffit
		$contenu .= '<div class="alert alert-danger">Les identifiants sont obligatoires</div>';

	// S'il n'y a pas d'erreur sur le formulaire, on vérifie l'existence du couple pseudo, mdp dans la base
	if (empty ($contenu)) // donc pas d'erreur
		{
		$resultat = executerRequete ("SELECT * FROM membre WHERE pseudo = :pseudo", array (':pseudo' => $_POST['pseudo']));
		if ($resultat->rowCount() == 1) // donc le pseudo existe : vérifier le mot de passe
			{
			$membre = $resultat->fetch (PDO::FETCH_ASSOC);
			if (password_verify($_POST['mdp'], $membre['mdp'])) // On est obligé d'utiliser password_verify() : password_hash() retourne une clé différente à chaque appel (salage)
				{
				$_SESSION['membre'] = $membre; // on met l'array $membre dans la session
				$_SESSION['tri'] = 'date_enregistrement';
				header ('location:profil.php'); // pseudo et mdp étant corrects, on redirige l'internaute vers la page profil
				exit ();
				}
			else // mauvais mot de passe
				{
				$contenu .= '<div class="alert alert-danger">Erreur sur les identifiants</div>';
				$message = '';
				}
			}
		else // le pseudo n'existe pas dans la base
			{
			$contenu .= '<div class="alert alert-danger">Erreur sur les identifiants</div>';
			$message = '';
			}
		} // fin if (empty ($contenu))
	} // fin du if ($_POST)

require_once 'inc/header.php';
?>
<h1 class="mt-4">Connexion</h1>
<?php
echo $message; // pour la deconnexion
echo $contenu; // pour la connexion
?>

<form method="post" action="#">
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
