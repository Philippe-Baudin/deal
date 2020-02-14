<?php
$repertoire='';
require_once 'inc/init.php';
if (!estAdmin())
	{
	exit ();
	}
$page = (int)($_POST['page'] ?? 0);
$tri  = $_POST['tri'] ?? $_SESSION['tri'] ?? 'date_enregistrement';
$sens = $_POST['sens'] ?? $_SESSION['sens'] ?? 'ASC';

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

// Mémoriser les consignes dans la session pour qu'elles survivent à de futurs changement de page
$_SESSION['tri'] = $tri;
$_SESSION['sens'] = $sens;
$_SESSION['page'] = $page;

// Compter les annonces, pour la pagination
$resultat = executerRequete ("SELECT COUNT(*) FROM annonce");
$nombreAnnonces = $resultat ? ($resultat->fetch(PDO::FETCH_NUM)[0]) : 0;

// En déduire les notes à afficher, en fonction du numéro de page
if ($nombreAnnonces > TAILLE_PAGE_ANNONCE)
	{
	$annonceDebut = TAILLE_PAGE_ANNONCE*$page;
	$limite = ' LIMIT '.$annonceDebut.','.TAILLE_PAGE_ANNONCE;
	$nombrePages = ceil ($nombreAnnonces/TAILLE_PAGE_ANNONCE);
	}

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
                              FROM annonce
                              INNER JOIN categorie ON annonce.categorie_id=categorie.id
                              LEFT JOIN membre ON annonce.membre_id=membre.id
                              ORDER BY $tri $sens".($limite??''));


// Affichage du tableau
$marqueurTri = '<span style="color:grey;">'.(($sens=='ASC') ? '&nbsp;&or;' : '&nbsp;&#94;').'</span>';
?>
<table class="table">
	<thead class="thead-dark">
		<tr>
			<th scope="col" class="tri" id="id_annonce"         >Id<?php                 if ($tri=='id_annonce')         echo $marqueurTri?></th>
			<th scope="col" class="tri" id="titre"              >Titre<?php              if ($tri=='titre')              echo $marqueurTri?></th>
			<th scope="col" class="tri" id="description_courte" >Description courte<?php if ($tri=='description_courte') echo $marqueurTri?></th>
			<th scope="col" class="tri" id="description_longue" >Description longue<?php if ($tri=='description_longue') echo $marqueurTri?></th>
			<th scope="col" class="tri" id="prix"               >Prix<?php               if ($tri=='prix')               echo $marqueurTri?></th>
			<th scope="col" class="tri" id="photo"              >Photo<?php              if ($tri=='photo')              echo $marqueurTri?></th>
			<th scope="col" class="tri" id="pays"               >Pays<?php               if ($tri=='pays')               echo $marqueurTri?></th>
			<th scope="col" class="tri" id="ville"              >Ville<?php              if ($tri=='ville')              echo $marqueurTri?></th>
			<th scope="col" class="tri" id="adresse"            >Adresse<?php            if ($tri=='adresse')            echo $marqueurTri?></th>
			<th scope="col" class="tri" id="code_postal"        >CP<?php                 if ($tri=='code_postal')        echo $marqueurTri?></th>
			<th scope="col" class="tri" id="pseudo"             >Membre<?php             if ($tri=='pseudo')             echo $marqueurTri?></th>
			<th scope="col" class="tri" id="categorie"          >Catégorie<?php          if ($tri=='categorie')          echo $marqueurTri?></th>
			<th scope="col" class="tri" id="date_enregistrement">Date<?php               if ($tri=='date_enregistrement')echo $marqueurTri?></th>
			<th scope="col">Action</th>
		</tr>
	</thead>
	<tbody>
		<?php

		// pour chaque ligne retournée par la requête, une ligne de tableau
		define ('NOMBRE_CARACTERES_MAX', 12);
		while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC))
			{
			extract ($ligne);
			if (strlen ($description_courte) > NOMBRE_CARACTERES_MAX+3) $description_courte = mb_substr($description_courte, 0, NOMBRE_CARACTERES_MAX, 'UTF-8').'...';
			if (strlen ($description_longue) > NOMBRE_CARACTERES_MAX+3) $description_longue = mb_substr($description_longue, 0, NOMBRE_CARACTERES_MAX, 'UTF-8').'...';
			if (strlen ($adresse)            > NOMBRE_CARACTERES_MAX+3) $adresse            = mb_substr($adresse,            0, NOMBRE_CARACTERES_MAX, 'UTF-8').'...';
			echo '<tr style="vertical-align:text-top;">';
			echo     '<th scope="row">' . $id_annonce . '</th>';
			echo     '<td>' . $titre . '</td>';
			echo     '<td>' . $description_courte . '</td>';
			echo     '<td>' . $description_longue . '</td>';
			echo     '<td>' . sprintf("%.2f €", $prix) . '</td>';
			echo     '<td><img src="' . $photo . '" style="max-height:7vh;width:auto" ></td>';
			echo     '<td>' . $pays . '</td>';
			echo     '<td>' . $ville . '</td>';
			echo     '<td>' . $adresse . '</td>';
			echo     '<td>' . $code_postal . '</td>';
			echo     '<td>' . (($pseudo=='NULL')?'':$pseudo) . '</td>';
			echo     '<td>' . $categorie . '</td>';
			echo     '<td>' . $date_enregistrement . '</td>';
			echo     '<td>';
			echo         '<a href="fiche_annonce.php?id='.$id_annonce.'" class="lien-noir">'.LOUPE.'</a> ';
			echo         '<a href="?modification='.$id_annonce.'&page='.$page.'#formulaire"  class="lien-noir">'.MODIFIER.'</a> ';
			echo         '<span class="lien-noir demande-suppression" id="suppression_'.$id_annonce.'">'.POUBELLE.'</span>';
			echo     '</td>';
			echo '</tr>';
			}
		?>
	</tbody>
</table>
<?php
// Pagination
if (isset($limite))
	pagination ($page, $nombrePages);
