<?php require('header.php'); ?>
<?php
//echo '<pre>'; print_r($_POST); echo '</pre>';
//Si se recarga la página se deserealiza la variable enviada con las propiedades y se le cambia la expresion '\"' por '"' al contenido
if ( !empty($_POST['propiedad']) && !is_array($_POST['propiedad'])) :
	$_POST['propiedad'] = preg_replace('%(\\\\")%','"',$_POST['propiedad']);
	$_POST['propiedad'] = unserialize($_POST['propiedad']);
	$_POST['contenido'] = preg_replace('%(\\\\")%','"',$_POST['contenido']);
endif;

// Obtenemos los datos del cliente y preparamos el envío del correo
if ( $_POST['accion'] == 'Enviar Carta Oferta' ) {
	if ( !empty($_POST['id_cliente']) ) {
		
		$sql = "SELECT CONCAT(nombre_cliente,' ',apellidos_cliente) AS nombre_cliente, email FROM clientes WHERE id_cliente=".$_POST['id_cliente'];
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		if ($row = $result->fetch_assoc()) {
			$nombre_destinatario = $row['nombre_cliente'];
			$email_destinatario = $row['email'];
			//Reemplaza los campos especiales con los datos necesarios
			$contenidoHTML = str_replace(array('[NOMBRE CLIENTE]','[EMAIL CLIENTE]'), array($nombre_destinatario, $email_destinatario), $_POST['contenido']);
		} else {
			$msgbox = 'ERROR: Cliente no encontrado.';
			$icon = 'error';
		}
		$result->close();
		
	} elseif ( !empty($_POST['nombre']) && !empty($_POST['email']) ) {
		
		if ( preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST['email']) ) {
			$nombre_destinatario = trim($_POST['nombre']);
			$email_destinatario = trim($_POST['email']);
			//Reemplaza los campos especiales con los datos necesarios
			$contenidoHTML = str_replace(array('[NOMBRE CLIENTE]','[EMAIL CLIENTE]'), array($nombre_destinatario, $email_destinatario), $_POST['contenido']);
		} else {
			$msgbox = 'Especifique correctamente el correo electrónico opcional.';
			$icon = 'error';
		}
	
	} else {
		$msgbox = 'Especifique el cliente al cual enviar la carta de oferta.';
		$icon = 'error';
	}
	
	// Si recibe correctamente las variables entonces sigue con el envío del correo
	if ( !empty($nombre_destinatario) && !empty($email_destinatario) && !empty($contenidoHTML) && $icon != 'error' ) {
	
	$sql = "SELECT CONCAT(nombre,' ',apellido) AS nombre, email FROM usuarios WHERE id_usuario=".$_SESSION['id_sistema'];
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	if ($row = $result->fetch_assoc()) {
		$nombre_from = $row['nombre'];
		$email_from = $row['email'];
	} else {
		die('Error: Al consultar los datos del usuario a enviar el correo.');
	}
	$result->close();
	
	/*===================== INICIO ENVIAR CORREO =====================*/
	include('configuracion_correo.php');
	$mail->SetFrom($email_from, $nombre_from);
	$mail->AddReplyTo($email_from, $nombre_from);
	$mail->Subject = 'Carta Oferta';
	$mail->AddAddress($email_destinatario, $nombre_destinatario);
	$mail->AddBCC('admin@admin.com'); // o $mail->AddBCC($email_from);
	$mail->Body = $contenidoHTML;
	//$mail->Timeout = 50;
	if ( $mail->Send() ) {
		$msgbox = 'Carta Oferta enviada correctamente al destinatario "'.$nombre_destinatario.'" &lt;'.$email_destinatario.'&gt;<br />
					Si lo desea puede continuar y enviar este correo a un cliente distinto.';
		$icon = 'info';
		unset($_POST['id_cliente'], $_POST['nombre'], $_POST['email']);
	} else {
		$msgbox = '<strong>ERROR:</strong> Al enviar la carta de oferta al destinatario "'.$nombre_destinatario.'" &lt;'.$email_destinatario.'&gt;<br />
					Por favor inténtelo más tarde.';
		$icon = 'error';
	}
	$mail->ClearAddresses();
	$mail->SmtpClose();
	/*===================== FIN ENVIAR CORREO =====================*/
		
	}
	
}


