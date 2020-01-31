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

// Enregistrement d'un commentaire
if (!empty($_POST))
	{
	if (isset($_POST['commentaire']))
		{
		if (strlen($_POST['commentaire']) >= 3)
			executerRequete ("INSERT INTO commentaire (commentaire, membre_id, annonce_id, date_enregistrement) VALUES (:commentaire, :membre_id, :annonce_id, NOW())",
			                 array (':commentaire' => $_POST['commentaire'], ':membre_id' => $_SESSION['membre']['id'], ':annonce_id' => $_POST['id']));
		}
	else if (isset($_POST['avis']))
		{
		if (strlen($_POST['avis']) >= 3)
			executerRequete ("INSERT INTO note (avis, note, membre_id1, membre_id2, date_enregistrement) VALUES (:avis, :note, :membre_id1, :membre_id2, NOW())",
			                 array (':avis' => $_POST['avis'], ':note' => $_POST['note'], ':membre_id1' => $_SESSION['membre']['id'], ':membre_id2' => $_POST['auteur']));
		}
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

// Calculer la moyenne des notes de l'auteur de l'annonce
if (!empty($auteur))
	{
	$requete = executerRequete ("SELECT count(*) decompte, AVG(note) note FROM note WHERE membre_id2=:auteur", array(':auteur'=>$auteur));
	$resultat = $requete->fetch (PDO::FETCH_ASSOC);
	if ($resultat['decompte']>0)
		$note = $resultat['note']*1;
	}

// Aller chercher les commentaires sur cette annonce
if (!empty($auteur))
	{
	$requete = executerRequete ("SELECT commentaire,
	                                    pseudo auteur_commentaire,
	                                    DATE_FORMAT(commentaire.date_enregistrement,'%d/%m/%Y') date
	                             FROM commentaire, membre
	                             WHERE membre.id = membre_id AND annonce_id = :id
	                             ORDER BY commentaire.date_enregistrement DESC", array(':id'=>$id));
	if ($requete->rowCount() > 0)
		$commentaires = $requete->fetchAll (PDO::FETCH_ASSOC);
	}

// Suggestion d'autres annonces
/*
- de même catégorie
(- avec la meilleure coïncidence de mots)
- aléatoires
*/
$requete = executerRequete ("SELECT * FROM annonce WHERE categorie_id = :categorie_id AND id != :id ORDER BY RAND() LIMIT 4",
                            array ('categorie_id' => $id_categorie, ':id' => $id));
$nombreSuggestions = $requete->rowCount();
if ($nombreSuggestions == 0)
	{
	$requete = executerRequete ("SELECT * FROM annonce WHERE id != :id ORDER BY RAND() LIMIT 4", array (':id' => $id));
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
	$contenu .=     '<img src='.$photo.' style="max-width:100%">';
	$contenu .= '</div>';
	}
$contenu .=     '<div class="col-sm">';
$contenu .=         '<h3>Description :</h3>';
$contenu .=         '<p>'.str_replace(array("\r\n", "\n", "\r"), '<br />', $description).'</p>';
$contenu .=     '</div>';
$contenu .= '</div>';

// Infos sur l'auteur de l'annonce
$contenu .= '<div class="row">';
$contenu .=     '<div class="col-sm-3">';
$contenu .=         '<p>Date de publication : '.$date.'</p>';
$contenu .=     '</div>';
$contenu .=     '<div class="col-sm-3">';
$contenu .=         '<p>';
$contenu .=             'auteur : '.$pseudo;
if (isset ($note))
	$contenu .= ' '.noteEnEtoiles ($note);
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
$contenu .= '<div class="col-sm">';
if (isset ($commentaires))
	{
	$contenu .= '<div class="row">';
	$contenu .= '<p>Commentaires sur cette annonce :</p>';
	$contenu .= '</div>';
	foreach ($commentaires as $commentaire)
		{
		$contenu .= '<div class="row">';
		$contenu .=     '<div class="col-sm-3">';
		$contenu .=         '</p>de '.$commentaire['auteur_commentaire'].', le '.$commentaire['date'].'</p>';
		$contenu .=     '</div>';
		$contenu .=     '<div class="col-sm">';
		$contenu .=         '</p>'.$commentaire['commentaire'].'</p>';
		$contenu .=     '</div>';
		$contenu .= '</div>';
		}
	}
else
	{
	$contenu .= '<p>Aucun commentaire sur cette annonce</p>';
	}
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
	$contenu .= '<a href=?id='.$suggestion[$i]['id'].'><h6>'.$suggestion[$i]['titre'].'</h6></a>';
	if (empty ($suggestion[$i]['photo']))
		$contenu .= '<p>'.$suggestion[$i]['description_courte'].'</p>';
	else
		$contenu .= '<a href=?id='.$suggestion[$i]['id'].'><img src="'.$suggestion[$i]['photo'].'" style="max-width:90%"></a>';
	$contenu .= '</div>';
	}

// Lien permettant d'enregistrer un commentaire ou un avis
$contenu .= '</div>';
$contenu .= '<hr>';
$contenu .= '<div class="row">';
$contenu .= '<div class="col-sm-9">';
if (estConnecte())
	$contenu .= 'Deposer <a data-toggle="modal" href="#modaleCommentaire">un commentaire sur l\'annonce</a> ou <a data-toggle="modal" href="#modaleAvis">un avis sur '.$pseudo.'</a>';
else
	$contenu .= 'Deposer <a data-toggle="modal" href="#modaleConnexion">un commentaire sur l\'annonce</a> ou <a data-toggle="modal" href="#modaleConnexion">un avis sur '.$pseudo.'</a>';
$contenu .= '</div>';
$contenu .= '<div class="col-sm">';
$contenu .= '<a href="index.php">Retour vers les annonces</a>';
$contenu .= '</div>';
$contenu .= '</div>';

// Définition des fenêtres modales que seu un membre connecté peut activer
if (estConnecte())
  {
  // Fenetre modale pour enregistrer un  commentaire sur une annonce
  $contenu .= '<div class="modal fade" id="modaleCommentaire" tabindex="-1" role="dialog" aria-labelledby="modaleCommentaireTitle" aria-hidden="true">';
  $contenu .=   '<div class="modal-dialog modal-dialog-centered modal-lg" role="document">';
  $contenu .=     '<div class="modal-content">';
  $contenu .=       '<div class="modal-header">';
  $contenu .=         '<h5 class="modal-title" id="exampleModalLongTitle">Déposer un commentaire</h5>';
  $contenu .=         '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
  $contenu .=           '<span aria-hidden="true">&times;</span>';
  $contenu .=         '</button>';
  $contenu .=       '</div>';
  $contenu .=       '<div class="modal-body">';
  $contenu .=         '<form method="post" action="">';
  $contenu .=           '<input type="hidden" name="id" value="'.$id.'">';
  $contenu .=           '<div class="form-group">';
  $contenu .=             '<label for="commentaire" class="col-form-label">Postez un commentaire pour poser une question ou obtenir des précisions sur le produit ou le service proposé :</label>';
  $contenu .=             '<textarea class="form-control" id="commentaire" name="commentaire" rows="5"></textarea>';
  $contenu .=           '</div>';
  $contenu .=           '<div class="row">';
  $contenu .=             '<div class="col-sm-2">';
  $contenu .=               '<button type="submit" class="btn btn-primary">Envoyer</button>';
  $contenu .=             '</div>';
  $contenu .=             '<div class="col-sm-2">';
  $contenu .=               '<button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>';
  $contenu .=             '</div>';
  $contenu .=           '</div>';
  $contenu .=         '</form>';
  $contenu .=       '</div>';
  $contenu .=     '</div>';
  $contenu .=   '</div>';
  $contenu .= '</div>';

  if ($pseudo == $_SESSION['membre']['pseudo'])
  	{
  	// Fenêtre modale pour refuser qu'un membre se note lui-même
  	$contenu .= '<div class="modal fade" id="modaleAvis" tabindex="-1" role="dialog" aria-labelledby="modaleAvisTitle" aria-hidden="true">';
  	$contenu .=   '<div class="modal-dialog modal-dialog-centered modal-lg" role="document">';
  	$contenu .=     '<div class="modal-content">';
  	$contenu .=       '<div class="modal-header">';
  	$contenu .=         '<h5 class="modal-title" id="exampleModalLongTitle">Vous ne pouvez pas vous noter vous-même !</h5>';
  	$contenu .=         '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
  	$contenu .=           '<span aria-hidden="true">&times;</span>';
  	$contenu .=         '</button>';
  	$contenu .=       '</div>';
  	$contenu .=       '<div class="modal-body">';
  	$contenu .=          '<button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>';
  	$contenu .=       '</div>';
  	$contenu .=     '</div>';
  	$contenu .=   '</div>';
  	$contenu .= '</div>';
  	}
  else
  	{
  	// Fenêtre modale pour enregistrer un avis sur l'auteur de l'annonce
  	$contenu .= '<div class="modal fade" id="modaleAvis" tabindex="-1" role="dialog" aria-labelledby="modaleAvisTitle" aria-hidden="true">';
  	$contenu .=   '<div class="modal-dialog modal-dialog-centered modal-lg" role="document">';
  	$contenu .=     '<div class="modal-content">';
  	$contenu .=       '<div class="modal-header">';
  	$contenu .=         '<h5 class="modal-title" id="exampleModalLongTitle">Donnez votre avis sur '.$pseudo.' et notez l'.(($civilite=='M.')?'e':'a').' :</h5>';
  	$contenu .=         '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
  	$contenu .=           '<span aria-hidden="true">&times;</span>';
  	$contenu .=         '</button>';
  	$contenu .=       '</div>';
  	$contenu .=       '<div class="modal-body">';
  	$contenu .=         '<form method="post" action="">';
  	$contenu .=           '<input type="hidden" name="id" value="'.$id.'">';
  	$contenu .=           '<input type="hidden" name="auteur" value="'.$auteur.'">';
  	$contenu .=           '<div class="form-group">';
  	$contenu .=             '<label for="avis" class="col-form-label">Votre avis :</label>';
  	$contenu .=             '<textarea class="form-control" id="avis" name="avis" rows="5"></textarea>';
  	$contenu .=           '</div>';
  	$contenu .=           '<div class="input-group mb-3">';
  	$contenu .=             '<div class="input-group-prepend">';
  	$contenu .=               '<label class="input-group-text" for="note">Note</label>';
  	$contenu .=             '</div>';
  	$contenu .=             '<select id="note" name="note">';
  	$contenu .=               '<option>0</option>';
  	$contenu .=               '<option>1</option>';
  	$contenu .=               '<option>2</option>';
  	$contenu .=               '<option>3</option>';
  	$contenu .=               '<option>4</option>';
  	$contenu .=               '<option selected>5</option>';
  	$contenu .=             '</select>';
  	$contenu .=           '</div>';
  	$contenu .=           '<div class="row">';
  	$contenu .=             '<div class="col-sm-2">';
  	$contenu .=               '<button type="submit" class="btn btn-primary">Envoyer</button><br><br>';
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
  	}

  // Fenêtre modale pour contacter l'auteur de l'annonce
  $contenu .= '<div class="modal fade" id="modaleContact" tabindex="-1" role="dialog" aria-labelledby="modaleContactTitle" aria-hidden="true">';
  $contenu .=   '<div class="modal-dialog modal-dialog-centered modal-lg" role="document">';
  $contenu .=     '<div class="modal-content">';
  $contenu .=       '<div class="modal-header">';
  $contenu .=         '<h5 class="modal-title" id="exampleModalLongTitle">contacter '.$pseudo.' ('.$email.')</h5>';
  $contenu .=         '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
  $contenu .=           '<span aria-hidden="true">&times;</span>';
  $contenu .=         '</button>';
  $contenu .=       '</div>';
  $contenu .=       '<div class="modal-body">';
  $contenu .=         '<form method="post" action="">';
  $contenu .=           '<input type="hidden" name="id" value="'.$id.'">';
  $contenu .=           '<input type="hidden" name="auteur" value="'.$auteur.'">';
  $contenu .=           '<div class="form-group">';
  $contenu .=             '<label for="message" class="col-form-label">Votre message :</label>';
  $contenu .=             '<textarea class="form-control" id="message" name="message" rows="5"></textarea>';
  $contenu .=           '</div>';
  $contenu .=           '<div class="row">';
  $contenu .=             '<div class="col-sm-2">';
  $contenu .=               '<button type="submit" class="btn btn-primary">Envoyer</button><br><br>';
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
  }

require_once 'inc/header.php';
require_once 'connexion_modale.php';


echo $contenu;

require_once 'inc/footer.php';

