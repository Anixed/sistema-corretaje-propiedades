<?php require_once('connect-mysql.php'); require_once('functions.php'); ?>
<?php
session_start();
//echo '<pre>'; print_r($_POST); echo '</pre>';

//Guarda los campos actuales de la propiedad que se está ingresando en una variable SESION y redirigue a clientes.php
if ( isset($_GET['ingresar_cliente']) && $_GET['ingresar_cliente'] == 'si' ) {
	
	$_SESSION['campos_propiedad'] = array();
	//$_SESSION['campos_propiedad'] = serialize($_POST);
	foreach ($_POST as $key => $value) {
		$_SESSION['campos_propiedad'][$key] = $value;
	}
	//echo '<pre>'; print_r($_SESSION); echo '</pre>';
	
	//header('HTTP/1.1 301 Moved Permanently');
	//header('Location: clientes.php?ingresando_propiedad=si');
	exit();
}

//Si no viene desde el boton volver de la pantalla cliente entonces elimina la variable sesion con los datos de la propiedad
if ( isset($_SESSION['campos_propiedad']) && !(isset($_GET['volver_propiedad']) && $_GET['volver_propiedad'] == 'si') ) {
	//echo "no viene desde el boton volver!!!";
	unset($_SESSION['campos_propiedad']);
}

//Guarda en una variable la página anterior (referente)
if ( !empty($_GET['referer']) ) {
	$referer = $_GET['referer'];
}

//Usados en la posterior generación del código de la propiedad
$tipo_propiedad = ( !empty($_POST['tipo_propiedad']) ) ? $_POST['tipo_propiedad'] : 'Casa' ;
$operacion = ( !empty($_POST['operacion']) ) ? $_POST['operacion'] : 'Venta' ;

//Variable para comprobar si se esta editando el registro
if ( $_GET['accion'] == 'editar_propiedad' || $_POST['accion'] == 'Modificar' ) {
	$editar_propiedad = true;
}

//Comprueba que los campos necesarios esten completos
if ( !$_POST['accion'] || (isset($_SESSION['campos_propiedad']) && $_SESSION['campos_propiedad']['accion'] == 'Guardar') ) :
	//Guarda la variable global POST en una variable local $form, por seguridad (Aunque esto es opcional)
	$form = array();
	
	if ( isset($_SESSION['campos_propiedad']) && $_SESSION['campos_propiedad']['accion'] == 'Guardar' ) {
		
		unset($_SESSION['campos_propiedad']['cod_propiedad']);
		foreach ($_SESSION['campos_propiedad'] as $key => $value) {
			$form[$key] = $value;
		}
		unset($_SESSION['campos_propiedad']);
		
	} else {
		foreach ($_POST as $key => $value) {
			$form[$key] = $value;
		}
		unset($_POST);
	}
else :
	
	if ( isset($_POST['accion']) ) :
		
		$msgbox = array();
		$icon = '';
		$i = 0; $insert_sql_into = array(); $insert_sql_values = array();
		
		if ( empty($_POST['id_propietario']) ) {
			$msgbox[] = 'Especifique el propietario, dueño de la propiedad.';
			$icon = 'error';
		} else { $insert_sql_into[$i] = 'id_propietario'; $insert_sql_values[$i] = $_POST['id_propietario']; ++$i; }
		
		if ( !empty($_POST['nombre_propietario']) ) {
			$insert_sql_into[$i] = 'nombre_propietario'; $insert_sql_values[$i] = '\''.$_POST['nombre_propietario'].'\''; ++$i;
		}
		
		if ( !empty($_POST['tipo_propiedad']) ) {
			$insert_sql_into[$i] = 'tipo_propiedad'; $insert_sql_values[$i] = '\''.$_POST['tipo_propiedad'].'\''; ++$i;
		}
		if ( !empty($_POST['operacion']) ) {
			$insert_sql_into[$i] = 'operacion'; $insert_sql_values[$i] = '\''.$_POST['operacion'].'\''; ++$i;
		}
		
		if ( empty($_POST['valor']) ) {
			$msgbox[] = 'Especifique el valor de la propiedad.';
			$icon = 'error';
		} else {
			$_POST['valor'] = preg_replace('/[^0-9]/','',$_POST['valor']);
			$insert_sql_into[$i] = 'valor'; $insert_sql_values[$i] = '\''.$_POST['valor'].'\''; ++$i;
		}
		
		if ( empty($_POST['tipo_valor']) ) {
			$msgbox[] = 'Especifique el tipo de moneda.';
			$icon = 'error';
		} else { $insert_sql_into[$i] = 'tipo_valor'; $insert_sql_values[$i] = '\''.$_POST['tipo_valor'].'\''; ++$i; }
		
		if ( !empty($_POST['direccion']) ) {
			$insert_sql_into[$i] = 'direccion'; $insert_sql_values[$i] = '\''.trim($_POST['direccion']).'\''; ++$i;
			
			if ( !empty($_POST['num_direccion']) ) {
				$_POST['num_direccion'] = str_replace('#','',$_POST['num_direccion']); //preg_replace('/[#]/','',$_POST['num_direccion']);
				$insert_sql_into[$i] = 'num_direccion'; $insert_sql_values[$i] = '\''.trim($_POST['num_direccion']).'\''; ++$i;
			}
			if ( !empty($_POST['num_depa']) ) {
				$insert_sql_into[$i] = 'num_depa'; $insert_sql_values[$i] = '\''.trim($_POST['num_depa']).'\''; ++$i;
			}
			
		} else {
			$msgbox[] = 'Especifique correctamente la dirección de la propiedad.';
			$icon = 'error';
		}
		
		if ( !empty($_POST['sector']) ) {
			$insert_sql_into[$i] = 'sector'; $insert_sql_values[$i] = '\''.$_POST['sector'].'\''; ++$i;
		}
		
		if ( empty($_POST['comuna']) ) {
			$msgbox[] = 'Especifique la comuna dónde se ubica la propiedad.';
			$icon = 'error';
		} else { $insert_sql_into[$i] = 'comuna'; $insert_sql_values[$i] = '\''.$_POST['comuna'].'\''; ++$i; }
		
		if ( empty($_POST['ciudad']) ) {
			$msgbox[] = 'Especifique la ciudad dónde se ubica la propiedad.';
			$icon = 'error';
		} else { $insert_sql_into[$i] = 'ciudad'; $insert_sql_values[$i] = '\''.trim($_POST['ciudad']).'\''; ++$i; }
		
		if ( !empty($_POST['captador_id']) ) {
			$insert_sql_into[$i] = 'captador_id'; $insert_sql_values[$i] = $_POST['captador_id']; ++$i;
		}
		if ( !empty($_POST['comision_captador']) ) {
			$insert_sql_into[$i] = 'comision_captador'; $insert_sql_values[$i] = $_POST['comision_captador']; ++$i;
		}
		if ( !empty($_POST['exclusividad']) ) {
			$insert_sql_into[$i] = 'exclusividad'; $insert_sql_values[$i] = '\''.$_POST['exclusividad'].'\''; ++$i;
		}
		if ( !empty($_POST['observaciones']) ) {
			$insert_sql_into[$i] = 'observaciones'; $insert_sql_values[$i] = '\''.trim($_POST['observaciones']).'\''; ++$i;
		}
		if ( $_POST['activada'] != '' ) {
			$insert_sql_into[$i] = 'activada'; $insert_sql_values[$i] = $_POST['activada']; ++$i;
		}
		if ( $_POST['publicada'] != '' ) {
			$insert_sql_into[$i] = 'publicada'; $insert_sql_values[$i] = $_POST['publicada']; /*++$i*/;
		}
		
		if ( $icon == 'error' ) {
			$form = array();
			foreach ($_POST as $key => $value) {
				$form[$key] = $value;
			}
			unset($_POST, $insert_sql_into, $insert_sql_values);
		} else {
			
			$i = 0; $caract_sql_into = array(); $caract_sql_values = array();
			$patrones = array('/[^0-9,]/', '/[,]/');
			$sustituciones = array('', '.');
					
			if ( $_POST['dormitorios'] != '' ) {
				$caract_sql_into[$i] = 'dormitorios'; $caract_sql_values[$i] = $_POST['dormitorios']; ++$i;
			}
			if ( $_POST['banos'] != '' ) {
				$caract_sql_into[$i] = 'banos'; $caract_sql_values[$i] = $_POST['banos']; ++$i;
			}
			if ( $_POST['num_pisos'] != '' ) {
				$caract_sql_into[$i] = 'num_pisos'; $caract_sql_values[$i] = $_POST['num_pisos']; ++$i;
			}
			if ( !empty($_POST['orientacion']) ) {
				$caract_sql_into[$i] = 'orientacion'; $caract_sql_values[$i] = '\''.$_POST['orientacion'].'\''; ++$i;
			}
			if ( !empty($_POST['tipo_construccion']) ) {
				$caract_sql_into[$i] = 'tipo_construccion'; $caract_sql_values[$i] = '\''.$_POST['tipo_construccion'].'\''; ++$i;
			}
			if ( !empty($_POST['calefaccion']) ) {
				$caract_sql_into[$i] = 'calefaccion'; $caract_sql_values[$i] = '\''.$_POST['calefaccion'].'\''; ++$i;
			}
			if ( !empty($_POST['superficie_total']) ) {
				$_POST['superficie_total'] = preg_replace($patrones,$sustituciones,$_POST['superficie_total']);
				$caract_sql_into[$i] = 'superficie_total'; $caract_sql_values[$i] = '\''.$_POST['superficie_total'].'\''; ++$i;
			}
			if ( !empty($_POST['superficie_construida']) ) {
				$_POST['superficie_construida'] = preg_replace($patrones,$sustituciones,$_POST['superficie_construida']);
				$caract_sql_into[$i] = 'superficie_construida'; $caract_sql_values[$i] = '\''.$_POST['superficie_construida'].'\''; ++$i;
			}
			if ( $_POST['piso_numero'] != '' ) {
				$caract_sql_into[$i] = 'piso_numero'; $caract_sql_values[$i] = $_POST['piso_numero']; ++$i;
			}
			if ( $_POST['num_estacionamientos'] != '' ) {
				$caract_sql_into[$i] = 'num_estacionamientos'; $caract_sql_values[$i] = $_POST['num_estacionamientos']; ++$i;
			}
			if ( $_POST['num_privados'] != '' ) {
				$caract_sql_into[$i] = 'num_privados'; $caract_sql_values[$i] = $_POST['num_privados']; ++$i;
			}
			if ( $_POST['gastos_comunes'] != '' ) {
				$_POST['gastos_comunes'] = preg_replace('/[^0-9]/','',$_POST['gastos_comunes']);
				$caract_sql_into[$i] = 'gastos_comunes'; $caract_sql_values[$i] = $_POST['gastos_comunes']; ++$i;
			}
			if ( !empty($_POST['planta_libre']) ) {
				$caract_sql_into[$i] = 'planta_libre'; $caract_sql_values[$i] = '\''.$_POST['planta_libre'].'\''; ++$i;
			}
			if ( !empty($_POST['hectas_superficie']) ) {
				$_POST['hectas_superficie'] = preg_replace($patrones,$sustituciones,$_POST['hectas_superficie']);
				$caract_sql_into[$i] = 'hectas_superficie'; $caract_sql_values[$i] = '\''.$_POST['hectas_superficie'].'\''; ++$i;
			}
			if ( !empty($_POST['hectas_empastadas']) ) {
				$_POST['hectas_empastadas'] = preg_replace($patrones,$sustituciones,$_POST['hectas_empastadas']);
				$caract_sql_into[$i] = 'hectas_empastadas'; $caract_sql_values[$i] = '\''.$_POST['hectas_empastadas'].'\''; ++$i;
			}
			if ( !empty($_POST['hectas_riego']) ) {
				$_POST['hectas_riego'] = preg_replace($patrones,$sustituciones,$_POST['hectas_riego']);
				$caract_sql_into[$i] = 'hectas_riego'; $caract_sql_values[$i] = '\''.$_POST['hectas_riego'].'\''; ++$i;
			}
			if ( $_POST['num_potreros'] != '' ) {
				$caract_sql_into[$i] = 'num_potreros'; $caract_sql_values[$i] = $_POST['num_potreros']; ++$i;
			}
			if ( $_POST['num_casas_patronales'] != '' ) {
				$caract_sql_into[$i] = 'num_casas_patronales'; $caract_sql_values[$i] = $_POST['num_casas_patronales']; ++$i;
			}
			if ( $_POST['num_casas_inquilinos'] != '' ) {
				$caract_sql_into[$i] = 'num_casas_inquilinos'; $caract_sql_values[$i] = $_POST['num_casas_inquilinos']; ++$i;
			}
			if ( $_POST['num_bodegas'] != '' ) {
				$caract_sql_into[$i] = 'num_bodegas'; $caract_sql_values[$i] = $_POST['num_bodegas']; ++$i;
			}
			if ( !empty($_POST['clasificacion_suelos']) ) {
				$caract_sql_into[$i] = 'clasificacion_suelos'; $caract_sql_values[$i] = '\''.$_POST['clasificacion_suelos'].'\''; ++$i;
			}
			if ( !empty($_POST['aptitudes']) ) {
				$caract_sql_into[$i] = 'aptitudes'; $caract_sql_values[$i] = '\''.serialize($_POST['aptitudes']).'\''; ++$i;
			}
			if ( !empty($_POST['mtrs_frente']) ) {
				$_POST['mtrs_frente'] = preg_replace($patrones,$sustituciones,$_POST['mtrs_frente']);
				$caract_sql_into[$i] = 'mtrs_frente'; $caract_sql_values[$i] = '\''.$_POST['mtrs_frente'].'\''; ++$i;
			}
			if ( !empty($_POST['mtrs_fondo']) ) {
				$_POST['mtrs_fondo'] = preg_replace($patrones,$sustituciones,$_POST['mtrs_fondo']);
				$caract_sql_into[$i] = 'mtrs_fondo'; $caract_sql_values[$i] = '\''.$_POST['mtrs_fondo'].'\''; ++$i;
			}
			
			$otras_caracteristicas = array();
			if ( !empty($_POST['piscina']) ) $otras_caracteristicas['piscina'] = $_POST['piscina'];
			if ( !empty($_POST['estar']) ) $otras_caracteristicas['estar'] = $_POST['estar'];
			if ( !empty($_POST['suite']) ) $otras_caracteristicas['suite'] = $_POST['suite'];
			if ( !empty($_POST['cable']) ) $otras_caracteristicas['cable'] = $_POST['cable'];
			if ( !empty($_POST['telefono']) ) $otras_caracteristicas['telefono'] = $_POST['telefono'];
			if ( !empty($_POST['cocina_amoblada']) ) $otras_caracteristicas['cocina_amoblada'] = $_POST['cocina_amoblada'];
			if ( !empty($_POST['dependencias']) ) $otras_caracteristicas['dependencias'] = $_POST['dependencias'];
			if ( !empty($_POST['living_comedor']) ) $otras_caracteristicas['living_comedor'] = $_POST['living_comedor'];
			if ( !empty($_POST['bodega']) ) $otras_caracteristicas['bodega'] = $_POST['bodega'];
			if ( !empty($_POST['terraza']) ) $otras_caracteristicas['terraza'] = $_POST['terraza'];
			if ( !empty($_POST['derecho_agua']) ) $otras_caracteristicas['derecho_agua'] = $_POST['derecho_agua'];
			if ( !empty($_POST['sitio']) ) $otras_caracteristicas['sitio'] = $_POST['sitio'];
			
			//serializo el array para guardarlo en la base de datos
			$serialize_array = serialize($otras_caracteristicas);
			$caract_sql_into[$i] = 'otras_caracteristicas'; $caract_sql_values[$i] = '\''.$serialize_array.'\'';
			unset($otras_caracteristicas, $serialize_array);
		}
		
	endif;
