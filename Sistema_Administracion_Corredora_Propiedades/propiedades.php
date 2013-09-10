<?php require('header.php'); ?>
		<div id="content">
			<div id="msgbox">
				<p>Modulo de gestión de propiedades &mdash; Registros, consultas y mantención de todas las propiedades existentes.</p>
			</div><!--/msgbox-->
			<div id="content-page">
			<?php
			if ( isset($_GET['accion']) && $_GET['accion'] == 'propiedades_cliente' ) :
				include('clientes-propiedades-grilla.php');
			elseif ( isset($_GET['accion']) && $_GET['accion'] == 'editar_propiedad' ) :
				include('propiedades-form.php');
			elseif ( isset($_GET['volver_propiedad']) && $_GET['volver_propiedad'] == 'si' ) :
				include('propiedades-form.php');
			else :
				include('propiedades-grilla.php');
			endif;
			?>
			</div><!--/content-page-->
		</div><!--/content-->
		<?php include('footer.php'); ?>
	</div><!--/wrap-->
</body>
</html>