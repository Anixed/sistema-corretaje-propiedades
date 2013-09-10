<?php require('connect-mysql.php'); require('functions.php'); ?>
<?php
require_once('functions-privilegios-acceso.php');
session_start();

//echo '<pre>'; print_r($_GET); echo '</pre>';

$other_where = '';
if ( !empty($_GET['cliente']) ) :
	$other_where = ' AND agenda_visitas.id_cliente='.$_GET['cliente'];
elseif ( !empty($_GET['propiedad']) ) :
	$other_where = ' AND agenda_visitas.id_propiedad='.$_GET['propiedad'];
endif;

$fecha = $_GET['ver_evento'];
list($anio, $mes, $dia) = explode("-", $fecha);
$mes_sin_cero = ( substr($mes,0,1) == 0 ) ? substr($mes,-1) : $mes ;

$dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
$meses = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$dia_semana = date("w", mktime(0,0,0,$mes,$dia,$anio));

if ( !empty($_GET['cliente']) ) :
	$other_where = ' AND agenda_visitas.id_cliente='.$_GET['cliente'];
endif;

if ( $_GET['accion'] == 'finalizar-orden' && !empty($_GET['id_visita']) ) :
	
	$id_visita = $_GET['id_visita'];
	$observaciones = ( $_GET['observaciones'][$id_visita] == 'COMENTARIOS SOBRE LA VISITA' ) ? 'Ninguno' : $_GET['observaciones'][$id_visita] ;
	$sql = "UPDATE agenda_visitas SET observaciones='".$observaciones."', estado=2, fecha_finalizada=NOW() WHERE id_visita=".$id_visita;
	$mysqli->query($sql) or die('Error: '.$mysqli->error);
	unset($_GET);
	
endif;
if ( $_GET['accion'] == 'eliminar-orden' && !empty($_GET['id_visita']) ) :
	
	$id_visita = $_GET['id_visita'];
	$sql = "DELETE FROM agenda_visitas WHERE id_visita=".$id_visita;
	$mysqli->query($sql) or die('Error: '.$mysqli->error);
	unset($_GET);
	
