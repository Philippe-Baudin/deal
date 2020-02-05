<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// fiche_annonces.php
// affiche le détail d'une annonce de façon relativement esthétique,
// avec des liens pour contacter l'auteur et ajouter commentaires ou avis
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once 'inc/init.php';
$ficheAnnonce = true;

// Impossible d'afficher cette page sans id d'annonce : retour à l'accueil
if (!isset($_GET['id']))
	{
	header ('location:'.RACINE_SITE.'index.php');
	exit ();
	}

// Aller chercher l'annonce dans la base
$requete = executerRequete ("SELECT annonce.id id,
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
if (!$requete || $requete->rowCount() == 0)
	{
	header ('location:'.RACINE_SITE.'index.php');
	exit ();
	}
extract($requete->fetch(PDO::FETCH_ASSOC));

// Suggestion d'autres annonces
/*
- de même catégorie
(- avec la meilleure coïncidence de mots)
- aléatoires
*/
define('NOMBRE_MAXI_SUGGESTIONS', 4);
$requete = executerRequete ("SELECT * FROM annonce WHERE categorie_id = :categorie_id AND id != :id ORDER BY RAND() LIMIT ".NOMBRE_MAXI_SUGGESTIONS,
                            array ('categorie_id' => $id_categorie, ':id' => $id));
$nombreSuggestions = $requete->rowCount();
if ($nombreSuggestions == 0)
	{
	$requete = executerRequete ("SELECT * FROM annonce WHERE id != :id ORDER BY RAND() LIMIT ".NOMBRE_MAXI_SUGGESTIONS, array (':id' => $id));
	$nombreSuggestions = $requete->rowCount();
	}
if ($nombreSuggestions > 0)
	{
	$suggestion = $requete->fetchAll (PDO::FETCH_ASSOC);
	}


// ----------------------
// Affichage de l'annonce
// ----------------------
$contenu .= '<br>';

// Titre
$contenu .= '<div class="row">';
$contenu .=     '<div class="col-sm-9">';
$contenu .=         '<h2>'.$titre.'</h2>';
$contenu .=     '</div>';
$contenu .=     '<div class="col-sm">';
$contenu .=         '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modaleContact">Contacter ' .$pseudo. '</button>';
$contenu .=     '</div>';
$contenu .= '</div>';

// Photo et description
$contenu .= '<div class="row">';
if (!empty($photo))
	{
	$contenu .= '<div class="col-sm-4">';
	$contenu .=     '<img src='.$photo.' alt="image '.$titre.'" title="cliquez pour zoomer" class="zoomable" style="max-width:100%; max-height:300px">';
	$contenu .= '</div>';
	}
$contenu .=     '<div class="col-sm">';
$contenu .=         '<h3>Description :</h3>';
$contenu .=         '<p>'.str_replace(array("\r\n", "\n", "\r"), '<br>', $description).'</p>';
$contenu .=     '</div>';
$contenu .= '</div>';

// Infos sur l'auteur de l'annonce
$contenu .= '<div class="row">';
$contenu .=     '<div class="col-sm-3">';
$contenu .=         '<p>Date de publication : '.$date.'</p>';
$contenu .=     '</div>';
$contenu .=     '<div class="col-sm-3">';
$contenu .=         '<p>auteur : '.$pseudo.' <span id="affichage-note"></span>';
$contenu .=         '</p>';
$contenu .=     '</div>';
$contenu .=     '<div class="col-sm-2">';
$contenu .=         '<p>prix : '.sprintf("%.02f €",$prix).'</p>';
$contenu .=     '</div>';
$contenu .=     '<div class="col-sm">';
$contenu .=         '<p>adresse : '.$adresse.' '.$code_postal.' '.$ville.'</p>';
$contenu .=     '</div>';
$contenu .= '</div>';
$contenu .= '<hr>';

// Commentaires
$contenu .= '<div class="col-sm" id="affichage-commentaires">';
$contenu .= '</div>';
$contenu .= '<hr>';

// Suggestion d'autres annonces
$contenu .= '<div class="row">';
$contenu .= '<h5>Autres annonces pouvant vous intéresser :</h5>';
$contenu .= '</div>';
$contenu .= '<div class="row">';
for ($i=0; $i<$nombreSuggestions; $i++)
	{
	$contenu .= '<div class="col-sm-2">';
	$contenu .= '<a href="?id='.$suggestion[$i]['id'].'"><h6>'.$suggestion[$i]['titre'].'</h6></a>';
	if (empty ($suggestion[$i]['photo']))
		$contenu .= '<p>'.$suggestion[$i]['description_courte'].'</p>';
	else
		$contenu .= '<a href="?id='.$suggestion[$i]['id'].'"><img src="'.$suggestion[$i]['photo'].'" alt="image '.$suggestion[$i]['titre'].'" style="max-width:90%; max-height:200px"></a>';
	$contenu .= '</div>';
	}

// Lien permettant d'enregistrer un commentaire ou un avis
$contenu .= '</div>';
$contenu .= '<hr>';
$contenu .= '<div class="row">';
if (estAdmin())
	$contenu .= '<div class="col-sm-6">';
else
	$contenu .= '<div class="col-sm-7">';
if (estConnecte())
	$contenu .= 'Deposer <a data-toggle="modal" href="#modaleCommentaire">un commentaire sur l\'annonce</a> ou <a data-toggle="modal" href="#modaleAvis">un avis sur '.$pseudo.'</a>';
else
	$contenu .= 'Deposer <a data-toggle="modal" href="#modaleConnexion">un commentaire sur l\'annonce</a> ou <a data-toggle="modal" href="#modaleConnexion">un avis sur '.$pseudo.'</a>';
$contenu .= '</div>';
$contenu .= '<div class="col-sm-2">';
if (estAdmin())
	$contenu .= '<a href="gestion_annonces.php?modification='.$id.'#formulaire">Modifier</a>';
$contenu .= '</div>';
$contenu .= '<div class="col-sm">';
$contenu .= '<a href="index.php">Retour vers les annonces</a>';
$contenu .= '</div>';
$contenu .= '</div>';
?>
<?php
// Définition des fenêtres modales que seul un membre connecté peut activer
if (estConnecte())
	{
	?>
	<div class="modal fade" id="modaleCommentaire" tabindex="-1" role="dialog" aria-labelledby="modaleCommentaireTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modaleCommentaireTitle">Déposer un commentaire</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="commentaire" class="col-form-label">Postez un commentaire pour poser une question ou obtenir des précisions sur le produit ou le service proposé :</label>
						<textarea class="form-control" id="commentaire" name="commentaire" rows="5"></textarea>
					</div>
					<div class="row">
						<div class="col-sm-2">
							<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="envoyerCommentaire(<?php echo $id ?>,1);">Envoyer</button>
						</div>
						<div class="col-sm-2">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	if ($pseudo == $_SESSION['membre']['pseudo'])
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
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
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
							<div class="col-sm-2">
								<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="envoyerAvis(<?php echo $auteur ?>,1);">Envoyer</button>
							</div>
							<div class="col-sm">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
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
					<h5 class="modal-title" id="modaleContactTitle">contacter <?php echo $pseudo.' ('.$email ?>)</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form method="post" action="#">
						<input type="hidden" name="id" value="'.$id.'">
						<input type="hidden" name="auteur" value="'.$auteur.'">
						<div class="form-group">
							<label for="message" class="col-form-label">Votre message :</label>
							<textarea class="form-control" id="message" name="message" rows="5"></textarea>
						</div>
						<div class="row">
							<div class="col-sm-2">
								<button type="submit" class="btn btn-primary">Envoyer</button><br><br>
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
	<?php	
	}

require_once 'inc/header.php';
require_once 'connexion_modale.php';

echo $contenu;

?>

<!-- script de zoom des images -->
<script src="js/zoom.js"></script>
<!-- script d'ajout d'un commentaire -->
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
			$("#affichage-commentaires").html(retour);
			}
		$.post("nouveau_commentaire.php", commande, reponse,"html");
		$("#commentaire").val("");
		}
	envoyerCommentaire (<?php echo $id ?>);
</script>
<!-- script d'ajout d'un avis -->
<script>
	function envoyerAvis(id, avis) {
		if (typeof avis === "undefined")
			commande = {id:id};
		else
			{
			avis = $("#avis").val();
			note = $("#note").val();
			commande = {avis:avis, note:note, id:id};
			}
		$.post("nouvel_avis.php", commande,	function(retour){console.log(retour);$("#affichage-note").html(retour)},"html");
		$("#avis").val("");
		$("#note").val(5)
		}
	envoyerAvis (<?php echo $auteur ?>);
</script>

<?php
require_once 'inc/footer.php';
?>

