<?php require('connect-mysql.php'); require('functions.php'); ?>
<?php
require_once('functions-privilegios-acceso.php');
session_start();

//echo '<pre>'; print_r($_GET); echo '</pre>';

if ( confirmar_privilegios_acceso('agenda_visitas-registros', $_SESSION['id_sistema']) ) {
	echo '<strong>ERROR:</strong> No tiene permisos para acceder a la pantalla consultada.';
	exit();
}

if ( !empty($_GET['id_visita']) && !empty($_GET['horarios'][0]) && !empty($_GET['cliente']) && !empty($_GET['vendedor']) && !empty($_GET['observaciones']) ) :
	
	$id_visita = $_GET['id_visita'];
	list($hora, $min) = explode(':', $_GET['horarios'][0]);
	$hora_out = ( ($min + 30) >= 60 ) ? ($hora + 1).':00' : $hora.':30' ;
	
	$sql = "UPDATE agenda_visitas SET
			id_cliente=".$_GET['cliente'].",
			id_vendedor=".$_GET['vendedor'].",
			hora_in='".$_GET['horarios'][0]."',
			hora_out='".$hora_out."',
			observaciones='".$_GET['observaciones']."'
			WHERE id_visita=".$id_visita;
	$mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	// Procedemos a preparar el correo y a enviarlo
		$sql = "SELECT DATE_FORMAT(agenda_visitas.fecha_visita, '%d/%m/%Y') AS fecha_visita, agenda_visitas.estado, agenda_visitas.id_propiedad
				TIME_FORMAT(agenda_visitas.hora_in, '%H:%i') AS hora_in, TIME_FORMAT(agenda_visitas.hora_out, '%H:%i') AS hora_out,
				CONCAT(clientes.nombre_cliente,' ',clientes.apellidos_cliente) AS nombre_cliente, clientes.email AS email_cliente,
				CONCAT(usuarios.nombre,' ',usuarios.apellido) AS nombre_usuario, usuarios.email AS email_usuario
				FROM agenda_visitas, clientes, usuarios
				WHERE usuarios.id_usuario=agenda_visitas.id_vendedor
				AND clientes.id_cliente=agenda_visitas.id_cliente
				AND agenda_visitas.id_visita=".$id_visita;
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		if (!$orden_visita = $result->fetch_assoc()) :
			exit('ERROR: No se han encontrado los datos requeridos para continuar y generar el correo automático.');
		endif;
		
		if ( $orden_visita['estado'] == 1 ) :
		
		$CONTENIDO = '<strong>Estimado(a) '.$orden_visita['nombre_cliente'].'</strong><br />
		<br />
		Se ha modificado la orden de visita Nº'.$id_visita.' planificada para el día '.$orden_visita['fecha_visita'].'.<br/>
		A continuación se le adjunta la información actualizada de la orden de visita.<br/>
		<br/>
		<u><strong>ORDEN DE VISITA Nº'.$id_visita.'</strong></u><br/>
		Cliente: '.$orden_visita['nombre_cliente'].'<br/>
		Anfitrión: '.$orden_visita['nombre_usuario'].'<br/>
		Fecha de la visita: '.$orden_visita['fecha_visita'].' de '.$orden_visita['hora_in'].' hasta '.$orden_visita['hora_out'].'<br/>';
		
		$result->close();

		$sql = "SELECT id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor,
				CONCAT(direccion,' ',num_direccion) AS direccion, comuna, lat_googlemap, lng_googlemap
				FROM propiedades
				WHERE id_propiedad=".$orden_visita['id_propiedad'];
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		if ($row = $result->fetch_assoc()) :

		$row['valor'] = ( $row['tipo_valor'] == '$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;
		$CONTENIDO .= '
		<p>&nbsp;</p>
		<p><u><strong>PROPIEDAD Nº'.$row['id_propiedad'].':</strong></u></p>
		<ul>
			<li>C&oacute;digo: '.$row['cod_propiedad'].'</li>
			<li>Operaci&oacute;n: '.$row['operacion'].'</li>
			<li>Ubicaci&oacute;n: '.$row['direccion'].' - '.$row['comuna'].'</li>
			<li>Valor: '.$row['valor'].'</li>
		</ul>';

		if ( ceil($row['lat_googlemap']) != 0 && ceil($row['lng_googlemap']) != 0 ) :
			$address = $row['lat_googlemap'].','.$row['lng_googlemap'];
		else :
			$address = str_replace(' ', '+', $row['direccion'].','.$row['comuna'].',Chile');
		endif;

		$CONTENIDO .= '
		<p>
			<u><strong>Mapa Ubicaci&oacute;n</strong></u> (Clic sobre la imagen para ir a Google Maps)<br />
			<a target="_blank" href="http://maps.google.com/maps?q='.str_replace(' ', '+', $row['direccion'].','.$row['comuna'].',Chile').'&hl=es&ie=UTF8&hnear='.$address.'&t=m&z=16">
			<img src="http://maps.googleapis.com/maps/api/staticmap?center='.$address.'&size=610x300&maptype=hybrid&sensor=false&zoom=16&markers='.$address.'" id="mapa-'.$row['id_propiedad'].'" />
			</a>
		</p>
		';

		$result->close();
		endif;

		$CONTENIDO .= '
		<p style="text-align: center;">&nbsp;</p>
		<p style="text-align: center;">Sin otro particular, saluda atentamente.</p>
		<p style="text-align: center;">&nbsp;</p>
		<p style="text-align: center;">
		Los correos de respuesta a esta dirección no son leídos.<br />
		Para comunicarse con nosotros por favor hacerlo al siguiente correo: <strong>contacto@admin.com</strong></p>';
		
		/*===================== INICIO ENVIAR CORREO =====================*/
		include('configuracion_correo.php');
		$mail->SetFrom('no-reply@admin.com', 'No responder');
		$mail->AddReplyTo('contacto@admin.com', 'Contacto'); //$mail->AddReplyTo('secretaria@admin.com', 'Secretaria');
		$mail->Subject = 'Actualización: Orden de visita para el día '.$orden_visita['fecha_visita'];
		$mail->AddAddress($orden_visita['email_cliente'], $orden_visita['nombre_cliente']);
		$mail->AddBCC('admin@admin.com');
		$mail->Body = $contenidoHTML;
		//$mail->Timeout = 50;
		if ( $mail->Send() ) {
			$msgbox = 'La orden de visita Nº'.$id_visita.' se ha modificado satisfactoriamente.<br />
						Se ha enviado un correo con la información actualizada al cliente y al anfitrión. <a href="javascript:void(0);" class="submit small cerrar-ventana">Cerrar</a>';
			$icon = 'info';
		} else {
			$msgbox = 'La orden de visita Nº'.$id_visita.' se ha modificado satisfactoriamente.<br />
						Pero ha ocurrido un error al enviar el correo con la información actualizada de la visita. <a href="javascript:void(0);" class="submit small cerrar-ventana">Cerrar</a>';
			$icon = 'info';
		}
		$mail->ClearAddresses();
		$mail->SmtpClose();
		/*===================== FIN ENVIAR CORREO =====================*/
		
		else :
			$msgbox = 'La orden de visita Nº'.$id_visita.' se ha modificado satisfactoriamente.<a href="javascript:void(0);" class="submit small cerrar-ventana">Cerrar</a>';
			$icon = 'info';
		endif;
	
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
	<h2>Editando la orden de visita Nº <?php echo $_GET['id_visita'];?> con fecha <?php echo mysql_to_normal($_GET['ver_evento']);?></h2>
	<div class="<?php echo 'msgbox-'.$icon;?>"><?php echo $msgbox; ?></div>
	<form action="agenda_visitas-registros_edicion.php" method="get" id="visitas-eventos-formulario">
	<input type="hidden" name="ver_evento" id="ver_evento" value="<?php echo $_GET['ver_evento']; ?>" />
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
		if ( !empty($_GET['id_visita']) ) :
		$sql = "SELECT agenda_visitas.id_visita,
				TIME_FORMAT(agenda_visitas.hora_in, '%H:%i') AS hora_in, TIME_FORMAT(agenda_visitas.hora_out, '%H:%i') AS hora_out,
				agenda_visitas.observaciones, agenda_visitas.estado, DATE_FORMAT(agenda_visitas.fecha_ingreso, '%d/%m/%Y a las %H:%i') AS fecha_ingreso, DATE_FORMAT(agenda_visitas.fecha_finalizada, '%d/%m/%Y a las %H:%i') AS fecha_finalizada,
				clientes.id_cliente,
				propiedades.id_propiedad, propiedades.cod_propiedad, propiedades.tipo_propiedad,
				usuarios.id_usuario
				FROM agenda_visitas, clientes, usuarios, propiedades
				WHERE clientes.id_cliente=agenda_visitas.id_cliente
				AND propiedades.id_propiedad=agenda_visitas.id_propiedad
				AND usuarios.id_usuario=agenda_visitas.id_vendedor
				AND agenda_visitas.id_visita=".$_GET['id_visita']." LIMIT 1";
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		
		if ($row = $result->fetch_assoc()) :
			
			$vendedor = ( !empty($_GET['vendedor']) ) ? $_GET['vendedor'] : $row['id_usuario'] ;
			$cliente = ( !empty($_GET['cliente']) ) ? $_GET['cliente'] : $row['id_cliente'] ;
			$horario = ( isset($_GET['horarios'][0]) ) ? $_GET['horarios'][0] : $row['hora_in'] ;
			
			?>
				<script type="text/javascript">
				$(document).ready(function() {
					$('#horario-<?php echo $row['id_propiedad'];?> option[value="<?php echo $horario;?>"]').attr("selected",true);
				});
				</script>
				<input type="hidden" name="id_visita" id="id_visita" value="<?php echo $row['id_visita'];?>" />
				<input type="hidden" name="propiedad" id="propiedad" value="<?php echo $row['id_propiedad'];?>" />
				<tr>
				<td><?php echo $row['id_visita'];?></td>
				<td id="horario"><?php echo horario_propiedad($row['id_propiedad'], $_GET['ver_evento'], $vendedor, $cliente);?></td>
				<td><a href="propiedades.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" target="_blank"><?php echo $row['cod_propiedad'];?></a></td>
				<td>
					<p>
						<select name="cliente" id="cliente">
						<!--option value="">Seleccionar</option-->
						<?php
						$sql = "SELECT id_cliente, CONCAT(nombre_cliente,' ',apellidos_cliente) AS nombre_cliente
								FROM clientes ORDER BY apellidos_cliente ASC, nombre_cliente ASC";
						$result2 = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						if ($result2->num_rows > 0) {
							while ($client = $result2->fetch_array()) {
								if ( $cliente == $client['id_cliente'] ) {
									echo '<option value="'.$client['id_cliente'].'" selected="selected">'.$client['nombre_cliente'].'</option>';
								} else {
									echo '<option value="'.$client['id_cliente'].'">'.$client['nombre_cliente'].'</option>';
								}
							}
							$result2->close();
							unset($client);
						} ?>
						</select>
					</p>
				</td>
				<td>
					<p>
						<select name="vendedor" id="vendedor">
						<!--option value="">Seleccionar</option-->
						<?php
						$sql = "SELECT id_usuario, nombre, apellido FROM usuarios ORDER BY nombre ASC";
						$result2 = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						if ($result2->num_rows > 0) {
							while ($user = $result2->fetch_array()) {
								if ( $vendedor == $user['id_usuario'] ) {
									echo '<option value="'.$user['id_usuario'].'" selected="selected">'.$user['nombre'].' '.$user['apellido'].'</option>';
								} else {
								echo '<option value="'.$user['id_usuario'].'">'.$user['nombre'].' '.$user['apellido'].'</option>';
								}
							}
							$result2->close();
							unset($user);
						} ?>
						</select>
					</p>
				</td>
				<td><?php echo ($row['estado'] == 1)?'<span class="activa" title="Orden de Visita en Proceso">ACTIVA</span><br /><span title="Fecha en la cual se registró">'.$row['fecha_ingreso'].'</span>':'<span class="finalizada" title="Orden de Visita Realizada">FINALIZADA</span><br /><span title="Fecha en la cual se finalizó">'.$row['fecha_finalizada'].'</span>';?></td>
				</tr>
				<tr>
				<td colspan="5" valign="middle" style="vertical-align: middle;">
					<textarea name="observaciones" cols="98" rows="2"><?php echo $row['observaciones'];?></textarea>
				</td>
				<td>
					<button type="submit" class="button editar-orden" name="editar-orden" value="<?php echo $row['id_visita'];?>" title="Editar la Orden de Visita"><img src="images/edit.png" width="32" height="32" /><br /><span> Modificar</span></button>
				</td>
				</tr>
			<?php
		else :
			echo '<tr><td colspan="6">No se ha encontrando la orden de visita en la base de datos...</td></tr>';
		endif;
		$result->close();
		
		else :
			echo '<tr><td colspan="6">ERROR: Especifique el ID para consultar la orden de visita...</td></tr>';
		endif; ?>
		</tbody>
	</table>
	</form>
</div>