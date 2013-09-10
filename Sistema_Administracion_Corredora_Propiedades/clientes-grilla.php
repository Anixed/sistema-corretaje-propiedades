<?php
require_once('connect-mysql.php'); require_once('functions.php');
require_once('functions-privilegios-acceso.php');
session_start();

//echo '<pre>'; print_r($_GET); echo '</pre>';

//Obtiene las variables GET actuales para insertarlas en los links a de la grilla
$url_get = vars_get($_SERVER['REQUEST_URI']);
$url_get = preg_replace('/[&?]orderby=([\w_-]*)/','',$url_get);
if ( !empty($url_get) ) {
	$url_get = $url_get.'&';
} else {
	$url_get = '?';
}

$orderby = '';
if ( isset($_GET['orderby']) && !empty($_GET['orderby']) ) {
	$ordenar_busqueda = (!empty($_GET['ordenar_busqueda']))?$_GET['ordenar_busqueda']:'DESC';
	
	if ($_GET['orderby'] == 'nombre_cliente') {
		$orderby = 'apellidos_cliente '.$ordenar_busqueda.', nombre_cliente ASC';
	} else {
		$orderby = $_GET['orderby'].' '.$ordenar_busqueda;
	}
	
} else {
	$ordenar_busqueda = (!empty($_GET['ordenar_busqueda']))?$_GET['ordenar_busqueda']:'DESC';
	$orderby = 'id_cliente '.$ordenar_busqueda; //'fecha_ingreso '.$ordenar_busqueda;
}

$where = '';
if ( ( isset($_GET['tipo_busqueda']) && !empty($_GET['tipo_busqueda']) ) && ( !empty($_GET['busqueda_text']) && $_GET['busqueda_text'] != 'BUSCAR') ) {
	
	if ( $_GET['tipo_busqueda'] == 'rut_cliente' ) {
		$_GET['busqueda_text'] = preg_replace('/[. ]/', '', $_GET['busqueda_text']);
		$where = "WHERE rut_cliente='".$_GET['busqueda_text']."'";
	} elseif ( $_GET['tipo_busqueda'] == 'nombre_cliente' ) {
		$where = "WHERE apellidos_cliente LIKE '%".trim($_GET['busqueda_text'])."%' OR nombre_cliente LIKE '%".trim($_GET['busqueda_text'])."%'";
	} else {
		$where = "WHERE ".$_GET['tipo_busqueda']." LIKE '%".trim($_GET['busqueda_text'])."%'";
	}
	
}

if ( ($_GET['accion'] == 'eliminar_cliente') && !empty($_GET['id_cliente']) && !empty($_GET['rut_cliente']) && !empty($_GET['nombre_cliente']) ) :
	
	// Si no es administrador no puede acceder a eliminar el registro
	if ( !is_admin($_SESSION['id_sistema']) ) :
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: index.php?msgbox=sin_permiso&error=eliminar');
		exit();
	endif;
	// ==============================================================
	
	$id_cliente = $_GET['id_cliente'];
	
	if ( existe_registro_cliente($id_cliente, 'id_cliente') ) :
		
		if ( !cliente_tiene_propiedades($id_cliente, 'id_propietario') ) :
			$sql = "DELETE FROM clientes WHERE id_cliente=".$id_cliente;
			$mysqli->query($sql) or die('Error: '.$mysqli->error);
			$msgbox = 'El Cliente "'.$_GET['nombre_cliente'].'" ( R.U.T: '.formato_rut($_GET['rut_cliente']).' ) se ha eliminado satisfactoriamente.';
			$icon = 'info';
		else :
			$msgbox = 'No se ha podido eliminar al Cliente ( R.U.T: '.formato_rut($_GET['rut_cliente']).' ) porque tiene propiedades vinculadas, órdenes de visita pendientes, y/o ya existen registros vinculados a este cliente.';
			$icon = 'error';
		endif;
		
	else :
		$msgbox = 'No se ha encontrado el Cliente ID Nº:'.$id_cliente.'.';
		$icon = 'error';
	endif;
	unset($_GET);
	
