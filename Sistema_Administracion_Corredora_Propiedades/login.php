<?php
session_start();
require('connect-mysql.php');

if ( $_GET['logout'] === 'true' && session_id() === $_GET['PHPSESSID'] ) :
	
	setcookie('User-SISTEMA', '', time() - 3600, '', '',0);
	setcookie('Pass-SISTEMA', '', time() - 3600, '', '',0);
	//cerrar sesion
	session_unset();
	session_destroy();
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: login.php');
	exit();

elseif ( isset($_SESSION['login_sistema']) && $_SESSION['login_sistema'] === true ) :
	
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: index.php?'.session_name().'='.session_id());
	exit();
	
elseif ( ( isset($_COOKIE['User-SISTEMA']) && isset($_COOKIE['Pass-SISTEMA']) ) && !isset($_SESSION['login_sistema']) ) :
	
	$rut_usuario = $mysqli->real_escape_string($_COOKIE['User-SISTEMA']);
	$pass_usuario = $mysqli->real_escape_string($_COOKIE['Pass-SISTEMA']);
	$sql = "SELECT id_usuario, nombre, apellido, email, tipo_usuario FROM usuarios WHERE rut_usuario='".$rut_usuario."' and password='".$pass_usuario."'";
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ($row = $result->fetch_array()) {
		$_SESSION['login_sistema'] = true;
		$_SESSION['id_sistema'] = $row['id_usuario'];
		$_SESSION['name_sistema'] = $row['nombre'].' '.$row['apellido'];
		$_SESSION['tipo_sistema'] = $row['tipo_usuario'];
		$_SESSION['email_sistema'] = $row['email'];
		$result->close();
		$mysqli->close();
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: index.php?'.session_name().'='.session_id());
		exit();
	} else {
		setcookie('User-SISTEMA', '', time() - 3600, '', '',0);
		setcookie('Pass-SISTEMA', '', time() - 3600, '', '',0);
		//cerrar sesion
		session_unset();
		session_destroy();
		$result->close();
		$mysqli->close();
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: login.php');
		exit();
	}
	
endif;

if ($_POST['accion'] == 'Iniciar sesión') :
	
	if ( !empty($_POST['rut_usuario']) && !empty($_POST['pass_md5']) ) {
		if ( preg_match('/[0-9]{1,2}[.]?[0-9]{3}[.]?[0-9]{3}[-][0-9kK]{1}/', $_POST['rut_usuario']) ) {
		
		$_POST['rut_usuario'] = preg_replace('/[.]/', '', $_POST['rut_usuario']);
		
		//Utilizando mysql_real_escape_string filtramos las cadenas antes de enviarlas en la consulta SQL, esto para prevenir inyecciones SQL
		$rut_usuario = $mysqli->real_escape_string($_POST['rut_usuario']);
		$pass_usuario = $mysqli->real_escape_string($_POST['pass_md5']); //md5($_POST['pass_usuario']);
		
		$sql = "SELECT id_usuario, nombre, apellido, email, tipo_usuario FROM usuarios WHERE rut_usuario='".$rut_usuario."' and password='".$pass_usuario."'";
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		
		if ($row = $result->fetch_array()) {
			
			$sql = 'UPDATE usuarios SET ultimo_login=NOW() WHERE id_usuario='.$row['id_usuario'];
			$mysqli->query($sql) or die('Error: '.$mysqli->error);
			
			if ( isset($_POST['rememberme']) && $_POST['rememberme'] == 'yes' ) {
				setcookie('User-SISTEMA', $rut_usuario, time() + (7 * 86400), '', '',0);
				setcookie('Pass-SISTEMA', $pass_usuario, time() + (7 * 86400), '', '',0);
			} else {
				setcookie('User-SISTEMA', $rut_usuario);
				setcookie('Pass-SISTEMA', $pass_usuario);
			}
			//echo '<pre>'; print_r($_COOKIE); echo '</pre>';
			
			$_SESSION['login_sistema'] = true;
			$_SESSION['id_sistema'] = $row['id_usuario'];
			$_SESSION['name_sistema'] = $row['nombre'].' '.$row['apellido'];
			$_SESSION['tipo_sistema'] = $row['tipo_usuario'];
			$_SESSION['email_sistema'] = $row['email'];
			$result->close();
			$mysqli->close();
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: index.php?'.session_name().'='.session_id());
			exit();
			
		} else {
			$icon = 'error';
			$msgbox = 'Usuario y/o Contraseña inválidos';
		}
		$result->close();
		$mysqli->close();
		
		} else {
			$icon = 'error';
			$msgbox = 'Formato de RUT incorrecto';
		}
	} else {
		$icon = 'info';
		$msgbox = 'Para iniciar sesión, es requerido su RUT y Contraseña';
	}

else :
	$icon = 'info';
	$msgbox = 'Ingrese su RUT y Contraseña para iniciar sesión';
endif;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Sistema Corretaje de Propiedades &rsaquo; Log-In</title>
	<link rel="shortcut icon" href="favicon.ico" />
	<link href="css/styles-login.css" type="text/css" rel="stylesheet" media="screen" />
	<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.md5.js"></script>
	<script type="text/javascript">
	$(document).ready(function() {	
		//Encripta la contraseña antes de enviar el formulario
		$('#content-page').on('submit', '#login-form', function(event) {
			var pass_usuario = $('#pass_usuario').val();
			var pass_md5 = $.md5(pass_usuario);
			$('#pass_md5').val(pass_md5);
		});
		
		$('#rut_usuario').focus();
	});
	</script>
</head>
<body>
	<div id="wrap">
		<div id="header">
        <!--[if lt IE 8]>
        <div id="ie">
        <strong>Advertencia:</strong> Este sistema puede no responder como debería en Internet Explorer 7 o inferior, se recomienda usar <a href="http://www.mozilla-europe.org/es/firefox/" target="_blank">Firefox</a>, <a href="http://www.apple.com/es/safari/" target="_blank">Safari</a>, <a href="http://www.opera.com/" target="_blank">Opera</a> o <a href="http://www.google.com/chrome/?hl=es" target="_blank">Chrome</a> para su correcta ejecución.
        </div>
        <![endif]-->
		</div><!-- /header -->
		<div id="content-page">
            <div id="login">
            	<div id="logo">
	                <h1>Sistema Corretaje de Propiedades</h1>
	                <p class="<?php echo $icon;?>"><?php echo $msgbox;?></p>
                </div>
                <form action="login.php" method="post" id="login-form">
                <input type="hidden" name="pass_md5" id="pass_md5" />
					<p>
                    	<label for="rut_usuario">R.U.T. Usuario:</label>
                    	<input type="text" name="rut_usuario" id="rut_usuario" tabindex="1" maxlength="12" />
                    </p>
                    <p>
                    	<label for="pass_usuario">Contraseña:</label>
                    	<input type="password" name="pass_usuario" id="pass_usuario" tabindex="2" maxlength="18" />
                    </p>
                    <p>
						<label for="rememberme">Recordarme:</label>
                    	<input type="checkbox" name="rememberme" id="rememberme" value="yes" tabindex="3" checked="checked" />
                    </p>
                    <p>
						<input type="submit" name="accion" id="submit" value="Iniciar sesión" tabindex="4" />
                    </p>
                </form>
            </div><!-- /body -->
		</div><!-- /content -->
		<div id="footer">
		</div><!-- /footer -->
	</div>
</body>
</html>