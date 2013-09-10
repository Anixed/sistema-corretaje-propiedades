<?php
// Si es administrador puede eliminar los registros del sistema (propiedades, clientes, usuarios)
function is_admin($id_usuario) {
	global $mysqli;
	$acceso = false;
	
	$sql = "SELECT tipo_usuario, privilegios_opcionales FROM usuarios WHERE id_usuario=".$id_usuario;
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	$row = $result->fetch_assoc();
	
	$privilegios = array();
	$privilegios = unserialize($row['privilegios_opcionales']);
	
	if ( $row['tipo_usuario'] == 1 ) :
		$acceso = true;
	elseif ( $privilegios[3] == 'Administración' ) : //elseif ( in_array('Administración', $privilegios) ) :
		$acceso = true;
	else :
		$acceso = false;
	endif;
	
	return $acceso;
}

// Bloquea las opciones del menu según el tipo de usuario
function mostrar_opcion_menu($opcion_menu, $id_usuario) {
	global $mysqli;
	$acceso = false;
	
	$sql = "SELECT tipo_usuario, privilegios_opcionales FROM usuarios WHERE id_usuario=".$id_usuario;
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	$row = $result->fetch_assoc();
	
	if ( $opcion_menu == 'Administración de Pagos' && ( $row['tipo_usuario'] == 1 || $row['tipo_usuario'] == 2 ) ) : // Módulo Propiedades
		$acceso = true;
	elseif ( $opcion_menu == 'Administración' && $row['tipo_usuario'] == 1 ) : // Módulo Administración
		$acceso = true;
	endif;
	
	$privilegios = unserialize($row['privilegios_opcionales']);
	if ( !empty($privilegios) && $acceso == false ) : //Consultar los privilegios opcionales

		if ( $opcion_menu == 'Administración de Pagos' && in_array('Propiedades', $privilegios) ) :
			$acceso = true;
		elseif ( $opcion_menu == 'Administración' && in_array('Administración', $privilegios) ) :
			$acceso = true;
		endif;
		
	endif;
	
	return $acceso;
}

// Comprueba si el usuario tiene los privilegios necesarios para poder acceder a la pantalla consultada
function confirmar_privilegios_acceso($pagina, $id_usuario) {
	global $mysqli;
	$paginasNOpermitidas = array();
	
	$sql = "SELECT tipo_usuario, privilegios_opcionales FROM usuarios WHERE id_usuario=".$id_usuario;
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	$row = $result->fetch_assoc();
	
	if ( $row['tipo_usuario'] == 1 ) : //Administrador
		$paginasNOpermitidas = array();
	elseif ( $row['tipo_usuario'] == 2 ) : //Administrativo
		$paginasNOpermitidas = array("usuarios");
	elseif ( $row['tipo_usuario'] == 3 ) : //Secretaria
		$paginasNOpermitidas = array("administracion-de-pagos", "usuarios");
	elseif ( $row['tipo_usuario'] == 4 ) : //Vendedor
		$paginasNOpermitidas = array("administracion-de-pagos", "usuarios", "agenda_visitas-registros");
	endif;
	
	$privilegios = unserialize($row['privilegios_opcionales']);
	if ( !empty($privilegios) ) : //Consultar los privilegios opcionales

		if ( in_array('Administración', $privilegios) ) :
			$clave = array_search('usuarios', $paginasNOpermitidas);
			unset($paginasNOpermitidas[$clave]);
		endif;
		
		if ( in_array('Propiedades', $privilegios) ) :
			$clave = array_search('administracion-de-pagos', $paginasNOpermitidas);
			unset($paginasNOpermitidas[$clave]);
		endif;
		
		if ( in_array('Agenda Anfitriones', $privilegios) ) :
			$clave = array_search('agenda_visitas-registros', $paginasNOpermitidas);
			unset($paginasNOpermitidas[$clave]);
		endif;
		
	endif;
	
	if ( in_array($pagina, $paginasNOpermitidas) ) :
		return true;
	else :
		return false;
	endif;
}