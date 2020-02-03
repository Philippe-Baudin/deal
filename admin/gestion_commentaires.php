<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_commentaires.php
// affiche la liste des commentaires avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once '../inc/init.php';

$afficherFormulaire = false;
// Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers l'accueil
	header ('location:../index.php');
	exit ();
	}

// Modification d'un commentaire
if (!empty($_POST))
	{
	extract ($_POST);
	if (!isset ($commentaire) || strlen($commentaire) < 10 )
		$contenu .= '<div class="alert alert-danger">Le commentaire doit comprendre au moins 10 caractères.</div>';

	if (!isset ($pseudo) || strlen($pseudo) < 4 || strlen ($pseudo) > 100)
		$contenu .= '<div class="alert alert-danger">Le pseudo du membre doit être compris entre 4 et 20 caractères.</div>';
	else
		{
		$requete = executerRequete ("SELECT id FROM membre WHERE pseudo=:pseudo", array (':pseudo'=> $pseudo));
		if ($requete->rowCount() >= 1)
			$membre_id = $requete->fetch (PDO::FETCH_NUM)[0];
		else			
			$contenu .= '<div class="alert alert-danger">Le membre n\'existe pas.</div>';
		}

	if (!isset ($annonce) || !is_numeric($annonce))
		$contenu .= '<div class="alert alert-danger">L\'identifiant de l\'annonce doit être un nombre.</div>';
	else
		{
		$requete = executerRequete ("SELECT id FROM annonce WHERE id=:id", array (':id'=> $annonce));
		if ($requete->rowCount() < 1)
			$contenu .= '<div class="alert alert-danger">Il n\'y a pas d\'annonce d\'identifiant '.$annonce.'.</div>';
		}

	if (empty($contenu))
		{
		if (empty($date_enregistrement))
			{
			$requete = executerRequete ("REPLACE INTO commentaire VALUES (:id, :commentaire, :membre, :annonce, NOW())",
			                            array (':id' => $id, ':commentaire' => $commentaire, ':membre' => $membre_id, ':annonce' => $annonce));
			}
		else
			{
			$requete = executerRequete ("REPLACE INTO commentaire VALUES (:id, :commentaire, :membre, :annonce, :date_enregistrement)",
			                            array (':id' => $id, ':commentaire' => $commentaire, ':membre' => $membre_id, ':annonce' => $annonce, ':date_enregistrement' => $date_enregistrement));
			}
		}
	if (empty($contenu) && $requete)
		$contenu .= '<div class="alert alert-success">Le commentaire a été enregistrée.</div>';
	else
		{
		$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
		$afficherFormulaire = true;
		$commentaireCourant = $_POST;
		}
	}

// Suppression d'un commentaire
if (isset ($_GET['suppression'])) // Si on a 'suppression' dans l'URL c'est qu'on a cliqué sur "suppression" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("DELETE FROM commentaire WHERE id = :id", array (':id' => $_GET['suppression']));
	if ($resultat->rowCount() == 1)
		$contenu .= '<div class="alert alert-success">Le commentaire a bien été supprimé.</div>';
	else
		$contenu .= '<div class="alert alert-danger">Erreur lors de la suppression du commentaire.</div>';
	}
// Modification d'un commentaire
else if (isset ($_GET['modification'])) // Si on a 'modification' dans l'URL c'est qu'on a cliqué sur "modification" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("SELECT c.id id, commentaire, m.pseudo pseudo, annonce_id annonce, c.date_enregistrement date_enregistrement
	                              FROM commentaire c, membre m
	                              WHERE c.id = :id AND m.id = membre_id", array (':id' => $_GET['modification']));
	if ($resultat->rowCount() == 1)
		{
		$afficherFormulaire = true;
		$commentaireCourant = $resultat->fetch (PDO::FETCH_ASSOC);
		}
	else
		$contenu .= '<div class="alert alert-danger">Erreur interne.</div>';
	}

