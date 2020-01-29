<?php
require_once 'inc/init.php';
$affiche_formulaire = true; // pour n'afficher le formulaire que si le membre n'est pas inscrit

// Traitement du formulaire
//debug ($_POST);
if (!empty($_POST)) // c'est donc qu'on a cliqué sur "S'inscrire" (on pourrait juste tester if($_POST))
	{
	$contenu = validerMembre ($_POST);

	// S'il n'y a pas d'erreur sur le formulaire, on vérifie l'unicité du pseudo avant d'inscrire le membre
	if (empty ($contenu)) // donc pas d'erreur
		{
		// Vérifier l'unicité du pseudo en BDD
		$membre = executerRequete ("SELECT * FROM membre WHERE pseudo = :pseudo", array (':pseudo' => $_POST['pseudo']));
		//var_dump ($membre);
		if ($membre->rowCount()) // On devrait d'abord tester si $membre est différent de false, mais ici on fait simple.
			{
			// La requête renvoie au moins une ligne : le pseudo existe déjà
			$contenu .= '<div class="alert alert-danger">Le pseudo '.$_POST['pseudo'].' n\'est pas disponible. Veuillez en choisir un autre.</div>';
			}
		else
			{
			// hasher le mot de passe
			$mdp = password_hash ($_POST['mdp'], PASSWORD_DEFAULT); // à l'heure actuelle algo bcrypt. PASSWORD_DEFAULT sert à dire qu'on prendra, à l'avenir l'algo à la mode.

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
				$contenu .= '<div class="success alert-success">Vous êtes inscrit ! <a href = "connexion.php">Cliquez ici pour vous connecter.</a>.</div>';
				$affiche_formulaire = false;
				}
			else
				{
				$contenu .= '<div class="alert alert-danger">Ooops !... Erreur lors de l\'enregistrement : veuillez réessayer plus tard ...</div>';
				}
			} // fin du if rowCount()
		} // fin du if (empty ($contenu))
	} // fin du if (!empty($_POST))


require_once 'inc/header.php';
?>
<h1 class="mt-4">Inscription</h1>
<?php
echo $contenu; // pour afficher les messages
if ($affiche_formulaire) : // syntaxe particulière aidant à mélanger php et html. l'accolade fermante est remplacée par un "endif;" le "else" serait écrit "else:"
	?>
	<form method="post" action="">

		<div>
			<div><label for="pseudo">Pseudo</label></div>
			<div><input type="text" name="pseudo" id="pseudo" value="<?php echo $_POST['pseudo']??''; ?>"></div>
		</div>

		<div>
			<div><label for="mdp">Mot de passe</label></div>
			<div><input type="password" name="mdp" id="mdp" value="<?php echo $_POST['mdp']??''; ?>"></div>
		</div>

		<div>
			<div><label for="civilite">Civilité</label></div>
			<select name="civilite">
				<option value="M." selected>M.</option>
				<option value="Mme"<?php if (isset($_POST['civilite']) && $_POST['civilite']=='Mme') echo 'selected'; ?>>Mme</option>
			</select>
		</div>

		<div>
			<div><label for="nom">Nom</label></div>
			<div><input type="text" name="nom" id="nom" value="<?php echo $_POST['nom']??''; ?>"></div>
		</div>

		<div>
			<div><label for="prenom">Prénom</label></div>
			<div><input type="text" name="prenom" id="prenom" value="<?php echo $_POST['prenom']??''; ?>"></div>
		</div>

		<div>
			<div><label for="email">email</label></div>
			<div><input type="text" name="email" id="email" value="<?php echo $_POST['email']??''; ?>"></div>
		</div>

		<div>
			<div><label for="ville">Telephone</label></div>
			<div><input type="text" name="telephone" id="telephone" value="<?php echo $_POST['telephone']??''; ?>"></div>
		</div>

		<div><input type="submit" value="S'inscrire"></div>

	</form>
	<?php
endif;
require_once 'inc/footer.php';

