<?php

// Ca peut toujours servir ...
function debug ($var)
	{
	echo '<pre>';
	var_dump ($var);
	echo '</pre>';
	}

// ---------------------------
// Fonctions liées au membre :
// ---------------------------
// Si 'membre' existe dans la session, c'est que l'internaute est passé par la connexion avec le bon pseudo/mdp
// et que nous avons rempli la session avec ses infos.
function estConnecte()
	{
	return isset($_SESSION['membre']);
	}
// Indique si le membre est connecté et est un administrateur (par définition role='admin')
function estAdmin ()
	{
	return estConnecte() && $_SESSION['membre']['role']=='admin';
	}
function validerMembre ($donnees, $controlerMdp=true)
	{
	$retour = '';

	// Il faut valider chacun des champs du formulaire
	if (!isset($donnees['pseudo']) || !preg_match ('/^[-_a-zA-Z0-9ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝÞàáâãäåæçèéêëìíîïðñòóôõöùúûüýþÿĀāĂăĄąĆćĈĉĊċČčĎďĐđĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħĨĩĪīĬĭĮįİıĲĳĴĵĶķĸĹĺĻļĽľĿŀŁłŃńŅņŇňŊŋŌōŎŏŐőŒœŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŦŧŨũŪūŬŭŮůŰűŲųŴŵŶŷŸŹźŻżŽžſ]{4,45}$/u', $donnees['pseudo']))
		$retour .= '<div class="alert alert-danger">le pseudo est invalide.</div>';

	if ($controlerMdp)
		{
		if (!isset($donnees['mdp']) || strlen ($donnees['mdp']) < 8 || strlen ($donnees['mdp']) > 50)
			$retour .= '<div class="alert alert-danger">Le mot de passe doit être compris entre 8 et 50 caractères.</div>';
		}

	if (!isset($donnees['nom']) || !preg_match ('/^[-a-zA-ZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝÞàáâãäåæçèéêëìíîïðñòóôõöùúûüýþÿĀāĂăĄąĆćĈĉĊċČčĎďĐđĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħĨĩĪīĬĭĮįİıĲĳĴĵĶķĸĹĺĻļĽľĿŀŁłŃńŅņŇňŊŋŌōŎŏŐőŒœŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŦŧŨũŪūŬŭŮůŰűŲųŴŵŶŷŸŹźŻżŽžſ]{4,45}$/u', $donnees['nom']))
		$retour .= '<div class="alert alert-danger">Le nom est trop bizarre ...</div>';

	if (!isset($donnees['nom']) || !preg_match ('/^[-a-zA-ZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝÞàáâãäåæçèéêëìíîïðñòóôõöùúûüýþÿĀāĂăĄąĆćĈĉĊċČčĎďĐđĒēĔĕĖėĘęĚěĜĝĞğĠġĢģĤĥĦħĨĩĪīĬĭĮįİıĲĳĴĵĶķĸĹĺĻļĽľĿŀŁłŃńŅņŇňŊŋŌōŎŏŐőŒœŔŕŖŗŘřŚśŜŝŞşŠšŢţŤťŦŧŨũŪūŬŭŮůŰűŲųŴŵŶŷŸŹźŻżŽžſ]{4,45}$/u', $donnees['nom']))
		$retour .= '<div class="alert alert-danger">Le prénom est invalide.</div>';

	if (!isset($donnees['telephone']) || !preg_match ('#^[0-9]{10}$#', $donnees['telephone'])) // epression régulière linux-like
		$retour .= '<div class="alert alert-danger">Le numero de telephone est invalide.</div>';

	if (!isset($donnees['email']) || !filter_var($donnees['email'], FILTER_VALIDATE_EMAIL))
		$retour .= '<div class="alert alert-danger">L\'email est invalide</div>';

	if (!isset($donnees['civilite']) || ($donnees['civilite'] != 'M.' && $donnees['civilite'] != 'Mme'))
		$retour .= '<div class="alert alert-danger">Vous devez choisir M. ou Mme.</div>';

	return $retour;
	}

