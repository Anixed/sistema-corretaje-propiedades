<?php require_once('connect-mysql.php'); require_once('functions.php');

if ( ( $_GET['accion'] == 'propiedades_cliente' && !empty($_GET['id_cliente']) && !empty($_GET['rut_cliente']) && !empty($_GET['nombre_cliente']) )
	|| ( isset($_GET['busqueda_rut']) && !empty($_GET['busqueda_rut']) && $_GET['busqueda_rut'] != 'BUSCAR' ) ) :
	
	$where = '';
	$tipo_propiedad = array();
	
	if ( isset($_GET['busqueda_rut']) && !empty($_GET['busqueda_rut']) && $_GET['busqueda_rut'] != 'BUSCAR' ) :
		
		$_GET['busqueda_rut'] = preg_replace('/[. ]/', '', $_GET['busqueda_rut']);
		$result = $mysqli->query("SELECT id_cliente, CONCAT(nombre_cliente, ' ', apellidos_cliente) AS nombre_cliente FROM clientes WHERE rut_cliente='".$_GET['busqueda_rut']."'") or die('Error: '.$mysqli->error);
		if ($result->num_rows > 0) :
			$row = $result->fetch_assoc();
			$id_cliente = $row['id_cliente'];
			$nombre_cliente = $row['nombre_cliente'].' ( R.U.T: '.formato_rut($_GET['busqueda_rut']).' )';
		endif;
		$result->close();
		
	elseif ( isset($_GET['id_cliente']) && !empty($_GET['id_cliente']) ) :
		$id_cliente = $_GET['id_cliente'];
		$nombre_cliente = $_GET['nombre_cliente'].' ( R.U.T: '.formato_rut($_GET['rut_cliente']).' )';
	endif;
	
	$sql = "SELECT propiedades.tipo_propiedad
			FROM propiedades,clientes
			WHERE clientes.id_cliente=propiedades.id_propietario AND
			clientes.id_cliente=".$id_cliente."
			GROUP BY propiedades.tipo_propiedad ORDER BY propiedades.tipo_propiedad DESC";
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	if ($result->num_rows > 0) :
		while ($row = $result->fetch_assoc()) :
			$where = "AND propiedades.id_propietario=".$id_cliente;
			
			switch ( $row['tipo_propiedad'] ) {
				case 'Casa':
					$tipo_propiedad['casa'] = $row['tipo_propiedad'];
					break;
				case 'Departamento':
					$tipo_propiedad['departamento'] = $row['tipo_propiedad'];
					break;
				case 'Oficina':
					$tipo_propiedad['oficina'] = $row['tipo_propiedad'];
					break;
				case 'Local':
					$tipo_propiedad['local'] = $row['tipo_propiedad'];
					break;
				case 'Parcela':
					$tipo_propiedad['parcela'] = $row['tipo_propiedad'];
					break;
				case 'Campo':
					$tipo_propiedad['campo'] = $row['tipo_propiedad'];
					break;
				case 'Sitio':
					$tipo_propiedad['sitio'] = $row['tipo_propiedad'];
					break;
				case 'Bodega':
					$tipo_propiedad['bodega'] = $row['tipo_propiedad'];
					break;
				default:
					unset($tipo_propiedad);
			}
		
		endwhile;
	else :
		unset($tipo_propiedad);
	endif;
	
	$result->close();
	unset($_GET,$sql);

else :
	$nombre_cliente = 'Ningún Cliente Seleccionado';
