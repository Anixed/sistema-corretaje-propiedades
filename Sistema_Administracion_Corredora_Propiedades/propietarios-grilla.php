<?php require('connect-mysql.php'); require('functions.php');

//Obtiene las variables GET actuales para insertarlas en los links a de la grilla
$url_get = vars_get($_SERVER['REQUEST_URI']);
$url_get = preg_replace('/[&?]orderby=([\w_-]*)/','',$url_get);
if ( !empty($url_get) ) {
	$url_get = 'propietarios-grilla.php'.$url_get.'&';
} else {
	$url_get = 'propietarios-grilla.php'.'?';
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
	$orderby = 'nombre_cliente '.$ordenar_busqueda;
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
?>
<script type="text/javascript">
$(document).ready(function() {
	/* Cebreado con jQuery */
	$(".grilla tbody tr:odd").addClass("alt");
	
    $('.box-top-grilla').on('change', '#tipo_busqueda', function(event) {
    	if ( $(this).val() != '' ) {
    		$('#busqueda_text').val('').focus();
		}
    });
    
});
</script>
				<div id="content-clientes" class="content-clientes-grilla">
				<form action="propietarios-grilla.php" method="get" id="clientes-grilla">
					<div class="box-top-grilla left">
						<span class="buscar_por">Buscar por:</span><br />
						<p>
							<select name="tipo_busqueda" id="tipo_busqueda" tabindex="20">
								<option value="" <?php echo ($_GET['tipo_busqueda'] == '')?'selected="selected"':'';?>>Seleccionar</option>
								<option value="rut_cliente" <?php echo ($_GET['tipo_busqueda'] == 'rut_cliente')?'selected="selected"':'';?>>Rut</option>
								<option value="nombre_cliente" <?php echo ($_GET['tipo_busqueda'] == 'nombre_cliente')?'selected="selected"':'';?>>Nombre</option>
								<option value="direccion" <?php echo ($_GET['tipo_busqueda'] == 'direccion')?'selected="selected"':'';?>>Dirección</option>
							</select>
							<input type="text" id="busqueda_text" name="busqueda_text" onfocus="if (value == 'BUSCAR') {value =''}" onblur="if (value == '') {value = 'BUSCAR'}" value="<?php echo ($_GET['busqueda_text'])?$_GET['busqueda_text']:'BUSCAR';?>" tabindex="21" maxlength="50" />
					        <input name="accion" id="filtrar-propiedades" class="submit" type="submit" value="Filtrar" tabindex="22" />
						</p>
					</div>
					<div class="box-top-grilla right">
					</div>
				</form>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla">
						<thead>
							<tr>
								<th></th>
								<th><a href="<?php echo $url_get;?>orderby=rut_cliente">Rut</a></th>
								<th><a href="<?php echo $url_get;?>orderby=nombre_cliente">Nombre</a></th>
								<th><a href="<?php echo $url_get;?>orderby=direccion">Dirección</a></th>
								<th><a href="<?php echo $url_get;?>orderby=ciudad">Ciudad</a></th>
								<th>Fonos</th>
								<th><a href="<?php echo $url_get;?>orderby=fecha_ingreso">Fecha Ing.</a></th>
							</tr>
						</thead>
						<tbody>
						<?php
							$pages_limit = paginar_resultados("SELECT id_cliente FROM clientes ".$where,20);
							$sql = "SELECT id_cliente, rut_cliente, CONCAT(nombre_cliente,' ',apellidos_cliente) AS nombre_cliente, CONCAT(direccion,' ',num_direccion) AS direccion, num_depa, ciudad, telefono, celular, oficina, fecha_ingreso
									FROM clientes ".$where." ORDER BY ".$orderby." ".$pages_limit;
							$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
							
							if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) : ?>
									<tr id="cliente-<?php echo $row['id_cliente'];?>">
									<td><a href="?id_cliente=<?php echo $row['id_cliente'];?>&rut_cliente=<?php echo formato_rut($row['rut_cliente']);?>&nombre_cliente=<?php echo $row['nombre_cliente'];?>" class="select-propietario"><img src="images/add2.png" title="Seleccionar Cliente" alt="Seleccionar" /></a></td>
									<td><?php echo $row['rut_cliente'];?></td>
									<td><?php echo $row['nombre_cliente'];?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo $row['ciudad'];?></td>
									<td><?php echo $row['telefono'];?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="7">No se han encontrando clientes existentes en la base de datos...</td></tr>';
							endif; ?>
						</tbody>
					</table>
			    </div><!--/content-clientes-grilla-->