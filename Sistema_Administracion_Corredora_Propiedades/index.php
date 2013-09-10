<?php require('header.php');

if ( $_GET['msgbox'] == 'sin_permiso' && $_GET['error'] == 'eliminar' ) :
	$msgbox = '<strong>ERROR:</strong> No tiene los privilegios suficientes para eliminar registros en los módulos.';
	$icon = 'error';
endif;

if ( $_GET['msgbox'] == 'sin_permiso' && $_GET['error'] == 'usuario' ) :
	$msgbox = '<strong>ERROR:</strong> No tiene permisos para acceder a la pantalla consultada.';
	$icon = 'error';
endif;

?>
		<div class="<?php echo 'msgbox-'.$icon;?>"><?php echo $msgbox; ?></div>
		<div id="content">
			<div id="msgbox">
				<p>Bienvenido <strong><?php echo $_SESSION['name_sistema']; ?></strong> al <i>Sistema Corretaje de Propiedades</i> &mdash; {{Nombre Empresa}}</p>
			</div><!--/msgbox-->
			<div id="content-page" class="text-center">
			</div><!--/content-page-->
		</div><!--/content-->
		<?php include('footer.php'); ?>
	</div><!--/wrap-->
</body>
</html>