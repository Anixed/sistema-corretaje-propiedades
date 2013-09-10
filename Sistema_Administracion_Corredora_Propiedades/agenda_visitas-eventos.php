<?php require('connect-mysql.php'); require('functions.php'); ?>
<?php
$fecha = $_GET['agregar_evento'];
list($anio, $mes, $dia) = explode("-", $fecha);
$mes_sin_cero = ( substr($mes,0,1) == 0 ) ? substr($mes,-1) : $mes ;

$dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
$meses = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$dia_semana = date("w", mktime(0,0,0,$mes,$dia,$anio));
?>
<?php
//echo '<pre>'; print_r($_GET); echo '</pre>';

//Obtiene las variables GET actuales para insertarlas en los links a de la grilla
$url_get = vars_get($_SERVER['REQUEST_URI']);
$url_get = preg_replace('/[&?]orderby=([\w_-]*)/','',$url_get);
if ( !empty($url_get) ) {
	$url_get = 'agenda_visitas-eventos.php'.$url_get.'&';
} else {
	$url_get = 'agenda_visitas-eventos.php'.'?';
}

$orderby = '';
if ( isset($_GET['orderby']) && !empty($_GET['orderby']) ) {
	$ordenar_busqueda = (!empty($_GET['ordenar_busqueda']))?$_GET['ordenar_busqueda']:'DESC';
	$orderby = $_GET['orderby'].' '.$ordenar_busqueda;
} else {
	$ordenar_busqueda = (!empty($_GET['ordenar_busqueda']))?$_GET['ordenar_busqueda']:'DESC';
	$orderby = 'id_propiedad '.$ordenar_busqueda; //'fecha_ingreso '.$ordenar_busqueda;
}

//Obtiene los datos de la propiedad seleccionada para agendar visitas, para filtrar todas las propiedades parecidas
if ( !empty($_GET['propiedad']) ) :
	
	$id_propiedad = $_GET['propiedad'];
	$sql = "SELECT id_propiedad, tipo_propiedad, operacion, sector, comuna
			FROM propiedades WHERE id_propiedad=".$id_propiedad;
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	if ($row = $result->fetch_assoc()) :
		$_GET['operacion'] = $row['operacion'];
		$_GET['tipo_propiedad'] = (array) $row['tipo_propiedad'];
		$_GET['comuna'] = (array) $row['comuna'];
		$_GET['sector'] = $row['sector'];
		$_GET['propiedades_visitar'] = (array) $row['id_propiedad'];
		$_GET['horarios'] = array();
	endif;
	
endif;

$where = '';
if ( !empty($_GET['operacion']) ) {
	$where .= " AND operacion='".$_GET['operacion']."'";
}

if ( !empty($_GET['tipo_propiedad']) ) {
	$i = 0;
	foreach ($_GET['tipo_propiedad'] as $tipo) {
		if ( $i > 0 ) {
			$where .= " OR tipo_propiedad='".$tipo."'";
		} else {
			$where .= " AND ( tipo_propiedad='".$tipo."'";
			$i++;
		}
	}
	$where .= ' )';
}

if ( !empty($_GET['comuna'][0]) ) {
	$i = 0;
	foreach ($_GET['comuna'] as $comuna) {
		if ( $i > 0 ) {
			$where .= " OR comuna='".$comuna."'";
		} else {
			$where .= " AND ( comuna='".$comuna."'";
			$i++;
		}
	}
	$where .= ' )';
}

if ( !empty($_GET['sector']) ) {
	$where .= " AND sector LIKE '%".trim($_GET['sector'])."%'";
}
if ( !empty($_GET['direccion']) ) {
	$where .= " AND direccion LIKE '%".trim($_GET['direccion'])."%'";
}

