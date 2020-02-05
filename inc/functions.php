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
	if (!isset($donnees['pseudo']) || strlen ($donnees['pseudo']) < 4 || strlen ($donnees['pseudo']) > 20)
		$retour .= '<div class="alert alert-danger">Le pseudo doit être compris entre 4 et 20 caractères.</div>';
		//XXX ajouter test sur les caractères (regex)

	if ($controlerMdp)
		{
		if (!isset($donnees['mdp']) || strlen ($donnees['mdp']) < 8 || strlen ($donnees['mdp']) > 50)
			$retour .= '<div class="alert alert-danger">Le mot de passe doit être compris entre 8 et 50 caractères.</div>';
			//XXX ajouter test sur les caractères (regex)
		}

	if (!isset($donnees['nom']) || strlen ($donnees['nom']) < 2 || strlen ($donnees['nom']) > 45)
		$retour .= '<div class="alert alert-danger">Le nom doit être compris entre 2 et 45 caractères.</div>';

	if (!isset($donnees['prenom']) || strlen ($donnees['prenom']) < 2 || strlen ($donnees['prenom']) > 45)
		$retour .= '<div class="alert alert-danger">Le prénom doit être compris entre 2 et 45 caractères.</div>';

	if (!isset($donnees['telephone']) || !preg_match ('#^[0-9]{10}$#', $donnees['telephone'])) // epression régulière linux-like
		$retour .= '<div class="alert alert-danger">Le numero de telephone est invalide.</div>';
		//XXX permettre la saisie du tel comme 01 23 45 67 89
		// preg_match retourne 1 si la chaine proposée matche l'expression régulière, 0 sinon
		// l'expression est une string et est encadrée par des # (avec JavaScript, elle est encadrée par des / sans être une string)
		// on n'échappe pas les {}

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
		echo '	<li><a class="nav-link'.($titre=='Annonces'?' active':'').'" href="'.RACINE_SITE.'gestion_annonces.php">Annonces</a></li>';
		echo '	<li><a class="nav-link'.($titre=='Catégories'?' active':'').'" href="'.RACINE_SITE.'admin/gestion_categories.php">Catégories</a></li>';
		echo '	<li><a class="nav-link'.($titre=='Membres'?' active':'').'" href="'.RACINE_SITE.'admin/gestion_membres.php">Membres</a></li>';
		echo '	<li><a class="nav-link'.($titre=='Commentaires'?' active':'').'" href="'.RACINE_SITE.'admin/gestion_commentaires.php">Commentaires</a></li>';
		echo '	<li><a class="nav-link'.($titre=='Notes'?' active':'').'" href="'.RACINE_SITE.'admin/gestion_notes.php">Notes</a></li>';
		echo '	<li><a class="nav-link'.($titre=='Statistiques'?' active':'').'" href="'.RACINE_SITE.'admin/statistiques.php">Statistiques</a></li>';
		echo '</ul>';
		}
	}

// La pagination (BootStrap) des tableaux
function pagination ($page, $nombrePages)
	{
	echo '<nav aria-label="Page navigation example">';
	echo '<ul class="pagination">';
	echo '<li'.(($page==0)?'':' class="page-item"').'><a class="page-link" id="page_'.($page-1).'" onclick="return false" href="#">Précédente</a></li>';
	for ($i=0; $i<$nombrePages; $i++)
		echo '<li class="page-item'.(($i==$page)?' active':'').'"><a class="page-link" id="page_'.$i.'" onclick="return false" href="#">'.($i+1).'</a></li>';
	echo '<li'.(($page==$nombrePages-1)?'':' class="page-item"').'><a class="page-link" id="page_'.($page+1).'" onclick="return false" href="#">Suivante</a></li>';
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
