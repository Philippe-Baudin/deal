<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// gestion_notes.php
// affiche la liste des notes avec des liens vers modification et suppression
// ainsi qu'un formulaire de modification
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='../';
require_once '../inc/init.php';

$afficherFormulaire = false;

// Vérification administrateur
if (!estAdmin())
	{
	// Si l'utilisateur n'est pas connecté ou n'est pas admin, le rediriger vers l'accueil
	header ('location:../index.php');
	exit ();
	}

// Traitement de la modification d'une note
if (!empty($_POST))
	{
	extract ($_POST);
	if (!isset ($note) || !is_numeric($note) || $note*1 > 5)
		$contenu .= '<div class="alert alert-danger">La note doit être un nombre entre 0 et 5.</div>';

	if (!isset ($avis) || strlen($avis) < 3)
		$contenu .= '<div class="alert alert-danger">L\'avis doit comporter au moins 3 caractères.</div>';

	if (!isset ($auteur) || strlen($auteur) < 4 || strlen ($auteur) > 100)
		$contenu .= '<div class="alert alert-danger">Le pseudo de l\'auteur de la note doit être compris entre 4 et 20 caractères.</div>';
	else
		{
		$resultat = executerRequete ("SELECT id FROM membre WHERE pseudo=:auteur", array (':auteur'=> $auteur));
		if ($resultat->rowCount() >= 1)
			$auteur_id = $resultat->fetch (PDO::FETCH_NUM)[0];
		else			
			$contenu .= '<div class="alert alert-danger">Le membre "'.$auteur.'" n\'existe pas.</div>';
		}

	if (!isset ($cible) || strlen($cible) < 4 || strlen ($cible) > 100)
		$contenu .= '<div class="alert alert-danger">Le pseudo concerné par la note doit être compris entre 4 et 20 caractères.</div>';
	else
		{
		$resultat = executerRequete ("SELECT id FROM membre WHERE pseudo=:cible", array (':cible'=> $cible));
		if ($resultat->rowCount() >= 1)
			$cible_id = $resultat->fetch (PDO::FETCH_NUM)[0];
		else			
			$contenu .= '<div class="alert alert-danger">Le membre "'.$cible.'" n\'existe pas.</div>';
		}

	if (empty($contenu))
		{
		$resultat = executerRequete ("REPLACE INTO note VALUES (:id, :note, :avis, :auteur_id, :cible_id, :date_enregistrement)",
		                            array (':id' => $id, ':note' => $note, ':avis' => $avis, ':auteur_id' => $auteur_id, ':cible_id' => $cible_id, ':date_enregistrement' => $date_enregistrement));
		}
	if (empty($contenu) && $resultat)
		$contenu .= '<div class="alert alert-success">La note a été enregistrée.</div>';
	else
		{
		$contenu .= '<div class="alert alert-danger">Erreur lors de l\'enregistrement</div>';
		$afficherFormulaire = true;
		$noteCourante = $_POST;
		}

	}

