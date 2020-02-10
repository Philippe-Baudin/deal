<?php
$repertoire='../';
require_once '../inc/init.php';

if (!estAdmin())
	{
	exit ();
	}

// Les consignes envoyées par la requête AJAX
$tri   = $_POST['triNote']  ?? '0';
$sens  = $_POST['sensNote'] ?? '0';
$page  = (int)($_POST['pageNote'] ?? '0');

// Forcer le critère de tri
if (false === array_search ($tri, array (  "id",
                                           "note",
                                           "avis",
                                           "auteur",
                                           "cible",
                                           "date_enregistrement")))
	$tri = 'date_enregistrement';

// Forcer le sens du tri
if ($sens != 'DESC') $sens = 'ASC';

// Mémoriser les consignes dans la session pour qu'elles survivent à de futurs changement de page
$_SESSION['triNote']   = $tri;
$_SESSION['sensNote']  = $sens;
$_SESSION['pageNote']  = $page;

// Compter les notes
$resultat = executerRequete ("SELECT COUNT(*) FROM note");
$nombreNotes = $resultat ? ($resultat->fetch(PDO::FETCH_NUM)[0]) : 0;

// En déduire les notes à afficher, en fonction du numéro de page
if ($nombreNotes > TAILLE_PAGE_NOTE)
	{
	$noteDebut = TAILLE_PAGE_NOTE*$page;
	$limite = ' LIMIT '.$noteDebut.','.TAILLE_PAGE_NOTE;
	$nombrePages = ceil ($nombreNotes/TAILLE_PAGE_NOTE);
	}

// Sélectionner
$resultat = executerRequete ("SELECT n.id id, note, avis, m1.pseudo auteur, m2.pseudo cible, n.date_enregistrement date_enregistrement
                              FROM note n, membre m1, membre m2
                              WHERE m1.id = membre_id1 and m2.id = membre_id2 ORDER BY $tri $sens".($limite??''));


// Affichage du tableau des notes
$marqueurTri = '<span style="color:grey;">'.(($sens=='ASC') ? '&nbsp;&or;' : '&nbsp;&#94;').'</span>';
?>
<table class="table">
	<thead class="thead-dark">
		<tr>
			<th scope="col" class="tri" id="id">Id<?php if($tri=='id')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="note">Note<?php if($tri=='note')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="avis">Avis<?php if($tri=='avis')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="auteur">Auteur<?php if($tri=='auteur')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="cible">Membre<?php if($tri=='cible')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="date_enregistrement">Date<?php if($tri=='date_enregistrement')echo $marqueurTri?></th>
			<th>Action</th>
		</tr>
	</thead>
<?php
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) // pour chaque ligne retournée par la requête
	{
	extract ($ligne);
	echo '<tr>';
	echo     '<th scope="row">' . $id . '</th>';
	echo     '<td>' . noteEnEtoiles($note) . '</td>';
	echo     '<td>' . $avis . '</td>';
	echo     '<td>' . $auteur . '</td>';
	echo     '<td>' . $cible . '</td>';
	echo     '<td>' . $date_enregistrement . '</td>';
	// Là, il y a un petit bout de javaScript fûté : quand on retourne false dans un onclick, ça bloque le lien. Na.
	echo     '<td>';
	echo         '<a href="?modification='.$ligne['id'].'#formulaire" class="lien-noir">'.MODIFIER.'</a>'."\n";
	echo         '<a href="?suppression='.$ligne['id'].'" onclick="return confirm(\'Etes Vous certain de vouloir supprimer cette note ?\')" class="lien-noir">'.POUBELLE.'</a>';
	echo     '</td>';
	echo '</tr>';
	}
echo   '</table>';

// Pagination
if (isset($limite))
	pagination ($page, $nombrePages);
