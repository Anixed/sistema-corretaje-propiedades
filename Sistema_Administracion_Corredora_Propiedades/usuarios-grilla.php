<?php require_once('connect-mysql.php'); require_once('functions.php');

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
	
	if ($_GET['orderby'] == 'nombre') {
		$orderby = 'apellido '.$ordenar_busqueda.', nombre ASC';
	} else {
		$orderby = $_GET['orderby'].' '.$ordenar_busqueda;
	}
	
} else {
	$ordenar_busqueda = (!empty($_GET['ordenar_busqueda']))?$_GET['ordenar_busqueda']:'DESC';
	$orderby = 'id_usuario '.$ordenar_busqueda; //'fecha_ingreso '.$ordenar_busqueda;
}

$where = '';
if ( ( isset($_GET['tipo_busqueda']) && !empty($_GET['tipo_busqueda']) ) && ( !empty($_GET['busqueda_text']) && $_GET['busqueda_text'] != 'BUSCAR') ) {
	
	if ( $_GET['tipo_busqueda'] == 'rut_usuario' ) {
		$_GET['busqueda_text'] = preg_replace('/[. ]/', '', $_GET['busqueda_text']);
		$where = "WHERE rut_usuario='".$_GET['busqueda_text']."'";
	} elseif ( $_GET['tipo_busqueda'] == 'nombre' ) {
		$where = "WHERE apellido LIKE '%".trim($_GET['busqueda_text'])."%' OR nombre LIKE '%".trim($_GET['busqueda_text'])."%'";
	} else {
		$where = "WHERE ".$_GET['tipo_busqueda']." LIKE '%".trim($_GET['busqueda_text'])."%'";
	}
	
}

