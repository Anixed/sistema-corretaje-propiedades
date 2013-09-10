<?php require_once('connect-mysql.php'); require_once('functions.php');
require_once('functions-privilegios-acceso.php');
session_start();

//Declarado para que por defecto muestre la grilla según tipo Casa
$_GET['tipo_propiedad'] = ( !empty($_GET['tipo_propiedad']) ) ? $_GET['tipo_propiedad'] : 'Casa' ;

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
	$orderby = $_GET['orderby'].' '.$ordenar_busqueda;
} else {
	$ordenar_busqueda = (!empty($_GET['ordenar_busqueda']))?$_GET['ordenar_busqueda']:'DESC';
	$orderby = 'id_propiedad '.$ordenar_busqueda; //'fecha_ingreso '.$ordenar_busqueda;
}

$where = '';
if ( ( isset($_GET['tipo_busqueda']) && !empty($_GET['tipo_busqueda']) ) && ( !empty($_GET['busqueda_text']) && $_GET['busqueda_text'] != 'BUSCAR') ) {
	
	if ( $_GET['tipo_busqueda'] == 'cod_propiedad' ) {
		$where .= " AND cod_propiedad='".trim($_GET['busqueda_text'])."'";
		unset($_GET['tipo_propiedad'], $_GET['operacion']);
	} else {
		$where .= " AND ".$_GET['tipo_busqueda']." LIKE '%".trim($_GET['busqueda_text'])."%'";
	}
	
}

if ( isset($_GET['tipo_propiedad']) && !empty($_GET['tipo_propiedad']) ) {
	$where .= " AND tipo_propiedad='".$_GET['tipo_propiedad']."'";
}

if ( isset($_GET['operacion']) && !empty($_GET['operacion']) ) {
	$where .= " AND operacion='".$_GET['operacion']."'";
}

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

