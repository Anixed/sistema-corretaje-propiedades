<?php
//Definir la zona horaria predeterminada a usar. Disponible desde PHP 5.1
date_default_timezone_set('America/Santiago');
//echo date("d-m-Y H:i:s e");
//
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//error_reporting(E_ALL);
?>
<?php
session_start();
//echo "[INICIO SESION] "; print_r($_SESSION); echo " [/FIN SESION]";
if ( isset($_SESSION['login_sistema']) && $_SESSION['login_sistema'] === true ) :

require('connect-mysql.php'); require('functions.php');
//script que contiene los privilegios de acceso de los usuarios
require('functions-privilegios-acceso.php');

if ( confirmar_privilegios_acceso(title_page($_SERVER["SCRIPT_NAME"]), $_SESSION['id_sistema']) ) {
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: index.php?msgbox=sin_permiso&error=usuario');
	exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo title_page($_SERVER["REQUEST_URI"], true) ?></title>
	<link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
	<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.blockUI.js"></script>
	<script type="text/javascript" src="js/slide-menu.js"></script>
	<link rel="shortcut icon" href="favicon.ico" />
	
	<?php if ( title_page($_SERVER["REQUEST_URI"]) == 'agenda_visitas' ):?>
	<meta http-equiv="PRAGMA" content="NO-CACHE" />
	<meta http-equiv="EXPIRES" content="-1" />
	<script type="text/javascript" src="agenda_visitas_script.js"></script>
	<!--script type="text/javascript" src="js/vtip.js"></script-->
	<!-- jQuery UI Draggable -->
	<script src="js/jquery-ui/jquery.ui.core.js"></script>
	<script src="js/jquery-ui/jquery.ui.widget.js"></script>
	<script src="js/jquery-ui/jquery.ui.mouse.js"></script>
	<script src="js/jquery-ui/jquery.ui.draggable.js"></script>
	<script type="text/javascript">
	$(document).ready(function() {
		setTimeout(function() { $('#mensaje').fadeOut('fast'); }, 3000);
	});
	</script>
	<?php endif; ?>
	
	<?php if ( title_page($_SERVER["REQUEST_URI"]) == 'propiedades' || title_page($_SERVER["REQUEST_URI"]) == 'clientes-propiedades-grilla' ):?>
	<script type="text/javascript" src="propiedades_script.js"></script>
	<!-- jQuery UI Draggable -->
	<script src="js/jquery-ui/jquery.ui.core.js"></script>
	<script src="js/jquery-ui/jquery.ui.widget.js"></script>
	<script src="js/jquery-ui/jquery.ui.mouse.js"></script>
	<script src="js/jquery-ui/jquery.ui.draggable.js"></script>
	<!-- gmaps.js: una forma más fácil de utilizar los mapas de Google -->
	<script type="text/javascript" src="js/gmaps.js"></script>
	<link rel="stylesheet" href="js/tinybox2/style.css" />
	<script type="text/javascript" src="js/tinybox2/tinybox.js"></script>
	<?php endif; ?>
	
	<?php if ( title_page($_SERVER["REQUEST_URI"]) == 'propiedades-carta-oferta' ):?>
	<script type="text/javascript" src="propiedades-carta-oferta_script.js"></script>
	<?php endif; ?>
	
	<?php if ( title_page($_SERVER["REQUEST_URI"]) == 'clientes' ):?>
	<script type="text/javascript" src="clientes_script.js"></script>
	<script type="text/javascript" src="js/jquery.Rut.min.js"></script>
	<?php endif; ?>
	
	<?php if ( title_page($_SERVER["REQUEST_URI"]) == 'usuarios' ):?>
	<script type="text/javascript" src="usuarios_script.js"></script>
	<script type="text/javascript" src="js/jquery.Rut.min.js"></script>
	<?php endif; ?>
</head>

<body>
	<div id="wrap">
		<div id="login">
			<span>Bienvenido <i><?php echo $_SESSION['name_sistema']; ?></i> <strong>(<?php echo tipo_usuario($_SESSION['tipo_sistema']); ?>)</strong> | </span>
			<a href="login.php?logout=true&<?php echo session_name().'='.session_id();?>" target="_self" onClick="return confirm('¿Desea cerrar su sesión?');">[<strong>Cerrar Sesión</strong>]</a>
		</div>
		<div id="header">
			<div class="jquery-slide-menu" id="slide-menu">
				<ul class="nav-menu">
					<li><a href="index.php">Home</a></li>
					<li><a href="propiedades.php">Propiedades</a>
						<?php if ( mostrar_opcion_menu('Administración de Pagos', $_SESSION['id_sistema']) ) { //Si es administrador o es la administrativa ?>
						<ul>
							<li><a href="javascript:void(0);">Administración de Pagos</a></li>
						</ul>
						<?php } ?>
					</li>
					<li><a href="agenda_visitas.php">Agenda Anfitriones</a></li>
					<li><a href="clientes.php">Clientes</a>
						<ul>
							<li><a href="clientes-buscan.php">Buscan Propiedad</a></li>
							<li><a href="propiedades.php?accion=propiedades_cliente">Dueños de Propiedades</a></li>
						</ul>
					</li>
					<?php if ( mostrar_opcion_menu('Administración', $_SESSION['id_sistema']) ) { //Si es administrador ?>
					<li><a href="javascript:void(0);">Administración</a>
						<ul>
							<li><a href="usuarios.php">Usuarios del sistema</a></li>
						</ul>
					</li>
					<?php } ?>
				</ul>
			</div>
			<div id="loading">Loading...</div>
			<div class="clear"></div>
		</div>
<?php
//$mysqli->close();
else :
	setcookie('User-SISTEMA', '', time() - 3600, '', '',0);
	setcookie('Pass-SISTEMA', '', time() - 3600, '', '',0);
	//cerrar sesion
	session_unset();
	session_destroy();
	include('error.php');
	exit();
endif;
?>