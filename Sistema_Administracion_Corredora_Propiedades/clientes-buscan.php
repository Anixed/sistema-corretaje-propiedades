<?php require('header.php'); ?>
		<div id="content">
			<div id="msgbox">
				<p>Modulo de gestión de los clientes &mdash; Listado de clientes buscan ( tipo de propiedad que busca el cliente y que en su momento no se tenían )</p>
			</div><!--/msgbox-->
			<div id="content-page">
<?php
//echo '<pre>'; print_r($_GET); echo '</pre>';

//Obtiene las variables GET actuales para insertarlas en los links a de la grilla
$url_get = vars_get($_SERVER['REQUEST_URI']);
$url_get = preg_replace('/[&?]orderby=([\w_.-]*)/','',$url_get);
if ( !empty($url_get) ) {
	$url_get = $url_get.'&';
} else {
	$url_get = '?';
}

$orderby = '';
if ( isset($_GET['orderby']) && !empty($_GET['orderby']) ) {
	
	if ($_GET['orderby'] == 'nombre_cliente') {
		$orderby = 'clientes.apellidos_cliente ASC, clientes.nombre_cliente ASC';
	} else {
		$orderby = $_GET['orderby'].' ASC';
	}
	
} else {
	$orderby = 'clientes_buscan.fecha_ingreso ASC';
}

if ( $_GET['accion'] == 'Filtrar' ) :
	if ( isset($_GET['busqueda_rut']) && !empty($_GET['busqueda_rut']) && $_GET['busqueda_rut'] != 'BUSCAR' ) :
		
		if ( preg_match('/[0-9]{1,2}[.]?[0-9]{3}[.]?[0-9]{3}[-][0-9kK]{1}/', $_GET['busqueda_rut']) ) :
			$rut = preg_replace('/[.]/', '', $_GET['busqueda_rut']);
			$where = "AND clientes.rut_cliente='".$rut."'";
		else :
			$msgbox = 'Formato de R.U.T incorrecto.';
			$icon = 'error';
		endif;
		
	endif;
endif;

if ( $_GET['accion'] == 'eliminar_registro' && !empty($_GET['id_registro']) ) :
	
	$id_registro = $_GET['id_registro'];
	$sql = "DELETE FROM clientes_buscan WHERE id=".$id_registro;
	$mysqli->query($sql) or die('Error: '.$mysqli->error);
	$msgbox = 'El Registro se ha eliminado satisfactoriamente.';
	$icon = 'info';
	