// Une fonction qui vérifie qu'une chaîne de caractère représente un nombre à virgule flottante, en fonction de la locale
function isFloat($value)
	{
	$value = trim ($value);
	$locale = localeconv();
	$value = str_replace($locale['decimal_point'], '.', $value);
	$value = str_replace($locale['thousands_sep'], '', $value);
	return (strval(floatval($value)) == $value);
	}

// Les onglets permettant à l'admin de naviger sur les pages des différentes tables
function navigationAdmin ($titre)
	{
	if (estAdmin())
		{
		echo '<div style="text-align:center;">';
		if ($titre == 'Statistiques')
			echo '<h1 class="mt-4">'.$titre.'</h1>';
		else
			echo '<h1 class="mt-4">Gestion des '.$titre.'</h1>';
		echo '</div>';
		echo '<ul class="nav nav-tabs"> <!-- onglets -->';
		echo '	<li><a class="nav-link'.($titre=='Annonces'?' active" onclick="return false':'').'" href="'.RACINE_SITE.'gestion_annonces.php">Annonces</a></li>';
		echo '	<li><a class="nav-link'.($titre=='Catégories'?' active" onclick="return false':'').'" href="'.RACINE_SITE.'admin/gestion_categories.php">Catégories</a></li>';
		echo '	<li><a class="nav-link'.($titre=='Membres'?' active" onclick="return false':'').'" href="'.RACINE_SITE.'admin/gestion_membres.php">Membres</a></li>';
		echo '	<li><a class="nav-link'.($titre=='Commentaires'?' active" onclick="return false':'').'" href="'.RACINE_SITE.'admin/gestion_commentaires.php">Commentaires</a></li>';
		echo '	<li><a class="nav-link'.($titre=='Notes'?' active" onclick="return false':'').'" href="'.RACINE_SITE.'admin/gestion_notes.php">Notes</a></li>';
		echo '	<li><a class="nav-link'.($titre=='Statistiques'?' active" onclick="return false':'').'" href="'.RACINE_SITE.'admin/statistiques.php">Statistiques</a></li>';
		echo '</ul>';
		}
	}

// La pagination (BootStrap) des tableaux
function pagination ($page, $nombrePages)
	{
	echo '<nav aria-label="Page navigation example">';
	echo '<ul class="pagination">';
	echo '<li'.(($page==0)?'':' class="page-item"').'><a class="page-link" id="precedent_'.($page-1).'" onclick="return false" href="#">Précédente</a></li>';
	if ($nombrePages > 10)
		{
		if ($page < 5)
			{
			for ($i=0; $i<=$page+1; $i++)
				echo '<li class="page-item'.(($i==$page)?' active':'').'"><a class="page-link" id="page_'.$i.'" onclick="return false" href="#">'.($i+1).'</a></li>';
			echo '<li class="page-item"> &nbsp; ... &nbsp; </li>';
			for ($i=$nombrePages-2; $i<$nombrePages; $i++)
				echo '<li class="page-item"><a class="page-link" id="page_'.$i.'" onclick="return false" href="#">'.($i+1).'</a></li>';
			}
		else if ($page < $nombrePages-5)
			{
			for ($i=0; $i<2; $i++)
				echo '<li class="page-item"><a class="page-link" id="page_'.$i.'" onclick="return false" href="#">'.($i+1).'</a></li>';
			echo '<li class="page-item"> &nbsp; ... &nbsp; </li>';
			for ($i=$page-1; $i<=$page+1; $i++)
				echo '<li class="page-item'.(($i==$page)?' active':'').'"><a class="page-link" id="page_'.$i.'" onclick="return false" href="#">'.($i+1).'</a></li>';
			echo '<li class="page-item"> &nbsp; ... &nbsp; </li>';
			for ($i=$nombrePages-2; $i<$nombrePages; $i++)
				echo '<li class="page-item"><a class="page-link" id="page_'.$i.'" onclick="return false" href="#">'.($i+1).'</a></li>';
			}
		else
			{
			for ($i=0; $i<2; $i++)
				echo '<li class="page-item"><a class="page-link" id="page_'.$i.'" onclick="return false" href="#">'.($i+1).'</a></li>';
			echo '<li class="page-item"> &nbsp; ... &nbsp; </li>';
			for ($i=$page-2; $i<$nombrePages; $i++)
				echo '<li class="page-item'.(($i==$page)?' active':'').'"><a class="page-link" id="page_'.$i.'" onclick="return false" href="#">'.($i+1).'</a></li>';
			}
		}
	else
		{
		for ($i=0; $i<$nombrePages; $i++)
			echo '<li class="page-item'.(($i==$page)?' active':'').'"><a class="page-link" id="page_'.$i.'" onclick="return false" href="#">'.($i+1).'</a></li>';
		}
	echo '<li'.(($page==$nombrePages-1)?'':' class="page-item"').'><a class="page-link" id="suivant_'.($page+1).'" onclick="return false" href="#">Suivante</a></li>';
	echo '</ul>';
	echo '</nav>';
	}


