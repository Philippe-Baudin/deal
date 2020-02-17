<?php
$repertoire='';
require_once 'inc/init.php';
// Traitement du formulaire de connexion
$message = '';
if ($_POST)
	{
	if (isset ($_POST['pseudo']) && isset ($_POST['mdp'])) // si le formulaire a été envoyé
		{
		// Contrôle du formulaire
		if (empty($_POST['pseudo']) || empty($_POST['mdp'])) // empty vérifie si c'est 0, NULL, FALSE, '' ou non défini donc ça suffit
			$message .=  '<div class="alert alert-danger">Les identifiants sont obligatoires</div>';
		else // Si le pseudo et le mdp son là, on vérifie l'existence du couple pseudo, mdp dans la base
			{
			$resultat = executerRequete ("SELECT * FROM membre WHERE pseudo = :pseudo", array (':pseudo' => $_POST['pseudo']));
			if ($resultat->rowCount() == 1) // donc le pseudo existe : vérifier le mot de passe
				{
				$membre = $resultat->fetch (PDO::FETCH_ASSOC);
				if (password_verify($_POST['mdp'], $membre['mdp'])) // On est obligé d'utiliser password_verify() : password_hash() retourne une clé différente à chaque appel (salage)
					{
					$_SESSION['membre'] = $membre; // on met l'array $membre dans la session
					$_SESSION['tri'] = 'date_enregistrement';
					}
				else // mauvais mot de passe
					{
					$message .=  '<div class="alert alert-danger">Erreur sur les identifiants</div>';
					}
				}
			else // le pseudo n'existe pas dans la base
				{
				$message .=  '<div class="alert alert-danger">Erreur sur les identifiants</div>';
				}
			} // fin else if (empty ($_POST['pseudo'])
		if (empty($message))
			{
			if ($membre['role'] == 'admin')
				$message = '+';
			else
				$message = '-';
			$message .= $_POST['pseudo'];
			}
		echo $message;
		} // fin if (isset ($_POST['pseudo']) ...
	elseif (isset($_POST['deconnexion']))
		unset ($_SESSION['membre']); // on retire l'array $membre de la session
	} // fin du if ($_POST)