endif;
?>
<script type="text/javascript">
$(document).ready(function() {
    //Checkbox's
	$("#check-todos").change(function() {
		$("input[type=checkbox]").each(function() {
			if ( $("#check-todos:checked").length == 1 ){
				$(this).prop('checked', true); //this.checked = true;
			} else {
				$(this).prop('checked', false); //this.checked = false;
			}
		});
		$(".checkclientes").each(checkear);
	});
	
	//Cambia el fondo a las filas checkeadas
	$(".checkclientes").change(checkear);
	
	function checkear() {
		if ( $(this).is(':checked') ) {
			$(this).closest("tr").css("background-color","#d0d0d0"); //$(this).parent().parent().toggleClass("check");
		} else {
			var indice = $(".checkclientes").index(this);
			
			if ( indice%2 == 0 ) { //si es par
				$(this).closest("tr").css("background-color","#fbfcfc");
			} else {
				$(this).closest("tr").css("background-color","#f0f0f0");
			}
		}
	};
	
    // Read URL GET variables with JavaScript
	function getUrlVar(key,page) {
		var result = new RegExp(key + "=([^&]*)", "i").exec(page);
		return result && unescape(result[1]) || "";
	}
	
    //Eliminar registros
    $('#content-page').on('click', '.delete', function(event) {
		var page = $(this).attr('href');
		var id_registro = getUrlVar("id_registro",page);
		var eliminar = confirm('¿Está seguro que desea eliminar el registro Nº'+id_registro+'?');
		
		if ( eliminar == true ) {
			return true;
		}
        event.preventDefault(); //return false;
    });
    
    setTimeout(function() { $('#msgbox3').fadeOut('normal'); }, 4000);
});
</script>
				<div id="msgbox3" class="<?php echo 'msgbox-'.$icon;?>"><?php echo $msgbox; ?></div>
				<br />
				<div id="content-propiedades" class="content-propiedades-grilla">
				<form action="clientes-buscan.php" method="get">
					<div class="box-top-grilla left">
						<p>
							<span class="buscar_por">Buscar por R.U.T:</span>
							<input type="text" id="busqueda_rut" name="busqueda_rut" onfocus="if (value == 'BUSCAR') {value =''}" onblur="if (value == '') {value = 'BUSCAR'}" value="<?php echo ($_GET['busqueda_rut'])?$_GET['busqueda_rut']:'BUSCAR';?>" tabindex="1" maxlength="12" />
					        <input id="filtrar-propiedades" class="submit" type="submit" name="accion" value="Filtrar" tabindex="2" />
						</p>
					</div>
				</form>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla">
						<thead>
							<tr>
								<th class="checkbox"><input type="checkbox" id="check-todos" name="check-todos" title="Seleccionar Todos" /></th>
								<th><a href="<?php echo $url_get;?>orderby=id">Nº</a></th>
								<th><a href="<?php echo $url_get;?>orderby=nombre_cliente">Cliente</a></th>
								<th><a href="<?php echo $url_get;?>orderby=tipo_propiedad">Tipo Propiedad</a></th>
								<th><a href="<?php echo $url_get;?>orderby=operacion">Solicitud</a></th>
								<th><a href="<?php echo $url_get;?>orderby=valor_desde">Valor</a></th>
								<th><a href="<?php echo $url_get;?>orderby=sector">Sector</a></th>
								<th><a href="<?php echo $url_get;?>orderby=clientes_buscan.comuna">Comuna</a></th>
								<th><a href="<?php echo $url_get;?>orderby=clientes_buscan.ciudad">Ciudad</a></th>
								<th>Otras Caract.</th>
								<th><a href="<?php echo $url_get;?>orderby=clientes_buscan.fecha_ingreso">Fecha Ing.</a></th>
								<th>Detalles</th>
								<th>Opciones</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$class_color = 'class="alt"';
							
							$pages_limit = paginar_resultados("SELECT clientes_buscan.id FROM clientes_buscan, clientes
											WHERE clientes.id_cliente=clientes_buscan.id_cliente ".$where,30);
							
							$sql = "SELECT clientes_buscan.id, clientes_buscan.id_cliente, clientes.rut_cliente, CONCAT(clientes.nombre_cliente,' ',clientes.apellidos_cliente) AS nombre_cliente,
									tipo_propiedad, operacion, valor_desde, valor_hasta, tipo_valor, sector, clientes_buscan.comuna, clientes_buscan.ciudad,
									superficie_total, superficie_construida, clientes_buscan.observaciones, clientes_buscan.fecha_ingreso
									FROM clientes_buscan, clientes
									WHERE clientes.id_cliente=clientes_buscan.id_cliente
									".$where."
									ORDER BY ".$orderby." ".$pages_limit;
							$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
							
							if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									$tipos = unserialize($row['tipo_propiedad']);
									$comuna = unserialize($row['comuna']);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="registro-<?php echo $row['id'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkclientes" name="cliente-<?php echo $row['id_cliente'];?>" /></td>
									<td><?php echo $row['id'];?></td>
									<td><?php echo $row['nombre_cliente'];?><br />(<?php echo formato_rut($row['rut_cliente']);?>)</td>
									<td><?php echo implode(', ', $tipos);?></td>
									<td><?php echo $row['operacion'];?></td>
									<td>
									<?php if ( !empty($row['valor_desde']) ) { ?>
									<?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor_desde'],0,',','.') : number_format($row['valor_desde'],0,',','.').'UF' ;?>
									<?php } ?>
									<?php if ( !empty($row['valor_hasta']) ) { ?>
									<span> &raquo; </span>
									<?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor_hasta'],0,',','.') : number_format($row['valor_hasta'],0,',','.').'UF' ;?>
									<?php } ?>
									</td>
									<td><?php echo $row['sector'];?></td>
									<td><?php echo implode(', ', $comuna);?></td>
									<td><?php echo $row['ciudad'];?></td>
									<td>
									<?php if ( !empty($row['superficie_construida']) ) { ?>
									M<span class="super">2</span>C: <?php echo (is_numeric($row['superficie_construida']))?number_format($row['superficie_construida'],2,',','.'):$row['superficie_construida'];?>
									<?php } ?>
									<?php if ( !empty($row['superficie_total']) ) { ?>
									<br />
									M<span class="super">2</span>T: <?php echo (is_numeric($row['superficie_total']))?number_format($row['superficie_total'],2,',','.'):$row['superficie_total'];?>
									<?php } ?>
									</td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><?php echo ( !empty($row['observaciones']) ) ? $row['observaciones'] : 'Ninguna observación' ;?></td>
									<?php if ( is_admin($_SESSION['id_sistema']) ) { ?>
									<td><a href="clientes-buscan.php?accion=eliminar_registro&id_registro=<?php echo $row['id']?>&id_cliente=<?php echo $row['id_cliente']?>" class="delete"><img src="images/page_delete.png" title="Eliminar Registro" alt="Eliminar" /></a></td>
									<?php } else { ?>
									<td><img src="images/page_delete_no.png" width="16" height="16" /></td>
									<?php } ?>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="13">No se han encontrando registros existentes en la base de datos...</td></tr>';
							endif; ?>
						</tbody>
					</table>
			    </div><!--/content-propiedades-grilla-->
			</div><!--/content-page-->
		</div><!--/content-->
		<?php include('footer.php'); ?>
	</div><!--/wrap-->
</body>
</html>