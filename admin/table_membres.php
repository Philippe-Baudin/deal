<?php
$repertoire='../';
require_once '../inc/init.php';
if (!estAdmin())
	exit;

// Les consignes envoyées par la requête AJAX
$tri   = $_POST['triMembre']  ?? '0';
$sens  = $_POST['sensMembre'] ?? '0';
$page = (int)($_POST['pageMembre'] ?? '0');

// Forcer le critère de tri
if (false === array_search ($tri, array (  "id",
                                           "pseudo",
                                           "nom",
                                           "prenom",
                                           "telephone",
                                           "email",
                                           "civilite",
                                           "role",
                                           "date_enregistrement")))
	$tri = 'date_enregistrement';

// Forcer le sens du tri
if ($sens != 'DESC') $sens = 'ASC';

// Mémoriser les consignes dans la session pour qu'elles survivent à de futurs changement de page
$_SESSION['triMembre']   = $tri;
$_SESSION['sensMembre']  = $sens;
$_SESSION['pageMembre']  = $page;

// Compter les membres, pour la pagination
$resultat = executerRequete ("SELECT COUNT(*) FROM membre");
$nombreMembres = $resultat ? ($resultat->fetch(PDO::FETCH_NUM)[0]) : 0;

// Compter les annonces de chaque membre
$nombreAnnonces = array ();
$resultat = executerRequete ("SELECT membre_id, COUNT(id) nb from annonce group by membre_id");
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC))
	$nombreAnnonces[$ligne['membre_id']] = $ligne['nb'];

// En déduire les membres à afficher, en fonction du numéro de page
if ($nombreMembres > TAILLE_PAGE_MEMBRE)
	{
	$membreDebut = TAILLE_PAGE_MEMBRE*$page;
	$limite = ' LIMIT '.$membreDebut.','.TAILLE_PAGE_MEMBRE;
	$nombrePages = ceil ($nombreMembres/TAILLE_PAGE_MEMBRE);
	}

// Sélectionner
$resultat = executerRequete ("SELECT * FROM membre ORDER BY $tri $sens".($limite??''));

// Affichage du tableau des membres
$marqueurTri = '<span style="color:grey;">'.(($sens=='ASC') ? '&nbsp;&or;' : '&nbsp;&#94;').'</span>';
?>
<table class="table">
	<thead class="thead-dark">
		<tr>
			<th scope="col" class="tri" id="id">Id<?php if($tri=='id')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="pseudo">Pseudo<?php if($tri=='pseudo')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="civilite">Civilité<?php if($tri=='civilite')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="nom">Nom<?php if($tri=='nom')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="prenom">Prénom<?php if($tri=='prenom')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="email">email<?php if($tri=='email')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="telephone">Téléphone<?php if($tri=='telephone')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="role">Statut<?php if($tri=='role')echo $marqueurTri?></th>
			<th scope="col" class="tri" id="date_enregistrement">Date d'enregistrement<?php if($tri=='date_enregistrement')echo $marqueurTri?></th>
			<th scope="col">Action</th>
		</tr>
	</thead>
<?php
while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC))
	{
	extract ($ligne);
	echo "<tr>";
	echo     "<th scope='row'>$id</th>";
	echo     "<td>$pseudo</td>";
	echo     "<td>$civilite</td>";
	echo     "<td>$nom</td>";
	echo     "<td>$prenom</td>";
	echo     "<td>$email</td>";
	echo     "<td>$telephone</td>";
	echo     "<td>$role</td>";
	echo     "<td>$date_enregistrement</td>";

	// Là, il y a un petit bout de javaScript fûté : quand on retourne false dans un onclick, ça bloque le lien. Na.
	echo     '<td>';
	echo         '<a href="?page='.$page.'&modification='.$id.'#formulaire" class="lien-noir">'.MODIFIER.'</a>'."\n";
	echo         '<span class="lien-noir demande-suppression" id="'.$pseudo.'_'.$id.'_'.($nombreAnnonces[$id]??0).'">'.POUBELLE.'</span>';
	echo     '</td>';
	echo '</tr>';
	}
echo   '</table>';

// Pagination
if (isset($limite))
	pagination ($page, $nombrePages);