if ( !empty($_GET['precio1']) && !empty($_GET['precio2'])
	&& $_GET['precio1'] < $_GET['precio2'] ) {
	$_GET['precio1'] = preg_replace('/[^0-9]/','',$_GET['precio1']);
	$_GET['precio2'] = preg_replace('/[^0-9]/','',$_GET['precio2']);
	$where .= " AND tipo_valor='".$_GET['tipo_valor']."' AND valor BETWEEN '".$_GET['precio1']."' AND '".$_GET['precio2']."'";
}
?>
<script type="text/javascript">
$(document).ready(function() {
    $('.box-top-grilla').on('change', '#ordenar_busqueda', function(event) { //$("#ordenar_busqueda").change(function() {
		$("#parametros-busqueda").submit();
    });
    
    $('.box-top-grilla').on('change', '#tipo_busqueda', function(event) { //$("#tipo_busqueda").change(function() {
    	if ( $(this).val() != '' ) {
    		$('#busqueda_text').val('').focus(); //$('#busqueda_text').attr('value','');
		}
    });
    
    $('.box-top-grilla').on('change', '#tipo_propiedad, #operacion', function(event) {
    	if ( $('#tipo_busqueda').val() == 'cod_propiedad' && ( $('#busqueda_text').val() != '' && $('#busqueda_text').val() != 'BUSCAR' ) ) {
    		$('#busqueda_text').val('');
		}
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
});
</script>
<script type="text/javascript">
$(document).ready(function() {
	<?php if ( empty($_GET['superficie_construida1']) &&
		empty($_GET['superficie_construida2']) &&
		empty($_GET['precio1']) &&
		empty($_GET['precio2']) &&
		empty($_GET['dormitorios']) &&
		empty($_GET['banos']) ) { ?>
	$("#busqueda_avanzada").hide();
	<?php } ?>
	$('#content-propiedades').on('click', '.avanzada', function(event) {
		$('#busqueda_avanzada').slideToggle('normal');
		event.preventDefault();
	});
});
</script>
				<div id="box-popup">
					<a href="javascript:void(0);" class="cerrar"><img src="images/close2.png" title="Cerrar" alt="Cerrar" /></a>
					<div class="content-popup"></div>
				</div>
				<div id="content-propiedades" class="content-propiedades-grilla">
				<form action="" method="get" id="parametros-busqueda">
					<div class="box-top-grilla left">
						<span class="buscar_por">Ingresar:</span><br />
						<a href="propiedades-form.php?referer=propiedades-grilla" class="submit" id="agregar-propiedad">Nueva Propiedad</a>
					</div>
					<div class="box-top-grilla left">
						<span class="buscar_por">Buscar por:</span><br />
						<p>
							<select name="tipo_busqueda" class="text" id="tipo_busqueda" tabindex="1">
								<option value="" <?php echo ($_GET['tipo_busqueda'] == '')?'selected="selected"':'';?>>Seleccionar</option>
								<option value="cod_propiedad" <?php echo ($_GET['tipo_busqueda'] == 'cod_propiedad')?'selected="selected"':'';?>>Código</option>
								<option value="comuna" <?php echo ($_GET['tipo_busqueda'] == 'comuna')?'selected="selected"':'';?>>Comuna</option>
								<option value="sector" <?php echo ($_GET['tipo_busqueda'] == 'sector')?'selected="selected"':'';?>>Sector</option>
								<option value="direccion" <?php echo ($_GET['tipo_busqueda'] == 'direccion')?'selected="selected"':'';?>>Dirección</option>
							</select>
							<input type="text" id="busqueda_text" class="text" name="busqueda_text" onfocus="if (value == 'BUSCAR') {value =''}" onblur="if (value == '') {value = 'BUSCAR'}" value="<?php echo ($_GET['busqueda_text'])?$_GET['busqueda_text']:'BUSCAR';?>" tabindex="2" maxlength="50" />
							<span class="buscar_por">|</span>
							<select name="operacion" class="text" id="operacion" tabindex="4">
								<option value="" <?php echo ($_GET['operacion'] == '')?'selected="selected"':'';?>>Operación</option>
								<option value="Venta" <?php echo ($_GET['operacion'] == 'Venta')?'selected="selected"':'';?>>Venta</option>
								<option value="Arriendo" <?php echo ($_GET['operacion'] == 'Arriendo')?'selected="selected"':'';?>>Arriendo</option>
							</select>
							<span class="buscar_por">|</span>
							<select name="tipo_propiedad" class="text" id="tipo_propiedad" tabindex="3">
								<option value="" <?php echo ($_GET['tipo_propiedad'] == '')?'selected="selected"':'';?>>Tipo Propiedad</option>
								<option value="Casa" <?php echo ($_GET['tipo_propiedad'] == 'Casa')?'selected="selected"':'';?>>Casa</option>
								<option value="Departamento" <?php echo ($_GET['tipo_propiedad'] == 'Departamento')?'selected="selected"':'';?>>Departamento</option>
								<option value="Oficina" <?php echo ($_GET['tipo_propiedad'] == 'Oficina')?'selected="selected"':'';?>>Oficina</option>
								<option value="Local" <?php echo ($_GET['tipo_propiedad'] == 'Local')?'selected="selected"':'';?>>Local</option>
								<option value="Parcela" <?php echo ($_GET['tipo_propiedad'] == 'Parcela')?'selected="selected"':'';?>>Parcela</option>
								<option value="Campo" <?php echo ($_GET['tipo_propiedad'] == 'Campo')?'selected="selected"':'';?>>Campo</option>
								<option value="Sitio" <?php echo ($_GET['tipo_propiedad'] == 'Sitio')?'selected="selected"':'';?>>Sitio</option>
								<option value="Bodega" <?php echo ($_GET['tipo_propiedad'] == 'Bodega')?'selected="selected"':'';?>>Bodega</option>
							</select>
					        <input name="accion" id="filtrar-propiedades" class="submit" type="submit" value="Filtrar" tabindex="5" />
						</p>
						<span class="buscar_por avanzada">+ Busqueda Avanzada:</span>
						<div id="busqueda_avanzada">
							<span class="left" style="display: block; margin-right: 20px;">
								<span class="blue">Superficie construida (m2):</span>
								Desde <input type="text" id="superficie_construida1" size="9" name="superficie_construida1" value="<?php echo ($_GET['superficie_construida1'])?number_format($_GET['superficie_construida1'],2,',','.'):'';?>" maxlength="18" />
								Hasta <input type="text" id="superficie_construida2" size="9" name="superficie_construida2" value="<?php echo ($_GET['superficie_construida2'])?number_format($_GET['superficie_construida2'],2,',','.'):'';?>" maxlength="18" />
							</span>
							<span class="left" style="display: block; margin-right: 10px;">
								<span class="blue">Rango Precio:</span>
								Desde <input type="text" id="precio1" size="11" name="precio1" value="<?php echo ($_GET['precio1'])?number_format($_GET['precio1'],0,',','.'):'';?>" maxlength="18" />
								Hasta <input type="text" id="precio2" size="11" name="precio2" value="<?php echo ($_GET['precio2'])?number_format($_GET['precio2'],0,',','.'):'';?>" maxlength="18" />
								<select name="tipo_valor" style="width: 50px;" id="tipo_valor">
									<option value="$" <?php echo ($_GET['tipo_valor'] == '$')?'selected="selected"':'';?>>$</option>
									<option value="U.F." <?php echo ($_GET['tipo_valor'] == 'U.F.')?'selected="selected"':'';?>>U.F.</option>
								</select>
							</span>
							<div class="clear"></div>
							<span class="left" style="display: block; margin-right: 10px;">
								<span class="blue">Dormitorios:</span>
								<input type="text" id="dormitorios" size="4" name="dormitorios" value="<?php echo $_GET['dormitorios'];?>" maxlength="2" />
							</span>
							<span class="left" style="display: block; margin-right: 10px;">
								<span class="blue">Baños:</span>
								<input type="text" id="banos" size="4" name="banos" value="<?php echo $_GET['banos'];?>" maxlength="2" />
							</span>
						</div>
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
					<div class="box-top-grilla left" style="border-right: none; padding-right: 0;">
						<span class="buscar_por">Carta Oferta:</span><br />
						<a href="javascript:void(0);" class="submit" id="enviar-propiedad" title="Enviar carta de oferta con las propiedades seleccionadas">Enviar Propiedad</a>
					</div>
				</form>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla">
			        <form action="propiedades-carta-oferta.php" method="post" id="form-prop-carta-oferta">
						<thead>
							<tr>
								<th class="checkbox"><input type="checkbox" id="check-todos" name="check-todos" title="Seleccionar Todos" /></th>
								<?php if ( $_GET['tipo_propiedad'] == '' || $_GET['tipo_propiedad'] == 'Casa' ) : ?>
								<th><a href="<?php echo $url_get;?>orderby=id_propiedad">Código</a></th>
								<th><a href="<?php echo $url_get;?>orderby=tipo_propiedad">Tipo</a></th>
								<th><a href="<?php echo $url_get;?>orderby=operacion">Operación</a></th>
								<th><a href="<?php echo $url_get;?>orderby=comuna">Comuna</a></th>
								<th><a href="<?php echo $url_get;?>orderby=sector">Sector</a></th>
								<th><a href="<?php echo $url_get;?>orderby=direccion">Dirección</a></th>
								<th><a href="<?php echo $url_get;?>orderby=superficie_construida">M<span class="super">2</span>C</a></th>
								<th><a href="<?php echo $url_get;?>orderby=superficie_total">M<span class="super">2</span>T</a></th>
								<th colspan="3">Dormi. / Baños / Pisos</th>
								<th><a href="<?php echo $url_get;?>orderby=valor">Valor</a></th>
								<th><a href="<?php echo $url_get;?>orderby=fecha_ingreso">Fecha Ing.</a></th>
								<th colspan="4">Opciones</th>
								<?php elseif ( $_GET['tipo_propiedad'] == 'Departamento' ) : ?>
								<th><a href="<?php echo $url_get;?>orderby=id_propiedad">Código</a></th>
								<th><a href="<?php echo $url_get;?>orderby=tipo_propiedad">Tipo</a></th>
								<th><a href="<?php echo $url_get;?>orderby=operacion">Operación</a></th>
								<th><a href="<?php echo $url_get;?>orderby=comuna">Comuna</a></th>
								<th><a href="<?php echo $url_get;?>orderby=sector">Sector</a></th>
								<th><a href="<?php echo $url_get;?>orderby=direccion">Dirección</a></th>
								<th><a href="<?php echo $url_get;?>orderby=orientacion">Orientación</a></th>
								<th><a href="<?php echo $url_get;?>orderby=superficie_construida">M<span class="super">2</span>C</a></th>
								<th colspan="4">Dormi. / Baños / Estac. / Terraza</th>
								<th><a href="<?php echo $url_get;?>orderby=valor">Valor</a></th>
								<th><a href="<?php echo $url_get;?>orderby=fecha_ingreso">Fecha Ing.</a></th>
								<th colspan="4">Opciones</th>
								<?php elseif ( $_GET['tipo_propiedad'] == 'Oficina' || $_GET['tipo_propiedad'] == 'Local' ) : ?>
								<th><a href="<?php echo $url_get;?>orderby=id_propiedad">Código</a></th>
								<th><a href="<?php echo $url_get;?>orderby=tipo_propiedad">Tipo</a></th>
								<th><a href="<?php echo $url_get;?>orderby=operacion">Operación</a></th>
								<th><a href="<?php echo $url_get;?>orderby=comuna">Comuna</a></th>
								<th><a href="<?php echo $url_get;?>orderby=sector">Sector</a></th>
								<th><a href="<?php echo $url_get;?>orderby=direccion">Dirección</a></th>
								<th><a href="<?php echo $url_get;?>orderby=superficie_construida">M<span class="super">2</span>C</a></th>
								<th colspan="3">Baños / Nº Priv. / Nº Estac.</th>
								<th><a href="<?php echo $url_get;?>orderby=valor">Valor</a></th>
								<th><a href="<?php echo $url_get;?>orderby=fecha_ingreso">Fecha Ing.</a></th>
								<th colspan="4">Opciones</th>
								<?php elseif ( $_GET['tipo_propiedad'] == 'Parcela' || $_GET['tipo_propiedad'] == 'Campo' ) : ?>
								<th><a href="<?php echo $url_get;?>orderby=id_propiedad">Código</a></th>
								<th><a href="<?php echo $url_get;?>orderby=tipo_propiedad">Tipo</a></th>
								<th><a href="<?php echo $url_get;?>orderby=operacion">Operación</a></th>
								<th><a href="<?php echo $url_get;?>orderby=comuna">Comuna</a></th>
								<th><a href="<?php echo $url_get;?>orderby=sector">Sector</a></th>
								<th><a href="<?php echo $url_get;?>orderby=direccion">Dirección</a></th>
								<th><a href="<?php echo $url_get;?>orderby=hectas_superficie">Superficie Hás.</a></th>
								<th><a href="<?php echo $url_get;?>orderby=clasificacion_suelos">Tipo Suelo</a></th>
								<th>Aptitudes</th>
								<th><a href="<?php echo $url_get;?>orderby=valor">Valor</a></th>
								<th><a href="<?php echo $url_get;?>orderby=fecha_ingreso">Fecha Ing.</a></th>
								<th colspan="4">Opciones</th>
								<?php elseif ( $_GET['tipo_propiedad'] == 'Sitio' ) : ?>
								<th><a href="<?php echo $url_get;?>orderby=id_propiedad">Código</a></th>
								<th><a href="<?php echo $url_get;?>orderby=tipo_propiedad">Tipo</a></th>
								<th><a href="<?php echo $url_get;?>orderby=operacion">Operación</a></th>
								<th><a href="<?php echo $url_get;?>orderby=comuna">Comuna</a></th>
								<th><a href="<?php echo $url_get;?>orderby=sector">Sector</a></th>
								<th><a href="<?php echo $url_get;?>orderby=direccion">Dirección</a></th>
								<th><a href="<?php echo $url_get;?>orderby=mtrs_frente">M<span class="super">2</span>Frente</a></th>
								<th><a href="<?php echo $url_get;?>orderby=mtrs_fondo">M<span class="super">2</span>Fondo</a></th>
								<th><a href="<?php echo $url_get;?>orderby=superficie_total">M<span class="super">2</span>T. Terreno</a></th>
								<th><a href="<?php echo $url_get;?>orderby=valor">Valor</a></th>
								<th><a href="<?php echo $url_get;?>orderby=fecha_ingreso">Fecha Ing.</a></th>
								<th colspan="4">Opciones</th>
								<?php elseif ( $_GET['tipo_propiedad'] == 'Bodega' ) : ?>
								<th><a href="<?php echo $url_get;?>orderby=id_propiedad">Código</a></th>
								<th><a href="<?php echo $url_get;?>orderby=tipo_propiedad">Tipo</a></th>
								<th><a href="<?php echo $url_get;?>orderby=operacion">Operación</a></th>
								<th><a href="<?php echo $url_get;?>orderby=comuna">Comuna</a></th>
								<th><a href="<?php echo $url_get;?>orderby=sector">Sector</a></th>
								<th><a href="<?php echo $url_get;?>orderby=direccion">Dirección</a></th>
								<th><a href="<?php echo $url_get;?>orderby=superficie_construida">M<span class="super">2</span>C</a></th>
								<th><a href="<?php echo $url_get;?>orderby=superficie_total">M<span class="super">2</span>T</a></th>
								<th><a href="<?php echo $url_get;?>orderby=mtrs_frente">M<span class="super">2</span>Frente</a></th>
								<th><a href="<?php echo $url_get;?>orderby=mtrs_fondo">M<span class="super">2</span>Fondo</a></th>
								<th>Valor</th><th>Fecha Ing.</th><th colspan="4">Opciones</th>
								<?php endif; ?>
							</tr>
						</thead>
						<tbody>
						<?php
						$class_color = 'class="alt"';
						$patrones = array('/^0$/', '/^0.00$/');
						$sustituciones = array('-', '-');
						
						if ( $_SESSION['tipo_sistema'] == 4 ) {
							$vendedor = '&vendedor='.$_SESSION['id_sistema'];
						}
						
						// ___________________________ Grilla Casas ___________________________
						if ( $_GET['tipo_propiedad'] == '' || $_GET['tipo_propiedad'] == 'Casa' ) :
							$pages_limit = paginar_resultados("SELECT propiedades.id_propiedad FROM propiedades,caracteristicas_casa
												WHERE caracteristicas_casa.id_propiedad=propiedades.id_propiedad ".$where);
							
							$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
											dormitorios, banos, num_pisos, superficie_total, superficie_construida
											FROM propiedades,caracteristicas_casa
											WHERE caracteristicas_casa.id_propiedad=propiedades.id_propiedad
											".$where."
											ORDER BY ".$orderby." ".$pages_limit;
							$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
							
							if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" value="<?php echo $row['id_propiedad'];?>" name="propiedad[]" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo (!empty($row['sector']))?$row['sector']:'Ninguno';?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo (is_numeric($row['superficie_construida']))?number_format($row['superficie_construida'],2,',','.'):$row['superficie_construida'];?></td>
									<td><?php echo (is_numeric($row['superficie_total']))?number_format($row['superficie_total'],2,',','.'):$row['superficie_total'];?></td>
									<td><?php echo $row['dormitorios'];?></td>
									<td><?php echo $row['banos'];?></td>
									<td><?php echo $row['num_pisos'];?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<?php if ( is_admin($_SESSION['id_sistema']) ) { ?>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									<?php } else { ?>
									<td><img src="images/page_delete_no.png" width="16" height="16" /></td>
									<?php } ?>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="18">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
							endif; ?>
						
						<?php // ___________________________ Grilla Departamentos ___________________________
						elseif ( $_GET['tipo_propiedad'] == 'Departamento' ) :
						$pages_limit = paginar_resultados("SELECT propiedades.id_propiedad FROM propiedades,caracteristicas_departamento
											WHERE caracteristicas_departamento.id_propiedad=propiedades.id_propiedad ".$where);
						
						$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, num_depa, sector, comuna, fecha_ingreso,
										dormitorios, banos, num_estacionamientos, orientacion, superficie_construida, otras_caracteristicas
										FROM propiedades,caracteristicas_departamento
										WHERE caracteristicas_departamento.id_propiedad=propiedades.id_propiedad
										".$where."
										ORDER BY ".$orderby." ".$pages_limit;
						$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						
						if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
								
									$otras_caract_array = unserialize($row['otras_caracteristicas']);
									$row['terraza'] = ( $otras_caract_array['terraza'] ) ? $otras_caract_array['terraza'] : '-' ;
									unset($row['otras_caracteristicas'], $otras_caract_array);
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" value="<?php echo $row['id_propiedad'];?>" name="propiedad[]" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo (!empty($row['sector']))?$row['sector']:'Ninguno';?></td>
									<td><?php echo $row['direccion'];?><?php echo (!empty($row['num_depa']))?' Dpto. N°'.$row['num_depa']:'';?></td>
									<td><?php echo $row['orientacion'];?></td>
									<td><?php echo (is_numeric($row['superficie_construida']))?number_format($row['superficie_construida'],2,',','.'):$row['superficie_construida'];?></td>
									<td><?php echo $row['dormitorios'];?></td>
									<td><?php echo $row['banos'];?></td>
									<td><?php echo $row['num_estacionamientos'];?></td>
									<td><?php echo $row['terraza'];?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<?php if ( is_admin($_SESSION['id_sistema']) ) { ?>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									<?php } else { ?>
									<td><img src="images/page_delete_no.png" width="16" height="16" /></td>
									<?php } ?>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="19">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
							endif; ?>
						
						<?php // ___________________________ Grilla Oficinas y Locales ___________________________
						elseif ( $_GET['tipo_propiedad'] == 'Oficina' || $_GET['tipo_propiedad'] == 'Local' ) :
						$pages_limit = paginar_resultados("SELECT propiedades.id_propiedad FROM propiedades,caracteristicas_".strtolower($_GET['tipo_propiedad'])."
											WHERE caracteristicas_".strtolower($_GET['tipo_propiedad']).".id_propiedad=propiedades.id_propiedad ".$where);
						
						$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
										banos, num_privados, num_estacionamientos, superficie_construida
										FROM propiedades,caracteristicas_".strtolower($_GET['tipo_propiedad'])."
										WHERE caracteristicas_".strtolower($_GET['tipo_propiedad']).".id_propiedad=propiedades.id_propiedad
										".$where."
										ORDER BY ".$orderby." ".$pages_limit;
						$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						
						if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" value="<?php echo $row['id_propiedad'];?>" name="propiedad[]" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo (!empty($row['sector']))?$row['sector']:'Ninguno';?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo (is_numeric($row['superficie_construida']))?number_format($row['superficie_construida'],2,',','.'):$row['superficie_construida'];?></td>
									<td><?php echo $row['banos'];?></td>
									<td><?php echo $row['num_privados'];?></td>
									<td><?php echo $row['num_estacionamientos'];?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<?php if ( is_admin($_SESSION['id_sistema']) ) { ?>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									<?php } else { ?>
									<td><img src="images/page_delete_no.png" width="16" height="16" /></td>
									<?php } ?>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="17">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
							endif; ?>
						
						<?php // ___________________________ Grilla Parcelas y Campos ___________________________
						elseif ( $_GET['tipo_propiedad'] == 'Parcela' || $_GET['tipo_propiedad'] == 'Campo' ) :
						$pages_limit = paginar_resultados("SELECT propiedades.id_propiedad FROM propiedades,caracteristicas_".strtolower($_GET['tipo_propiedad'])."
											WHERE caracteristicas_".strtolower($_GET['tipo_propiedad']).".id_propiedad=propiedades.id_propiedad ".$where);
						
						$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
										hectas_superficie, clasificacion_suelos, aptitudes
										FROM propiedades,caracteristicas_".strtolower($_GET['tipo_propiedad'])."
										WHERE caracteristicas_".strtolower($_GET['tipo_propiedad']).".id_propiedad=propiedades.id_propiedad
										".$where."
										ORDER BY ".$orderby." ".$pages_limit;
						$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						
						if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									
									$aptitudes_array = unserialize($row['aptitudes']);
									$aptitudes = (!empty($aptitudes_array))?implode(', ', $aptitudes_array):'Ninguna';
									unset($row['aptitudes'], $aptitudes_array);
									
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" value="<?php echo $row['id_propiedad'];?>" name="propiedad[]" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo (!empty($row['sector']))?$row['sector']:'Ninguno';?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo (is_numeric($row['hectas_superficie']))?number_format($row['hectas_superficie'],2,',','.'):$row['hectas_superficie'];?></td>
									<td><?php echo (!empty($row['clasificacion_suelos']))?$row['clasificacion_suelos']:'Ninguna';?></td>
									<td><?php echo $aptitudes;?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<?php if ( is_admin($_SESSION['id_sistema']) ) { ?>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									<?php } else { ?>
									<td><img src="images/page_delete_no.png" width="16" height="16" /></td>
									<?php } ?>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="16">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
							endif; ?>
							
						<?php // ___________________________ Grilla Sitios ___________________________
						elseif ( $_GET['tipo_propiedad'] == 'Sitio' ) :
						$pages_limit = paginar_resultados("SELECT propiedades.id_propiedad FROM propiedades,caracteristicas_sitio
											WHERE caracteristicas_sitio.id_propiedad=propiedades.id_propiedad ".$where);
						
						$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
										superficie_total, mtrs_frente, mtrs_fondo
										FROM propiedades,caracteristicas_sitio
										WHERE caracteristicas_sitio.id_propiedad=propiedades.id_propiedad
										".$where."
										ORDER BY ".$orderby." ".$pages_limit;
						$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						
						if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" value="<?php echo $row['id_propiedad'];?>" name="propiedad[]" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo (!empty($row['sector']))?$row['sector']:'Ninguno';?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo (is_numeric($row['mtrs_frente']))?number_format($row['mtrs_frente'],2,',','.'):$row['mtrs_frente'];?></td>
									<td><?php echo (is_numeric($row['mtrs_fondo']))?number_format($row['mtrs_fondo'],2,',','.'):$row['mtrs_fondo'];?></td>
									<td><?php echo (is_numeric($row['superficie_total']))?number_format($row['superficie_total'],2,',','.'):$row['superficie_total'];?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<?php if ( is_admin($_SESSION['id_sistema']) ) { ?>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									<?php } else { ?>
									<td><img src="images/page_delete_no.png" width="16" height="16" /></td>
									<?php } ?>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="16">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
							endif; ?>
							
						<?php // ___________________________ Grilla Bodegas ___________________________
						elseif ( $_GET['tipo_propiedad'] == 'Bodega' ) :
						$pages_limit = paginar_resultados("SELECT propiedades.id_propiedad FROM propiedades,caracteristicas_bodega
											WHERE caracteristicas_bodega.id_propiedad=propiedades.id_propiedad ".$where);
						
						$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
										superficie_total, superficie_construida, mtrs_frente, mtrs_fondo
										FROM propiedades,caracteristicas_bodega
										WHERE caracteristicas_bodega.id_propiedad=propiedades.id_propiedad
										".$where."
										ORDER BY ".$orderby." ".$pages_limit;
						$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						
						if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" value="<?php echo $row['id_propiedad'];?>" name="propiedad[]" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo (!empty($row['sector']))?$row['sector']:'Ninguno';?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo (is_numeric($row['superficie_construida']))?number_format($row['superficie_construida'],2,',','.'):$row['superficie_construida'];?></td>
									<td><?php echo (is_numeric($row['superficie_total']))?number_format($row['superficie_total'],2,',','.'):$row['superficie_total'];?></td>
									<td><?php echo (is_numeric($row['mtrs_frente']))?number_format($row['mtrs_frente'],2,',','.'):$row['mtrs_frente'];?></td>
									<td><?php echo (is_numeric($row['mtrs_fondo']))?number_format($row['mtrs_fondo'],2,',','.'):$row['mtrs_fondo'];?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<?php if ( is_admin($_SESSION['id_sistema']) ) { ?>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									<?php } else { ?>
									<td><img src="images/page_delete_no.png" width="16" height="16" /></td>
									<?php } ?>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="17">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
							endif; ?>
						<?php endif; ?>
						</tbody>
					</form>
					</table>
			    </div><!--/content-propiedades-grilla-->