endif;

//Guarda la propiedad en la BD
if ($_POST['accion'] == 'Guardar') :
	
	$cod_propiedad = cod_correlativo_propiedades($tipo_propiedad,$operacion); //Genera el código de propiedad a usar
	list($codigo_tipo, $codigo_id) = explode('-', $cod_propiedad); //Divide el código de propiedad
	
	$sql = "INSERT INTO propiedades (
						id_propiedad,
						cod_propiedad,
						".implode(',', $insert_sql_into).",
						fecha_ingreso
						) VALUES (
						".$codigo_id.",
						'".$cod_propiedad."',
						".implode(',', $insert_sql_values).",
						NOW())";
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	unset($insert_sql_into, $insert_sql_values);
	
	if ( $result == 1 ) {
		$id_propiedad = $mysqli->insert_id; //Recupera el ID generado por la consulta anterior (normalmente INSERT) para una columna AUTO_INCREMENT.
		if ( $codigo_id != $id_propiedad ) {
			$mysqli->query('DELETE FROM propiedades WHERE id_propiedad='.$codigo_id) or die('Error: '.$mysqli->error);
			die('Error: Los IDs generados no coinciden.');
		}
		
		// Obtiene y guarda las coordenadas en base a la dirección de la propiedad
		$address = $_POST['direccion'].' '.$_POST['num_direccion'].', '.$_POST['comuna'].', Chile';
		update_coordenadas($id_propiedad, $address);
		
		$sql = "INSERT INTO caracteristicas_".strtolower($_POST['tipo_propiedad'])." (
						id_propiedad,
						".implode(',', $caract_sql_into)."
						) VALUES (
						".$id_propiedad.",
						".implode(',', $caract_sql_values)."
						)";
		$result2 = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		
		if ( $result2 == 1 ) {
			/*$msgbox = 'El Registro ( '.$_POST['tipo_propiedad'].' en '.$_POST['operacion'].' ) Nº'.$id_propiedad.' ( '.$cod_propiedad.' ) se ha guardado satisfactoriamente.
						<a href="propiedades-form.php?accion=editar_propiedad&id_propiedad='.$id_propiedad.'&cod_propiedad='.$cod_propiedad.'&tipo_propiedad='.$_POST['tipo_propiedad'].'&referer=propiedades-grilla" class="submit small">¿Desea modificarla o subir fotografías?</a>';
			$icon = 'info';*/
			
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: propiedades-form.php?accion=editar_propiedad&id_propiedad='.$id_propiedad.'&cod_propiedad='.$cod_propiedad.'&tipo_propiedad='.$_POST['tipo_propiedad'].'&referer=propiedades-grilla&upload_fotos=yes');
			
			unset($_POST, $caract_sql_into, $caract_sql_values);
			//Usados en la posterior generación del código de la propiedad
			$tipo_propiedad = 'Casa';
			$operacion = 'Venta';
		} else {
			$mysqli->query('DELETE FROM propiedades WHERE id_propiedad='.$codigo_id) or die('Error: '.$mysqli->error);
			die('Error: No se han guardado las características en la BD.');
		}
		
	}
	
endif;