if ( !empty($_GET['precio_desde']) && !empty($_GET['precio_hasta']) && $_GET['precio_desde'] < $_GET['precio_hasta'] ) {
	$_GET['precio_desde'] = preg_replace('/[^0-9]/','',$_GET['precio_desde']);
	$_GET['precio_hasta'] = preg_replace('/[^0-9]/','',$_GET['precio_hasta']);
	$where .= " AND tipo_valor='".$_GET['tipo_valor']."' AND valor BETWEEN '".$_GET['precio_desde']."' AND '".$_GET['precio_hasta']."'";
}

/*
if ( !empty($_GET['banos']) &&
	$_GET['tipo_propiedad'] != 'Campo' && $_GET['tipo_propiedad'] != 'Parcela' && $_GET['tipo_propiedad'] != 'Sitio' ) {
	$where .= " AND banos>='".$_GET['banos']."'";
} else {
	$_GET['banos'] = '';
}
if ( !empty($_GET['dormitorios']) &&
	$_GET['tipo_propiedad'] != 'Bodega' && $_GET['tipo_propiedad'] != 'Campo' && $_GET['tipo_propiedad'] != 'Parcela' && $_GET['tipo_propiedad'] != 'Sitio' && $_GET['tipo_propiedad'] != 'Local' && $_GET['tipo_propiedad'] != 'Oficina' ) {
	$where .= " AND dormitorios>='".$_GET['dormitorios']."'";
} else {
	$_GET['dormitorios'] = '';
}

if ( !empty($_GET['superficie_construida1']) && !empty($_GET['superficie_construida2'])
	&& $_GET['superficie_construida1'] < $_GET['superficie_construida2'] ) {
	$patrones = array(); $sustituciones = array();
	$patrones[0] = '/[^0-9,]/'; $patrones[1] = '/[,]/';
	$sustituciones[0] = ''; $sustituciones[1] = '.';
	$_GET['superficie_construida1'] = preg_replace($patrones,$sustituciones,$_GET['superficie_construida1']);
	$_GET['superficie_construida2'] = preg_replace($patrones,$sustituciones,$_GET['superficie_construida2']);
	$where .= " AND valor BETWEEN '".$_GET['superficie_construida1']."' AND '".$_GET['superficie_construida2']."'";
}
*/

