<?php
require_once '../inc/init.php';
if (!estAdmin())
	{
	exit ();
	}
// Les consignes envoyées par la requête AJAX
$tri   = $_POST['triMembre']  ?? '0';
$sens  = $_POST['sensMembre'] ?? '0';
$page = (int)($_POST['page'] ?? 0);

// Mémoriser les consignes dans la session pour qu'elles survivent à de futurs changement de page
$_SESSION['triMembre']   = $tri;
$_SESSION['sensMembre']  = $sens;
$_SESSION['pageMembre']  = $page;

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

// Compter les membres, pour la pagination
$resultat = executerRequete ("SELECT COUNT(*) FROM membre");
$nombreMembres = $resultat ? ($resultat->fetch(PDO::FETCH_NUM)[0]) : 0;

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
?>
<table class="table">
	<thead class="thead-dark">
		<tr>
			<th scope="col" class="tri" id="id">Id</th>
			<th scope="col" class="tri" id="pseudo">Pseudo</th>
			<th scope="col" class="tri" id="civilite">Civilité</th>
			<th scope="col" class="tri" id="nom">Nom</th>
			<th scope="col" class="tri" id="prenom">Prénom</th>
			<th scope="col" class="tri" id="email">email</th>
			<th scope="col" class="tri" id="telephone">Téléphone</th>
			<th scope="col" class="tri" id="role">Statut</th>
			<th scope="col" class="tri" id="date_enregistrement">Date d'enregistrement</th>
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
	echo         '<a href="?page='.$page.'&modification='.$ligne['id'].'#formulaire" class="lien-noir">'.MODIFIER.'</a>'."\n";
	switch ($nombreMembres[$id]??'0')
		{
		case '0':
			echo '<a href="?page='.$page.'&suppression='.$ligne['id'].'" onclick="return confirm(\'Etes-vous certain de vouloir supprimer le compte de '.$pseudo.' ?\')" class="lien-noir">'.POUBELLE.'</a>';
			break;
		case '1':
			echo '<a href="?page='.$page.'&suppression='.$ligne['id'].'" onclick="return confirm(\''.$pseudo.' a déposé une annonce qui sera inaccessible aux utilisateurs si vous supprimez son compte. Etes-vous certain de vouloir supprimer son compte ?\')" class="lien-noir">'.POUBELLE.'</a>';
			break;
		default :
			echo '<a href="?page='.$page.'&suppression='.$ligne['id'].'" onclick="return confirm(\''.$pseudo.' a déposé '.($nombreMembres[$id]??'0').' annonces qui seront inaccessibles aux utilisateurs si vous supprimez son compte. Etes-vous certain de vouloir supprimer son compte ?\')" class="lien-noir">'.POUBELLE.'</a>';
			break;
		}
	echo     '</td>';
	echo '</tr>';
	}
echo   '</table>';

// Pagination
/*
if (isset($limite))
	{
	pagination ($page, $nombrePages);
	}
*/