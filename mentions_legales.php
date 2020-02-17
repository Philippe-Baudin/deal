<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// mentions_legales.php
// mentions légales : utilisation des cookies, de la base de donnée, confidentialité, origine des annonces
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$repertoire='';
require_once 'inc/init.php';
$pageCourante = 'mentions_legales.php';

// Header standard, inscription et connexion
// -----------------------------------------
require_once 'inc/header.php';
require_once 'connexion_modale.php';
require_once 'inscription_modale.php';
echo $contenu;
?>
<div class="titre"><h1>Mentions légales</h1></div>
<h4 class="mt-5">Auteur et responsable de la publication : Philippe BAUDIN</h4>
<hr class="mt-5">
<p>Ce site (https://philippe-baudin.fr/deal) est un exercice dans le cadre de ma formation de Développeur Intégrateur WEB au sein de l'<a href="https://www.ifocop.fr/">IFOCOP</a>.<br>Ce n'est donc pas un véritable site de petites annonces.</p>
<p>En particulier :</p>
<ul>
	<li>Les annonces publiées sont fictives et ne correspondent à aucun bien ou service réel.</li>
	<li>Les personnes ou sociétés éventuellement mentionnées le sont à leur corps défendant et ne peuvent en aucun cas être considéré comme responsable de ce qui est écrit sur ce site.</li>
	<li>J'ai utilisé, à titre d'exemple, des annonces et des images que j'ai trouvé sur internet. Elle ne sont pas nécessairement libres de droit. Ne les réutilisez pas sans vous en assurer au préalable.</li>
</ul>
<hr>
<h3 id="cookies" class="mt-5">Les cookies</h3>
<p>Le seul cookie utilisé par ce site est le cookie de session, qui sert à ce qu'on puisse naviguer de page en page sur ce site en conservant certaines informations (connexion, choix dans des boîtes de sélection, page courante dans des tableaux&nbsp;...)<br>Si vous interdisez les cookies, vous ne pourrez déposer ni annonce, ni commentaire ni avis, mais vous pourrez consulter les annonces, et les commentaires et utiliser la barre de recherche.</p>
<p>Ce cookie est effacé automatiquement à l'arrêt du navigateur.</p>
<p>Il n'y a donc aucun traqueur, espion, ou autre sournoiseries qu'on rencontre d'ordinaire sur le WEB.</p>
<hr>
<h3 class="mt-5">Confidentialité</h3>
<p>Les annonces, commentaires et avis sont publiés sur le site et donc visibles pour tout le monde, et l'adresse mail que vous aurez donnée est visible pour toute personne connectée.</p>
<p>Les profils sont privés, mais me sont accessibles, ainsi qu'aux correcteurs de cet exercice (formateurs de l'IFOCOP).</p>
<p>Ces données ne seront communiqué à personne d'autre et ne seront utilisées à rien d'autre qu'à vérifier le bon fonctionnement du site.</p>
<hr>
<h3 class="mt-5">Stockage des données</h3>
<p>Toutes les données que vous saisissez lors de l'inscription, les annonces, les commentaires et les avis que vous pouvez poster sont conservés. Ils seront, dans tous les cas, effacées avant le premier janvier 2021.</p>
<p>Il n'y a aucun stockage d'autres informations que celles que vous avez volontairement saisies.</p>
<p>Comme il n'y a pas de contrôle de véracité de l'adresse mail, de l'état-civil ou du numéro de téléphone, je vous encourage à utiliser des données fictives.<br>L'adresse mail doit simplement avoir la forme x.y@z et le numéro de téléphone être une suite de 10 chiffres.</p>
<hr>
<h3 class="mt-5">Suppression des données</h3>
<p>D'aventuelles informations illégales seront, bien entendu, supprimées.</p>
<p>Je me réserve aussi le droit de supprimer tout contenu à ma guise, à des fins de test.</p>
<p>Les formateurs de l'IFOCOP auront le même droit.</p>

<?php
// Et le footer standard
require_once 'inc/footer.php';