if ( ($_GET['accion'] == 'eliminar_usuario') && !empty($_GET['id_usuario']) && !empty($_GET['rut_usuario']) && !empty($_GET['nombre_usuario']) ) :
	$id_usuario = $_GET['id_usuario'];
	
	if ( existe_registro_usuario($id_usuario, 'id_usuario') ) :
		
		if ( !usuario_operaciones_pendientes($id_usuario, 'id_vendedor') ) :
			$sql = "DELETE FROM usuarios WHERE id_usuario=".$id_usuario;
			$mysqli->query($sql) or die('Error: '.$mysqli->error);
			$msgbox = 'El Usuario "'.$_GET['nombre_usuario'].'" ( R.U.T: '.formato_rut($_GET['rut_usuario']).' ) se ha eliminado satisfactoriamente.';
			$icon = 'info';
		else :
			$msgbox = 'No se ha podido eliminar al Usuario ( R.U.T: '.formato_rut($_GET['rut_usuario']).' ) porque tiene operaciones pendientes, y/o ya existen registros vinculados a este usuario.';
			$icon = 'error';
		endif;
		
	else :
		$msgbox = 'No se ha encontrado el Usuario ID Nº:'.$id_usuario.'.';
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
		$("#usuarios-grilla").submit();
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
		$(".checkusuarios").each(checkear);
	});
	
	//Cambia el fondo a las filas checkeadas
	$(".checkusuarios").change(checkear);
	
	function checkear() {
		if ( $(this).is(':checked') ) {  
			$(this).closest("tr").css("background-color","#d0d0d0"); //$(this).parent().parent().toggleClass("check"); //selecciona el padre del TD
		} else {
			var indice = $(".checkusuarios").index(this);
			
			if ( indice%2 == 0 ) { //si es par
				$(this).closest("tr").css("background-color","#fbfcfc");
			} else {
				$(this).closest("tr").css("background-color","#f0f0f0");
			}
		}
	};
});
</script>
				<div id="content-usuarios" class="content-clientes-grilla">
				<form action="usuarios-grilla.php" method="get" id="usuarios-grilla">
					<div class="box-top-grilla left">
						<span class="buscar_por">Ingresar:</span><br />
						<a href="usuarios-form.php" class="submit" id="agregar-usuario">Nuevo Usuario</a>
					</div>
					<div class="box-top-grilla left">
						<span class="buscar_por">Buscar por:</span><br />
						<p>
							<select name="tipo_busqueda" id="tipo_busqueda" tabindex="20">
								<option value="" <?php echo ($_GET['tipo_busqueda'] == '')?'selected="selected"':'';?>>Seleccionar</option>
								<option value="rut_usuario" <?php echo ($_GET['tipo_busqueda'] == 'rut_usuario')?'selected="selected"':'';?>>Rut</option>
								<option value="nombre" <?php echo ($_GET['tipo_busqueda'] == 'nombre')?'selected="selected"':'';?>>Nombre</option>
								<option value="email" <?php echo ($_GET['tipo_busqueda'] == 'email')?'selected="selected"':'';?>>E-Mail</option>
							</select>
							<input type="text" id="busqueda_text" name="busqueda_text" onfocus="if (value == 'BUSCAR') {value =''}" onblur="if (value == '') {value = 'BUSCAR'}" value="<?php echo ($_GET['busqueda_text'])?$_GET['busqueda_text']:'BUSCAR';?>" tabindex="21" maxlength="50" />
					        <input name="accion" class="submit" type="submit" value="Filtrar" tabindex="22" />
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
								<th><a href="<?php echo $url_get;?>orderby=rut_usuario">Rut</a></th>
								<th><a href="<?php echo $url_get;?>orderby=nombre">Nombre</a></th>
								<th><a href="<?php echo $url_get;?>orderby=email">E-Mail</a></th>
								<th><a href="<?php echo $url_get;?>orderby=tipo_usuario">Tipo</a></th>
								<th><a href="<?php echo $url_get;?>orderby=ultimo_login">Último acceso</a></th>
								<th><a href="<?php echo $url_get;?>orderby=fecha_ingreso">Fecha Ing.</a></th>
								<th colspan="4">Opciones</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$class_color = 'class="alt"';
							
							$pages_limit = paginar_resultados("SELECT id_usuario FROM usuarios ".$where,10);
							$sql = "SELECT id_usuario, rut_usuario, CONCAT(nombre,' ',apellido) AS nombre_usuario, email, tipo_usuario, ultimo_login, fecha_ingreso
									FROM usuarios
									".$where." ORDER BY ".$orderby." ".$pages_limit;
							$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
							
							if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="usuario-<?php echo $row['id_usuario'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkusuarios" name="usuario-<?php echo $row['id_usuario'];?>" /></td>
									<td><?php echo $row['rut_usuario'];?></td>
									<td><?php echo $row['nombre_usuario'];?></td>
									<td><?php echo $row['email'];?></td>
									<td><?php echo tipo_usuario($row['tipo_usuario']);?></td>
									<td><?php echo ($row['ultimo_login'] != NULL) ? mysql_to_normal($row['ultimo_login']):'Nunca';?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="usuarios-form.php?accion=editar_usuario&id_usuario=<?php echo $row['id_usuario'];?>&rut_usuario=<?php echo $row['rut_usuario'];?>" class="editar"><img src="images/page_edit.png" title="Editar Usuario" alt="Editar" /></a></td>
									<?php //if ( $row['tipo_usuario'] == 4 ) { ?>
									<td><a href="agenda_visitas.php?vendedor=<?php echo $row['id_usuario'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<?php /*} else { ?>
									<td></td>
									<?php }*/ ?>
									<td><a href="javascript:void(0);" target="_blank"><img src="images/book.png" title="Historial del usuario" alt="Propiedades" /></a></td>
									<td><a href="usuarios-grilla.php?accion=eliminar_usuario&id_usuario=<?php echo $row['id_usuario'];?>&rut_usuario=<?php echo $row['rut_usuario'];?>&nombre_usuario=<?php echo $row['nombre_usuario'];?>" class="delete"><img src="images/page_delete.png" title="Eliminar Usuario" alt="Eliminar" /></a></td>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="11">No se han encontrando usuarios existentes en la base de datos...</td></tr>';
							endif; ?>
						</tbody>
					</table>
			    </div><!--/content-clientes-grilla-->