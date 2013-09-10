<?php require('connect-mysql.php'); require('functions.php');

function redimensionar_img($source, $destino, $width_d, $height_d, $dimen_in_name_image = true) {
	list($width_s, $height_s, $type, $attr) = getimagesize($source); //obtengo información de la imagen
	$width_d_o = $width_d;
	$height_d_o = $height_d;
	$r_aspect_d = $width_d/$height_d;
	$r_aspect_s = $width_s/$height_s;
	$width_tmp = $height_s*$r_aspect_d;
	$height_tmp = $width_s/$r_aspect_d;
	if( $width_s > $width_tmp ) {
		$x_origen = ($width_s - $width_tmp)/2;
		$width_s = $width_tmp;
	} else{
		$y_origen = ($height_s - $height_tmp)/2;
		$height_s = $height_tmp;
	}

	switch ($type) {
		case 1: $gd_s = imagecreatefromgif($source); break;
		case 2: $gd_s = imagecreatefromjpeg($source); break;
		case 3: $gd_s = imagecreatefrompng($source); break;
		default:
			return false;//die("El formato de imagen no esta dentro del rango valido.");
			break;
	}
	
	$gd_d = imagecreatetruecolor($width_d_o, $height_d_o); //Creamos una imagen en blanco de tamaño $width_d_o y $height_d_o
	$white = imagecolorallocate($gd_d,  255, 255, 255);
	imagefilledrectangle($gd_d, 0, 0, $width_d_o, $height_d_o, $white);
	imagecopyresampled($gd_d, $gd_s, 0, 0, $x_origen, $y_origen, $width_d, $height_d, $width_s, $height_s); //Guardar la imagen redimensionada donde indica la ruta
	
	if ( $dimen_in_name_image == true ) {
		$filename = $destino.'-'.$width_d.'x'.$height_d.'.jpg';
		imagejpeg($gd_d, $filename, 95);
	} else {
		$filename = $destino.'.jpg';
		imagejpeg($gd_d, $filename, 95);
	}
	
	//Liberamos la memoria asociada a la imagen.
	imagedestroy($gd_s);
	imagedestroy($gd_d);
	
	return $filename;
}

//echo '<pre>'; print_r($_FILES); echo '</pre>';

if ( $_GET['accion'] == 'eliminar' && !empty($_GET['foto']) ) :
	$id_foto = $_GET['foto'];
	
	$result = $mysqli->query("SELECT imagen_real, imagen_thumb FROM propiedades_fotos WHERE id=".$id_foto) or die('Error: '.$mysqli->error);
	if ( $row = $result->fetch_assoc() ) :
		$mysqli->query('DELETE FROM propiedades_fotos WHERE id='.$id_foto) or die('Error: '.$mysqli->error);
		chmod($row['imagen_real'], 0777); unlink($row['imagen_real']);
		chmod($row['imagen_thumb'], 0777); unlink($row['imagen_thumb']);
		//$msgbox = 'La fotografía se ha eliminado correctamente.';
		//$icon = 'info';
	endif;
endif;

if ( $_POST['accion'] == 'Subir Foto' ) :