if ( empty($_POST['contenido']) && !empty($_POST['propiedad']) ) :

$meses = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$dia = date("d"); $mes = intval(date("m")); $anio = date("Y");
$fecha = $meses[$mes].' '.$dia.' del '.$anio;

$CONTENIDO = '
<p>
	Sistema Corretaje de Propiedades</p>
<p>
	&nbsp;</p>
<p style="text-align: right;">
	TEMUCO, '.$fecha.'.</p>
<p>
	&nbsp;</p>
<p>
	<strong>SE&Ntilde;OR(A)</strong><br />
	<strong>[NOMBRE CLIENTE]</strong><br />
	[EMAIL CLIENTE]</p>
<p>
	&nbsp;</p>
<p>
	<u><strong>PRESENTE</strong></u></p>
<p>
	De mi consideraci&oacute;n:<br />
	Por medio de la presente adjunto a Usted, carta oferta de propiedad(es):</p>
<p>
	<u><strong>Documentos adjuntos:</strong></u></p>
<ul>
	<li>
		Informaci&oacute;n de la propiedad</li>
	<li>
		Mapa ubicaci&oacute;n y zonificaci&oacute;n con Google</li>
</ul>
<p>
	<u><strong>Honorarios corredor:</strong></u></p>
<ul>
	<li>
		2% Sobre el valor total de la propiedad, m&aacute;s impuesto</li>
</ul>';

