<?php
require_once '../inc/init.php';
if (!estAdmin())
	{
	exit ();
	}

// Les consignes envoyées par la requête AJAX
$tri   = $_POST['triCommentaire']  ?? '0';
$sens  = $_POST['sensCommentaire'] ?? '0';
$page  = (int)($_POST['pageCommentaire'] ?? '0');

// Forcer le critère de tri
if (false === array_search ($tri, array (  "id",
                                           "commentaire",
                                           "pseudo",
                                           "annonce",
                                           "date_enregistrement")))
	$tri = 'date_enregistrement';

// Forcer le sens du tri
if ($sens != 'DESC') $sens = 'ASC';

// Mémoriser les consignes dans la session pour qu'elles survivent à de futurs changement de page
$_SESSION['triCommentaire']   = $tri;
$_SESSION['sensCommentaire']  = $sens;
$_SESSION['pageCommentaire']  = $page;

// Compter les commentaires
$resultat = executerRequete ("SELECT COUNT(*) FROM commentaire");
$nombreCommentaires = $resultat ? ($resultat->fetch(PDO::FETCH_NUM)[0]) : 0;

// En déduire les commentaires à afficher, en fonction du numéro de page
if ($nombreCommentaires > TAILLE_PAGE_COMMENTAIRE)
	{
	$commentaireDebut = TAILLE_PAGE_COMMENTAIRE*$page;
	$limite = ' LIMIT '.$commentaireDebut.','.TAILLE_PAGE_COMMENTAIRE;
	$nombrePages = ceil ($nombreCommentaires/TAILLE_PAGE_COMMENTAIRE);
	}

// Sélectionner
$resultat = executerRequete ("SELECT c.id id, c.commentaire commentaire, m.pseudo pseudo, a.id annonce, c.date_enregistrement date_enregistrement
                              FROM commentaire c, membre m, annonce a
                              WHERE c.membre_id=m.id AND c.annonce_id=a.id ORDER BY $tri $sens".($limite??''));


// Affichage du tableau des commentaires
?>
<table class="table">
	<thead class="thead-dark">
		<tr>
			<th scope="col" class="tri" id="id">Id</th>
			<th scope="col" class="tri" id="pseudo">Membre</th>
			<th scope="col" class="tri" id="annonce">Annonce</th>
			<th scope="col" class="tri" id="commentaire">Commentaire</th>
			<th scope="col" class="tri" id="date_enregistrement">Date</th>
			<th scope="col"">Action</th>
		</tr>
	</thead>
<?php
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) // pour chaque ligne retournée par la requête
	{
	extract ($ligne);
	echo '<tr>';
	echo     '<th scope="row">' . $id . '</th>';
	echo     '<td>' . $pseudo . '</td>';
	echo     '<td>' . $annonce . '</td>';
	echo     '<td>' . $commentaire . '</td>';
	echo     '<td>' . $date_enregistrement . '</td>';
	         // Là, il y a un petit bout de javaScript fûté : quand on retourne false dans un onclick, ça bloque le lien. Na.
	echo     '<td>';
	echo         '<a href="?modification='.$ligne['id'].'#formulaire" class="lien-noir">'.MODIFIER.'</a>'."\n";
	echo         '<a href="?suppression='.$ligne['id'].'" onclick="return confirm(\'Etes-vous certain de vouloir supprimer ce commentaire ?\')" class="lien-noir">'.POUBELLE.'</a>';
	echo     '</td>';
	echo '</tr>';
	}
echo   '</table>';

// Pagination
if (isset($limite))
	pagination ($page, $nombrePages);