if ( !empty($_POST['ubicacion']) ) :
set_time_limit(0);
if ( is_uploaded_file($_FILES['upimg']['tmp_name']) && $_FILES['upimg']['error'] == UPLOAD_ERR_OK ) : //Si devuelve 4 es porque no se subió nada
	
	$typeAccepted = array("image/jpg", "image/jpeg", "image/gif", "image/png");
	if ( in_array($_FILES["upimg"]["type"], $typeAccepted) ) :
	//if ($_FILES["upimg"]["type"] == "image/jpg" || $_FILES["upimg"]["type"] == "image/jpeg" || $_FILES["upimg"]["type"] == "image/gif" || $_FILES["upimg"]["type"] == "image/png") :
		
		$id_propiedad = $_POST['id_propiedad'];
		$result = $mysqli->query("SELECT tipo_propiedad FROM propiedades WHERE id_propiedad=".$id_propiedad) or die('Error: '.$mysqli->error);
		$row = $result->fetch_assoc();
		$tipo_propiedad = strtolower($row['tipo_propiedad']); //$tipo_propiedad = $_POST['tipo_propiedad'];
		
		//Directorios a usar
		$upload_dir_originales = 'uploads/'.$tipo_propiedad.'/originales/';
		$upload_dir_thumbs = 'uploads/'.$tipo_propiedad.'/thumbs/';
		//Cambiar privilegios de los directorios para acceder y guardar en ellos (Opcional)
		//chmod($upload_dir_originales, 0777);
		//chmod($upload_dir_thumbs, 0777);
		
		$upload_filename = filtrar_nombre_archivo($_FILES['upimg']['name']); //basename($_FILES['upimg']['name']);
		$tmp_image = $_FILES['upimg']['tmp_name'];
		$name_image = $upload_dir_originales.$tipo_propiedad.'-ID'.$id_propiedad.'-'.$upload_filename;
		
		//si la imagen es subida al directorio "originales" del servidor
		if ( copy($tmp_image, $name_image) ) : //move_uploaded_file($tmp_image, $name_image)
			//Generar y guardar un thumbnail en la carpeta "thumbs"
			$ubicacion = strtolower(filtrar_nombre_archivo($_POST['ubicacion']));
			$imagen_real = redimensionar_img($name_image, $upload_dir_thumbs.$tipo_propiedad.'-ID'.$id_propiedad.'-'.$ubicacion, 680, 425, false);
			$imagen_thumb = redimensionar_img($name_image, $upload_dir_thumbs.$tipo_propiedad.'-ID'.$id_propiedad.'-'.$ubicacion, 240, 150);
			
			if ( !empty($imagen_thumb) ) :
			$sql = "INSERT INTO propiedades_fotos (id_propiedad, imagen_real, imagen_thumb, nombre_imagen) VALUES (
					".$id_propiedad.",
					'".$imagen_real."',
					'".$imagen_thumb."',
					'".$_POST['ubicacion']."')";
			$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
			if ( $result == 1 ) :
				$msgbox = 'Fotografía guardada correctamente.';
				$icon = 'info';
				unset($_POST);
			else :
				$id_foto = $mysqli->insert_id; //Recupera el ID generado por la consulta anterior (normalmente INSERT) para una columna AUTO_INCREMENT.
				$mysqli->query('DELETE FROM propiedades_fotos WHERE id='.$id_foto) or die('Error: '.$mysqli->error);
				chmod($name_image, 0777); unlink($name_image);
				chmod($imagen_real, 0777); unlink($imagen_real);
				chmod($imagen_thumb, 0777); unlink($imagen_thumb);
				$msgbox = 'ERROR: Al guardar en la base de datos.';
				$icon = 'error';
			endif;
			else :
				$msgbox = 'ERROR: Al generar thumbnail de la fotografía.';
				$icon = 'error';
			endif;
			
		endif;
		
	else :
		$msgbox = 'ERROR: Formato de archivo no permitido (Ejem. jpg, gif, o png)';
		$icon = 'error';
		unset($_FILES);
	endif;
else :
	$msgbox = 'ERROR: Al subir el archivo.';
	$icon = 'error';
	unset($_FILES);
endif;

else :
	$msgbox = 'ERROR: Debe especificar la ubicación de la fotografía.';
	$icon = 'error';
endif;