// Demande de modification d'une note
else if (isset ($_GET['modification'])) // Si on a 'modification' dans l'URL c'est qu'on a cliqué sur "modification" dans le tableau ci-dessous
	{
	$resultat = executerRequete ("SELECT n.id id, note, avis, m1.pseudo auteur, m2.pseudo cible, n.date_enregistrement date_enregistrement
	                              FROM note n, membre m1, membre m2
	                              WHERE n.id = :id AND m1.id = membre_id1 and m2.id = membre_id2", array (':id' => $_GET['modification']));
	if ($resultat->rowCount() == 1)
		{
		$afficherFormulaire = true;
		$noteCourante = $resultat->fetch (PDO::FETCH_ASSOC);
		}
	else
		$contenu .= '<div class="alert alert-danger">Erreur interne.</div>';
	}

// Affichage du tableau des notes : 
$contenu .='<div class="table-responsive" id="tableau">';
$contenu .='</div>';


require_once '../inc/header.php';

// Emplacement du message de retour de suppression d'un commentaire
echo '<div id="messageSuppression"></div>';

// Modale de confirmation de la supression d'un commentaire'
modaleSuppression ('cette note', false);


// Navigation entre les pages d'administration
navigationAdmin ('Notes');

echo $contenu; // pour afficher notamment le tableau des notes
if ($afficherFormulaire)
	{
	isset ($noteCourante) && extract ($noteCourante);

	// Formulaire de création/modification des notes
?>
	<div class="cadre-formulaire">
		<form id="formulaire" method="post" action="gestion_notes.php">
			<input type="hidden" name="id" value="<?php echo $id ?>"> <!-- hidden => éviter de le modifier par accident. value="0" => lors de l'insertion le SGBD utilisera l'auto-incrémentation -->
			<div class="form-row">
				<div class="form-group col-md-3">
					<label for="note">Note :</label>
					<select id="note" name="note">
						<option value="0" <?php echo (isset($note)&&$note=='0')?'selected':'' ?>>0 &star;&star;&star;&star;&star;</option>
						<option value="1" <?php echo (isset($note)&&$note=='1')?'selected':'' ?>>1 &starf;&star;&star;&star;&star;</option>
						<option value="2" <?php echo (isset($note)&&$note=='2')?'selected':'' ?>>2 &starf;&starf;&star;&star;&star;</option>
						<option value="3" <?php echo (isset($note)&&$note=='3')?'selected':'' ?>>3 &starf;&starf;&starf;&star;&star;</option>
						<option value="4" <?php echo (isset($note)&&$note=='4')?'selected':'' ?>>4 &starf;&starf;&starf;&starf;&star;</option>
						<option value="5" <?php echo (isset($note)&&$note=='5')?'selected':'' ?>>5 &starf;&starf;&starf;&starf;&starf;</option>
					</select>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-12">
					<label for="avis">Avis :</label>
					<textarea style="height:20vh" name="avis" id="avis" class="form-control"><?php echo $avis ?></textarea>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group col-md-3">
					<label for="auteur">Auteur de l'avis :</label>
					<input type="text" name="auteur" id="auteur" class="form-control" value="<?php echo $auteur??'' ?>">
				</div>
				<div class="form-group col-md-3">
					<label for="cible">Membre concerné :</label>
					<input type="text" name="cible" id="cible" class="form-control" value="<?php echo $cible??'' ?>">
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
	} // fin du if ($afficherFormulaire)
	?>
<script>
	$(function(){ // document ready

		// Le tri et le numéro de page
		<?php
			echo 'let tri  = "'.($_SESSION["triNote"]??0).'";';
			echo 'let sens = "'.($_SESSION["sensNote"]??0).'";';
			echo 'let page = "'.($_SESSION["pageNote"]??0).'";';
		?>
		let cible;

		// clic sur le bouton 'oui' de la fenêtre modale de confirmation de suppression
		$(".ok-suppression").on ('click', function(){
			$.post('suppression_note.php', {id:cible},function(reponse){
				$('#modaleSuppression').modal('hide');
				$('#messageSuppression').html(reponse);
				afficherTableau ();
				}, 'html');
			});

		// réception et traitement de la réponse à la requête AJAX d'affichage du tableau
		function reponse (contenu)
			{
			$('#tableau').html(contenu);
			<?php if ($afficherFormulaire) echo 'location.hash = "#formulaire"' ?>

			// clic sur une icône "poubelle" du tableau
			$(".demande-suppression").on ('click', function(e){
				cible = e.currentTarget.id.replace(/[^0-9]/g,'');
				$('#modaleSuppression').modal('show');
				});

			// clic sur une des cases de la pagination
			$('.page-item').on('click', 'a', function(e)
				{
				page = e.target.id.replace(/[^0-9]/g, '');
				afficherTableau ();
				});
			}

		// Lancement de la requête AJAX d'affichage du tableau
		function afficherTableau ()
			{
			// arrêter les listener de demande de supression
			$(".demande-suppression").off("click");

			// Emission de la requête AJAX
			$.post('table_notes.php', { triNote  : tri, sensNote : sens, pageNote : page,}, reponse, 'html');
			}

	    // trier si on clique sur une entête du tableau
		$('#tableau').on('click', 'th.tri', function(e){
			if (tri == e.target.id) sens = ((sens=='ASC')?'DESC':'ASC');
			else { tri = e.target.id; sens='ASC'; }
			afficherTableau();
		});

		// A l'affichage de la page, lancer une première fois la requête AJAX
		afficherTableau ();

	}); // document ready
</script>

<?php
require_once '../inc/footer.php';
