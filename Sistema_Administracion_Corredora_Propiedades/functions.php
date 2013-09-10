<?php
function dateZone($format, $zone = 0) {
	return date($format, time() - date('Z') + $zone*3600); 
}

function mysql_to_normal($fecha) {
	list($fecha, $hora) = explode(' ', $fecha);
	
	if ( preg_match('/[0-9]{4}[-][0-9]{2}[-][0-9]{2}/', $fecha) ) {
		$f = split('-', $fecha);
		return $f[2].'/'.$f[1].'/'.$f[0];
	} else {
		return $fecha;
	}
	
}

function filtrar_nombre_archivo($string) {
	$acentuadas = array('á', 'à', 'ä', 'â', 'ã', 'Á', 'À', 'Â', 'Ä', 'Ã',
						'é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë',
						'í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î',
						'ó', 'ò', 'ö', 'ô', 'õ', 'Ó', 'Ò', 'Ö', 'Ô', 'Õ',
						'ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü',
						'ñ', 'Ñ', 'ç', 'Ç');
	$normales = array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A',
						'e', 'e', 'e', 'e', 'E', 'E', 'E', 'E',
						'i', 'i', 'i', 'i', 'I', 'I', 'I', 'I',
						'o', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'O',
						'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U',
						'n', 'N', 'c', 'C');
	$string = str_replace($acentuadas, $normales, $string);
	return preg_replace('/[^0-9a-zA-Z_.-]/', '_', $string);
}

function title_page($page_name, $title_page=false) {
	$title = basename($page_name, '.php');
	$title = preg_replace("/.php(.)*/i","",$title);
	if ( $title_page == true ) {
		$title = preg_split("/[_-]+/", $title);
		$title = join(" ", $title);
		$title = ucfirst($title);
		return $title.' | Sistema Corretaje de Propiedades';
	} else {
		return $title;
	}
}

//Pagina los resultados de la consulta con la clase paginador
function paginar_resultados($sql,$items_page = 30) {
	global $mysqli;
	
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	if ($result->num_rows > 0) :
		require('class/paginator.class.php');
		$pages = new Paginator;
		$pages->items_per_page = $items_page;
		$pages->items_total = $result->num_rows;
		$pages->mid_range = 9;
		$pages->paginate();
		echo '<div id="paginator">';
		echo '<div id="total_pages">Página: '.$pages->current_page.' de '.$pages->num_pages.'</div>';
		echo '<div id="pages">'.$pages->display_pages().'</div>';
		echo '</div>';
		return $pages->limit;
	endif;
}

//Código correlativo para las propiedades
function cod_correlativo_propiedades($tipo_propiedad,$operacion,$cod_propiedad='') {
	global $mysqli;
	
	if ( !empty($cod_propiedad) ) {
		
		list($codigo_tipo, $codigo_id) = explode('-', $cod_propiedad);
		if ( $operacion == 'Venta' ) $operacion = 'V';
		elseif ( $operacion == 'Arriendo' ) $operacion = 'A';
		return substr($codigo_tipo, 0, -1).$operacion.'-'.$codigo_id;
		
	} else {
		//Select last_insert_id() as id_propiedad from propiedades limit 1;
		//SELECT id_propiedad FROM propiedades ORDER BY id_propiedad DESC LIMIT 1;
		//SELECT max(id_propiedad) FROM propiedades
		
		//Obtener el último auto_increment generado para la tabla
		$sql = "SELECT `AUTO_INCREMENT`
				FROM INFORMATION_SCHEMA.TABLES
				WHERE TABLE_SCHEMA = 'atromber_sistema' AND TABLE_NAME = 'propiedades'";
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		if ( $row = $result->fetch_row() ) {
			
			if ( $operacion == 'Venta' ) $operacion = 'V';
			elseif ( $operacion == 'Arriendo' ) $operacion = 'A';
			
			switch ( $tipo_propiedad ) {
				case 'Casa':
					return 'R'.$operacion.'-'.($row[0]);
					break;
				case 'Departamento':
					return 'D'.$operacion.'-'.($row[0]);
					break;
				case 'Oficina':
					return 'O'.$operacion.'-'.($row[0]);
					break;
				case 'Local':
					return 'L'.$operacion.'-'.($row[0]);
					break;
				case 'Parcela':
					return 'P'.$operacion.'-'.($row[0]);
					break;
				case 'Campo':
					return 'C'.$operacion.'-'.($row[0]);
					break;
				case 'Sitio':
					return 'S'.$operacion.'-'.($row[0]);
					break;
				case 'Bodega':
					return 'B'.$operacion.'-'.($row[0]);
					break;
				default:
					return 'ERROR: 0';
			}
		} else {
			return 'ERROR: 0';
		}
		$result->close();
	}
}

