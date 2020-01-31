<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_notes.php
// affiche la liste des notes avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
require_once '../inc/init.php';

$afficherFormulaire = false;
//1. Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers connection
	header ('location:../connexion.php');
	exit ();
	}

//8. Modification d'une note
if (!empty($_POST))
	{
	extract ($_POST);
	if (!isset ($note) || !is_numeric($note) || $note*1 > 20)
		$contenu .= '<div class="alert alert-danger">La note doit être un nombre entre 0 et 20.</div>';

	if (!isset ($avis) || strlen($avis) < 3)
		$contenu .= '<div class="alert alert-danger">L\'avis doit comporter au moins 3 caractères.</div>';

	if (!isset ($auteur) || strlen($auteur) < 4 || strlen ($auteur) > 100)
		$contenu .= '<div class="alert alert-danger">Le pseudo de l\'auteur de la note doit être compris entre 4 et 20 catactères.</div>';
	else
		{
		$requete = executerRequete ("SELECT id FROM membre WHERE pseudo=:auteur", array (':auteur'=> $auteur));
		if ($requete->rowCount() >= 1)
			$auteur_id = $requete->fetch (PDO::FETCH_NUM)[0];
		else			
			$contenu .= '<div class="alert alert-danger">Le membre "'.$auteur.'" n\'existe pas.</div>';
		}

	if (!isset ($cible) || strlen($cible) < 4 || strlen ($cible) > 100)
		$contenu .= '<div class="alert alert-danger">Le pseudo concerné par la note doit être compris entre 4 et 20 catactères.</div>';
	else
		{
		$requete = executerRequete ("SELECT id FROM membre WHERE pseudo=:cible", array (':cible'=> $cible));
		if ($requete->rowCount() >= 1)
			$cible_id = $requete->fetch (PDO::FETCH_NUM)[0];
		else			
			$contenu .= '<div class="alert alert-danger">Le membre "'.$cible.'" n\'existe pas.</div>';
		}

	if (empty($contenu))
		{
		$requete = executerRequete ("REPLACE INTO note VALUES (:id, :note, :avis, :auteur_id, :cible_id, :date_enregistrement)",
		                            array (':id' => $id, ':note' => $note, ':avis' => $avis, ':auteur_id' => $auteur_id, ':cible_id' => $cible_id, ':date_enregistrement' => $date_enregistrement));
		}
	if (empty($contenu) && $requete)
		$contenu .= '<div class="alert alert-success">La note a été enregistrée.</div>';
	else
		$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
	}

//7. Suppression d'une note
if (isset ($_GET['suppression'])) // Si on a 'suppression' dans l'URL c'est qu'on a cliqué sur "suppression" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("DELETE FROM note WHERE id = :id", array (':id' => $_GET['suppression']));
	if ($resultat->rowCount() == 1)
		$contenu .= '<div class="alert alert-success">La note a bien été supprimé.</div>';
	else
		$contenu .= '<div class="alert alert-danger">Erreur lors de la suppression de la note.</div>';
	}
// Modification d'une note
else if (isset ($_GET['modification'])) // Si on a 'modification' dans l'URL c'est qu'on a cliqué sur "modification" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("SELECT n.id id, note, avis, m1.pseudo auteur, m2.pseudo cible, n.date_enregistrement date_enregistrement
	                              FROM note n, membre m1, membre m2
	                              WHERE n.id = :id AND m1.id = membre_id1 and m2.id = membre_id2", array (':id' => $_GET['modification']));
	if ($resultat->rowCount() == 1)
		{
		$afficherFormulaire = true;
		$note_courante = $resultat->fetch (PDO::FETCH_ASSOC);
		}
	else
		$contenu .= '<div class="alert alert-danger">Erreur interne.</div>';
	}

//6. Affichage du tableau des notes : 
$resultat = executerRequete ("SELECT n.id id, note, avis, m1.pseudo auteur, m2.pseudo cible, n.date_enregistrement date_enregistrement
                              FROM note n, membre m1, membre m2
                              WHERE m1.id = membre_id1 and m2.id = membre_id2");
$contenu .='<div class="table-responsive">';
$contenu .=   '<table class="table">';
$contenu .=      '<thead class="thead-dark">';
$contenu .=          '<tr>';
$contenu .=              '<th>Id</th>';
$contenu .=              '<th>Note</th>';
$contenu .=              '<th>Avis</th>';
$contenu .=              '<th>Auteur</th>';
$contenu .=              '<th>Membre</th>';
$contenu .=              '<th>Date</th>';
$contenu .=              '<th>Action</th>';
$contenu .=          '</tr>';
$contenu .=      '</thead>';
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) // pour chaque ligne retournée par la requête
	{
	extract ($ligne);
	$contenu .= '<tr>';
	$contenu .= '    <th scope="row">' . $id . '</th>';
	$contenu .= '    <td>' . noteEnEtoiles($note) . '</td>';
	$contenu .= '    <td>' . $avis . '</td>';
	$contenu .= '    <td>' . $auteur . '</td>';
	$contenu .= '    <td>' . $cible . '</td>';
	$contenu .= '    <td>' . $date_enregistrement . '</td>';
	// Là, il y a un petit bout de javaScript fûté : quand on retourne false dans un onclick, ça bloque le lien. Na.
	$contenu .= '<td><a href="?modification='.$ligne['id'].'#formulaire">Modifier</a>'."\n".'<a href="?suppression='.$ligne['id'].'" onclick="return confirm(\'Etes Vous certain de vouloir supprimer cette note ?\')">Supprimer</a></td>';
	$contenu .= '</tr>';
	}
$contenu .=   '</table>';
$contenu .='</div>';
$id = 0;
$titre = '';
$mots_cles = '';


require_once '../inc/header.php';

//2. Navigation entre les pages d'administration
navigation_admin ('Notes');

echo $contenu; // pour afficher notamment le tableau des notes
if ($afficherFormulaire)
	{
	isset ($note_courante) && extract ($note_courante);

	//3. Formulaire de création/modification des notes
?>
	<form id="formulaire" method="post" action="gestion_notes.php" >
		<div>
			<input type="hidden" name="id" value="<?php echo $id??0 ?>"> <!-- hidden => éviter de le modifier par accident. value="0" => lors de l'insertion le SGBD utilisera l'auto-incrémentation -->
		</div>

		<div>
			<div><label for="note">Note</label></div>
			<div><input type="text" name="note" id="note" value="<?php echo $note??'' ?>"></div>
		</div>

		<div>
			<div><label for="note">Avis</label></div>
			<div><textarea style="width:80vw;height:20vh" type="text" name="avis" id="note"><?php echo $avis ?></textarea>
		</div>
		
		<div>
			<div><label for="pseudo">Auteur</label></div>
			<div><input type="text" name="auteur" id="auteur" value="<?php echo $auteur??'' ?>"></div>
		</div>

		<div>
			<div><label for="mots_cles">Membre</label></div>
			<div><input type="text" name="cible" id="cible" value="<?php echo $cible??'' ?>"></div>
		</div>

		<div>
			<div><label for="mots_cles">Date Enregistrement</label></div>
			<div><input type="text" name="date_enregistrement" id="date_enregistrement" value="<?php echo $date_enregistrement??'' ?>"></div>
		</div>

		<div class="mt-2"><input type="submit" value="Enregistrer"></div>
	</form>
<?php

	}

require_once '../inc/footer.php';