endif;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Gestión de fotografías de propiedades</title>
	<link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
	<link rel="shortcut icon" href="favicon.ico" />
	<style type="text/css">
	body { background: #dfdfdf; font-size: 0.7em; }
	</style>
</head>
<body>
			<div class="<?php echo 'msgbox-'.$icon;?>"><?php echo $msgbox; ?></div>
			<div id="content-propiedades" class="content-propiedades-form">
				<form action="propiedades-upload-fotos.php?id_propiedad=<?php echo $_GET['id_propiedad']; ?>&tipo_propiedad=<?php echo $_GET['tipo_propiedad']; ?>" id="propiedades-upload-fotos" method="post" enctype="multipart/form-data">
				<!-- MAX_FILE_SIZE must precede the file input field -->
				<input name="MAX_FILE_SIZE" type="hidden" value="1227248" />
				<input type="hidden" name="accion" id="accion" value="Subir Foto" />
				<input type="hidden" name="tipo_propiedad" id="tipo_propiedad" value="<?php echo $_GET['tipo_propiedad']; ?>" />
				<input type="hidden" name="id_propiedad" id="id_propiedad" value="<?php echo $_GET['id_propiedad']; ?>" />
					<div class="upload-fotos">
						<h2>Subir Foto:</h2>
						<p><strong>Las imágenes deben ser formato jpg, gif, o png y tener un peso máximo de 2MB</strong></p>
						<p>
							<label for="upimg" class="big-name">Seleccione fotografía:</label>
					        <input type="file" name="upimg" id="upimg" class="upload" />
						</p>
						<p>
							<label for="ubicacion" class="big-name">Seleccione ubicación:</label>
							<select name="ubicacion" id="ubicacion">
								<option value="" <?php echo ($_POST['ubicacion'] == '')?'selected="selected"':'';?>>Seleccionar</option>
								<option value="Living" <?php echo ($_POST['ubicacion'] == 'Living')?'selected="selected"':'';?>>Living</option>
								<option value="Comedor" <?php echo ($_POST['ubicacion'] == 'Comedor')?'selected="selected"':'';?>>Comedor</option>
								<option value="Cocina" <?php echo ($_POST['ubicacion'] == 'Cocina')?'selected="selected"':'';?>>Cocina</option>
								<option value="Dormitorio Principal" <?php echo ($_POST['ubicacion'] == 'Dormitorio Principal')?'selected="selected"':'';?>>Dormitorio Principal</option>
								<option value="Dormitorios" <?php echo ($_POST['ubicacion'] == 'Dormitorios')?'selected="selected"':'';?>>Dormitorios</option>
								<option value="Baños" <?php echo ($_POST['ubicacion'] == 'Baños')?'selected="selected"':'';?>>Baños</option>
								<option value="Estar" <?php echo ($_POST['ubicacion'] == 'Estar')?'selected="selected"':'';?>>Estar</option>
								<option value="Terraza" <?php echo ($_POST['ubicacion'] == 'Terraza')?'selected="selected"':'';?>>Terraza</option>
								<option value="Jardín" <?php echo ($_POST['ubicacion'] == 'Jardín')?'selected="selected"':'';?>>Jardín</option>
								<option value="Exterior" <?php echo ($_POST['ubicacion'] == 'Exterior')?'selected="selected"':'';?>>Exterior</option>
								<option value="Otras" <?php echo ($_POST['ubicacion'] == 'Otras')?'selected="selected"':'';?>>Otras</option>
							</select>
						</p>
					</div>
					<hr />
					<div class="botones">
						<p>
							<input type="submit" name="accion" class="submit" value="Subir Foto" />
							<input type="reset" class="button" value="Limpiar" />
						</p>
					</div>
					<hr />
					<center>
					<?php
					if ( !empty($_GET['id_propiedad']) ) {
						$sql = "SELECT id, imagen_real, imagen_thumb, nombre_imagen FROM propiedades_fotos
								WHERE id_propiedad=".$_GET['id_propiedad']." ORDER BY id DESC";
						$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						if ($result->num_rows > 0) {
							while ($row = $result->fetch_array()) {
								echo '<div class="foto-propiedad">';
								echo '<a href="propiedades-upload-fotos.php?id_propiedad='.$_GET['id_propiedad'].'&accion=eliminar&foto='.$row['id'].'" onClick="return confirm(\'¿Está seguro que desea eliminar la fotografía?\');" class="eliminar-foto">Eliminar</a>';
								echo '<img src="'.$row['imagen_thumb'].'" id="foto-'.$row['id'].'" title="'.$row['nombre_imagen'].'" width="200" />';
								echo '<span>'.$row['nombre_imagen'].'</span>';
								echo '</div>';
							}
							$result->close();
						} else {
							echo '<img src="images/no-image.png" id="sin-image" title="Propiedad sin fotografías" width="200" />';
						}
					} else {
						echo '<span>ERROR: No se ha difinido el ID de la propiedad.</span>';
					}
					?>
					</center>
					<div class="clear"></div>
					<hr />
				</form>
			</div>
</body>
</html>