			</div>  <!-- class="col-12" -->
		</div> <!-- class="row" -->
	</div> <!-- class="container" -->

	<!-- footer -->
	<footer>
	<!--<footer style="position:sticky;bottom:0;">-->
		<div class="container">
			<hr>
			<div class="row text-center">
				<div class="col-sm-4">
				</div>
				<div class="col-sm-4">
					<p>&copy; Deal - 2020</p>
				</div>
				<div class="col-sm-4">
					<a href="<?php echo RACINE_SITE?>mentions_legales.php" class="lien-noir">Mentions Légales</a>
				</div>
			</div>
		</div>
	</footer>

	<script>
	function deconnexion(retour)
		{
		$(".connected")  .css("display", "none");
		$(".unconnected").css("display", "inline");
		$(".admin")      .css("display", "none");
		$(".pas-admin")  .css("display", "inline");
		$("#espace-membre").html("Espace membre");
		<?php if (empty($pageCourante)){ ?>
			window.location.href = "<?php echo RACINE_SITE?>index.php";
		<?php }?>
		};
	$("#deconnexion").click(_=>{
		$.post("<?php echo RACINE_SITE?>connexion_ajax.php", {deconnexion:1}, deconnexion, "html");
		});
	</script>
</body>
</html>