foreach ($_POST['propiedad'] as $key => $id_propiedad) {
$sql = "SELECT id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor,
		CONCAT(direccion,' ',num_direccion) AS direccion, comuna, lat_googlemap, lng_googlemap
		FROM propiedades
		WHERE id_propiedad=".$id_propiedad;
$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
if ($row = $result->fetch_assoc()) :

$row['valor'] = ( $row['tipo_valor'] == '$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;
$CONTENIDO .= '
<p>
	&nbsp;</p>
<p>
	<u><strong>PROPIEDAD Nº'.$row['id_propiedad'].':</strong></u></p>
<ul>
	<li>
		C&oacute;digo: '.$row['cod_propiedad'].'</li>
	<li>
		Operaci&oacute;n: '.$row['operacion'].'</li>
	<li>
		Ubicaci&oacute;n: '.$row['direccion'].' - '.$row['comuna'].'</li>
	<!--li>
		Superficie: '.number_format($row['superficie_total'],2,',','.').'</li-->
	<li>
		Valor: '.$row['valor'].'</li>
	<li>
		Detalle (opcional): Ninguno</li>
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

}

$CONTENIDO .= '
<p style="text-align: center;">
	&nbsp;</p>
<p style="text-align: center;">
	Sin otro particular, saluda atentamente.</p>
<p style="text-align: center;">
	&nbsp;</p>
<p style="text-align: center;">
	<strong>'.$_SESSION['name_sistema'].'<br />
	Sistema Corretaje de Propiedades</strong></p>';

else:
	$CONTENIDO = $_POST['contenido'];
endif;

?>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="ckfinder/ckfinder.js"></script>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function() {//window.onload = function() {
	var contenido = CKEDITOR.replace('contenido', {
		toolbar : 'Full'
	});
	CKFinder.setupCKEditor(contenido, 'ckfinder/');
});//}
//]]>
</script>
<script type="text/javascript">
$(document).ready(function() {
    //Comprueba que los campos requeridos esten llenos antes de enviar el correo
	$('#content-page').on('submit', '#carta-oferta-grilla', function(event) {
		if ( $('#id_cliente').val() != '' || ($('#nombre').val() != '' && $('#email').val() != '') ) {
			return true;
		} else {
			event.preventDefault(); //return false;
			alert('Especifique el cliente al cual enviar la carta de oferta.');
		}
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
    //Recarga la pagina con AJAX para el paginador de los clientes
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
    
});
</script>
		<div id="content">
			<div id="msgbox">
				<p>Modulo de gestión de propiedades &mdash; Envío de Carta de Oferta con las propiedades seleccionadas al cliente.</p>
			</div><!--/msgbox-->
			<div id="content-page">
				<div class="<?php echo 'msgbox-'.$icon;?>"><?php echo $msgbox; ?></div>
				<div id="box-popup">
					<a href="javascript:void(0);" class="cerrar"><img src="images/close2.png" title="Cerrar" alt="Cerrar" /></a>
					<div class="content-popup"></div>
				</div>
				<div id="content-carta-oferta" class="content-propiedades-grilla">
				<br />
				<?php
				if ( is_array($_POST['propiedad']) && count($_POST['propiedad']) >= 1 ) :
				?>
				<?php if ( count($_POST['propiedad']) == 1 ) { ?>
				<strong>1 Propiedad seleccionada y adjuntada a la carta de oferta.</strong>
				<?php } else { ?>
				<strong><?php echo count($_POST['propiedad']); ?> Propiedades seleccionadas y adjuntadas a la carta de oferta.</strong>
				<?php } ?>
				<form action="propiedades-carta-oferta.php" method="post" id="carta-oferta-grilla">
				<input type="hidden" name="url_varsget" id="url_varsget" value="<?php echo ( isset($_POST['url_varsget']) ) ? $_POST['url_varsget'] : vars_get($_SERVER["HTTP_REFERER"]) /*getCurrentUrl()*/ ; ?>" />
				<input type="hidden" name="propiedad" id="propiedad" value='<?php echo serialize($_POST['propiedad']); ?>' />
				<br />
				<p>
					<label for="id_cliente">Cliente:<span>*</span></label>
					<input type="hidden" name="cliente" id="cliente" value="<?php echo $_POST['cliente']; ?>" />
					<select name="id_cliente" id="id_cliente" tabindex="1">
					<option value="">Seleccionar</option>
					<?php
					$sql = "SELECT id_cliente, CONCAT(nombre_cliente,' ',apellidos_cliente) AS nombre_cliente
							FROM clientes ORDER BY apellidos_cliente ASC, nombre_cliente ASC";
					$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
					if ($result->num_rows > 0) {
						while ($row = $result->fetch_array()) {
							echo '<option value="'.$row['id_cliente'].'">'.$row['nombre_cliente'].'</option>';
						}
						$result->close();
					} ?>
					</select>
					<a href="propietarios-grilla.php" id="buscar_cliente" class="submit small">Buscar cliente</a>
				</p>
				<br />
				<strong>( Llene los siguientes campos si desea enviar la carta de oferta a otro correo. )</strong>
				<p>
					<span style="display:block;float:left;margin:5px 2px 0 0;">Nombre: (Opcional)<br />
						<input type="text" id="nombre" size="25" name="nombre" value="<?php echo $_POST['nombre'];?>" maxlength="60" />
					</span>
					<span style="display:block;float:left;margin:5px 2px 0 0;">E-Mail: (Opcional)<br />
						<input type="text" id="email" size="20" name="email" value="<?php echo $_POST['email'];?>" maxlength="80" />
					</span>
					<div class="clear"></div>
				</p>
				<p>
					<label for="contenido" style="width: auto;">Contenido del correo:<span>*</span> (Puede editar lo que estime conveniente antes de enviar este correo al cliente)</label>
					<div class="clear"></div>
					<textarea name="contenido" id="contenido" tabindex="2"><?php echo $CONTENIDO;?></textarea>
				</p>
				<p>
					<input type="submit" name="accion" class="submit" value="Enviar Carta Oferta" tabindex="3" />
					<input type="button" id="cancelar-envio" class="button" value="Volver al listado" tabindex="4" />
				</p>
				</form>
				<?php
				else :
					echo '<strong>ERROR:</strong> No ha seleccionado ninguna propiedad. <a href="propiedades.php" class="submit">Volver</a>';
					//exit();
				endif;
				?>
			    </div><!--/content-propiedades-grilla-->
			</div><!--/content-page-->
		</div><!--/content-->
		<?php include('footer.php'); ?>
	</div><!--/wrap-->
</body>
</html>