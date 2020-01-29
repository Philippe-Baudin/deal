<?php
require_once 'inc/init.php';
if (!estAdmin())
	{
	header ('location:'.RACINE_SITE.'connexion.php');
	exit ();
	}
$page = ($_POST['page'] ?? 1) -1;
$tri  = $_POST['tri'] ?? $_SESSION['tri'] ?? 'date_enregistrement';
$sens  = $_POST['sens'] ?? $_SESSION['sens'] ?? 'ASC';
$_SESSION['tri'] = $tri;
$_SESSION['sens'] = $sens;

// Forcer le critère de tri
if (false === array_search ($tri, array (  "id_annonce",
                                           "titre",
                                           "description_courte",
                                           "description_longue",
                                           "prix",
                                           "photo",
                                           "pays",
                                           "ville",
                                           "adresse",
                                           "code_postal",
                                           "pseudo",
                                           "categorie",
                                           "date_enregistrement")))
	$tri = 'date_enregistrement';

// Forcer le sens du tri
if ($sens != 'DESC') $sens = 'ASC';

// Requête donnant les éléments à afficher dans le tableau
$resultat = executerRequete ("SELECT annonce.id id_annonce,
                                     annonce.titre titre,
                                     description_courte,
                                     description_longue,
                                     prix,
                                     photo,
                                     pays,
                                     ville,
                                     adresse,
                                     code_postal,
                                     pseudo,
                                     categorie.titre categorie,
                                     annonce.date_enregistrement date_enregistrement
                              FROM annonce, categorie, membre
                              WHERE membre_id=membre.id AND categorie_id=categorie.id
                              ORDER BY ".$tri." ".$sens." LIMIT ".(taillePage*$page).','.taillePage);

// Affichage du tableau
?>
<table class="table">
	<thead class="thead-dark">
		<tr>
			<th scope="col" class="tri" id="id_annonce"         >Id</th>
			<th scope="col" class="tri" id="titre"              >Titre</th>
			<th scope="col" class="tri" id="description_courte" >Description courte</th>
			<th scope="col" class="tri" id="description_longue" >Description longue</th>
			<th scope="col" class="tri" id="prix"               >Prix</th>
			<th scope="col" class="tri" id="photo"              >Photo</th>
			<th scope="col" class="tri" id="pays"               >Pays</th>
			<th scope="col" class="tri" id="ville"              >Ville</th>
			<th scope="col" class="tri" id="adresse"            >Adresse</th>
			<th scope="col" class="tri" id="code_postal"        >CP</th>
			<th scope="col" class="tri" id="pseudo"             >Membre</th>
			<th scope="col" class="tri" id="categorie"          >Catégorie</th>
			<th scope="col" class="tri" id="date_enregistrement">Date</th>
			<th scope="col">Action</th>
		</tr>
	</thead>
	<tbody>
		<?php

		// les pages sont numérotées de 0 à n-1 dans la requête et de 1 à n dans le tableau de résultats
		$page ++;

		// pour chaque ligne retournée par la requête, une ligne de tableau
		while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC))
			{
			extract ($ligne);
			if (strlen ($description_courte) > 20) $description_courte = mb_substr($description_courte, 0, 20, 'UTF-8').'...';
			if (strlen ($description_longue) > 20) $description_longue = mb_substr($description_longue, 0, 20, 'UTF-8').'...';
			echo '<tr>';
			echo '    <th scope="row">' . $id_annonce . '</th>';
			echo '    <td>' . $titre . '</td>';
			echo '    <td>' . $description_courte . '</td>';
			echo '    <td>' . $description_longue . '</td>';
			echo '    <td>' . sprintf("%.2f €", $prix) . '</td>';
			echo '    <td><img src="' . $photo . '" style="width:100%; height:auto" ></td>';
			echo '    <td>' . $pays . '</td>';
			echo '    <td>' . $ville . '</td>';
			echo '    <td>' . $adresse . '</td>';
			echo '    <td>' . $code_postal . '</td>';
			echo '    <td>' . $pseudo . '</td>';
			echo '    <td>' . $categorie . '</td>';
			echo '    <td>' . $date_enregistrement . '</td>';
			echo '    <td>';
			echo '        <a href="fiche_annonce.php?id='.$ligne['id_annonce'].'">Voir</a>';
			echo '        <a href="?modification='.$ligne['id_annonce'].'&page='.$page.'#formulaire" ">Modifier</a>';
			echo '        <a href="?suppression='.$ligne['id_annonce'].'&page='.$page.'" onclick="return confirm(\'Etes Vous certain de vouloir supprimer cette annonce ?\')">Supprimer</a>';
			echo '    </td>';
			echo '</tr>';
			}
		?>
	</tbody>
</table>
