<?php require('header.php'); ?>
		<div id="content">
			<div id="msgbox">
				<p>Modulo de gestión de los usuarios del sistema {{Nombre Empresa}} &mdash; Registros, mantención y privilegios de todos los usuarios existentes.</p>
			</div><!--/msgbox-->
			<div id="content-page">
			<div id="msgbox2"></div>
			<div id="content-usuarios" class="content-clientes-form">
				<div id="left-form"><?php include('usuarios-form.php'); ?></div>
				<div id="right-grilla"><?php include('usuarios-grilla.php'); ?></div>
			</div><!--/content-clientes-form-->
			</div><!--/content-page-->
		</div><!--/content-->
		<?php include('footer.php'); ?>
	</div><!--/wrap-->
</body>
</html>