function valor_UF() {
	global $mysqli;
	$sql = "SELECT UF FROM administracion WHERE id=1";
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	if ($row = $result->fetch_assoc()) {
		return number_format($row['UF'],2,',','.');
	} else {
		return 'ERROR! No disponible';
	}
}
//Convierte el valor de uf a pesos y viceversa
function valor_conversion($valor,$moneda) {
	if ( !empty($valor) && !empty($moneda) ) {
		global $mysqli;
		$total = 0;
		
		$sql = "SELECT UF FROM administracion WHERE id=1";
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		if ($row = $result->fetch_assoc()) {
			$valor = str_replace('.', '', $valor);
			if ( $moneda == '$' ) {
				$total = ($valor / $row['UF']);
				return 'UF '.number_format($total,0,',','.').'.-';
			} elseif ( $moneda == 'U.F.' ) {
				$total = ($valor * $row['UF']);
				return '$ '.number_format($total,0,',','.').'.-';
			}
		} else {
			return 'ERROR! No disponible';
		}
	} else return 0;
}

//Coloca los puntos en el lugar correspondiente para mostrar el rut con el formato correcto.
function formato_rut($rut) {
	list($rut, $digi_verificador) = explode('-', $rut);
	//$count = strlen($rut);
	return substr($rut, 0, -6).'.'.substr($rut, -6, 3).'.'.substr($rut, -3).'-'.$digi_verificador;
}

//Obtiene la URL actual (toda) con las variables get incluidas
function getCurrentUrl() {
	$domain = $_SERVER['HTTP_HOST'];
	$url = "http://" . $domain . $_SERVER['REQUEST_URI'];
	return $url;
}

//Obtiene las variables get (hash) de la URL
function vars_get($url) {
	list($url_script, $hash_get) = explode('?', $url);
	
	if ( !empty($hash_get) ) return '?'.$hash_get;
}

//Comprueba si el rut o email del cliente ya existe en la BD, o también verifica si el cliente existe
function existe_registro_cliente($valor, $campo) {
	global $mysqli;
	
	$sql = "SELECT id_cliente
			FROM clientes
			WHERE ".$campo."='".$valor."'";
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ($result->num_rows > 0) {
		return true;
	} else {
		return false;
	}
}
//Comprueba si el rut o email del usuario ya existe en la BD, o también verifica si el usuario existe
function existe_registro_usuario($valor, $campo) {
	global $mysqli;
	
	$sql = "SELECT id_usuario
			FROM usuarios
			WHERE ".$campo."='".$valor."'";
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ($result->num_rows > 0) {
		return true;
	} else {
		return false;
	}
}
//Comprueba si la propiedad solicitada existe
function existe_registro_propiedad($valor, $campo) {
	global $mysqli;
	
	$sql = "SELECT id_propiedad
			FROM propiedades
			WHERE ".$campo."='".$valor."'";
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ($result->num_rows > 0) {
		return true;
	} else {
		return false;
	}
}

//Obtiene el nombre y apellido del usuario
function nombre_usuario($id_usuario) {
	global $mysqli;
	$sql = "SELECT CONCAT(nombre,' ',apellido) AS nombre FROM usuarios WHERE id_usuario=".$id_usuario;
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	if ($row = $result->fetch_assoc()) {
		return $row['nombre'];
	} else {
		return 'ERROR: Usuario no encontrado.';
	}
}
//Obtiene el nombre y apellido del cliente
function nombre_cliente($id_cliente) {
	global $mysqli;
	$sql = "SELECT CONCAT(nombre_cliente,' ',apellidos_cliente) AS nombre FROM clientes WHERE id_cliente=".$id_cliente;
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	if ($row = $result->fetch_assoc()) {
		return $row['nombre'];
	} else {
		return 'ERROR: Cliente no encontrado.';
	}
}