//Modificar la propiedad
if ($_POST['accion'] == 'Modificar') :
	$id_propiedad = $_POST['id_propiedad'];
	$cod_propiedad = $_POST['cod_propiedad'];
	$tipo_propiedad = strtolower($_POST['tipo_propiedad']);
	
	if ( existe_registro_propiedad($id_propiedad, 'id_propiedad') ) :
		//Actualiza la propiedad
		$update_sql = '';
		$count = count($insert_sql_into);
		for($i=0; $i<$count; $i++) {
			$update_sql .= $insert_sql_into[$i].'='.$insert_sql_values[$i].', ';
		}
		$update_sql = substr($update_sql, 0, -2);
		$sql = "UPDATE propiedades SET cod_propiedad='".$cod_propiedad."', ".$update_sql." WHERE id_propiedad=".$id_propiedad;
		$mysqli->query($sql) or die('Error: '.$mysqli->error);
		
		// Obtiene y guarda las coordenadas en base a la dirección de la propiedad
		$address = $_POST['direccion'].' '.$_POST['num_direccion'].', '.$_POST['comuna'].', Chile';
		update_coordenadas($id_propiedad, $address);
		
		//Actualiza las caracteristicas
		$update_sql = '';
		$count = count($caract_sql_into);
		for($i=0; $i<$count; $i++) {
			$update_sql .= $caract_sql_into[$i].'='.$caract_sql_values[$i].', ';
		}
		$update_sql = trim($update_sql, ', ');
		$sql = "UPDATE caracteristicas_".$tipo_propiedad." SET ".$update_sql." WHERE id_propiedad=".$id_propiedad;
		$mysqli->query($sql) or die('Error: '.$mysqli->error);
		
		$msgbox = 'El Registro ( '.$_POST['tipo_propiedad'].' en '.$_POST['operacion'].' ) Nº'.$id_propiedad.' ( '.$cod_propiedad.' ) se ha modificado satisfactoriamente. <a href="propiedades.php" class="submit small">Volver al listado</a>';
		$icon = 'info';
	else :
		$msgbox = 'No se ha encontrado la Propiedad Nº'.$id_propiedad.' ( '.$cod_propiedad.' )';
		$icon = 'error';
	endif;
	unset($_POST, $insert_sql_into, $insert_sql_values, $caract_sql_into, $caract_sql_values, $editar_propiedad, $update_sql);
	
	//Usados en la posterior generación del código de la propiedad
	$tipo_propiedad = 'Casa';
	$operacion = 'Venta';
endif;

//Eliminar propiedad
if ( ($_GET['accion'] == 'eliminar_propiedad') && !empty($_GET['id_propiedad']) && !empty($_GET['tipo_propiedad']) ) :
	$id_propiedad = $_GET['id_propiedad'];
	$cod_propiedad = $_GET['cod_propiedad'];
	$tipo_propiedad = strtolower($_GET['tipo_propiedad']);
	$eliminar_propiedad = true;
	
	$sql = "SELECT cod_propiedad, id_propietario, nombre_propietario, tipo_propiedad, operacion, valor, tipo_valor, direccion, num_direccion, num_depa, sector, comuna, ciudad, captador_id, comision_captador, exclusividad, observaciones, activada, publicada, fecha_ingreso,
				caracteristicas_".$tipo_propiedad.".*
				FROM propiedades,caracteristicas_".$tipo_propiedad."
				WHERE caracteristicas_".$tipo_propiedad.".id_propiedad=propiedades.id_propiedad AND
				propiedades.id_propiedad=".$id_propiedad;
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ($form = $result->fetch_assoc()) {
		
		$form['valor'] = number_format($form['valor'],0,',','.');
		if ($form['superficie_total'] > 0) $form['superficie_total'] = number_format($form['superficie_total'],2,',','.');
		if ($form['superficie_construida'] > 0) $form['superficie_construida'] = number_format($form['superficie_construida'],2,',','.');
		if ($form['hectas_superficie'] > 0) $form['hectas_superficie'] = number_format($form['hectas_superficie'],2,',','.');
		if ($form['hectas_empastadas'] > 0) $form['hectas_empastadas'] = number_format($form['hectas_empastadas'],2,',','.');
		if ($form['hectas_riego'] > 0) $form['hectas_riego'] = number_format($form['hectas_riego'],2,',','.');
		if ($form['mtrs_frente'] > 0) $form['mtrs_frente'] = number_format($form['mtrs_frente'],2,',','.');
		if ($form['mtrs_fondo'] > 0) $form['mtrs_fondo'] = number_format($form['mtrs_fondo'],2,',','.');
		
		if ( !empty($form['aptitudes']) ) {
			$unserialize_array = unserialize($form['aptitudes']);
			$form = array_merge($form, $unserialize_array);
			unset($form['aptitudes'], $unserialize_array);
		}
		if ( !empty($form['otras_caracteristicas']) ) {
			$unserialize_array = unserialize($form['otras_caracteristicas']);
			$form = array_merge($form, $unserialize_array);
			unset($form['otras_caracteristicas'], $unserialize_array);
		}
		$patrones = array('/^0$/', '/^0.00$/');
		$sustituciones = array('', '');
		$form = preg_replace($patrones, $sustituciones, $form);
		
		$msgbox = '¿Desea eliminar el Registro Nº'.$_GET['id_propiedad'].' ( '.$_GET['cod_propiedad'].' )? <a href="propiedades-form.php?accion=Eliminar&id_propiedad='.$_GET['id_propiedad'].'&cod_propiedad='.$_GET['cod_propiedad'].'&tipo_propiedad='.$_GET['tipo_propiedad'].'" id="delete-propiedad" class="submit small">Eliminar</a>';
		$icon = 'error';
	} else {
		$eliminar_propiedad = false;
		$msgbox = 'No se ha encontrado la Propiedad Nº'.$id_propiedad.' ( '.$cod_propiedad.' )';
		$icon = 'error';
	}
	$result->close();
	unset($_GET);
endif;

if ( ($_GET['accion'] == 'Eliminar') && !empty($_GET['id_propiedad']) && !empty($_GET['tipo_propiedad']) ) :
	
	// Si no es administrador no puede acceder a eliminar el registro
	require('functions-privilegios-acceso.php');
	if ( !is_admin($_SESSION['id_sistema']) ) :
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: index.php?msgbox=sin_permiso&error=eliminar');
		exit();
	endif;
	// ==============================================================
	
	$id_propiedad = $_GET['id_propiedad'];
	$cod_propiedad = $_GET['cod_propiedad'];
	$tipo_propiedad = strtolower($_GET['tipo_propiedad']);
	
	if ( existe_registro_propiedad($id_propiedad, 'id_propiedad') ) :
		
		if ( !propiedades_operaciones_pendientes($id_propiedad, 'id_propiedad') ) :
			$sql = "DELETE FROM propiedades WHERE id_propiedad=".$id_propiedad;
			$mysqli->query($sql) or die('Error: '.$mysqli->error);
			//$sql = "DELETE FROM caracteristicas_".$tipo_propiedad." WHERE id_propiedad=".$id_propiedad;
			//$mysqli->query($sql) or die('Error: '.$mysqli->error);
			$msgbox = 'El Registro Nº'.$id_propiedad.' ( '.$cod_propiedad.' ) se ha eliminado satisfactoriamente.
						<a href="propiedades.php" class="submit small">Volver al listado</a>';
			$icon = 'info';
		else :
			if ( $_SESSION['tipo_sistema'] == 4 ) {
				$vendedor = '&vendedor='.$_SESSION['id_sistema'];
			}
			$msgbox = 'No se ha podido eliminar la Propiedad Nº'.$id_propiedad.' ( '.$cod_propiedad.' ) porque tiene órdenes de visita pendientes, y/o ya existen registros vinculados a esta propiedad.
						<a href="agenda_visitas.php?accion=visitas_propiedad'.$vendedor.'&id_propiedad='.$id_propiedad.'&cod_propiedad='.$cod_propiedad.'" class="submit small">Ver ordenes de visita</a>';
			$icon = 'error';
		endif;
		
	else :
		$msgbox = 'No se ha encontrado la Propiedad Nº'.$id_propiedad.' ( '.$cod_propiedad.' )';
		$icon = 'error';
	endif;
	unset($_POST, $_GET, $editar_propiedad);
	
	//Usados en la posterior generación del código de la propiedad
	$tipo_propiedad = 'Casa';
	$operacion = 'Venta';
endif;

