<?php require('header.php'); ?>
		<div id="content">
			<div id="msgbox">
				<p>
					<?php if ( isset($_GET['ingresando_propiedad']) && $_GET['ingresando_propiedad'] == 'si' ) { ?>
					Ingrese al nuevo cliente y luego vuelva a la pantalla de propiedades para completar el registro.
					<a href="propiedades.php?volver_propiedad=si" id="volver_propiedad" class="submit small">Volver al registro de la propiedad</a>
					<?php } else { ?>
					Modulo de gestión de los clientes/propietarios &mdash; Registros, consultas y mantención de todos los clientes existentes.
					<?php } ?>
				</p>
			</div><!--/msgbox-->
			<div id="content-page">
			<div id="msgbox2"></div>
			<div id="content-clientes" class="content-clientes-form">
				<div id="left-form"><?php include('clientes-form.php'); ?></div>
				<div id="right-grilla"><?php include('clientes-grilla.php'); ?></div>
			</div><!--/content-clientes-form-->
			</div><!--/content-page-->
		</div><!--/content-->
		<?php include('footer.php'); ?>
	</div><!--/wrap-->
</body>
</html>