//Comprueba si la propiedad tiene operaciones pendientes, Ejem: órdenes de visitas activas
//por lo que primero hay que esperar a que se elimine la orden de visita y despues proceder a eliminar la propiedad
function propiedades_operaciones_pendientes($valor, $campo) {
	global $mysqli;
	
	$sql = "SELECT id_visita
			FROM agenda_visitas
			WHERE ".$campo."='".$valor."'"; // AND estado='1' si el estado de la orden de visita es pendiente
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ($result->num_rows > 0) {
		return true;
	} else {
		return false;
	}
}
//Comprueba si el usuario tiene operaciones pendientes, Ejem: órdenes de visitas activas
//por lo que primero hay que esperar a que se elimine la orden de visita y despues proceder a eliminar al usuario
function usuario_operaciones_pendientes($valor, $campo) {
	global $mysqli;
	
	$sql = "SELECT id_visita
			FROM agenda_visitas
			WHERE ".$campo."='".$valor."'"; // AND estado='1' si el estado de la orden de visita es pendiente
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ($result->num_rows > 0) {
		return true;
	} else {
		return false;
	}
}
//Comprueba si el cliente tiene propiedades registradas en el sistema u órdenes de visita pendientes
//por lo que primero hay que eliminar sus propiedades, eliminar sus ordenes de visita, y despues proceder a eliminarlo a él
function cliente_tiene_propiedades($valor, $campo) {
	global $mysqli;
	
	$sql = "SELECT id_propiedad
			FROM propiedades
			WHERE ".$campo."='".$valor."'";
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ($result->num_rows > 0) {
		return true;
	} else {
		$sql = "SELECT id_visita FROM agenda_visitas WHERE id_cliente='".$valor."'"; // AND estado='1' si el estado de la orden de visita es pendiente
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);	
		if ($result->num_rows > 0) { return true; }
		else                       { return false; }
	}
}
//Consulta si el cliente tiene propiedades, para especificar qué tipo de usuario es
function propiedades_cliente($id_cliente,$tipo_consulta) {
	global $mysqli;
	
	if ( $tipo_consulta == 'arrendatario' ) {
		$sql = "SELECT id_propiedad FROM propiedades WHERE id_propietario=".$id_cliente;
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		if ($result->num_rows > 0) :
			return true;
		else :
			return false;
		endif;
	}
		$sql = "SELECT id_propiedad FROM propiedades,clientes
				WHERE clientes.id_cliente=propiedades.id_propietario AND id_propietario=".$id_cliente;
	
}