if ( $_GET['accion'] == 'guardar-orden' && !empty($_GET['id_cliente']) && !empty($_GET['id_vendedor']) && !empty($_GET['propiedades_visitar']) && !empty($_GET['horarios']) ) :
	
	$insert = '';
	$id_cliente = $_GET['id_cliente'];
	$id_vendedor = $_GET['id_vendedor'];
	$fecha_visita = $_GET['agregar_evento'];
	
	//elimina los elementos vacios del array y vuelve a ordenar los indices
	$_GET['horarios'] = array_values(array_diff($_GET['horarios'], array('')));
	$_GET['observaciones'] = array_values(array_diff($_GET['observaciones'], array('')));
	$horarios = $_GET['horarios'];
	$observaciones = $_GET['observaciones'];
	
	foreach ($_GET['propiedades_visitar'] as $key => $id_propiedad) {
		list($hora, $min) = explode(':', $horarios[$key]);
		$hora_out = ( ($min + 30) >= 60 ) ? ($hora + 1).':00' : $hora.':30' ;
		$insert .= "(".$id_cliente.", ".$id_propiedad.", ".$id_vendedor.", '".$horarios[$key]."', '".$hora_out."', '".$fecha_visita."', '".$observaciones[$key]."', NOW()),";
	}
	$insert = substr($insert, 0, -1);
	
	$sql = "INSERT INTO agenda_visitas (id_cliente, id_propiedad, id_vendedor, hora_in, hora_out, fecha_visita, observaciones, fecha_ingreso) VALUES ".$insert;
	$ok = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ( $ok == 1 ) :
		
		$id_visita = $mysqli->insert_id; //Recupera el ID generado por la consulta anterior (normalmente INSERT) para una columna AUTO_INCREMENT.
		$sql = "SELECT DATE_FORMAT(agenda_visitas.fecha_visita, '%d/%m/%Y') AS fecha_visita,
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
		
		$CONTENIDO = '<strong>Estimado(a) '.$orden_visita['nombre_cliente'].'</strong><br />
		<br />
		Se ha planificado una orden de visita a una de nuestras propiedades.<br/>
		A continuación se le adjunta la información relacionada a la orden de visita.<br/>
		<br/>
		<u><strong>ORDEN DE VISITA Nº'.$id_visita.'</strong></u><br/>
		Cliente: '.$orden_visita['nombre_cliente'].'<br/>
		Anfitrión: '.$orden_visita['nombre_usuario'].'<br/>
		Fecha de la visita: '.$orden_visita['fecha_visita'].' de '.$orden_visita['hora_in'].' hasta '.$orden_visita['hora_out'].'<br/>';
		
		$result->close();

		foreach ($_GET['propiedades_visitar'] as $key => $id_propiedad) {
		$sql = "SELECT id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor,
				CONCAT(direccion,' ',num_direccion) AS direccion, comuna, lat_googlemap, lng_googlemap
				FROM propiedades
				WHERE id_propiedad=".$id_propiedad;
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
		}

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
		$mail->Subject = 'Orden de visita para el día '.$orden_visita['fecha_visita'].', Propiedades {{Nombre Empresa}}';
		$mail->AddAddress($orden_visita['email_cliente'], $orden_visita['nombre_cliente']);
		$mail->AddBCC('admin@admin.com');
		$mail->Body = $contenidoHTML;
		//$mail->Timeout = 50;
		if ( $mail->Send() ) {
			$url_varsget = 'msgbox=orden_visita&estado=ok&cliente='.$id_cliente.'&vendedor='.$id_vendedor.'&propiedades='.count($_GET['propiedades_visitar']);
		} else {
			$url_varsget = 'msgbox=orden_visita&estado=no&cliente='.$id_cliente.'&vendedor='.$id_vendedor.'&propiedades='.count($_GET['propiedades_visitar']);
		}
		$mail->ClearAddresses();
		$mail->SmtpClose();
		/*===================== FIN ENVIAR CORREO =====================*/
		
		$url_varsget = ( !empty($_GET['url_varsget']) ) ? $_GET['url_varsget'].'&'.$url_varsget : '?'.$url_varsget ;
		unset($_GET, $insert, $sql, $where);
		
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: agenda_visitas-calendario.php'.$url_varsget);
		exit();
	endif;
	
endif;