endif;
?>
<script type="text/javascript">
$(document).ready(function() {
	<?php if ( !empty($msgbox) ) { ?>
	var msgbox = '<?php echo $msgbox; ?>';
	$("#msgbox2").addClass("<?php echo 'msgbox-'.$icon;?>").html(msgbox).show();
	<?php } ?>
	
    $('.box-top-grilla').on('change', '#ordenar_busqueda', function(event) { //$("#ordenar_busqueda").change(function() {
		$("#clientes-grilla").submit();
    });
    
    $('.box-top-grilla').on('change', '#tipo_busqueda', function(event) {
    	if ( $(this).val() != '' ) {
    		$('#busqueda_text').val('').focus();
		}
    });
    
    //Checkbox's
	$("#check-todos").change(function() {
		$("input[type=checkbox]").each(function() {
			if ( $("#check-todos:checked").length == 1 ){
				$(this).prop('checked', true);
			} else {
				$(this).prop('checked', false);
			}
		});
		$(".checkclientes").each(checkear);
	});
	
	//Cambia el fondo a las filas checkeadas
	$(".checkclientes").change(checkear);
	
	function checkear() {
		if ( $(this).is(':checked') ) {  
			$(this).closest("tr").css("background-color","#d0d0d0"); //$(this).parent().parent().toggleClass("check"); //selecciona el padre del TD
		} else {
			var indice = $(".checkclientes").index(this);
			
			if ( indice%2 == 0 ) { //si es par
				$(this).closest("tr").css("background-color","#fbfcfc");
			} else {
				$(this).closest("tr").css("background-color","#f0f0f0");
			}
		}
	};
});
</script>
				<div id="content-clientes" class="content-clientes-grilla">
				<form action="clientes-grilla.php" method="get" id="clientes-grilla">
					<div class="box-top-grilla left">
						<span class="buscar_por">Ingresar:</span><br />
						<a href="clientes-form.php" class="submit" id="agregar-cliente">Nuevo Cliente</a>
					</div>
					<div class="box-top-grilla left">
						<span class="buscar_por">Buscar por:</span><br />
						<p>
							<select name="tipo_busqueda" id="tipo_busqueda" tabindex="20">
								<option value="" <?php echo ($_GET['tipo_busqueda'] == '')?'selected="selected"':'';?>>Seleccionar</option>
								<option value="rut_cliente" <?php echo ($_GET['tipo_busqueda'] == 'rut_cliente')?'selected="selected"':'';?>>Rut</option>
								<option value="nombre_cliente" <?php echo ($_GET['tipo_busqueda'] == 'nombre_cliente')?'selected="selected"':'';?>>Nombre</option>
								<option value="direccion" <?php echo ($_GET['tipo_busqueda'] == 'direccion')?'selected="selected"':'';?>>Dirección</option>
								<option value="ciudad" <?php echo ($_GET['tipo_busqueda'] == 'ciudad')?'selected="selected"':'';?>>Ciudad</option>
							</select>
							<input type="text" id="busqueda_text" name="busqueda_text" onfocus="if (value == 'BUSCAR') {value =''}" onblur="if (value == '') {value = 'BUSCAR'}" value="<?php echo ($_GET['busqueda_text'])?$_GET['busqueda_text']:'BUSCAR';?>" tabindex="21" maxlength="50" />
					        <input name="accion" id="filtrar-propiedades" class="submit" type="submit" value="Filtrar" tabindex="22" />
						</p>
					</div>
					<div class="box-top-grilla left">
					<input type="hidden" name="orderby" id="orderby" value="<?php echo $_GET['orderby']; ?>" />
						<br />
						<span class="buscar_por">Ordenar Busqueda:</span><br />
						<p>
							<select name="ordenar_busqueda" class="text" id="ordenar_busqueda" style="margin-left:0;width:120px;">
								<option value="DESC" <?php echo ($_GET['ordenar_busqueda'] == 'DESC')?'selected="selected"':'';?>>Descendente</option>
								<option value="ASC" <?php echo ($_GET['ordenar_busqueda'] == 'ASC')?'selected="selected"':'';?>>Ascendente</option>
							</select>
						</p>
					</div>
				</form>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla">
						<thead>
							<tr>
								<th class="checkbox"><input type="checkbox" id="check-todos" name="check-todos" title="Seleccionar Todos" /></th>
								<th><a href="<?php echo $url_get;?>orderby=rut_cliente">Rut</a></th>
								<th><a href="<?php echo $url_get;?>orderby=nombre_cliente">Nombre</a></th>
								<th><a href="<?php echo $url_get;?>orderby=direccion">Dirección</a></th>
								<th><a href="<?php echo $url_get;?>orderby=ciudad">Ciudad</a></th>
								<th>Fonos</th>
								<th><a href="<?php echo $url_get;?>orderby=fecha_ingreso">Fecha Ing.</a></th>
								<th colspan="4">Opciones</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$class_color = 'class="alt"';
							
							$pages_limit = paginar_resultados("SELECT id_cliente FROM clientes ".$where,25);
							$sql = "SELECT id_cliente, rut_cliente, CONCAT(nombre_cliente,' ',apellidos_cliente) AS nombre_cliente, CONCAT(direccion,' ',num_direccion) AS direccion, num_depa, ciudad, telefono, celular, oficina, fecha_ingreso
									FROM clientes
									".$where." ORDER BY ".$orderby." ".$pages_limit;
							$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
							
							if ($result->num_rows > 0) :
								
								if ( $_SESSION['tipo_sistema'] == 4 ) {
									$vendedor = '&vendedor='.$_SESSION['id_sistema'];
								}
								
								while ($row = $result->fetch_assoc()) :
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="cliente-<?php echo $row['id_cliente'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkclientes" name="cliente-<?php echo $row['id_cliente'];?>" /></td>
									<td><?php echo $row['rut_cliente'];?></td>
									<td><?php echo $row['nombre_cliente'];?></td>
									<td><?php echo $row['direccion'];?><?php echo (!empty($row['num_depa']))?' Dpto. N°'.$row['num_depa']:'';?></td>
									<td><?php echo $row['ciudad'];?></td>
									<td><?php echo (!empty($row['telefono']))?'Tel.'.$row['telefono']:'';?><?php echo (!empty($row['celular']))?' Cel.'.$row['celular']:'';?><?php echo (!empty($row['oficina']))?' Of.'.$row['oficina']:'';?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="clientes-form.php?accion=editar_cliente&id_cliente=<?php echo $row['id_cliente'];?>&rut_cliente=<?php echo $row['rut_cliente'];?>" class="editar"><img src="images/page_edit.png" title="Editar Cliente" alt="Editar" /></a></td>
									<?php if ( $_SESSION['tipo_sistema'] == 4 ) { ?>
									<td><a href="agenda_visitas.php?accion=visitas_cliente&vendedor=<?php echo $_SESSION['id_sistema'];?>&id_cliente=<?php echo $row['id_cliente'];?>&rut_cliente=<?php echo $row['rut_cliente'];?>&nombre_cliente=<?php echo $row['nombre_cliente'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<?php } else { ?>
									<td><a href="agenda_visitas.php?accion=visitas_cliente<?php echo $vendedor;?>&id_cliente=<?php echo $row['id_cliente'];?>&rut_cliente=<?php echo $row['rut_cliente'];?>&nombre_cliente=<?php echo $row['nombre_cliente'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<?php } ?>
									<td><a href="propiedades.php?accion=propiedades_cliente&id_cliente=<?php echo $row['id_cliente'];?>&rut_cliente=<?php echo $row['rut_cliente'];?>&nombre_cliente=<?php echo $row['nombre_cliente'];?>" target="_blank"><img src="images/house.png" title="Propiedades del cliente" alt="Propiedades" /></a></td>
									<?php if ( is_admin($_SESSION['id_sistema']) ) { ?>
									<td><a href="clientes-grilla.php?accion=eliminar_cliente&id_cliente=<?php echo $row['id_cliente'];?>&rut_cliente=<?php echo $row['rut_cliente'];?>&nombre_cliente=<?php echo $row['nombre_cliente'];?>" class="delete"><img src="images/page_delete.png" title="Eliminar Cliente" alt="Eliminar" /></a></td>
									<?php } else { ?>
									<td><img src="images/page_delete_no.png" width="16" height="16" /></td>
									<?php } ?>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="11">No se han encontrando clientes existentes en la base de datos...</td></tr>';
							endif; ?>
						</tbody>
					</table>
			    </div><!--/content-clientes-grilla-->