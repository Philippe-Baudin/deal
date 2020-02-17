<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// fiche_annonces.php
// affiche le détail d'une annonce de façon relativement esthétique,
// avec des liens pour contacter l'auteur et ajouter commentaires ou avis
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='';
require_once 'inc/init.php';
$pageCourante = 'fiche_annonce.php';

// Impossible d'afficher cette page sans id d'annonce : retour à l'accueil
if (!isset($_GET['id']))
	{
	header ('location:'.RACINE_SITE.'index.php');
	exit ();
	}

// Aller chercher l'annonce dans la base
$resultat = executerRequete ("SELECT annonce.id id,
                                     annonce.titre titre,
                                     description_longue description,
                                     prix,
                                     photo,
                                     pays,
                                     ville,
                                     adresse,
                                     code_postal,
                                     membre.id auteur,
                                     civilite,
                                     pseudo,
                                     email,
                                     categorie.titre categorie,
                                     categorie.id id_categorie,
                                     DATE_FORMAT(annonce.date_enregistrement,'%d/%m/%Y') date
                              FROM annonce
                              RIGHT JOIN membre ON membre_id = membre.id
                              RIGHT JOIN categorie ON categorie_id = categorie.id
                              WHERE annonce.id = :id", array(':id'=>$_GET['id']));
// Si l'annonce n'existe pas, retour vers l'accueil
if (!$resultat || $resultat->rowCount() == 0)
	{
	header ('location:'.RACINE_SITE.'index.php');
	exit ();
	}
extract($resultat->fetch(PDO::FETCH_ASSOC));


// Envoyer un email à l'auteur d'une annonce
if (!empty($_POST) && !isset($_POST['OK']) && estConnecte())
	{
	if (!isset ($_POST['message']))
		$contenu .= '<div class="alert alert-success">Erreur lors de l\'envoi du message.</div>';
	else if (strlen($_POST['message']) < 4)
		$contenu .= '<div class="alert alert-success">Message trop court.</div>';
	if (empty ($contenu))
		{
		$resultat = executerRequete ("SELECT pseudo, email FROM membre WHERE id=:auteur", array (':auteur' => $_POST['auteur']));
		if ($resultat->rowCount() != 1)
			$contenu .= '<div class="alert alert-success">Erreur lors de l\'envoi du message.</div>';
		else
			{
			extract($resultat->fetch(PDO::FETCH_ASSOC));
			$to = $email;
			$subject = 'Message de '.$_SESSION['membre']['pseudo'];
			if (!mail ($to, $subject, str_replace ("\n.", "\n..", $_POST['message'])))
				$contenu .= '<div class="alert alert-success">Erreur lors de l\'envoi du message.</div>';
			}
		}
	}


// Suggestion d'autres annonces
/*
- de même catégorie
(- avec la meilleure coïncidence de mots)
- aléatoires
*/
define('NOMBRE_MAXI_SUGGESTIONS', 4);
$resultat = executerRequete ("SELECT * FROM annonce WHERE categorie_id = :categorie_id AND id != :id ORDER BY RAND() LIMIT ".NOMBRE_MAXI_SUGGESTIONS,
                            array ('categorie_id' => $id_categorie, ':id' => $id));
$nombreSuggestions = $resultat->rowCount();
if ($nombreSuggestions == 0)
	{
	$resultat = executerRequete ("SELECT * FROM annonce WHERE id != :id ORDER BY RAND() LIMIT ".NOMBRE_MAXI_SUGGESTIONS, array (':id' => $id));
	$nombreSuggestions = $resultat->rowCount();
	}
if ($nombreSuggestions > 0)
	{
	$suggestion = $resultat->fetchAll (PDO::FETCH_ASSOC);
	}


// ----------------------
// Affichage de l'annonce
// ----------------------
$contenu .= '<br>';

// Titre
$contenu .= '<div class="row justify-content-between">';
$contenu .=     '<div class="col-md-auto">';
$contenu .=         '<h2>'.$titre.'</h2>';
$contenu .=     '</div>';
$contenu .=     '<div class="col-md-2 connected">';
$contenu .=         '<div class="row justify-content-md-center">';
$contenu .=             '<button type="button" class="btn btn-primary connected" data-toggle="modal" data-target="#modaleContact">';
$contenu .=                ' Contacter ' .$pseudo;
$contenu .=             '</button>';
$contenu .=         '</div>';
$contenu .=     '</div>';
$contenu .= '</div>';

// Photo et description
$contenu .= '<div class="row">';
if (!empty($photo))
	{
	$contenu .= '<div class="col-md-4">';
	$contenu .=     '<img src='.$photo.' alt="image '.$titre.'" title="cliquez pour zoomer" class="zoomable" style="max-width:100%; max-height:300px">';
	$contenu .= '</div>';
	}
$contenu .=     '<div class="col-md">';
$contenu .=         '<h3>Description :</h3>';
$contenu .=         '<p>'.str_replace(array("\r\n", "\n", "\r"), '<br>', $description).'</p>';
$contenu .=     '</div>';
$contenu .= '</div>';

// Infos sur l'auteur de l'annonce
$contenu .= '<div class="row">';
$contenu .=     '<div class="col-md-3">';
$contenu .=         '<p>Date de publication : '.$date.'</p>';
$contenu .=     '</div>';
$contenu .=     '<div class="col-md-3">';
$contenu .=         '<p>';
$contenu .=             'auteur : ';
$contenu .=             '<a class="admin" href="admin/gestion_membres.php?modification='.$auteur.'#formulaire">'.$pseudo.' </a>';
$contenu .=             '<span class="pas-admin">'.$pseudo.' </span>';
$contenu .=             '<span id="affichage-note"></span>';
$contenu .=         '</p>';
$contenu .=     '</div>';
$contenu .=     '<div class="col-md-2">';
$contenu .=         '<p>prix : '.sprintf("%.02f €",$prix).'</p>';
$contenu .=     '</div>';
$contenu .=     '<div class="col-md">';
$contenu .=         '<p>adresse : '.$adresse.' '.$code_postal.' '.$ville.'</p>';
$contenu .=     '</div>';
$contenu .= '</div>';
$contenu .= '<hr>';

// Commentaires
$contenu .= '<div class="col-md" id="affichage-commentaires">';
$contenu .= '</div>';
$contenu .= '<hr>';

// Suggestion d'autres annonces
$contenu .= '<div class="row">';
$contenu .=      '<h5>Autres annonces pouvant vous intéresser :</h5>';
$contenu .= '</div>';
$contenu .= '<div class="row">';
for ($i=0; $i<$nombreSuggestions; $i++)
	{
	$contenu .= '<div class="col-md-2">';
	$contenu .=     '<a href="?id='.$suggestion[$i]['id'].'"><h6>'.$suggestion[$i]['titre'].'</h6></a>';
	if (empty ($suggestion[$i]['photo']))
		$contenu .= '<p>'.$suggestion[$i]['description_courte'].'</p>';
	else
		$contenu .= '<a href="?id='.$suggestion[$i]['id'].'"><img src="'.$suggestion[$i]['photo'].'" alt="image '.$suggestion[$i]['titre'].'" title="'.$suggestion[$i]['description_courte'].'" style="max-width:90%; max-height:200px"></a>';
	$contenu .= '</div>';
	}

// Liens permettant d'enregistrer un commentaire ou un avis
$contenu .= '</div>';
$contenu .= '<hr>';
$contenu .= '<div class="row justify-content-between">';
$contenu .= '<div class="col-md-auto">';
$contenu .=     '<span class="connected">Déposer <a data-toggle="modal" href="#modaleCommentaire">un commentaire sur l\'annonce</a> ou <a data-toggle="modal" href="#modaleAvis">un avis sur '.$pseudo.'</a></span>';
$contenu .=     '<span class="unconnected">Connectez-vous pour déposer <a data-toggle="modal" href="#modaleConnexion">un commentaire sur l\'annonce</a> ou <a data-toggle="modal" href="#modaleConnexion">un avis sur '.$pseudo.'</a></span>';
$contenu .=     '</div>';

// Lien permettant à un admin de modifier l'annonce
$contenu .=     '<div class="col-md-2">';
$contenu .=     '<a class="admin" href="gestion_annonces.php?modification='.$id.'#formulaire">Modifier</a>';
$contenu .=     '</div>';

// Lien de retour à la liste des annonces : soit l'accueil, soit la page de recherche, soit la page back-end de gestion des annonces
$contenu .=     '<div class="col-md-3">';
$contenu .=         '<script>';
$contenu .=             'function revenir()';
$contenu .=                 '{';
$contenu .=                 'index = document.referrer.indexOf("?");';
$contenu .=                 'if (index == -1) index = 0;';
$contenu .=                 'if(document.referrer.substr(index-19,19)=="/deal/recherche.php")';
$contenu .=                     '{';
$contenu .=                     'window.location.href="recherche.php";';
$contenu .=                     '}';
if (estConnecte())
	{
	$contenu .=             'else if (document.referrer.substr(index-16,16)=="/deal/profil.php")';
	$contenu .=                 '{';
	$contenu .=                 'window.location.href="profil.php";';
	$contenu .=                 '}';
	if (estAdmin())
		{
		$contenu .=         'else if (document.referrer.substr(index-26,26)=="/deal/gestion_annonces.php")';
		$contenu .=             '{';
		$contenu .=             'window.location.href="gestion_annonces.php";';
		$contenu .=             '}';
		}
	}
$contenu .=                 'else';
$contenu .=                     '{';
$contenu .=                     'window.location.href="index.php";';
$contenu .=                     '}';
$contenu .=                 'return false;';
$contenu .=                 '}';
$contenu .=         '</script>';
$contenu .=         '<a href="#" onclick="revenir()">Retour vers les annonces</a>';
$contenu .=     '</div>';
$contenu .= '</div>';

require_once 'inc/header.php';
require_once 'connexion_modale.php';

?>
<!-- script d'ajout de commentaire et d'avis -->
<script>
	function envoyerCommentaire(id, commentaire)
		{
		if (typeof commentaire === "undefined")
			commande = {id:id};
		else
			{
			commentaire = $("#commentaire").val();
			commande = {commentaire:commentaire, id:id};
			}
		function reponse (retour)
			{
			if (retour.substr (0,17) == '<div class="alert')
				{
				$("#message-erreur-commentaire").html (retour);
				}
			else
				{
				$("#affichage-commentaires").html(retour);
				$("#message-erreur-commentaire").html ('');
				$("#modaleCommentaire").modal('hide');
				}
			}
		$.post("nouveau_commentaire.php", commande, reponse,"html");
		$("#commentaire").val("");
		}
	envoyerCommentaire (<?php echo $id ?>);

	function envoyerAvis(id, avis) {
		if (typeof avis === "undefined")
			commande = {id:id};
		else
			{
			avis = $("#avis").val();
			note = $("#note").val();
			commande = {avis:avis, note:note, id:id};
			}
		$.post("nouvel_avis.php", commande,	function(retour){
			if (retour.substr (0,17) == '<div class="alert')
				{
				$("#message-erreur-avis").html (retour);
				}
			else
				{
				$("#message-erreur-avis").html ('');
				$("#affichage-note").html(retour);
				$("#modaleAvis").modal('hide');
				}
			},"html");
		$("#avis").val("");
		$("#note").val(5)
		}
	envoyerAvis (<?php echo $auteur ?>);
</script>
<div class="modal fade" id="modaleCommentaire" tabindex="-1" role="dialog" aria-labelledby="modaleCommentaireTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modaleCommentaireTitle">Déposer un commentaire</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#message-erreur-commentaire').html ('')">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div id="message-erreur-commentaire"></div>
				<div class="form-group">
					<label for="commentaire" class="col-form-label">Postez un commentaire pour poser une question ou obtenir des précisions sur le produit ou le service proposé :</label>
					<textarea class="form-control" id="commentaire" name="commentaire" rows="5"></textarea>
				</div>
				<div class="row">
					<div class="col-md-2">
						<button type="button" class="btn btn-primary" onclick="envoyerCommentaire(<?php echo $id ?>,1);">Envoyer</button>
					</div>
					<div class="col-md-2">
						<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#message-erreur-commentaire').html ('')">Annuler</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
if (isset ($_SESSION['membre']) && $pseudo == $_SESSION['membre']['pseudo'])
	{
	// Fenêtre modale pour refuser qu'un membre se note lui-même
	?>
	<div class="modal fade" id="modaleAvis" tabindex="-1" role="dialog" aria-labelledby="modaleAvisTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modaleAvisTitle">Vous ne pouvez pas vous noter vous-même !</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
				</div>
			</div>
		</div>
	</div>
	<?php
	}
else
	{
	// Fenêtre modale pour enregistrer un avis sur l'auteur de l'annonce
	?>
	<div class="modal fade" id="modaleAvis" tabindex="-1" role="dialog" aria-labelledby="modaleAvisTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modaleAvisTitle">Donnez votre avis sur <?php echo $pseudo.' et notez l'.(($civilite=='M.')?'e':'a') ?> :</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#message-erreur-avis').html ('')">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="message-erreur-avis"></div>
					<input type="hidden" name="id" value="'.$id.'">
					<input type="hidden" name="auteur" value="'.$auteur.'">
					<div class="form-group">
						<label for="avis" class="col-form-label">Votre avis :</label>
						<textarea class="form-control" id="avis" name="avis" rows="5"></textarea>
					</div>
					<div class="input-group mb-3">
						<div class="input-group-prepend">
							<label class="input-group-text" for="note">Note</label>
						</div>
						<select id="note" name="note">
							<option value="0">0 &star;&star;&star;&star;&star;</option>
							<option value="1">1 &starf;&star;&star;&star;&star;</option>
							<option value="2">2 &starf;&starf;&star;&star;&star;</option>
							<option value="3">3 &starf;&starf;&starf;&star;&star;</option>
							<option value="4">4 &starf;&starf;&starf;&starf;&star;</option>
							<option value="5" selected>5 &starf;&starf;&starf;&starf;&starf;</option>
						</select>
					</div>
					<div class="row">
						<div class="col-md-2">
							<button type="button" class="btn btn-primary" onclick="envoyerAvis(<?php echo $auteur?>,1)">Envoyer</button>
						</div>
						<div class="col-md">
							<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#message-erreur-avis').html ('')">Annuler</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	}
// Fenêtre modale pour contacter l'auteur de l'annonce
?>		
<div class="modal fade" id="modaleContact" tabindex="-1" role="dialog" aria-labelledby="modaleContactTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modaleContactTitle">Contacter <?php echo $pseudo.' ('.$email ?>)</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="post" action="#">
					<input type="hidden" name="id" value="<?php echo $id?>">
					<input type="hidden" name="auteur" value="<?php echo $auteur?>">
					<div class="form-group">
						<label for="message" class="col-form-label">Votre message :</label>
						<textarea class="form-control" id="message" name="message" rows="5"></textarea>
					</div>
					<div class="row">
						<div class="col-md-2">
							<button type="submit" class="btn btn-primary">Envoyer</button>
						</div>
						<div class="col-md">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php	

echo $contenu;

?>

<!-- script de zoom des images -->
<script src="js/zoom.js"></script>

<?php
require_once 'inc/footer.php';
?>

