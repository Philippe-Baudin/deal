<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Deal</title>
	<!-- CDN Bootstrap CSS -->
	<script
		src="https://kit.fontawesome.com/0937b307e2.js"
		crossorigin="anonymous">
	</script>
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
</head>
<body>

<?php
// Deconnexion de l'internaute
if (isset ($_GET['action']) && $_GET['action'] == 'deconnexion')
	{
	unset ($_SESSION['membre']); // supprime la partie 'membre' de la session (sans toucher à un éventuel panier)
	header ('location:'.RACINE_SITE.'index.php');
	exit ();
	}
?>

	<!-- navigation -->
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<div class="container">
			<!-- marque -->
			<a class="navbar-brand" href="<?php echo RACINE_SITE . 'index.php'; ?>">DEAL</a> <!-- on utilise la constante RACINE_SITE pour avoir un lien absolu, ne pas dépendre de l'emplacement du fichier ou ceci sera inclu -->

			<!-- le menu burger -->
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nav1" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
				<span class=navbar-toggler-icon></span>
			</button>

			<!-- le menu classique -->
			<div class="collapse navbar-collapse" id="nav1">
				<ul class="navbar-nav ml-auto"> <!-- ml-auto signifie margin-left auto -->
					<?php
					echo '<li><a class="nav-link" href="'.RACINE_SITE.'gestion_annonces.php?creation">Nouvelle annonce</a></li>';
					if (estConnecte())
						{
						echo '<li><a class="nav-link" href="'.RACINE_SITE.'profil.php">Profil</a></li>';
						echo '<li><a class="nav-link" href="'.RACINE_SITE.'?action=deconnexion">Deconnexion</a></li>';
						}
					else
						{
						echo '<li><a class="nav-link" href="'.RACINE_SITE.'inscription.php">Inscription</a></li>';
						echo '<li><a class="nav-link" data-toggle="modal" href="#modaleConnexion">Connexion</a></li>';
						}
					if (estAdmin())
						{
						echo '<li><a class="nav-link" href="'.RACINE_SITE.'gestion_annonces.php">Administration</a></li>';
						}
					?>
				</ul>
			</div> <!-- fin menu classique -->
		</div> <!-- fin div container -->
	</nav>

	<!-- Contenu de la page -->
	<div class="container" style="min-height:80vh;"> <!-- in fine, il faudrait mettre le style dans un fichier CSS -->
		<div class="row">
			<div class="col-12">