//Editar propiedad
if ( (($_GET['accion'] == 'editar_propiedad') && !empty($_GET['id_propiedad']) && !empty($_GET['tipo_propiedad']))
		|| (isset($_SESSION['campos_propiedad']) && $_SESSION['campos_propiedad']['accion'] == 'Modificar') ) :
	
	
	if ( !empty($_GET['id_propiedad']) && !empty($_GET['tipo_propiedad']) ) {
		
		$id_propiedad = $_GET['id_propiedad'];
		$cod_propiedad = $_GET['cod_propiedad'];
		$tipo_propiedad = strtolower($_GET['tipo_propiedad']);
		
	} elseif ( $_SESSION['campos_propiedad']['accion'] == 'Modificar' && !empty($_SESSION['campos_propiedad']['id_propiedad']) ) {
		
		$editar_propiedad = true;
		$id_propiedad = $_SESSION['campos_propiedad']['id_propiedad'];
		$cod_propiedad = $_SESSION['campos_propiedad']['cod_propiedad'];
		$tipo_propiedad = strtolower($_SESSION['campos_propiedad']['tipo_propiedad']);
		
	} else {
		echo 'ERROR: Al consultar el ID de la propiedad a editar!';
		exit(); 
	}
	unset($_SESSION['campos_propiedad']);
	
	$sql = "SELECT cod_propiedad, id_propietario, nombre_propietario, tipo_propiedad, operacion, valor, tipo_valor, direccion, num_direccion, num_depa, sector, comuna, ciudad, captador_id, comision_captador, exclusividad, observaciones, activada, publicada, fecha_ingreso,
				caracteristicas_".$tipo_propiedad.".*
				FROM propiedades,caracteristicas_".$tipo_propiedad."
				WHERE caracteristicas_".$tipo_propiedad.".id_propiedad=propiedades.id_propiedad AND
				propiedades.id_propiedad=".$id_propiedad;
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ($form = $result->fetch_assoc()) {
		
		$form['valor'] = number_format($form['valor'],0,',','.');
		if ($form['superficie_total'] > 0) $form['superficie_total'] = number_format($form['superficie_total'],2,',','.');
		if ($form['superficie_construida'] > 0) $form['superficie_construida'] = number_format($form['superficie_construida'],2,',','.');
		if ($form['hectas_superficie'] > 0) $form['hectas_superficie'] = number_format($form['hectas_superficie'],2,',','.');
		if ($form['hectas_empastadas'] > 0) $form['hectas_empastadas'] = number_format($form['hectas_empastadas'],2,',','.');
		if ($form['hectas_riego'] > 0) $form['hectas_riego'] = number_format($form['hectas_riego'],2,',','.');
		if ($form['mtrs_frente'] > 0) $form['mtrs_frente'] = number_format($form['mtrs_frente'],2,',','.');
		if ($form['mtrs_fondo'] > 0) $form['mtrs_fondo'] = number_format($form['mtrs_fondo'],2,',','.');
		
		if ( !empty($form['aptitudes']) ) {
			$unserialize_array = unserialize($form['aptitudes']);
			$form = array_merge($form, $unserialize_array);
			unset($form['aptitudes'], $unserialize_array);
		}
		if ( !empty($form['otras_caracteristicas']) ) {
			$unserialize_array = unserialize($form['otras_caracteristicas']);
			$form = array_merge($form, $unserialize_array);
			unset($form['otras_caracteristicas'], $unserialize_array);
		}
		$patrones = array('/^0$/', '/^0.00$/');
		$sustituciones = array('', '');
		$form = preg_replace($patrones, $sustituciones, $form);
		
		$msgbox = 'Editando la Propiedad Nº'.$form['id_propiedad'].' ( '.$form['cod_propiedad'].' )';
		$icon = 'info';
		
		if ( $_GET['upload_fotos'] == 'yes' ) {
			$msgbox = 'El Registro ( '.$form['tipo_propiedad'].' en '.$form['operacion'].' ) Nº'.$form['id_propiedad'].' ( '.$form['cod_propiedad'].' ) se ha guardado satisfactoriamente. <a href="propiedades.php" class="submit small">Volver al listado</a>';
		}
		
	} else {
		$editar_propiedad = false;
		$msgbox = 'No se ha encontrado la Propiedad Nº'.$id_propiedad.' ( '.$cod_propiedad.' )';
		$icon = 'error';
	}
	$result->close();
	unset($_GET);
endif;