?>
<script type="text/javascript">
$(document).ready(function() {
	/* Cebreado con jQuery */
	$(".grilla tbody tr:odd").addClass("alt");
	
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
    $('#content-eventos').on('click', '#paginator #pages a, table.grilla thead tr th a', function(event) {
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
	
	$('#content-eventos').on('click', '.buscar_por', function(event) {
		$('#busqueda_propiedad').slideToggle('normal');
		event.preventDefault();
	});
	
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
	/*---------------------------------------*/
	<?php if ( !empty($_GET['id_cliente']) ) { ?>
	$("#id_cliente option[value=<?php echo $_GET['id_cliente'];?>]").attr("selected",true);
	<?php } ?>
	/*---------------------------------------*/
	<?php if ( !empty($_GET['id_vendedor']) ) { ?>
	$("#id_vendedor option[value=<?php echo $_GET['id_vendedor'];?>]").attr("selected",true);
	<?php } ?>
	/*---------------------------------------*/
	<?php if ( !empty($_GET['tipo_propiedad']) && is_array($_GET['tipo_propiedad']) ) {
	$propiedades = '';
	foreach ($_GET['tipo_propiedad'] as $tipo) {
		$propiedades .= '#tipo_propiedad option[value="'.$tipo.'"],';
	}
	$propiedades = substr($propiedades, 0, -1);
	?>
	$('<?php echo $propiedades;?>').attr("selected",true);
	<?php } ?>
	/*---------------------------------------*/
	<?php if ( !empty($_GET['comuna']) && is_array($_GET['comuna']) ) {
	$comuna = '';
	foreach ($_GET['comuna'] as $valor) {
		$comuna .= '#comuna option[value="'.$valor.'"],';
	}
	$comuna = substr($comuna, 0, -1);
	?>
	$('<?php echo $comuna;?>').attr("selected",true);
	<?php } ?>
	/*---------------------------------------*/
	<?php if ( !empty($_GET['propiedades_visitar']) ) {
	$checks = ''; $horario = '';
	$_GET['horarios'] = array_values(array_diff($_GET['horarios'], array('')));
	foreach ($_GET['propiedades_visitar'] as $key => $valor) {
		$checks .= '#check-'.$valor.',';
		$horario .= '#horario-'.$valor.' option[value="'.$_GET['horarios'][$key].'"],';
	}
	$checks = substr($checks, 0, -1); //Para quitar la última coma
	$horario = substr($horario, 0, -1);
	?>
	$('<?php echo $horario;?>').attr("selected",true);
	$('<?php echo $checks;?>').prop('checked', true).closest("tr").css("background-color","#d0d0d0");
	<?php } ?>
	/*---------------------------------------*/
	$('#sector option[value="<?php echo (!empty($_GET['sector']))?$_GET['sector']:'';?>"]').attr("selected",true);
});
</script>
<div id="content-eventos">
	<div id="box-popup">
		<a href="javascript:void(0);" class="cerrar"><img src="images/close2.png" title="Cerrar" alt="Cerrar" /></a>
		<div class="content-popup"></div>
	</div>
	<h2>Orden de Visitas para el <?php echo $dias[$dia_semana].', '.$dia.' de '.$meses[$mes_sin_cero].' de '.$anio;?></h2>
	<form action="agenda_visitas-eventos.php" method="get" id="visitas-eventos-formulario">
	<input type="hidden" name="accion" id="accion" value="" />
	<input type="hidden" name="agregar_evento" id="agregar_evento" value="<?php echo $_GET['agregar_evento']; ?>" />
	<!--input type="hidden" name="url_referer" id="url_referer" value="<?php echo basename($_SERVER["HTTP_REFERER"]); ?>" /-->
	<input type="hidden" name="url_varsget" id="url_varsget" value="<?php echo ( isset($_GET['url_varsget']) ) ? $_GET['url_varsget'] : vars_get($_SERVER["HTTP_REFERER"]) ; ?>" />
	<p>
		<label for="id_cliente">Cliente:<span>*</span></label>
		<input type="hidden" name="cliente" id="cliente" value="<?php echo $_GET['cliente']; ?>" />
		<select name="id_cliente" id="id_cliente" tabindex="1">
		<?php if ( empty($_GET['cliente']) ) { $where_cliente = ''; ?>
		<option value="">Seleccionar</option>
		<?php } else {
			$where_cliente = 'WHERE id_cliente='.$_GET['cliente'];
		}
		$sql = "SELECT id_cliente, CONCAT(nombre_cliente,' ',apellidos_cliente) AS nombre_cliente
				FROM clientes ".$where_cliente." ORDER BY apellidos_cliente ASC, nombre_cliente ASC";
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
	<p>
		<label for="id_vendedor">Vendedor:<span>*</span></label>
		<input type="hidden" name="vendedor" id="vendedor" value="<?php echo $_GET['vendedor']; ?>" />
		<select name="id_vendedor" id="id_vendedor" tabindex="1">
		<?php if ( empty($_GET['vendedor']) ) { $where_vendedor = ''; ?>
		<option value="">Seleccionar</option>
		<?php } else {
			$where_vendedor = 'WHERE id_usuario='.$_GET['vendedor'];
		}
		$sql = "SELECT id_usuario, nombre, apellido FROM usuarios ".$where_vendedor." ORDER BY nombre ASC";
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_array()) {
				echo '<option value="'.$row['id_usuario'].'">'.$row['nombre'].' '.$row['apellido'].'</option>';
			}
			$result->close();
		} ?>
		</select>
	</p>
	<span class="buscar_por" style="display: block; margin-bottom: 8px;">&raquo; Busqueda Propiedad:</span>
	<div id="busqueda_propiedad">
	<p>
		<label for="operacion">Operación:<span>*</span></label>
		<select name="operacion" id="operacion" tabindex="2">
			<option value="" <?php echo ($_GET['operacion'] == '')?'selected="selected"':'';?>>Todos</option>
			<option value="Venta" <?php echo ($_GET['operacion'] == 'Venta')?'selected="selected"':'';?>>Venta</option>
			<option value="Arriendo" <?php echo ($_GET['operacion'] == 'Arriendo')?'selected="selected"':'';?>>Arriendo</option>
		</select>
	</p>
	<p><strong>* CTRL + CLIC para seleccionar más de 1 tipo simultáneamente.</strong></p>
	<p class="left" style="margin-right: 10px;">
		<label for="tipo_propiedad">Tipos:<span>*</span></label>
		<select name="tipo_propiedad[]" id="tipo_propiedad" size="8" multiple="multiple" tabindex="3">
			<option value="Casa">Casa</option>
			<option value="Departamento">Departamento</option>
			<option value="Oficina">Oficina</option>
			<option value="Local">Local</option>
			<option value="Parcela">Parcela</option>
			<option value="Campo">Campo</option>
			<option value="Sitio">Sitio</option>
			<option value="Bodega">Bodega</option>
		</select>
	</p>
	<p class="left">
		<label for="comuna">Comunas:</label>
		<select name="comuna[]" id="comuna" size="8" multiple="multiple" tabindex="4">
			<option value="">Todas las Comunas</option>
			<?php
			$sql = "SELECT COMUNA_NOMBRE FROM comuna ORDER BY COMUNA_NOMBRE ASC";
			$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_array()) {
					echo '<option value="'.$row['COMUNA_NOMBRE'].'">'.$row['COMUNA_NOMBRE'].'</option>';
				}
				$result->close();
			} ?>
		</select>
	</p>
	<div class="clear"></div>
	<p>
		<label for="sector">Sector:</label>
		<!--input type="text" id="sector" size="30" name="sector" value="<?php echo $_GET['sector'];?>" tabindex="5" maxlength="50" /-->
		<select name="sector" id="sector" tabindex="5">
			<option value="">Seleccionar</option>
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
		<label for="direccion">Dirección:</label>
		<input type="text" id="direccion" size="30" name="direccion" value="<?php echo $_GET['direccion'];?>" tabindex="6" maxlength="200" />
	</p>
	<p>
		<label for="precio_desde">Precio:</label>
		Desde <input type="text" id="precio_desde" size="11" name="precio_desde" value="<?php echo ($_GET['precio_desde'])?number_format($_GET['precio_desde'],0,',','.'):'';?>" maxlength="18" />
		Hasta <input type="text" id="precio_hasta" size="11" name="precio_hasta" value="<?php echo ($_GET['precio_hasta'])?number_format($_GET['precio_hasta'],0,',','.'):'';?>" maxlength="18" />
		<select name="tipo_valor" style="width: 50px;" id="tipo_valor">
			<option value="$" <?php echo ($_GET['tipo_valor'] == '$')?'selected="selected"':'';?>>$</option>
			<option value="U.F." <?php echo ($_GET['tipo_valor'] == 'U.F.')?'selected="selected"':'';?>>U.F.</option>
		</select>
	</p>
	<?php /* ?>
	<p class="left" style="margin-right: 10px;">
		<label for="dormitorios">Dormitorios:</label>
		<input type="text" id="dormitorios" size="4" name="dormitorios" value="<?php echo $_GET['dormitorios'];?>" maxlength="2" />
	</p>
	<p class="left">
		<label for="banos">Baños:</label>
		<input type="text" id="banos" size="4" name="banos" value="<?php echo $_GET['banos'];?>" maxlength="2" />
	</p>
	<?php */ ?>
	<div class="clear"></div>
	<p class="buttons">
		<input name="accion" id="filtrar-propiedades" class="submit" type="submit" value="Filtrar" tabindex="7" />
		<input type="button" id="cancelar-ingreso" class="button" value="Cancelar" tabindex="8" />
		<!--input type="reset" class="button" value="Limpiar Campos" tabindex="9" onclick="try{document.getElementById('id_cliente').focus();}catch(e){}" /-->
	</p>
	</div>
	
	<div class="clear"></div>
	<table cellpadding="0" cellspacing="0" class="grilla">
		<thead>
			<tr>
				<th class="checkbox"><input type="checkbox" id="check-todos" name="check-todos" title="Seleccionar Todos" /></th>
				<th><a href="<?php echo $url_get;?>orderby=id_propiedad">Código</a></th>
				<th><a href="<?php echo $url_get;?>orderby=tipo_propiedad">Tipo</a></th>
				<th><a href="<?php echo $url_get;?>orderby=sector">Propiedad (Sector / Dirección)</a></th>
				<th><a href="<?php echo $url_get;?>orderby=comuna">Comuna</a></th>
				<th>Ficha</th>
				<th><a href="<?php echo $url_get;?>orderby=valor">Valor</a></th>
				<th>Horario</th>
				<th>Observación Inicial</th>
			</tr>
		</thead>
		<tbody>
		<?php
		$existen_propiedades = false;
		
		if ( !empty($where) ) :
		
		$pages_limit = paginar_resultados("SELECT id_propiedad FROM propiedades WHERE ".substr($where, 4),10);
		
		$sql = "SELECT id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, direccion, num_direccion, sector, comuna
				FROM propiedades
				WHERE ".substr($where, 4)."
				ORDER BY ".$orderby." ".$pages_limit;
		$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
		
		if ($result->num_rows > 0) :
			//$i = 0;
			$existen_propiedades = true;
			while ($row = $result->fetch_assoc()) : ?>
				<tr id="propiedad-<?php echo $row['id_propiedad'];?>">
				<td class="checkbox"><input type="checkbox" class="checkpropiedades" id="check-<?php echo $row['id_propiedad'];?>" name="propiedades_visitar[]" value="<?php echo $row['id_propiedad'];?>" /></td>
				<td><?php echo $row['cod_propiedad'];?></td>
				<td><?php echo $row['tipo_propiedad'].'-'.$row['operacion'];?></td>
				<td><?php echo $row['sector'].' / '.$row['direccion'].' '.$row['num_direccion'];?></td>
				<td><?php echo $row['comuna'];?></td>
				<td><a href="propiedades.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" target="_blank" class="ficha"><img src="images/page_house.png" title="Ver ficha Propiedad" alt="Ficha" /></a></td>
				<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
				<td><?php echo horario_propiedad($row['id_propiedad'],$_GET['agregar_evento'],$_GET['id_vendedor'],$_GET['id_cliente']);?></td>
				<td><textarea name="observaciones[]" class="mini" cols="20" rows="1"><?php //echo $_GET['observaciones'][$i];?></textarea></td>
				</tr>
			<?php
			//++$i;
			endwhile;
			$result->close();
		else :
			echo '<tr><td colspan="9">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
		endif;
		
		else :
			echo '<tr><td colspan="9">Realice la búsqueda de propiedades para la orden de visitas...</td></tr>';
		endif; ?>
		</tbody>
	</table>
	<?php if ( $existen_propiedades == true ) { ?>
	<button type="button" class="button" id="guardar-orden"><img src="images/add-big.png" width="32" height="32" /><span> Guardar &amp; Enviar Orden de Visitas</span></button>
	<script type="text/javascript">
	$(document).ready(function() {
		$("#busqueda_propiedad").hide();
	});
	</script>
	<?php } ?>
	</form>
</div>