// Affichage du tableau des commentaires : 
$resultat = executerRequete ("SELECT c.id id, c.commentaire commentaire, m.pseudo pseudo, a.id annonce, c.date_enregistrement date_enregistrement
                              FROM commentaire c, membre m, annonce a
                              WHERE c.membre_id=m.id AND c.annonce_id=a.id");
$contenu .='<div class="table-responsive">';
$contenu .=   '<table class="table">';
$contenu .=      '<thead class="thead-dark">';
$contenu .=          '<tr>';
$contenu .=              '<th>Id</th>';
$contenu .=              '<th>Membre</th>';
$contenu .=              '<th>Annonce</th>';
$contenu .=              '<th>Commentaire</th>';
$contenu .=              '<th>Date</th>';
$contenu .=              '<th>Action</th>';
$contenu .=          '</tr>';
$contenu .=      '</thead>';
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) // pour chaque ligne retournée par la requête
	{
	extract ($ligne);
	$contenu .= '<tr>';
	$contenu .=     '<th scope="row">' . $id . '</th>';
	$contenu .=     '<td>' . $pseudo . '</td>';
	$contenu .=     '<td>' . $annonce . '</td>';
	$contenu .=     '<td>' . $commentaire . '</td>';
	$contenu .=     '<td>' . $date_enregistrement . '</td>';
	                    // Là, il y a un petit bout de javaScript fûté : quand on retourne false dans un onclick, ça bloque le lien. Na.
	$contenu .=     '<td>';
	$contenu .=         '<a href="?modification='.$ligne['id'].'#formulaire" class="lien-noir">'.MODIFIER.'</a>'."\n";
	$contenu .=         '<a href="?suppression='.$ligne['id'].'" onclick="return confirm(\'Etes-vous certain de vouloir supprimer ce commentaire ?\')" class="lien-noir">'.POUBELLE.'</a>';
	$contenu .=     '</td>';
	$contenu .= '</tr>';
	}
$contenu .=   '</table>';
$contenu .='</div>';
$id = 0;
$titre = '';
$mots_cles = '';


require_once '../inc/header.php';

// Navigation entre les pages d'administration
navigationAdmin ('Commentaires');

echo $contenu; // pour afficher notamment le tableau des commentaires
if ($afficherFormulaire)
	{
	isset ($commentaireCourant) && extract ($commentaireCourant);

	// Formulaire de création/modification des commentaires
?>
	<div class="cadre-formulaire">
		<form id="formulaire" method="post" action="gestion_commentaires.php">
			<input type="hidden" name="id" value="<?php echo $id??0 ?>"> <!-- hidden => éviter de le modifier par accident. value="0" => lors de l'insertion le SGBD utilisera l'auto-incrémentation -->
			<div class="form-row">
				<div class="form-group col-md-12">
					<label for="commentaire">Commentaire :</label>
					<textarea style="height:20vh" name="commentaire" id="commentaire" class="form-control"><?php echo $commentaire??'' ?></textarea>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-3">
					<label for="pseudo">Membre :</label>
					<input type="text" name="pseudo" id="pseudo" class="form-control" value="<?php echo $pseudo??'' ?>">
				</div>
				<div class="form-group col-md-3">
					<label for="annonce">Annonce :</label>
					<input type="text" name="annonce" id="annonce" class="form-control" value="<?php echo $annonce??'' ?>">
				</div>
				<div class="form-group col-md-6">
					<label for="date_enregistrement">Date enregistrement :</label>
					<input type="text" name="date_enregistrement" id="date_enregistrement" class="form-control" value="<?php echo $date_enregistrement??'' ?>">
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-4">
				</div>
				<div class="form-group col-md-6">
					<button type="submit" class="btn btn-primary">&nbsp; Enregistrer &nbsp;</button>
				</div>
			</div>
		</form>
	</div>
<?php
	}

require_once '../inc/footer.php';