//Si ingresó el nuevo cliente y viene desde el boton volver de la pantalla,
//entonces trae el ID y nombre del usuario para seleccionarlo en el ingreso de la propiedad
if ( isset($_SESSION['propietario']) && (isset($_GET['volver_propiedad']) && $_GET['volver_propiedad'] == 'si') ) {
	$form['id_propietario'] = $_SESSION['propietario']['id'];
	$form['nombre_propietario'] = $_SESSION['propietario']['nombre'];
	unset($_SESSION['propietario']);
} else {
	unset($_SESSION['propietario']);
}
?>
<script type="text/javascript">
$(document).ready(function() {
	
	//Enable draggable functionality on any DOM element.
	//Move the draggable object by clicking on it with the mouse and dragging it anywhere within the viewport.
	//$( "#box-popup" ).draggable();
	
	//Cambia las caracteristicas según el tipo de propiedad seleccionado
    $('.content-propiedades-form').on('change', '#tipo_propiedad', function(event) { //$("#tipo_propiedad").change(function() {
    	//var operacion = $('#operacion').val();
    	//var tipo_propiedad = $(this).val();
    	$('#accion').val('');
        $.ajax({
            type: 'POST',
            url: 'propiedades-form.php',
            data: $('#propiedades-formulario').serialize(),
            success: function(data) {
				$('#content-page').html(data);
            }
        });
        return false;
    });
    
    //Genera el código según la operación entre Venta/Arriendo
    $('.content-propiedades-form').on('change', '#operacion', function(event) { //$("#operacion").change(function() {
    	var operacion = $(this).val(); //$(this).attr('value');
    	var tipo_propiedad = $('#tipo_propiedad').val();
        $.ajax({
            type: 'POST',
            url: 'propiedades-cod_correlativo.php',
            data: 'tipo_propiedad='+tipo_propiedad+'&operacion='+operacion<?php echo ($editar_propiedad == true) ? '+\'&cod_propiedad='.$form['cod_propiedad'].'\'' : '' ;?>,
            success: function(data) {
				$('#cod_propiedad').val(data);
            }
        });
        return false;
    });
    
    //Envía la consulta de la grilla por AJAX
	$('#box-popup').on('submit', '#clientes-grilla', function(event) {
		$.ajax({
			type: 'GET',
			url: $(this).attr('action'),
			data: $(this).serialize(),
			success: function(data) {
				$('.content-popup').html(data);
			}
		});
		return false;
	});
    //Recarga la pagina con AJAX para el paginador de los propietarios
    $('#content-page').on('click', '#paginator #pages a, table.grilla thead tr th a', function(event) {
		var page = $(this).attr('href');
        $.ajax({
            type: 'GET',
            url: page,
            //data: 'page='+page,
            success: function(data) {
				$('.content-popup').html(data);
            }
        });
        return false;
    });
    
    //How can I format numbers as money in JavaScript? http://stackoverflow.com/questions/149055/how-can-i-format-numbers-as-money-in-javascript
	Number.prototype.formatMoney = function(c, d, t) {
		var n = this, c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
		return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
	};
    
    //Convierte el valor de uf a pesos y viceversa
    $('.content-propiedades-form').on('blur', '#valor', valor_conversion);
    $('.content-propiedades-form').on('click', '.tipo_valor', valor_conversion);
    function valor_conversion() {
    	var UF = '<?php echo valor_UF();?>'.replace(/[^0-9,]/g,'').replace(',','.');
    	var valor = $('#valor').val().replace(/[^0-9]/g,'');
    	var tipo_valor = $('.tipo_valor:checked').val(); //$('input[name="tipo_valor"]:checked').val();
		/*$('input[name="tipo_valor"]').each(function() {
			if ( $(this).is(':checked') ) {
				tipo_valor = $(this).val();
				return false;
			}
		});*/
    	
    	if ( tipo_valor == '$' ) {
    		var total = (valor / UF);
    		total = total.formatMoney(0,',','.');
    		total = 'UF '+total+'.-';
    	} else if ( tipo_valor == 'U.F.' ) {
    		var total = (valor * UF);
    		total = total.formatMoney(0,',','.');
    		total = '$ '+total+'.-';
    	}
    	$('#valor-conversion').val(total);
    };
    
    <?php if ( $eliminar_propiedad == true ) { ?>
    //Recarga la página con la accion de eliminar la propiedad
    $('#content-page').on('click', '#delete-propiedad', function(event) { //$("#delete-propiedad").click(function() {
    	var page = $(this).attr('href');
        $.ajax({
            type: 'POST',
            url: page,
            //data: 'page='+page,
            success: function(data) {
                $('#content-propiedades').fadeOut("normal", function() {
					$('#content-page').html(data);
				});
            }
        });
        return false;
    });
    
    //Deshabilita todos los campos en el caso de eliminar la propiedad
	$("#propiedades-formulario input, #propiedades-formulario select, #propiedades-formulario textarea").each(function(count) {
		$(this).prop('disabled', true); //jQuery 1.6+
		//$(this).attr('disabled','disabled'); //jQuery 1.5 and below
		//alert(count);
	});
	$('#cancelar-ingreso').prop('disabled', false);
	$('#buscar_propietario').prop('disabled', false).attr('href','javascript:void(0);');
	$('#ingresar_propietario').prop('disabled', false).attr('href','javascript:void(0);');
	$('#gestionar_sector').prop('disabled', false).attr('href','javascript:void(0);');
    <?php } ?>
	/*---------------------------------------*/
	$('#sector option[value="<?php echo (!empty($form['sector']))?$form['sector']:'';?>"]').attr("selected",true);
	$('#comuna option[value="<?php echo (!empty($form['comuna']))?$form['comuna']:'Temuco';?>"]').attr("selected",true);
	$('#calefaccion option[value="<?php echo (!empty($form['calefaccion']))?$form['calefaccion']:'';?>"]').attr("selected",true);
	$('.activada[value=<?php echo (!empty($form['activada']) || !isset($form['activada']))?1:0;?>]').attr("checked",true);
	$('.publicada[value=<?php echo (!empty($form['publicada']) || !isset($form['publicada']))?1:0;?>]').attr("checked",true);
	/*---------------------------------------*/
});
</script>
				<div class="<?php echo 'msgbox-'.$icon;?>"><?php
					if ( is_array($msgbox) ) {
						echo '<strong>ERROR:</strong><br />';
						echo '- '.implode('<br />- ', $msgbox);
					} else {
						echo $msgbox;
					}
				?></div>
				<div id="box-popup">
					<a href="javascript:void(0);" class="cerrar"><img src="images/close2.png" title="Cerrar" alt="Cerrar" /></a>
					<div class="content-popup"></div>
				</div>
				<div id="content-propiedades" class="content-propiedades-form">
				<form action="propiedades-form.php" method="post" id="propiedades-formulario">
				<?php if ( $editar_propiedad == true ) { ?>
				<input type="hidden" name="accion" id="accion" value="Modificar" />
				<input type="hidden" name="id_propiedad" id="id_propiedad" value="<?php echo $form['id_propiedad']; ?>" />
				<input type="hidden" name="tipo_propiedad" id="tipo_propiedad" value="<?php echo $form['tipo_propiedad']; ?>" />
				<?php } else { ?>
				<input type="hidden" name="accion" id="accion" value="Guardar" />
				<?php } ?>
				<input type="hidden" name="url_referer" id="url_referer" value="<?php echo ( isset($form['url_referer']) ) ? $form['url_referer'] : $referer ; ?>" />
				<input type="hidden" name="url_varsget" id="url_varsget" value="<?php echo ( isset($form['url_varsget']) ) ? $form['url_varsget'] : vars_get($_SERVER["HTTP_REFERER"]) /*getCurrentUrl()*/ ; ?>" />
					<fieldset>
						<h2>Propiedad</h2>
							<p>
								<label for="cod_propiedad">Código:<span>*</span></label>
								<input type="text" id="cod_propiedad" style="font-weight:bold;" size="10" name="cod_propiedad" value="<?php echo ($editar_propiedad == true || $eliminar_propiedad == true) ? $form['cod_propiedad'] : cod_correlativo_propiedades($tipo_propiedad,$operacion); ?>" readonly="readonly" />
							</p>
							<p>
								<label for="tipo_propiedad">Tipo:<span>*</span></label>
								<select name="tipo_propiedad" id="tipo_propiedad" tabindex="1" <?php echo ($editar_propiedad == true) ? 'disabled="disabled"' : '' ; ?>>
                                	<!--option value="" <?php echo ($form['tipo_propiedad'] == '')?'selected="selected"':'';?>>Seleccionar</option-->
                                    <option value="Casa" <?php echo ($form['tipo_propiedad'] == 'Casa')?'selected="selected"':'';?>>Casa</option>
                                    <option value="Departamento" <?php echo ($form['tipo_propiedad'] == 'Departamento')?'selected="selected"':'';?>>Departamento</option>
                                    <option value="Oficina" <?php echo ($form['tipo_propiedad'] == 'Oficina')?'selected="selected"':'';?>>Oficina</option>
                                    <option value="Local" <?php echo ($form['tipo_propiedad'] == 'Local')?'selected="selected"':'';?>>Local</option>
                                    <option value="Parcela" <?php echo ($form['tipo_propiedad'] == 'Parcela')?'selected="selected"':'';?>>Parcela</option>
                                    <option value="Campo" <?php echo ($form['tipo_propiedad'] == 'Campo')?'selected="selected"':'';?>>Campo</option>
                                    <option value="Sitio" <?php echo ($form['tipo_propiedad'] == 'Sitio')?'selected="selected"':'';?>>Sitio</option>
                                    <option value="Bodega" <?php echo ($form['tipo_propiedad'] == 'Bodega')?'selected="selected"':'';?>>Bodega</option>
								</select>
							</p>
							<p>
								<label for="operacion">Operación:<span>*</span></label>
								<select name="operacion" id="operacion" tabindex="2">
                                    <option value="Venta" <?php echo ($form['operacion'] == 'Venta')?'selected="selected"':'';?>>Venta</option>
                                    <option value="Arriendo" <?php echo ($form['operacion'] == 'Arriendo')?'selected="selected"':'';?>>Arriendo</option>
								</select>
							</p>
							<p>
								<label for="tipo_valor" class="no_margin">Moneda:<span>*</span></label>
								<input type="radio" name="tipo_valor" class="tipo_valor" tabindex="3" value="$" <?php echo ($form['tipo_valor'] == '$')?'checked="checked"':'';?>/><span> $ Pesos</span>
								<input type="radio" name="tipo_valor" class="tipo_valor" tabindex="3" value="U.F." <?php echo ($form['tipo_valor'] == 'U.F.')?'checked="checked"':'';?>/><span> U.F.</span>
								<span title="Valor Actual U.F."> ($<?php echo valor_UF();?>)</span>
								<div class="clear"></div>
							</p>
							<p>
								<label for="valor">Valor:<span>*</span></label>
								<input type="text" id="valor" size="14" name="valor" value="<?php echo $form['valor'];?>" tabindex="4" maxlength="18" />
							</p>
							<p>
								<label for="valor">Conversión:</label>
								<input type="text" id="valor-conversion" size="14" name="valor-conversion" value="<?php echo valor_conversion($form['valor'],$form['tipo_valor']);?>" title="Valor convertido de UF &raquo; $ | $ &raquo; UF" disabled="disabled" maxlength="18" style="cursor:pointer;" />
							</p>
							<p>
								<input type="radio" name="activada" class="activada" value="1" /><span> Activa</span>
								<input type="radio" name="activada" class="activada" value="0" /><span> Inactiva</span>
								<span style="background:#E74B2F;color:#E74B2F;margin:0 5px;">|</span>
								<input type="radio" name="publicada" class="publicada" value="1" /><span> Publicar</span>
								<input type="radio" name="publicada" class="publicada" value="0" /><span> Despublicar</span>
								<div class="clear"></div>
							</p>
						<h2>Propietario</h2>
							<p>
								<input type="text" id="nombre_propietario" size="44" name="nombre_propietario" value="<?php echo $form['nombre_propietario'];?>" readonly="readonly" tabindex="5" maxlength="250" />
								<input type="hidden" id="id_propietario" name="id_propietario" value="<?php echo $form['id_propietario'];?>" />
								<br />
								<a href="propietarios-grilla.php" id="buscar_propietario" class="submit small"><?php echo ($editar_propiedad == true) ? 'Cambiar propietario' : 'Buscar propietario' ;?></a>
								<a href="propiedades-form.php?ingresar_cliente=si" id="ingresar_propietario" class="submit small">Ingresar propietario</a>
							</p>
						<h2>Otros datos</h2>
							<p>
								<label for="fecha_ingreso">Fecha Ing.</label>
								<input type="text" id="fecha_ingreso" size="11" name="fecha_ingreso" value="<?php echo ($editar_propiedad == true || $eliminar_propiedad == true) ? mysql_to_normal($form['fecha_ingreso']) : date("d/m/Y") ;?>"<?php echo ($editar_propiedad == true) ? 'readonly="readonly"' : '' ; ?> tabindex="6" maxlength="10" />
							</p>
							<p>
								<label for="exclusividad">Exclusividad:</label>
								<input type="radio" name="exclusividad" value="Si" <?php echo ($form['exclusividad'] == 'Si')?'checked="checked"':'';?> tabindex="7" /><span> Si</span>
								<input type="radio" name="exclusividad" value="No" <?php echo (empty($form['exclusividad']) || $form['exclusividad'] == 'No')?'checked="checked"':'';?> tabindex="7" /><span> No</span>
								<div class="clear"></div>
							</p>
							<p>
								<label for="comision_captador">% Comisión:</label>
								<input type="text" id="comision_captador" size="8" name="comision_captador" value="<?php echo $form['comision_captador'];?>" tabindex="8" maxlength="3" title="% estimado a recibir" />
							</p>
							<p>
								<label for="captador_id">Captador:</label>
								<input type="text" id="captador_id" size="30" name="captador_id" value="<?php echo $form['captador_id'];?>" tabindex="9" maxlength="100" />
							</p>
					</fieldset>
					<fieldset>
						<h2>Ubicación</h2>
							<p>
								<label for="sector">Sector:
								<a href="gestion-sectores.php" id="gestionar_sector"><img src="images/add2.png" title="Ingresar Sector" alt="[+]" /></a>
								</label>
								<!--input type="text" id="sector" size="30" name="sector" value="<?php echo $form['sector'];?>" tabindex="10" maxlength="50" /-->
								<select name="sector" id="sector" tabindex="10">
									<option value="">Ninguno</option>
									<?php
									$sql = "SELECT sector_nombre FROM sectores ORDER BY sector_nombre ASC";
									$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
									if ($result->num_rows > 0) {
										while ($row = $result->fetch_array()) {
											echo '<option value="'.$row['sector_nombre'].'">'.$row['sector_nombre'].'</option>';
										}
										$result->close();
									} ?>
								</select>
							</p>
							<p>
								<!--label for="direccion">Dirección:<span>*</span></label-->
								<span style="display:block;float:left;margin:5px 2px 0 0;">Dirección: Calle<span style="color:#ff0000;">*</span><br />
									<input type="text" id="direccion" size="24" name="direccion" value="<?php echo $form['direccion'];?>" tabindex="11" maxlength="100" />
								</span>
								<span style="display:block;float:left;margin:5px 2px 0 0;">N°<span style="color:#ff0000;">*</span><br />
									<input type="text" id="num_direccion" size="4" name="num_direccion" value="<?php echo $form['num_direccion'];?>" tabindex="11" maxlength="10" />
								</span>
								<span style="display:block;float:left;margin:5px 0 0 0;">Dpto.<br />
									<input type="text" id="num_depa" size="4" name="num_depa" value="<?php echo $form['num_depa'];?>" tabindex="11" maxlength="10" />
								</span>
								<div class="clear"></div>
							</p>
							<p>
								<label for="comuna">Comuna:<span>*</span></label>
								<!--input type="text" id="comuna" size="30" name="comuna" value="<?php echo $form['comuna'];?>" tabindex="12" maxlength="20" /-->
								<select name="comuna" id="comuna" tabindex="12">
									<option value="">Seleccionar</option>
									<?php
									$sql = "SELECT COMUNA_NOMBRE FROM comuna ORDER BY COMUNA_NOMBRE ASC";
									$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
									if ($result->num_rows > 0) {
										while ($row = $result->fetch_array()) {
											/*if ( $form['comuna'] == $row['COMUNA_NOMBRE'] )
												echo '<option value="'.$row['COMUNA_NOMBRE'].'" selected="selected">'.$row['COMUNA_NOMBRE'].'</option>';
											else*/
											echo '<option value="'.$row['COMUNA_NOMBRE'].'">'.$row['COMUNA_NOMBRE'].'</option>';
										}
										$result->close();
									} ?>
								</select>
							</p>
							<p>
								<label for="ciudad">Ciudad:<span>*</span></label>
								<input type="text" id="ciudad" size="30" name="ciudad" value="<?php echo $form['ciudad'];?>" tabindex="13" maxlength="25" />
							</p>
						<h2>Mapa Propiedad</h2>
							<p>
							<?php if ($editar_propiedad == true || $eliminar_propiedad == true) { ?>
								<a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $form['id_propiedad'];?>&cod_propiedad=<?php echo $form['cod_propiedad'];?>" class="submit" id="google-maps-consultar">Ver mapa (Google Maps)</a>
							<?php } else { ?>
								<a href="google_map.php?accion=mapa_propiedad_buscar" class="submit" id="google-maps-search">Ver mapa (Google Maps)</a>
							<?php } ?>
							</p>
						<h2>Observaciones</h2>
							<p>
								<textarea name="observaciones" cols="41" rows="6" tabindex="14"><?php echo $form['observaciones'];?></textarea>
							</p>
					</fieldset>
					<?php if ( $form['tipo_propiedad'] == '' || $form['tipo_propiedad'] == 'Casa' ) : ?>
					<fieldset>
						<h2>Características</h2>
							<p>
								<label for="dormitorios">Dormitorios:</label>
								<input type="text" id="dormitorios" size="4" name="dormitorios" value="<?php echo $form['dormitorios'];?>" tabindex="15" maxlength="2" />
							</p>
							<p>
								<label for="banos">Baños:</label>
								<input type="text" id="banos" size="4" name="banos" value="<?php echo $form['banos'];?>" tabindex="16" maxlength="2" />
							</p>
							<p>
								<label for="num_pisos">Nº de Pisos:</label>
								<input type="text" id="num_pisos" size="4" name="num_pisos" value="<?php echo $form['num_pisos'];?>" tabindex="17" maxlength="2" />
							</p>
							<p>
								<label for="tipo_construccion">Construcción:</label>
								<select name="tipo_construccion" id="tipo_construccion" tabindex="18">
									<option value="" <?php echo ($form['tipo_construccion'] == '')?'selected="selected"':'';?>>Seleccionar</option>
                                    <option value="Sólida" <?php echo ($form['tipo_construccion'] == 'Sólida')?'selected="selected"':'';?>>Sólida</option>
                                    <option value="Mixta" <?php echo ($form['tipo_construccion'] == 'Mixta')?'selected="selected"':'';?>>Mixta</option>
                                    <option value="Ligera" <?php echo ($form['tipo_construccion'] == 'Ligera')?'selected="selected"':'';?>>Ligera</option>
								</select>
							</p>
							<p>
								<label for="calefaccion">Calefacción:</label>
								<select name="calefaccion" id="calefaccion" tabindex="19">
									<option value="">Ninguna</option>
									<?php
									$sql = "SELECT calefaccion_nombre FROM calefaccion ORDER BY calefaccion_nombre ASC";
									$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
									if ($result->num_rows > 0) {
										while ($row = $result->fetch_array()) {
											echo '<option value="'.$row['calefaccion_nombre'].'">'.$row['calefaccion_nombre'].'</option>';
										}
										$result->close();
									} ?>
									<option value="Otra">Otra</option>
								</select>
							</p>
							<p>
								<label for="superficie_total" class="big-name">Superficie total:</label>
								<input type="text" id="superficie_total" size="11" name="superficie_total" value="<?php echo $form['superficie_total'];?>" tabindex="20" maxlength="16" />
								<span>M2</span>
							</p>
							<p>
								<label for="superficie_construida" class="big-name">Superficie construida:</label>
								<input type="text" id="superficie_construida" size="11" name="superficie_construida" value="<?php echo $form['superficie_construida'];?>" tabindex="21" maxlength="16" />
								<span>M2</span>
							</p>
						<h2>Otras Características</h2>
							<p>
								<label for="piscina" class="no_margin">Piscina:</label>
								<input type="checkbox" name="piscina" value="Si" <?php echo ($form['piscina'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="estar" class="no_margin">Estar:</label>
								<input type="checkbox" name="estar" value="Si" <?php echo ($form['estar'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="suite" class="no_margin">Suite:</label>
								<input type="checkbox" name="suite" value="Si" <?php echo ($form['suite'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="cable" class="no_margin">T.V. Cable:</label>
								<input type="checkbox" name="cable" value="Si" <?php echo ($form['cable'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="telefono" class="no_margin">Teléfono:</label>
								<input type="checkbox" name="telefono" value="Si" <?php echo ($form['telefono'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="cocina_amoblada" class="no_margin medium-name">Cocina Amoblada:</label>
								<input type="checkbox" name="cocina_amoblada" value="Si" <?php echo ($form['cocina_amoblada'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="dependencias" class="no_margin medium-name">Dependencias:</label>
								<input type="checkbox" name="dependencias" value="Si" <?php echo ($form['dependencias'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="living_comedor" class="no_margin medium-name">Living/Comedor:</label>
								<input type="radio" name="living_comedor" value="Junto" <?php echo ($form['living_comedor'] == 'Junto')?'checked="checked"':'';?>/><span> Junto</span>
								<input type="radio" name="living_comedor" value="Separado" <?php echo ($form['living_comedor'] == 'Separado')?'checked="checked"':'';?>/><span> Separado</span>
								<div class="clear"></div>
							</p>
					</fieldset>
					<?php elseif ( $form['tipo_propiedad'] == 'Departamento' ) : ?>
					<fieldset>
						<h2>Características</h2>
							<p>
								<label for="dormitorios">Dormitorios:</label>
								<input type="text" id="dormitorios" size="4" name="dormitorios" value="<?php echo $form['dormitorios'];?>" tabindex="15" maxlength="2" />
							</p>
							<p>
								<label for="banos">Baños:</label>
								<input type="text" id="banos" size="4" name="banos" value="<?php echo $form['banos'];?>" tabindex="16" maxlength="2" />
							</p>
							<p>
								<label for="piso_numero">Piso Nº:</label>
								<input type="text" id="piso_numero" size="4" name="piso_numero" value="<?php echo $form['piso_numero'];?>" tabindex="17" maxlength="2" />
							</p>
							<p>
								<label for="num_estacionamientos" class="big-name">Nº Estacionamientos:</label>
								<input type="text" id="num_estacionamientos" size="4" name="num_estacionamientos" value="<?php echo $form['num_estacionamientos'];?>" tabindex="18" maxlength="2" />
							</p>
							<p>
								<label for="orientacion">Orientación:</label>
								<select name="orientacion" id="orientacion" tabindex="19">
									<option value="" <?php echo ($form['orientacion'] == '')?'selected="selected"':'';?>>Seleccionar</option>
                                    <option value="Oriente" <?php echo ($form['orientacion'] == 'Oriente')?'selected="selected"':'';?>>Oriente</option>
                                    <option value="Poniente" <?php echo ($form['orientacion'] == 'Poniente')?'selected="selected"':'';?>>Poniente</option>
                                    <option value="Sur" <?php echo ($form['orientacion'] == 'Sur')?'selected="selected"':'';?>>Sur</option>
                                    <option value="Norte" <?php echo ($form['orientacion'] == 'Norte')?'selected="selected"':'';?>>Norte</option>
                                    <option value="Sur-Oriente" <?php echo ($form['orientacion'] == 'Sur-Oriente')?'selected="selected"':'';?>>Sur-Oriente</option>
                                    <option value="Sur-Poniente" <?php echo ($form['orientacion'] == 'Sur-Poniente')?'selected="selected"':'';?>>Sur-Poniente</option>
                                    <option value="Nor-Oriente" <?php echo ($form['orientacion'] == 'Nor-Oriente')?'selected="selected"':'';?>>Nor-Oriente</option>
                                    <option value="Nor-Poniente" <?php echo ($form['orientacion'] == 'Nor-Poniente')?'selected="selected"':'';?>>Nor-Poniente</option>
                                    <option value="Norte-Sur" <?php echo ($form['orientacion'] == 'Norte-Sur')?'selected="selected"':'';?>>Norte-Sur</option>
                                    <option value="Oriente-Poniente" <?php echo ($form['orientacion'] == 'Oriente-Poniente')?'selected="selected"':'';?>>Oriente-Poniente</option>
                                    <option value="Todas" <?php echo ($form['orientacion'] == 'Todas')?'selected="selected"':'';?>>Todas</option>
								</select>
							</p>
							<p>
								<label for="tipo_construccion">Construcción:</label>
								<select name="tipo_construccion" id="tipo_construccion" tabindex="20">
									<option value="" <?php echo ($form['tipo_construccion'] == '')?'selected="selected"':'';?>>Seleccionar</option>
                                    <option value="Sólida" <?php echo ($form['tipo_construccion'] == 'Sólida')?'selected="selected"':'';?>>Sólida</option>
                                    <option value="Mixta" <?php echo ($form['tipo_construccion'] == 'Mixta')?'selected="selected"':'';?>>Mixta</option>
                                    <option value="Ligera" <?php echo ($form['tipo_construccion'] == 'Ligera')?'selected="selected"':'';?>>Ligera</option>
								</select>
							</p>
							<p>
								<label for="calefaccion">Calefacción:</label>
								<select name="calefaccion" id="calefaccion" tabindex="21">
									<option value="">Ninguna</option>
									<?php
									$sql = "SELECT calefaccion_nombre FROM calefaccion ORDER BY calefaccion_nombre ASC";
									$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
									if ($result->num_rows > 0) {
										while ($row = $result->fetch_array()) {
											echo '<option value="'.$row['calefaccion_nombre'].'">'.$row['calefaccion_nombre'].'</option>';
										}
										$result->close();
									} ?>
									<option value="Otra">Otra</option>
								</select>
							</p>
							<p>
								<label for="superficie_construida" class="big-name">Superficie construida:</label>
								<input type="text" id="superficie_construida" size="11" name="superficie_construida" value="<?php echo $form['superficie_construida'];?>" tabindex="22" maxlength="16" />
								<span>M2</span>
							</p>
							<p>
								<label for="gastos_comunes" class="big-name">Gastos Comunes ($):</label>
								<input type="text" id="gastos_comunes" size="11" name="gastos_comunes" value="<?php echo $form['gastos_comunes'];?>" tabindex="23" maxlength="18" />
							</p>
						<h2>Otras Características</h2>
							<p>
								<label for="estar" class="no_margin">Estar:</label>
								<input type="checkbox" name="estar" value="Si" <?php echo ($form['estar'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="suite" class="no_margin">Suite:</label>
								<input type="checkbox" name="suite" value="Si" <?php echo ($form['suite'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="cable" class="no_margin">T.V. Cable:</label>
								<input type="checkbox" name="cable" value="Si" <?php echo ($form['cable'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="telefono" class="no_margin">Teléfono:</label>
								<input type="checkbox" name="telefono" value="Si" <?php echo ($form['telefono'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="cocina_amoblada" class="no_margin medium-name">Cocina Amoblada:</label>
								<input type="checkbox" name="cocina_amoblada" value="Si" <?php echo ($form['cocina_amoblada'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="dependencias" class="no_margin medium-name">Dependencias:</label>
								<input type="checkbox" name="dependencias" value="Si" <?php echo ($form['dependencias'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="living_comedor" class="no_margin medium-name">Living/Comedor:</label>
								<input type="radio" name="living_comedor" value="Junto" <?php echo ($form['living_comedor'] == 'Junto')?'checked="checked"':'';?>/><span> Junto</span>
								<input type="radio" name="living_comedor" value="Separado" <?php echo ($form['living_comedor'] == 'Separado')?'checked="checked"':'';?>/><span> Separado</span>
								<div class="clear"></div>
							</p>
							<p>
								<label for="bodega" class="no_margin medium-name">Bodega:</label>
								<input type="checkbox" name="bodega" value="Si" <?php echo ($form['bodega'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="terraza" class="no_margin medium-name">Terraza:</label>
								<input type="checkbox" name="terraza" value="Si" <?php echo ($form['terraza'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
					</fieldset>
					<?php elseif ( $form['tipo_propiedad'] == 'Oficina' ) : ?>
					<fieldset>
						<h2>Características</h2>
							<p>
								<label for="planta_libre" class="no_margin">Planta Libre:</label>
								<input type="checkbox" name="planta_libre" value="Si" <?php echo ($form['planta_libre'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="banos">Baños:</label>
								<input type="text" id="banos" size="4" name="banos" value="<?php echo $form['banos'];?>" tabindex="15" maxlength="2" />
							</p>
							<p>
								<label for="num_privados">Nº Privados:</label>
								<input type="text" id="num_privados" size="4" name="num_privados" value="<?php echo $form['num_privados'];?>" tabindex="16" maxlength="2" />
							</p>
							<p>
								<label for="num_estacionamientos" class="big-name">Nº Estacionamientos:</label>
								<input type="text" id="num_estacionamientos" size="4" name="num_estacionamientos" value="<?php echo $form['num_estacionamientos'];?>" tabindex="17" maxlength="2" />
							</p>
							<p>
								<label for="tipo_construccion">Construcción:</label>
								<select name="tipo_construccion" id="tipo_construccion" tabindex="18">
									<option value="" <?php echo ($form['tipo_construccion'] == '')?'selected="selected"':'';?>>Seleccionar</option>
                                    <option value="Sólida" <?php echo ($form['tipo_construccion'] == 'Sólida')?'selected="selected"':'';?>>Sólida</option>
                                    <option value="Mixta" <?php echo ($form['tipo_construccion'] == 'Mixta')?'selected="selected"':'';?>>Mixta</option>
                                    <option value="Ligera" <?php echo ($form['tipo_construccion'] == 'Ligera')?'selected="selected"':'';?>>Ligera</option>
								</select>
							</p>
							<p>
								<label for="calefaccion">Calefacción:</label>
								<select name="calefaccion" id="calefaccion" tabindex="19">
									<option value="">Ninguna</option>
									<?php
									$sql = "SELECT calefaccion_nombre FROM calefaccion ORDER BY calefaccion_nombre ASC";
									$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
									if ($result->num_rows > 0) {
										while ($row = $result->fetch_array()) {
											echo '<option value="'.$row['calefaccion_nombre'].'">'.$row['calefaccion_nombre'].'</option>';
										}
										$result->close();
									} ?>
									<option value="Otra">Otra</option>
								</select>
							</p>
							<p>
								<label for="superficie_construida" class="big-name">Superficie construida:</label>
								<input type="text" id="superficie_construida" size="11" name="superficie_construida" value="<?php echo $form['superficie_construida'];?>" tabindex="20" maxlength="16" />
								<span>M2</span>
							</p>
							<p>
								<label for="gastos_comunes" class="big-name">Gastos Comunes ($):</label>
								<input type="text" id="gastos_comunes" size="11" name="gastos_comunes" value="<?php echo $form['gastos_comunes'];?>" tabindex="21" maxlength="18" />
							</p>
						<h2>Otras Características</h2>
							<p>
								<label for="cable" class="no_margin">T.V. Cable:</label>
								<input type="checkbox" name="cable" value="Si" <?php echo ($form['cable'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="telefono" class="no_margin">Teléfono:</label>
								<input type="checkbox" name="telefono" value="Si" <?php echo ($form['telefono'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
							<p>
								<label for="bodega" class="no_margin">Bodega:</label>
								<input type="checkbox" name="bodega" value="Si" <?php echo ($form['bodega'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
					</fieldset>
					<?php elseif ( $form['tipo_propiedad'] == 'Local' ) : ?>
					<fieldset>
						<h2>Características</h2>
							<p>
								<label for="banos">Baños:</label>
								<input type="text" id="banos" size="4" name="banos" value="<?php echo $form['banos'];?>" tabindex="15" maxlength="2" />
							</p>
							<p>
								<label for="num_pisos">Nº Pisos:</label>
								<input type="text" id="num_pisos" size="4" name="num_pisos" value="<?php echo $form['num_pisos'];?>" tabindex="16" maxlength="2" />
							</p>
							<p>
								<label for="num_privados">Nº Privados:</label>
								<input type="text" id="num_privados" size="4" name="num_privados" value="<?php echo $form['num_privados'];?>" tabindex="17" maxlength="2" />
							</p>
							<p>
								<label for="num_estacionamientos" class="big-name">Nº Estacionamientos:</label>
								<input type="text" id="num_estacionamientos" size="4" name="num_estacionamientos" value="<?php echo $form['num_estacionamientos'];?>" tabindex="18" maxlength="2" />
							</p>
							<p>
								<label for="tipo_construccion">Construcción:</label>
								<select name="tipo_construccion" id="tipo_construccion" tabindex="19">
									<option value="" <?php echo ($form['tipo_construccion'] == '')?'selected="selected"':'';?>>Seleccionar</option>
                                    <option value="Sólida" <?php echo ($form['tipo_construccion'] == 'Sólida')?'selected="selected"':'';?>>Sólida</option>
                                    <option value="Mixta" <?php echo ($form['tipo_construccion'] == 'Mixta')?'selected="selected"':'';?>>Mixta</option>
                                    <option value="Ligera" <?php echo ($form['tipo_construccion'] == 'Ligera')?'selected="selected"':'';?>>Ligera</option>
								</select>
							</p>
							<p>
								<label for="calefaccion">Calefacción:</label>
								<select name="calefaccion" id="calefaccion" tabindex="20">
									<option value="">Ninguna</option>
									<?php
									$sql = "SELECT calefaccion_nombre FROM calefaccion ORDER BY calefaccion_nombre ASC";
									$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
									if ($result->num_rows > 0) {
										while ($row = $result->fetch_array()) {
											echo '<option value="'.$row['calefaccion_nombre'].'">'.$row['calefaccion_nombre'].'</option>';
										}
										$result->close();
									} ?>
									<option value="Otra">Otra</option>
								</select>
							</p>
							<p>
								<label for="superficie_construida" class="big-name">Superficie construida:</label>
								<input type="text" id="superficie_construida" size="11" name="superficie_construida" value="<?php echo $form['superficie_construida'];?>" tabindex="21" maxlength="16" />
								<span>M2</span>
							</p>
						<h2>Otras Características</h2>
							<p>
								<label for="bodega" class="no_margin">Bodega:</label>
								<input type="checkbox" name="bodega" value="Si" <?php echo ($form['bodega'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
					</fieldset>
					<?php elseif ( $form['tipo_propiedad'] == 'Parcela' || $form['tipo_propiedad'] == 'Campo' ) : ?>
					<fieldset>
						<h2>Características</h2>
							<p>
								<label for="hectas_superficie" class="medium-name">Superficie Hás.:</label>
								<input type="text" id="hectas_superficie" size="11" name="hectas_superficie" value="<?php echo $form['hectas_superficie'];?>" tabindex="15" maxlength="16" />
							</p>
							<p>
								<label for="hectas_empastadas" class="medium-name">Hás. Empastadas:</label>
								<input type="text" id="hectas_empastadas" size="11" name="hectas_empastadas" value="<?php echo $form['hectas_empastadas'];?>" tabindex="16" maxlength="16" />
							</p>
							<p>
								<label for="hectas_riego">Hás. Riego:</label>
								<input type="text" id="hectas_riego" size="11" name="hectas_riego" value="<?php echo $form['hectas_riego'];?>" tabindex="17" maxlength="16" />
							</p>
							<p>
								<label for="num_potreros">Nº Potreros:</label>
								<input type="text" id="num_potreros" size="4" name="num_potreros" value="<?php echo $form['num_potreros'];?>" tabindex="18" maxlength="2" />
							</p>
							<p>
								<label for="num_casas_patronales" class="big-name">Nº Casas Patronales:</label>
								<input type="text" id="num_casas_patronales" size="4" name="num_casas_patronales" value="<?php echo $form['num_casas_patronales'];?>" tabindex="19" maxlength="2" />
							</p>
							<p>
								<label for="num_casas_inquilinos" class="big-name">Nº Casas Inquilinos:</label>
								<input type="text" id="num_casas_inquilinos" size="4" name="num_casas_inquilinos" value="<?php echo $form['num_casas_inquilinos'];?>" tabindex="20" maxlength="2" />
							</p>
							<p>
								<label for="num_bodegas">Nº Bodegas:</label>
								<input type="text" id="num_bodegas" size="4" name="num_bodegas" value="<?php echo $form['num_bodegas'];?>" tabindex="21" maxlength="2" />
							</p>
							<p>
								<label for="clasificacion_suelos" class="big-name">Clasificación Suelos:</label>
								<select name="clasificacion_suelos" id="clasificacion_suelos" class="medium" tabindex="22">
									<option value="" <?php echo ($form['clasificacion_suelos'] == '')?'selected="selected"':'';?>>Ninguna</option>
                                    <option value="Trumao" <?php echo ($form['clasificacion_suelos'] == 'Trumao')?'selected="selected"':'';?>>Trumao</option>
                                    <option value="Arcilloso" <?php echo ($form['clasificacion_suelos'] == 'Arcilloso')?'selected="selected"':'';?>>Arcilloso</option>
                                    <option value="Terraza Fluvial" <?php echo ($form['clasificacion_suelos'] == 'Terraza Fluvial')?'selected="selected"':'';?>>Terraza Fluvial</option>
                                    <option value="Turistico" <?php echo ($form['clasificacion_suelos'] == 'Turistico')?'selected="selected"':'';?>>Turistico</option>
                                    <option value="G3-31" <?php echo ($form['clasificacion_suelos'] == 'G3-31')?'selected="selected"':'';?>>G3-31</option>
								</select>
							</p>
							<p>
								<label for="aptitudes" class="no_margin medium-name">Aptitudes:</label>
								<div class="clear"></div>
								<input type="checkbox" name="aptitudes[0]" value="Agrícola" <?php echo ($form['aptitudes'][0] == 'Agrícola')?'checked="checked"':'';?>/><span> Agrícola</span>
								<input type="checkbox" name="aptitudes[1]" value="Forestal" <?php echo ($form['aptitudes'][1] == 'Forestal')?'checked="checked"':'';?>/><span> Forestal</span>
								<input type="checkbox" name="aptitudes[2]" value="Ganadera" <?php echo ($form['aptitudes'][2] == 'Ganadera')?'checked="checked"':'';?>/><span> Ganadera</span>
								<input type="checkbox" name="aptitudes[3]" value="Turística" <?php echo ($form['aptitudes'][3] == 'Turística')?'checked="checked"':'';?>/><span> Turística</span>
								<div class="clear"></div>
							</p>
						<h2>Otras Características</h2>
							<p>
								<label for="derecho_agua" class="no_margin medium-name">Derechos Agua:</label>
								<input type="checkbox" name="derecho_agua" value="Si" <?php echo ($form['derecho_agua'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
					</fieldset>
					<?php elseif ( $form['tipo_propiedad'] == 'Sitio' ) : ?>
					<fieldset>
						<h2>Características</h2>
							<p>
								<label for="superficie_total" class="medium-name">Superficie total:</label>
								<input type="text" id="superficie_total" size="11" name="superficie_total" value="<?php echo $form['superficie_total'];?>" tabindex="15" maxlength="16" />
								<span>M2</span>
							</p>
							<p>
								<label for="mtrs_frente">Mtrs. Frente:</label>
								<input type="text" id="mtrs_frente" size="11" name="mtrs_frente" value="<?php echo $form['mtrs_frente'];?>" tabindex="16" maxlength="16" />
							</p>
							<p>
								<label for="mtrs_fondo">Mtrs. Fondo:</label>
								<input type="text" id="mtrs_fondo" size="11" name="mtrs_fondo" value="<?php echo $form['mtrs_fondo'];?>" tabindex="17" maxlength="16" />
							</p>
					</fieldset>
					<?php elseif ( $form['tipo_propiedad'] == 'Bodega' ) : ?>
					<fieldset>
						<h2>Características</h2>
							<p>
								<label for="banos">Baños:</label>
								<input type="text" id="banos" size="4" name="banos" value="<?php echo $form['banos'];?>" tabindex="15" maxlength="2" />
							</p>
							<p>
								<label for="num_privados">Nº Privados:</label>
								<input type="text" id="num_privados" size="4" name="num_privados" value="<?php echo $form['num_privados'];?>" tabindex="16" maxlength="2" />
							</p>
							<p>
								<label for="num_estacionamientos" class="big-name">Nº Estacionamientos:</label>
								<input type="text" id="num_estacionamientos" size="4" name="num_estacionamientos" value="<?php echo $form['num_estacionamientos'];?>" tabindex="17" maxlength="2" />
							</p>
							<p>
								<label for="tipo_construccion">Construcción:</label>
								<select name="tipo_construccion" id="tipo_construccion" tabindex="18">
									<option value="" <?php echo ($form['tipo_construccion'] == '')?'selected="selected"':'';?>>Seleccionar</option>
                                    <option value="Sólida" <?php echo ($form['tipo_construccion'] == 'Sólida')?'selected="selected"':'';?>>Sólida</option>
                                    <option value="Mixta" <?php echo ($form['tipo_construccion'] == 'Mixta')?'selected="selected"':'';?>>Mixta</option>
                                    <option value="Ligera" <?php echo ($form['tipo_construccion'] == 'Ligera')?'selected="selected"':'';?>>Ligera</option>
								</select>
							</p>
							<p>
								<label for="superficie_total" class="big-name">Superficie total:</label>
								<input type="text" id="superficie_total" size="11" name="superficie_total" value="<?php echo $form['superficie_total'];?>" tabindex="19" maxlength="16" />
								<span>M2</span>
							</p>
							<p>
								<label for="superficie_construida" class="big-name">Superficie construida:</label>
								<input type="text" id="superficie_construida" size="11" name="superficie_construida" value="<?php echo $form['superficie_construida'];?>" tabindex="20" maxlength="16" />
								<span>M2</span>
							</p>
							<p>
								<label for="mtrs_frente">Mtrs. Frente:</label>
								<input type="text" id="mtrs_frente" size="11" name="mtrs_frente" value="<?php echo $form['mtrs_frente'];?>" tabindex="21" maxlength="16" />
							</p>
							<p>
								<label for="mtrs_fondo">Mtrs. Fondo:</label>
								<input type="text" id="mtrs_fondo" size="11" name="mtrs_fondo" value="<?php echo $form['mtrs_fondo'];?>" tabindex="22" maxlength="16" />
							</p>
						<h2>Otras Características</h2>
							<p>
								<label for="sitio" class="no_margin">Sitio:</label>
								<input type="checkbox" name="sitio" value="Si" <?php echo ($form['sitio'] == 'Si')?'checked="checked"':'';?>/>
								<div class="clear"></div>
							</p>
					</fieldset>
					<?php endif; ?>
					<fieldset>
						<h2>Fotos 
							<?php if ( $editar_propiedad == true ) { ?>
							<button type="button" id="gestion-fotos"><img src="images/image.png" width="16" height="16" /><span> Gestión</span></button>
							<?php } ?>
						</h2>
						<div class="fotos-propiedades">
							<?php
							if ( $editar_propiedad == true || $eliminar_propiedad == true ) {
								include('propiedades-lista-fotos.php');
							} else {
								echo '<img src="images/no-image.png" id="sin-image" title="Propiedad sin fotografías" width="190" />';
								echo '<center>Primero debe guardar la propiedad para luego subir las fotografías.</center>';
							}
							?>
						</div>
					</fieldset>
					<div class="clear"></div>
					<div class="botones">
						<p>
							<?php if ( $editar_propiedad == true ) { ?>
							<input type="submit" name="accion" class="submit" value="Modificar Registro" tabindex="9" />
							<input type="button" id="cancelar-ingreso" class="button" value="Cancelar" tabindex="10" />
							<?php } elseif ( $eliminar_propiedad == true ) { ?>
							<input type="button" id="cancelar-ingreso" class="button" value="Cancelar" tabindex="10" />
							<?php } else { ?>
							<input type="submit" name="accion" class="submit" value="Guardar Registro" tabindex="9" />
							<input type="button" id="cancelar-ingreso" class="button" value="Cancelar" tabindex="10" />
							<input type="reset" class="button" value="Limpiar Campos" tabindex="10" onclick="try{document.getElementById('tipo_propiedad').focus();}catch(e){}" />
							<?php } ?>
						</p>
					</div>
				</form>
				</div><!--/content-propiedades-form-->