endif;
?>
<script type="text/javascript">
$(document).ready(function() {
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
				<div id="content-propiedades" class="content-propiedades-grilla">
				<form action="?accion=propiedades_cliente" method="get">
				<input type="hidden" name="accion" value="propiedades_cliente" />
					<div class="box-top-grilla left">
						<p>
							<span class="buscar_por">Buscar por R.U.T:</span>
							<input type="text" id="busqueda_rut" name="busqueda_rut" onfocus="if (value == 'BUSCAR') {value =''}" onblur="if (value == '') {value = 'BUSCAR'}" value="<?php echo ($_GET['busqueda_rut'])?$_GET['busqueda_rut']:'BUSCAR';?>" tabindex="1" maxlength="12" />
					        <input id="filtrar-propiedades" class="submit" type="submit" value="Filtrar" tabindex="2" />
						</p>
					</div>
					<div class="box-top-grilla nom_propietario left">
						<span>Propiedades vinculadas a los clientes.</span><br />
						<span class="nombre_propietario">&mdash; <?php echo $nombre_cliente; ?></span>
					</div>
				</form>
					<?php
					$class_color = 'class="alt"';
					$patrones = array('/^0$/', '/^0.00$/');
					$sustituciones = array('-', '-');
					
					if ( $_SESSION['tipo_sistema'] == 4 ) {
						$vendedor = '&vendedor='.$_SESSION['id_sistema'];
					}
					?>
					<?php if ( !isset($tipo_propiedad) ) : ?>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla">
						<thead>
							<tr><th>Propiedades vinculadas</th></tr>
						</thead>
						<tbody>
							<tr><td>El cliente no tiene propiedades ingresadas.</td></tr>
						</tbody>
					</table>
					<?php endif; ?>
					
					<?php // ___________________________ Grilla Casas ___________________________
					if ( $tipo_propiedad['casa'] == 'Casa' ) : ?>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla">
						<thead>
							<tr class="title-propiedades"><th colspan="5">Propiedades &mdash; Casas</th></tr>
							<tr>
								<th></th>
								<th>Código</th><th>Tipo</th><th>Operación</th><th>Comuna</th><th>Sector</th><th>Dirección</th>
								<th>M<span class="super">2</span>C</th><th>M<span class="super">2</span>T</th>
								<th colspan="3">Dormi. / Baños / Pisos</th><th>Valor</th><th>Fecha Ing.</th><th colspan="4">Opciones</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
											dormitorios, banos, num_pisos, superficie_total, superficie_construida
											FROM propiedades,caracteristicas_casa
											WHERE caracteristicas_casa.id_propiedad=propiedades.id_propiedad
											".$where."
											ORDER BY fecha_ingreso DESC";
							$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
							
							if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" name="propiedad-<?php echo $row['id_propiedad'];?>" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo $row['sector'];?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo (is_numeric($row['superficie_construida']))?number_format($row['superficie_construida'],2,',','.'):$row['superficie_construida'];?></td>
									<td><?php echo (is_numeric($row['superficie_total']))?number_format($row['superficie_total'],2,',','.'):$row['superficie_total'];?></td>
									<td><?php echo $row['dormitorios'];?></td>
									<td><?php echo $row['banos'];?></td>
									<td><?php echo $row['num_pisos'];?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="18">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
							endif; ?>
						</tbody>
					</table>
					
					<?php endif; ?>
					<?php // ___________________________ Grilla Departamentos ___________________________
					if ( $tipo_propiedad['departamento'] == 'Departamento' ) : ?>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla">
						<thead>
							<tr class="title-propiedades"><th colspan="5">Propiedades &mdash; Departamentos</th></tr>
							<tr>
								<th></th>
								<th>Código</th><th>Tipo</th><th>Operación</th><th>Comuna</th><th>Sector</th><th>Dirección</th><th>Orientación</th>
								<th>M<span class="super">2</span>C</th><th colspan="4">Dormi. / Baños / Estac. / Terraza</th>
								<th>Valor</th><th>Fecha Ing.</th><th colspan="4">Opciones</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
										dormitorios, banos, num_estacionamientos, superficie_construida, otras_caracteristicas
										FROM propiedades,caracteristicas_departamento
										WHERE caracteristicas_departamento.id_propiedad=propiedades.id_propiedad
										".$where."
										ORDER BY fecha_ingreso DESC";
						$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						
						if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
								
									$otras_caract_array = unserialize($row['otras_caracteristicas']);
									$row['terraza'] = ( $otras_caract_array['terraza'] ) ? $otras_caract_array['terraza'] : '-' ;
									unset($row['otras_caracteristicas'], $otras_caract_array);
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" name="propiedad-<?php echo $row['id_propiedad'];?>" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo $row['sector'];?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo $row['orientacion'];?></td>
									<td><?php echo (is_numeric($row['superficie_construida']))?number_format($row['superficie_construida'],2,',','.'):$row['superficie_construida'];?></td>
									<td><?php echo $row['dormitorios'];?></td>
									<td><?php echo $row['banos'];?></td>
									<td><?php echo $row['num_estacionamientos'];?></td>
									<td><?php echo $row['terraza'];?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="19">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
							endif; ?>
						</tbody>
					</table>
					
					<?php endif; ?>
					<?php // ___________________________ Grilla Oficinas y Locales ___________________________
					if ( $tipo_propiedad['oficina'] == 'Oficina' || $tipo_propiedad['local'] == 'Local' ) : ?>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla">
						<thead>
							<tr class="title-propiedades"><th colspan="5">Propiedades &mdash; Oficinas &amp; Locales</th></tr>
							<tr>
								<th></th>
								<th>Código</th><th>Tipo</th><th>Operación</th><th>Comuna</th><th>Sector</th><th>Dirección</th>
								<th>M<span class="super">2</span>C</th><th colspan="3">Baños / Nº Priv. / Nº Estac.</th>
								<th>Valor</th><th>Fecha Ing.</th><th colspan="4">Opciones</th>
							</tr>
						</thead>
						<tbody>
						<?php
						if ( $tipo_propiedad['oficina'] == 'Oficina' ) :
						$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
										banos, num_privados, num_estacionamientos, superficie_construida
										FROM propiedades,caracteristicas_oficina
										WHERE caracteristicas_oficina.id_propiedad=propiedades.id_propiedad
										".$where."
										ORDER BY fecha_ingreso DESC";
						$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						
						if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" name="propiedad-<?php echo $row['id_propiedad'];?>" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo $row['sector'];?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo (is_numeric($row['superficie_construida']))?number_format($row['superficie_construida'],2,',','.'):$row['superficie_construida'];?></td>
									<td><?php echo $row['banos'];?></td>
									<td><?php echo $row['num_privados'];?></td>
									<td><?php echo $row['num_estacionamientos'];?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									</tr>
							<?php endwhile;
									$result->close();
								else :
									echo '<tr><td colspan="17">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
								endif;
							endif; ?>
							
							<?php
							if ( $tipo_propiedad['local'] == 'Local' ) :
							$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
											banos, num_privados, num_estacionamientos, superficie_construida
											FROM propiedades,caracteristicas_local
											WHERE caracteristicas_local.id_propiedad=propiedades.id_propiedad
											".$where."
											ORDER BY fecha_ingreso DESC";
							$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
							
							if ($result->num_rows > 0) :
									while ($row = $result->fetch_assoc()) :
										$row = preg_replace($patrones, $sustituciones, $row);
										
										$class_color = ($class_color == '')?' class="alt"':'';?>
										<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
										<td class="checkbox"><input type="checkbox" class="checkpropiedades" name="propiedad-<?php echo $row['id_propiedad'];?>" /></td>
										<td><?php echo $row['cod_propiedad'];?></td>
										<td><?php echo $row['tipo_propiedad'];?></td>
										<td><?php echo $row['operacion'];?></td>
										<td><?php echo $row['comuna'];?></td>
										<td><?php echo $row['sector'];?></td>
										<td><?php echo $row['direccion'];?></td>
										<td><?php echo (is_numeric($row['superficie_construida']))?number_format($row['superficie_construida'],2,',','.'):$row['superficie_construida'];?></td>
										<td><?php echo $row['banos'];?></td>
										<td><?php echo $row['num_privados'];?></td>
										<td><?php echo $row['num_estacionamientos'];?></td>
										<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
										<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
										<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
										<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
										<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
										<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
										</tr>
								<?php endwhile;
										$result->close();
									else :
										echo '<tr><td colspan="17">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
									endif;
								endif; ?>
						</tbody>
					</table>
					
					<?php endif; ?>
					<?php // ___________________________ Grilla Parcelas y Campos ___________________________
					if ( $tipo_propiedad['parcela'] == 'Parcela' || $tipo_propiedad['campo'] == 'Campo' ) : ?>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla">
						<thead>
							<tr class="title-propiedades"><th colspan="5">Propiedades &mdash; Parcelas &amp; Campos</th></tr>
							<tr>
								<th></th>
								<th>Código</th><th>Tipo</th><th>Operación</th><th>Comuna</th><th>Sector</th><th>Dirección</th>
								<th>Superficie Hás.</th><th>Tipo Suelo</th><th>Aptitudes</th>
								<th>Valor</th><th>Fecha Ing.</th><th colspan="4">Opciones</th>
							</tr>
						</thead>
						<tbody>
						<?php
						if ( $tipo_propiedad['parcela'] == 'Parcela' ) :
						$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
										hectas_superficie, clasificacion_suelos, aptitudes
										FROM propiedades,caracteristicas_parcela
										WHERE caracteristicas_parcela.id_propiedad=propiedades.id_propiedad
										".$where."
										ORDER BY fecha_ingreso DESC";
						$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						
						if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									
									$aptitudes_array = unserialize($row['aptitudes']);
									unset($row['aptitudes']);
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" name="propiedad-<?php echo $row['id_propiedad'];?>" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo $row['sector'];?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo (is_numeric($row['hectas_superficie']))?number_format($row['hectas_superficie'],2,',','.'):$row['hectas_superficie'];?></td>
									<td><?php echo $row['clasificacion_suelos'];?></td>
									<td><?php echo implode(', ', $aptitudes_array);?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Orden de Visita" alt="Agenda Visitas" /></a></td>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									</tr>
							<?php endwhile;
									$result->close();
								else :
									echo '<tr><td colspan="16">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
								endif;
							endif;?>
							
							<?php
							if ( $tipo_propiedad['campo'] == 'Campo' ) :
							$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
											hectas_superficie, clasificacion_suelos, aptitudes
											FROM propiedades,caracteristicas_campo
											WHERE caracteristicas_campo.id_propiedad=propiedades.id_propiedad
											".$where."
											ORDER BY fecha_ingreso DESC";
							$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
							
							if ($result->num_rows > 0) :
									while ($row = $result->fetch_assoc()) :
										
										$aptitudes_array = unserialize($row['aptitudes']);
										unset($row['aptitudes']);
										$row = preg_replace($patrones, $sustituciones, $row);
										
										$class_color = ($class_color == '')?' class="alt"':'';?>
										<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
										<td class="checkbox"><input type="checkbox" class="checkpropiedades" name="propiedad-<?php echo $row['id_propiedad'];?>" /></td>
										<td><?php echo $row['cod_propiedad'];?></td>
										<td><?php echo $row['tipo_propiedad'];?></td>
										<td><?php echo $row['operacion'];?></td>
										<td><?php echo $row['comuna'];?></td>
										<td><?php echo $row['sector'];?></td>
										<td><?php echo $row['direccion'];?></td>
										<td><?php echo (is_numeric($row['hectas_superficie']))?number_format($row['hectas_superficie'],2,',','.'):$row['hectas_superficie'];?></td>
										<td><?php echo $row['clasificacion_suelos'];?></td>
										<td><?php echo implode(', ', $aptitudes_array);?></td>
										<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
										<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
										<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
										<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
										<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
										<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
										</tr>
								<?php endwhile;
										$result->close();
									else :
										echo '<tr><td colspan="16">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
									endif;
								endif;?>
						</tbody>
					</table>
					
					<?php endif; ?>
					<?php // ___________________________ Grilla Sitios ___________________________
					if ( $tipo_propiedad['sitio'] == 'Sitio' ) : ?>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla">
						<thead>
							<tr class="title-propiedades"><th colspan="5">Propiedades &mdash; Sitios</th></tr>
							<tr>
								<th></th>
								<th>Código</th><th>Tipo</th><th>Operación</th><th>Comuna</th><th>Sector</th><th>Dirección</th>
								<th>M<span class="super">2</span>Frente</th><th>M<span class="super">2</span>Fondo</th><th>M<span class="super">2</span>T. Terreno</th>
								<th>Valor</th><th>Fecha Ing.</th><th colspan="4">Opciones</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
										superficie_total, mtrs_frente, mtrs_fondo
										FROM propiedades,caracteristicas_sitio
										WHERE caracteristicas_sitio.id_propiedad=propiedades.id_propiedad
										".$where."
										ORDER BY fecha_ingreso DESC";
						$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						
						if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" name="propiedad-<?php echo $row['id_propiedad'];?>" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo $row['sector'];?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo (is_numeric($row['mtrs_frente']))?number_format($row['mtrs_frente'],2,',','.'):$row['mtrs_frente'];?></td>
									<td><?php echo (is_numeric($row['mtrs_fondo']))?number_format($row['mtrs_fondo'],2,',','.'):$row['mtrs_fondo'];?></td>
									<td><?php echo (is_numeric($row['superficie_total']))?number_format($row['superficie_total'],2,',','.'):$row['superficie_total'];?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="16">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
							endif; ?>
						</tbody>
					</table>
					
					<?php endif; ?>
					<?php // ___________________________ Grilla Bodegas ___________________________
					if ( $tipo_propiedad['bodega'] == 'Bodega' ) : ?>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla">
						<thead>
							<tr class="title-propiedades"><th colspan="5">Propiedades &mdash; Bodegas</th></tr>
							<tr>
								<th></th>
								<th>Código</th><th>Tipo</th><th>Operación</th><th>Comuna</th><th>Sector</th><th>Dirección</th>
								<th>M<span class="super">2</span>C</th><th>M<span class="super">2</span>T</th>
								<th>M<span class="super">2</span>Frente</th><th>M<span class="super">2</span>Fondo</th>
								<th>Valor</th><th>Fecha Ing.</th><th colspan="4">Opciones</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$sql = "SELECT propiedades.id_propiedad, cod_propiedad, tipo_propiedad, operacion, valor, tipo_valor, CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, fecha_ingreso,
										superficie_total, superficie_construida, mtrs_frente, mtrs_fondo
										FROM propiedades,caracteristicas_bodega
										WHERE caracteristicas_bodega.id_propiedad=propiedades.id_propiedad
										".$where."
										ORDER BY fecha_ingreso DESC";
						$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
						
						if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) :
									$row = preg_replace($patrones, $sustituciones, $row);
									
									$class_color = ($class_color == '')?' class="alt"':'';?>
									<tr id="propiedad-<?php echo $row['id_propiedad'];?>" <?php echo $class_color;?>>
									<td class="checkbox"><input type="checkbox" class="checkpropiedades" name="propiedad-<?php echo $row['id_propiedad'];?>" /></td>
									<td><?php echo $row['cod_propiedad'];?></td>
									<td><?php echo $row['tipo_propiedad'];?></td>
									<td><?php echo $row['operacion'];?></td>
									<td><?php echo $row['comuna'];?></td>
									<td><?php echo $row['sector'];?></td>
									<td><?php echo $row['direccion'];?></td>
									<td><?php echo (is_numeric($row['superficie_construida']))?number_format($row['superficie_construida'],2,',','.'):$row['superficie_construida'];?></td>
									<td><?php echo (is_numeric($row['superficie_total']))?number_format($row['superficie_total'],2,',','.'):$row['superficie_total'];?></td>
									<td><?php echo (is_numeric($row['mtrs_frente']))?number_format($row['mtrs_frente'],2,',','.'):$row['mtrs_frente'];?></td>
									<td><?php echo (is_numeric($row['mtrs_fondo']))?number_format($row['mtrs_fondo'],2,',','.'):$row['mtrs_fondo'];?></td>
									<td><?php echo ( $row['tipo_valor']=='$' ) ? '$'.number_format($row['valor'],0,',','.').'.-' : number_format($row['valor'],0,',','.').'.- UF' ;?></td>
									<td><?php echo mysql_to_normal($row['fecha_ingreso']);?></td>
									<td><a href="propiedades-form.php?accion=editar_propiedad&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="editar"><img src="images/page_edit.png" title="Editar Propiedad" alt="Editar" /></a></td>
									<td><a href="google_map.php?accion=mapa_propiedad_consultar&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>" class="google-maps"><img src="images/map_magnify.png" title="Google Map" alt="Mapa" /></a></td>
									<td><a href="agenda_visitas.php?accion=visitas_propiedad<?php echo $vendedor;?>&id_propiedad=<?php echo $row['id_propiedad'];?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>"><img src="images/vcard.png" title="Agenda Visitas" alt="Agenda Visitas" /></a></td>
									<td><a href="propiedades-form.php?accion=eliminar_propiedad&id_propiedad=<?php echo $row['id_propiedad']?>&cod_propiedad=<?php echo $row['cod_propiedad'];?>&tipo_propiedad=<?php echo $row['tipo_propiedad'];?>&referer=clientes-propiedades-grilla" class="delete"><img src="images/page_delete.png" title="Eliminar Propiedad" alt="Eliminar" /></a></td>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="17">No se han encontrando propiedades existentes en la base de datos...</td></tr>';
							endif; ?>
						</tbody>
					</table>
					<?php endif; ?>
			    </div><!--/content-propiedades-grilla-->