endif;
?>
<script type="text/javascript">
$(document).ready(function() {
	/* Cebreado con jQuery */
	$(".grilla tbody tr:odd").addClass("alt");
	
    //Checkbox's
	$("#check-todos").change(function() {
		$("input[type=checkbox]").each(function() {
			if ( $("#check-todos:checked").length == 1 ){
				$(this).prop('checked', true); //this.checked = true;
			} else {
				$(this).prop('checked', false); //this.checked = false;
			}
		});
		$(".checkpropiedades").each(checkear);
	});
	
	//Cambia el fondo a las filas checkeadas
	$(".checkpropiedades").change(checkear);
	
	function checkear() {
		if ( $(this).is(':checked') ) {  
			$(this).closest("tr").css("background-color","#d0d0d0"); //$(this).parent().parent().toggleClass("check");
		} else {
			var indice = $(".checkpropiedades").index(this);
			
			if ( indice%2 == 0 ) { //si es par
				$(this).closest("tr").css("background-color","#fbfcfc");
			} else {
				$(this).closest("tr").css("background-color","#f0f0f0");
			}
		}
	};
});
</script>
<div id="content-eventos">
	<h2>Agenda de Visitas para el <?php echo $dias[$dia_semana].', '.$dia.' de '.$meses[$mes_sin_cero].' de '.$anio;?></h2>
	<form action="agenda_visitas-registros.php" method="get" id="visitas-eventos-formulario">
	<input type="hidden" name="accion" id="accion" value="" />
	<input type="hidden" name="id_visita" id="id_visita" value="" />
	<input type="hidden" name="ver_evento" id="ver_evento" value="<?php echo $fecha; ?>" />
	<table cellpadding="0" cellspacing="0" class="grilla">
		<thead>
			<tr>
				<th>Nº Orden</th>
				<th>Horario</th>
				<th>Propiedad</th>
				<th>Cliente</th>
				<th>Vendedor</th>
				<th>Estado</th>
			</tr>
		</thead>
		<tbody>
		<?php
		if ( !empty($fecha) ) :
		$sql = "SELECT agenda_visitas.id_visita,
				TIME_FORMAT(agenda_visitas.hora_in, '%H:%i') AS hora_in, TIME_FORMAT(agenda_visitas.hora_out, '%H:%i') AS hora_out,
				agenda_visitas.observaciones, agenda_visitas.estado, DATE_FORMAT(agenda_visitas.fecha_ingreso, '%d/%m/%Y a las %H:%i') AS fecha_ingreso, DATE_FORMAT(agenda_visitas.fecha_finalizada, '%d/%m/%Y a las %H:%i') AS fecha_finalizada, clientes.id_cliente, clientes.rut_cliente, clientes.nombre_cliente,
				propiedades.id_propiedad, propiedades.cod_propiedad, propiedades.tipo_propiedad,
				usuarios.nombre, usuarios.apellido
				FROM agenda_visitas, clientes, usuarios, propiedades
				WHERE clientes.id_cliente=agenda_visitas.id_cliente
				AND propiedades.id_propiedad=agenda_visitas.id_propiedad
				AND usuarios.id_usuario=agenda_visitas.id_vendedor
				AND fecha_visita='".$fecha."'
				".$other_where."
				ORDER BY hora_in ASC";
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		
		if ($result->num_rows > 0) :
			//$i = 1;
			while ($row = $result->fetch_assoc()) : ?>
				<tr id="evento-detalles-<?php echo $row['id_visita'];?>">
				<td><?php echo $row['id_visita']; //$i ?></td>
				<td><?php echo $row['hora_in'].' &raquo; '.$row['hora_out'];?></td>
				<td><a href="propiedades.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" target="_blank"><?php echo $row['cod_propiedad'];?></a></td>
				<td><a href="clientes.php?accion=editar_cliente&id_cliente=<?php echo $row['id_cliente'];?>&rut_cliente=<?php echo $row['rut_cliente'];?>" target="_blank"><?php echo $row['nombre_cliente'];?></a></td>
				<td><?php echo $row['nombre'].' '.$row['apellido'];?></td>
				<td><?php echo ($row['estado'] == 1)?'<span class="activa" title="Orden de Visita en Proceso">ACTIVA</span><br /><span title="Fecha en la cual se registró">'.$row['fecha_ingreso'].'</span>':'<span class="finalizada" title="Orden de Visita Realizada">FINALIZADA</span><br /><span title="Fecha en la cual se finalizó">'.$row['fecha_finalizada'].'</span>';?></td>
				</tr>
				<tr id="evento-form-<?php echo $row['id_visita'];?>">
				<td colspan="5" valign="middle" style="vertical-align: middle;">
					<button type="button" class="button editar-orden" name="editar-orden" value="<?php echo $row['id_visita'];?>" <?php echo (!confirmar_privilegios_acceso(title_page($_SERVER["SCRIPT_NAME"]), $_SESSION['id_sistema']))?'':'disabled="disabled"';?> title="Editar la Orden de Visita"><img src="<?php echo (!confirmar_privilegios_acceso(title_page($_SERVER["SCRIPT_NAME"]), $_SESSION['id_sistema']))?'images/edit.png':'images/edit-disabled.png"';?>" width="32" height="32" /><br /><span> Editar</span></button>
					<textarea name="observaciones[<?php echo $row['id_visita'];?>]" cols="90" rows="2" <?php echo ($row['estado'] == 1)?'':'readonly="readonly"';?> onfocus="if (value == 'COMENTARIOS SOBRE LA VISITA') {value =''}" onblur="if (value == '') {value = 'COMENTARIOS SOBRE LA VISITA'}"><?php echo (!empty($row['observaciones']))?$row['observaciones']:'COMENTARIOS SOBRE LA VISITA';?></textarea>
				</td>
				<td>
					<button type="button" class="button finalizar-orden" name="finalizar-orden" value="<?php echo $row['id_visita'];?>" <?php echo ($row['estado'] == 1)?'':'disabled="disabled"';?> title="Finalizar la Orden de Visita"><img src="<?php echo ($row['estado'] == 1)?'images/accept.png':'images/accept-disabled.png"';?>" width="32" height="32" /><br /><span> Terminar</span></button>
					<button type="button" class="button eliminar-orden" name="eliminar-orden" value="<?php echo $row['id_visita'];?>" <?php echo (is_admin($_SESSION['id_sistema']))?'':'disabled="disabled"';?> title="Eliminar Orden de Visita"><img src="images/delete-big.png" width="32" height="32" /><br /><span> Eliminar</span></button>
				</td>
				</tr>
			<?php
			//++$i;
			endwhile;
			$result->close();
		else :
			echo '<tr><td colspan="6">No se han encontrando órdenes de visita existentes en la base de datos...</td></tr>';
		endif;
		
		else :
			echo '<tr><td colspan="6">Especifique la fecha para consultar las órdenes de visitas...</td></tr>';
		endif; ?>
		</tbody>
	</table>
	</form>
</div>