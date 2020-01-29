<?php
require_once 'inc/init.php';

if (!estConnecte())
	{
	header ('location:'.RACINE_SITE.'connexion.php');
	exit ();
	}
c'est pas fini !
debug ($_POST);

// Traitement du formulaire
if (!empty($_POST))
	{
	if (isset ($_POST['note']))
		{
		// L'internaute a envoyé le formulaire : enregistrer le nouveau commentaire
		extract ($_POST);
		if (empty($commentaire))
			$contenu .= '<div class="alert alert-warning">Il faut saisir un formulaire avant de valider.</div>';
		else
			$resultat = executerRequete ("INSERT INTO commentaire
			                              VALUES (0, :commentaire,                 :membre_id,                              :annonce_id,    NOW())",
			                              array (   ':commentaire'=>$commentaire, ':membre_id'=>$_SESSION['membre']['id'], ':annonce_id'=>$id_annonce));
		if (empty ($contenu) && $resultat)
			{
			header ('location:'.RACINE_SITE.'fiche_annonce.php?id='.$id_annonce);
			exit ();
			}
		else
			$contenu .= '<div class="alert alert-danger">Erreur lors de l\'ajout du commentaire.</div>';
		}
	else
		{
		// On débarque tout juste : selectionner les avis déjà émis sur ce membre
		foreach ($_POST as $pseudo => $vide);
		$requete = executerRequete ("SELECT avis, note, m1.pseudo auteur
		                             FROM commentaire, membre m1, membre m2
		                             WHERE membre_id1=m1.id AND membre_id2=m2.id AND m2.pseudo=:pseudo
		                             ORDER BY date DESC", array (':pseudo' => $pseudo));
		$listeAvis = $requete->fetchAll (PDO::FETCH_ASSOC);
		$contenu .= '<h4>Avis déjà formulés sur '.$pseudo.' :</h4>';
		$nombreAvis = $requete->rowCount();
		for ($i=0; $i<$nombreAvis; $i++)
			{
			extract ($listeAvis[$i]);
			$contenu .= "<h5>de $pseudo, le $date</h5>";
			$contenu .= "<p>note : $note</p>";
			$contenu .= "<p>$avis</p>";
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

