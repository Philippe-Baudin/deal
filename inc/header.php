<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Deal</title>
	<!-- Font Awesome CSS -->
	<script
		src="https://kit.fontawesome.com/0937b307e2.js"
		crossorigin="anonymous">
	</script>
	<!-- JQuery -->
	<script
		src="https://code.jquery.com/jquery-3.4.1.min.js"
		integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
		crossorigin="anonymous">
	</script>
	<!--<link href="<?php echo RACINE_SITE ?>js/jquery-ui.css" rel="stylesheet">-->
	<!-- AJAX -->
	<script
		src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
		integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
		crossorigin="anonymous">
	</script>
	<!-- CSS BootStrap -->
	<script
		src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"
		integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy"
		crossorigin="anonymous">
	</script>	
	<link
		rel="stylesheet"
		href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
		integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO"
		crossorigin="anonymous"
	>
<!--	<link rel="stylesheet" href="<?php echo RACINE_SITE ?>css/jquery-ui.min.css">-->
	<link rel="stylesheet" href="<?php echo RACINE_SITE ?>css/style.css">

	<!--<link rel="stylesheet" href="<?php echo RACINE_SITE ?>css/style.css">-->
</head>
<body>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<a class="navbar-brand" href="<?php echo RACINE_SITE ?>index.php">Deal</a>

		<div class="collapse navbar-collapse" id="navbarTogglerDemo03">
			<ul class="navbar-nav mr-auto mt-2 mt-lg-0">
				<?php 
				if (estConnecte())
					{
					echo '<li class="nav-item active">';
					echo     '<a class="nav-link" href="'.RACINE_SITE.'gestion_annonces.php?creation">Déposer une annonce</a>';
					echo '</li>';
					}
				if (estAdmin())
					{
					echo '<li class="nav-item">';
					echo     '<a class="nav-link" href="'.RACINE_SITE.'gestion_annonces.php">Administration</a>';
					echo '</li>';
					}
				?>
			</ul>
			<ul class="navbar-nav mr-auto mt-2 mt-lg-0">
				<li class="nav-item active dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">						
						<i class="fas fa-user"></i> &nbsp;<?php echo $_SESSION['membre']['pseudo']??'Espace membre' ?>
					</a>
					<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">						
						<?php
// Ce serait bien de changer ça dynamiquement quand on se connecte/déconnecte, sans avoir à rafraichir la page
						if (estConnecte())
							{
							echo '<a class="dropdown-item" href="'.RACINE_SITE.'profil.php">Profil</a>';
							echo '<a class="dropdown-item" href="'.retablirGET(true).'">Deconnexion</a>';
							}
						else
							{
							echo '<a class="dropdown-item" data-toggle="modal" href="#modaleConnexion">Connexion</a>';
							echo '<a class="dropdown-item" data-toggle="modal" href="#modaleInscription">Inscription</a>';
							}
						?>
					</div>
				</li>
			</ul>
			<form class="form-inline my-2 my-lg-0" method="post" action="<?php echo RACINE_SITE.'recherche.php'?>">
				<input class="form-control mr-sm-2" type="search" placeholder="Recherche" aria-label="Search" title="rechercher" name="mots-cles" value="<?php $_POST['mots-cles']??''?>">
				<button class="btn btn-outline-success my-2 my-sm-0" type="submit"><img src="<?php echo RACINE_SITE.'img/loupe.png' ?>" title="rechercher"></button>
			</form>
		</div>
	</nav>

<!-- machinerie de zoom des images -->
<div id="zoom">
	<div id="cadre">
		<img src="img/pixel.gif" alt="">
	</div>
</div>


	<!-- Contenu de la page -->
	<div class="container" style="min-height:80vh;width:100%;max-width:100vw"> <!-- in fine, il faudrait mettre le style dans un fichier CSS -->
		<div class="row">
			<div class="col-12">