// Convertir une note en suite d'étoiles FontAwesome
function noteEnEtoiles ($note)
	{
	$i=0;
	$retour = '<span style="font-size:0.7rem">';
	$nombreEtoiles = floor($note);
	$demiEtoile = floor($note-$nombreEtoiles+.5);
	for ($i=0; $i<$nombreEtoiles; $i++)
		$retour .= '<i class="fas fa-star"></i>'; // étoile pleine
	if ($demiEtoile)
		{
		$retour .= '<i class="fas fa-star-half-alt"></i>'; // demi-étoile
		$i++;
		}
	for (; $i<5; $i++)
		$retour .= '<i class="far fa-star"></i>'; // étoile vide
	$retour .= '</span>';
	return $retour;
	}

// générer les paramêtres d'URL à partie du contenu de $_GET
function retablirGET ($deconnexion)
	{
	$retour = '';
	$separateur = '?';
	if ($deconnexion)
		{
		$retour .= '?action=deconnexion';
		$separateur = '&';
		}
	if (!empty($_GET))
		{
		foreach ($_GET as $indice => $valeur)
			{
			if ($indice != 'action' || $indice != 'deconnexion')
				{
				$retour .= $separateur.$indice.'='.$valeur;
				$separateur = '&';
				}
			}
		}
	return $retour;
	}

// Modale de confirmation de la suppression de quelque chose ...
function modaleSuppression ($titre, $plus)
	{
	?>
	<div class="modal modal<?php echo ($plus?'_lg':'')?>" id="modaleSuppression" tabindex="-1" role="dialog" aria-labelledby="modaleSuppressionTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal<?php echo ($plus?'_lg':'')?>" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modaleSuppressionTitle">Etes vous certain de vouloir supprimer <?php echo $titre?>&nbsp;?</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<?php if($plus){?>
							<div class="col-sm-12" id="complement"></div><div class="col-sm-12"><hr></div>
						<?php }?>
						<div class="col-sm-2">
							<button type="button" class="btn btn-primary ok-suppression">Oui</button>
						</div>
						<div class="col-sm-2">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	}


// ----------------------------------------------
// Requete SQL :
// ----------------------------------------------
// Exécuter une reqête SQL protégée en 
// Retourne un objet PDOStatement en cas de succès, false en cas d'erreur
function executerRequete ($requete, $param=array()) // param = array vide par défaut
	{
	foreach ($param as $marqueur => $variable)
		$param[$marqueur] = htmlspecialchars ($variable); // évite les injections XSS et CSS

	global $pdo;
	$resultat = $pdo->prepare($requete); // évite les injections SQL
	if ($resultat->execute($param)) // $param permet à execute() d'associer chaque marqueur à sa valeur
		return $resultat; // $resultat est un PDOStatement
	else
		return false;
	}
