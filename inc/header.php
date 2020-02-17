<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Petites Annonces</title>
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
	<link rel="stylesheet" href="<?php echo RACINE_SITE ?>css/style.css">
	<link rel="icon" href="<?php echo RACINE_SITE ?>img/deal.gif" type="image/x_icon">
	<style>
		<?php
		if (estConnecte())
			{
			echo '.connected {display:inline;}';
			echo '.unconnected {display:none;}';
			}
		else
			{
			echo '.connected {display:none;}';
			echo '.unconnected {display:inline;}';
			}
		if (estAdmin())
			{
			echo '.admin {display:inline;}';
			echo '.pas-admin {display:none;}';
			}
		else
			{
			echo '.admin {display:none;}';
			echo '.pas-admin {display:inline;}';
			}
		?>
	</style>
</head>
<body>

	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<a class="navbar-brand" href="<?php echo RACINE_SITE?>index.php">Deal</a>

		<div class="collapse navbar-collapse" id="navbarTogglerDemo03">
			<ul class="navbar-nav mr-auto mt-2 mt-lg-0">
				<li class="nav-item active">
					<a class="nav-link connected" href="<?php echo RACINE_SITE?>gestion_annonces.php?creation">Déposer une annonce</a>
				</li>
				<li class="nav-item">
					<a class="nav-link admin" href="<?php echo RACINE_SITE?>gestion_annonces.php">Administration</a>
				</li>
			</ul>
			<ul class="navbar-nav mr-auto mt-2 mt-lg-0">
				<li class="nav-item active dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">						
						<i class="fas fa-user"></i> &nbsp;<span id="espace-membre"><?php echo $_SESSION['membre']['pseudo']??'Espace membre' ?></span>
					</a>
					<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">						
						<a class="dropdown-item connected" href="<?php echo RACINE_SITE?>profil.php">Profil</a>
						<span class="dropdown-item connected lien-noir" id="deconnexion">Deconnexion</span>
						<a class="dropdown-item connexion unconnected" data-toggle="modal" href="#modaleConnexion">Connexion</a>
						<a class="dropdown-item inscription unconnected" data-toggle="modal" href="#modaleInscription">Inscription</a>
					</div>
				</li>
			</ul>
			<form class="form-inline my-2 my-lg-0" method="post" action="<?php echo RACINE_SITE.'recherche.php?page=0'?>">
				<input class="form-control mr-sm-2" type="search" placeholder="Recherche" aria-label="Search" title="rechercher" name="mots-cles" value="<?php if(!empty($_SESSION['mots-cles']))echo implode(' ',$_SESSION['mots-cles'])?>">
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
	<div class="container">
		<div class="row">
			<div class="col-12">
				<?php if (empty ($_SESSION['cookie'])): $_SESSION['cookie']=true?>
					<div class="alert alert-dark alert-dismissible fade show" role="alert">
						<strong>
							Ce site utilise des cookies. Si votre navigateur est réglé pour les refuser,
							vous ne pourrez pas vous connecter et vous ne pourrez donc déposer ni annonce ni commentaire ni avis.
							<a href="mentions_legales.php#cookie" class="alert-link souligner">En savoir plus</a>
						</strong>
	  					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						    <span aria-hidden="true">&times;</span>
	  					</button>
					</div>
				<?php endif;?>
