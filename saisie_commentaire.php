<?php
$repertoire='';
require_once 'inc/init.php';

if (!estConnecte())
	{
	header ('location:'.RACINE_SITE.'connexion.php');
	exit ();
	}

debug ($_POST);

// Traitement du formulaire
//debug ($_POST);
if (!empty($_POST))
	{
	if (isset ($_POST['commentaire']))
		{
		// L'internaute a envoyé le formulaire : enregistrer le nouveau commentaire
		extract ($_POST);
		if (empty($commentaire))
			{
			$contenu .= '<div class="alert alert-warning">Il faut saisir un formulaire avant de valider.</div>';
			}
		else
			$resultat = executerRequete ("INSERT INTO commentaire
			                              VALUES (0, :commentaire,                 :membre_id,                              :annonce_id,    NOW())",
			                              array (':commentaire'=>$commentaire, ':membre_id'=>$_SESSION['membre']['id'], ':annonce_id'=>$id_annonce));
		if (empty ($contenu) && $resultat)
			{
			header ('location:'.RACINE_SITE.'fiche_annonce.php?id='.$id_annonce);
			exit ();
			}
		else
			{
			$contenu .= '<div class="alert alert-danger">Erreur lors de l\'ajout du commentaire.</div>';

			}
		}
	else
		{
		// On débarque tout juste : selectionner les commentaires déjà saisis pour cette annonce
		foreach ($_POST as $id_annonce => $vide);
		$resultat = executerRequete ("SELECT commentaire, commentaire.date_enregistrement date, pseudo
		                              FROM commentaire, membre
		                              WHERE annonce_id=:id_annonce AND membre.id=membre_id
		                              ORDER BY date DESC", array (':id_annonce' => $id_annonce));
		$commentaires = $resultat->fetchAll (PDO::FETCH_ASSOC);
		$contenu .= '<h4>Commentaires précédents :</h4>';
		$nombreCommentaires = $resultat->rowCount();
		for ($i=0; $i<$nombreCommentaires; $i++)
			{
			extract ($commentaires[$i]);
			$contenu .= "<h5>de $pseudo, le $date</h5>";
			$contenu .= "<p>$commentaire</p>";
			$contenu .= "<hr>";
			}
		}
	 }

require_once 'inc/header.php';

echo $contenu; // pour afficher les messages

	?>
	<form method="post" action="">

		<input type="hidden" name="id_annonce" value="<?php echo $id_annonce ?>">
		<div>
			<div><label for="commentaire">Saisissez votre commentaire</label></div>
			<div><textarea style="width:100%;height:20vh" type="text" name="commentaire" id="commentaire"></textarea>
		</div>

		<div><input type="submit" value="Valider"></div>

	</form>
	<?php
require_once 'inc/footer.php';

