///////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////// Zoom ///////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ((requete=matchMedia("(min-width: 680px)")).matches)
	{

	// Inutile de refaire les mêmes JQueries ad nauseam
	let zoomable     = $(".zoomable"); // les images zoomables
	let support_zoom = $("#zoom");     // la div contenant l'image zoomée
	let cible_zoom   = $("#zoom img"); // l'image zoomée
	let body         = $("body");      // le body, pour manipuler sa scrollbar

	// Fonctions pour zoomer et revenir à l'état initial
	function zoomer (evenement)
		{
		body.css ("overflowY", "hidden"); // pour empêcher de scroller quand la page est cachée
		cible_zoom.attr ("src", evenement.target.src); // donner son adresse à l'image zoomée
		support_zoom.css( "zIndex", 2 );  // mettre l'image zoomée au premier plan
		cible_zoom.css ("maxWidth", "100%"); // et lui donner la largeur maxi
		}
	function dezoomer ()
		{
		support_zoom.css( "zIndex", -2 ) // cacher le zoom sous la page
		body.css ("overflowY", "auto"); // remettre le scrolling
		cible_zoom.attr ("src", "img/pixel.gif"); // remplacer l'image par une image transparente de 1x1 pixel
		cible_zoom.css ("maxWidth", "1px") // et remettre la taille au mini pour permettre la prochaine transition
		}

	// Listeners
	zoomable.click (zoomer);
	support_zoom.click (dezoomer);

	}