//Genera el intervalo de horas entre la hora de entrada y la hora de salida
function MostrarIntervalo($HoraDesde, $HoraHasta, $Minutos_aumentar=45) {
	$hora = array();
	
	if ( $HoraDesde['hora'] <= 9 ) {
		$HoraDesde['hora'] = '0'.$HoraDesde['hora'];
	}
	$hora[] = $HoraDesde['hora'].":".$HoraDesde['min'];
	
	do {
		$HoraDesde['min'] = $HoraDesde['min'] + $Minutos_aumentar;
		if ($HoraDesde['min'] >= 60) {
			$HoraDesde['min'] = '00';
			$HoraDesde['hora'] = $HoraDesde['hora'] + 1;
			if ($HoraDesde['hora'] >= 24) {
				$HoraDesde['hora'] = 0;
			}
		}
		
		if ( $HoraDesde['hora'] <= 9 && !is_string($HoraDesde['hora']) ) {
			$HoraDesde['hora'] = '0'.$HoraDesde['hora'];
		}
		$hora[] = $HoraDesde['hora'].":".$HoraDesde['min'];
		
		if ( ($HoraDesde['hora'] == $HoraHasta['hora']) and (intval($HoraDesde['min']) == intval($HoraHasta['min'])) ) {
			break;
		}
		
	} while (true);
	
	return $hora;
}
//Genera el horario disponible para la propiedad
function horario_propiedad($id_propiedad,$fecha_visita,$id_vendedor,$id_cliente) {
	global $mysqli;
	$options = '';
	$horas = array(); $horas_ocupadas = array();
	
	$horas = MostrarIntervalo(array('hora'=>9,'min'=>'00'), array('hora'=>18,'min'=>'00'), 30);
	
	//Consulta de horarios de la propiedad
	$sql = "SELECT TIME_FORMAT(hora_in, '%H:%i') AS hora_in, TIME_FORMAT(hora_out, '%H:%i') AS hora_out FROM agenda_visitas
			WHERE id_propiedad=".$id_propiedad." AND fecha_visita='".$fecha_visita."'";
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	while ($row = $result->fetch_assoc()) {
		$horas_ocupadas[] = $row['hora_in'];
		$horas_ocupadas[] = $row['hora_out'];
	}
	//Consulta de horarios del vendedor
	if ( !empty($id_vendedor) ) {
		$sql = "SELECT TIME_FORMAT(hora_in, '%H:%i') AS hora_in, TIME_FORMAT(hora_out, '%H:%i') AS hora_out FROM agenda_visitas
				WHERE id_vendedor=".$id_vendedor." AND fecha_visita='".$fecha_visita."'";
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		while ($row = $result->fetch_assoc()) {
			$horas_ocupadas[] = $row['hora_in'];
			$horas_ocupadas[] = $row['hora_out'];
		}
	}
	//Consulta de horarios del cliente
	if ( !empty($id_cliente) ) {
		$sql = "SELECT TIME_FORMAT(hora_in, '%H:%i') AS hora_in, TIME_FORMAT(hora_out, '%H:%i') AS hora_out FROM agenda_visitas
				WHERE id_cliente=".$id_cliente." AND fecha_visita='".$fecha_visita."'";
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		while ($row = $result->fetch_assoc()) {
			$horas_ocupadas[] = $row['hora_in'];
			$horas_ocupadas[] = $row['hora_out'];
		}
	}
	$horas_ocupadas = array_unique($horas_ocupadas); //Elimina valores duplicados de un array
	//$horas_ocupadas = array_values(array_unique($horas_ocupadas)); //Elimina valores duplicados de un array y ordena los indices secuencialmente
	
	if ( !empty($horas_ocupadas) ) {
		foreach ($horas as $key => $hora) {
			if ( !in_array($hora, $horas_ocupadas) ) {
				$options .= '<option value="'.$hora.'">'.$hora.'</option>';
			} else {
				$options .= '<option value="'.$hora.'" disabled="disabled">'.$hora.'</option>';
			}
		}
	} else {
		foreach ($horas as $key => $hora) {
			$options .= '<option value="'.$hora.'">'.$hora.'</option>';
		}
	}
	
	return '<select name="horarios[]" id="horario-'.$id_propiedad.'" class="horarios">
			<option value="">00:00</option>'.$options.'
			</select>';
}

function tipo_usuario($nivel) {
	switch ( $nivel ) {
		case 1:
			return 'Administrador';
			break;
		case 2:
			return 'Administrativo';
			break;
		case 3:
			return 'Secretaria';
			break;
		case 4:
			return 'Vendedor';
			break;
		default:
			return 'ERROR: Ningún nivel rol asociado!';
	}
}

//Obtiene y guarda las coordenadas en base a la dirección de la propiedad
function update_coordenadas($id_propiedad, $address) {

global $mysqli;
define("MAPS_HOST", "maps.google.com");
define("KEY", "AIzaSyCyw05X0k7tKQauApDDWG8qndnUvOneBck"); //My API key

// Initialize delay in geocode speed
$delay = 0;
$base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . KEY;

$request_url = $base_url . "&q=" . urlencode($address);
$xml = simplexml_load_file($request_url) or die("url not loading");

$status = $xml->Response->Status->code;
if (strcmp($status, "200") == 0) {
	// Successful geocode
	$coordinates = $xml->Response->Placemark->Point->coordinates;
	$coordinatesSplit = split(",", $coordinates);
	// Format: Longitude, Latitude, Altitude
	$lat = $coordinatesSplit[1];
	$lng = $coordinatesSplit[0];
	
	// Actualizar y agregar las coordenadas obtenidas a la propiedad
	$sql = sprintf("UPDATE propiedades SET lat_googlemap = '%s', lng_googlemap = '%s' WHERE id_propiedad = '%s' LIMIT 1;",
			$mysqli->real_escape_string($lat), $mysqli->real_escape_string($lng), $mysqli->real_escape_string($id_propiedad));
	$mysqli->query($sql) or die('Error: '.$mysqli->error);

} else if (strcmp($status, "620") == 0) {
	// sent geocodes too fast
	$delay += 100000;
} else {
	// failure to geocode
	//echo "Address " . $address . " failed to geocoded.<br />";
	//echo "Received status " . $status . "\n";
}
usleep($delay);
}
