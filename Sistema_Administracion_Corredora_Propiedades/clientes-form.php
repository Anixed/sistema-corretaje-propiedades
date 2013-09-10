<?php require_once('connect-mysql.php'); require_once('functions.php'); ?>
<?php
session_start();
//echo '<pre>'; print_r($_POST); echo '</pre>';

//Variable para comprobar si se esta editando el registro
if ( $_GET['accion'] == 'editar_cliente' || $_POST['accion'] == 'Modificar' ) {
	$editar_cliente = true;
}

//Comprueba que los campos necesarios esten completos
if ( !$_POST['accion'] ) :
	//Guarda la variable global POST en una variable local $form, por seguridad
	$form = array();
	foreach ($_POST as $key => $value) {
		$form[$key] = $value;
	}
	unset($_POST);
else :
	
	if ( isset($_POST['accion']) ) :
		
		$msgbox = array();
		$icon = '';
		$i = 0; $insert_sql_into = array(); $insert_sql_values = array();
		
		if ( !empty($_POST['rut_cliente']) && ( !isset($editar_cliente) && $editar_cliente != true ) ) {
			
			$rut_completo = $_POST['rut_cliente'].'-'.$_POST['digito_verificador'];
			if ( preg_match('/[0-9]{1,2}[.]?[0-9]{3}[.]?[0-9]{3}[-][0-9kK]{1}/', $rut_completo) ) {
				
				$rut_buscar = preg_replace('/[.]/', '', $rut_completo);
				if ( !existe_registro_cliente($rut_buscar, 'rut_cliente') ) {
					$insert_sql_into[$i] = 'rut_cliente'; $insert_sql_values[$i] = '\''.$rut_buscar.'\''; ++$i;
				} else {
					$msgbox[] = '<strong>El RUT ('.$rut_completo.') ya existe en la base de datos.</strong>';
					$icon = 'error';
				}
				
			} else {
				$msgbox[] = 'Formato de R.U.T incorrecto.';
				$icon = 'error';
			}
			
		} elseif ( !isset($editar_cliente) && $editar_cliente != true ) {
			$msgbox[] = 'Especifique el R.U.T del cliente.';
			$icon = 'error';
		}
		
		if ( !empty($_POST['nombre_cliente']) && !empty($_POST['apellidos_cliente']) ) {
			$insert_sql_into[$i] = 'nombre_cliente'; $insert_sql_values[$i] = '\''.trim($_POST['nombre_cliente']).'\''; ++$i;
			$insert_sql_into[$i] = 'apellidos_cliente'; $insert_sql_values[$i] = '\''.trim($_POST['apellidos_cliente']).'\''; ++$i;
		} else {
			$msgbox[] = 'Especifique el nombre y apellido del cliente.';
			$icon = 'error';
		}
		
		if ( !empty($_POST['direccion']) ) {
			$insert_sql_into[$i] = 'direccion'; $insert_sql_values[$i] = '\''.trim($_POST['direccion']).'\''; ++$i;
			if ( !empty($_POST['num_direccion']) ) {
				$_POST['num_direccion'] = preg_replace('/[#]/','',$_POST['num_direccion']);
				$insert_sql_into[$i] = 'num_direccion'; $insert_sql_values[$i] = '\''.trim($_POST['num_direccion']).'\''; ++$i;
			}
			if ( !empty($_POST['num_depa']) ) {
				$insert_sql_into[$i] = 'num_depa'; $insert_sql_values[$i] = '\''.trim($_POST['num_depa']).'\''; ++$i;
			}
		} else {
			$msgbox[] = 'Especifique correctamente la dirección de la propiedad.';
			$icon = 'error';
		}
		
		if ( empty($_POST['comuna']) ) {
			$msgbox[] = 'Especifique la comuna dónde vive el cliente.';
			$icon = 'error';
		} else { $insert_sql_into[$i] = 'comuna'; $insert_sql_values[$i] = '\''.$_POST['comuna'].'\''; ++$i; }
		
		if ( empty($_POST['ciudad']) ) {
			$msgbox[] = 'Especifique la ciudad dónde vive el cliente.';
			$icon = 'error';
		} else { $insert_sql_into[$i] = 'ciudad'; $insert_sql_values[$i] = '\''.trim($_POST['ciudad']).'\''; ++$i; }
		
		if ( !empty($_POST['sexo_cliente']) ) {
			$insert_sql_into[$i] = 'sexo_cliente'; $insert_sql_values[$i] = '\''.$_POST['sexo_cliente'].'\''; ++$i;
		}
		
		if ( !empty($_POST['telefono']) ) {
			$insert_sql_into[$i] = 'telefono'; $insert_sql_values[$i] = '\''.trim($_POST['telefono']).'\''; ++$i;
		}
		if ( !empty($_POST['celular']) ) {
			$insert_sql_into[$i] = 'celular'; $insert_sql_values[$i] = '\''.trim($_POST['celular']).'\''; ++$i;
		}
		if ( !empty($_POST['oficina']) ) {
			$insert_sql_into[$i] = 'oficina'; $insert_sql_values[$i] = '\''.trim($_POST['oficina']).'\''; ++$i;
		}
		if ( !empty($_POST['fax']) ) {
			$insert_sql_into[$i] = 'fax'; $insert_sql_values[$i] = '\''.trim($_POST['fax']).'\''; ++$i;
		}
		
		if ( !empty($_POST['email']) && preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST['email']) ) {
			
			if ( $_POST['email_registrado'] != $_POST['email'] ) {
				$_POST['email'] = trim($_POST['email']);
				//if ( !existe_registro_cliente($_POST['email'], 'email') ) {
					$insert_sql_into[$i] = 'email'; $insert_sql_values[$i] = '\''.$_POST['email'].'\''; ++$i;
				/*} else {
					$msgbox[] = '<strong>El E-mail del Cliente ya existe en la base de datos.</strong>';
					$icon = 'error';
				}*/
			}
			
		} else {
			$msgbox[] = 'Especifique correctamente el correo electrónico del cliente.';
			$icon = 'error';
		}
		
		if ( !empty($_POST['observaciones']) ) {
			$insert_sql_into[$i] = 'observaciones'; $insert_sql_values[$i] = '\''.trim($_POST['observaciones']).'\''; ++$i;
		}
		
		if ( !isset($editar_cliente) && $editar_cliente != true ) {
			if ( $_POST['fecha_ingreso'] == date("d/m/Y") ) {
				$insert_sql_into[$i] = 'fecha_ingreso'; $insert_sql_values[$i] = 'NOW()';
			} elseif ( !empty($_POST['fecha_ingreso']) && preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $_POST['fecha_ingreso']) ) {
				$insert_sql_into[$i] = 'fecha_ingreso'; $insert_sql_values[$i] = '\''.$_POST['fecha_ingreso'].'\'';
			} else {
				$msgbox[] = 'Especifique correctamente la fecha de ingreso.';
				$icon = 'error';
			}
		}
		
		//Campos del formulario clientes buscan
		if ( $_POST['busca_propiedad'] == 'Si' ) :
			$busca_sql_into = array(); $busca_sql_values = array();
			$patrones = array('/[^0-9,]/', '/[,]/');
			$sustituciones = array('', '.');
			
			if ( !empty($_POST['operacion']) ) {
				$busca_sql_into[$i] = 'operacion'; $busca_sql_values[$i] = '\''.$_POST['operacion'].'\''; ++$i;
			}			
			if ( !empty($_POST['tipo_propiedad']) ) {
				$tipo_propiedad = serialize($_POST['tipo_propiedad']);
				$busca_sql_into[$i] = 'tipo_propiedad'; $busca_sql_values[$i] = '\''.$tipo_propiedad.'\''; ++$i;
			}
			if ( !empty($_POST['comuna_propiedad']) ) {
				$comuna_propiedad = serialize($_POST['comuna_propiedad']);
				$busca_sql_into[$i] = 'comuna'; $busca_sql_values[$i] = '\''.$comuna_propiedad.'\''; ++$i;
			}
			if ( !empty($_POST['ciudad_propiedad']) ) {
				$busca_sql_into[$i] = 'ciudad'; $busca_sql_values[$i] = '\''.trim($_POST['ciudad_propiedad']).'\''; ++$i;
			}
			if ( !empty($_POST['sector']) ) {
				$busca_sql_into[$i] = 'sector'; $busca_sql_values[$i] = '\''.trim($_POST['sector']).'\''; ++$i;
			}
			if ( !empty($_POST['valor_desde']) ) {
				$valor_desde = preg_replace('/[^0-9]/','',$_POST['valor_desde']);
				$busca_sql_into[$i] = 'valor_desde'; $busca_sql_values[$i] = '\''.$valor_desde.'\''; ++$i;
			}
			if ( !empty($_POST['valor_hasta']) ) {
				$valor_hasta = preg_replace('/[^0-9]/','',$_POST['valor_hasta']);
				$busca_sql_into[$i] = 'valor_hasta'; $busca_sql_values[$i] = '\''.$valor_hasta.'\''; ++$i;
			}
			if ( !empty($_POST['tipo_valor']) ) {
				$busca_sql_into[$i] = 'tipo_valor'; $busca_sql_values[$i] = '\''.$_POST['tipo_valor'].'\''; ++$i;
			}
			if ( !empty($_POST['superficie_total']) ) {
				$_POST['superficie_total'] = preg_replace($patrones,$sustituciones,$_POST['superficie_total']);
				$busca_sql_into[$i] = 'superficie_total'; $busca_sql_values[$i] = '\''.$_POST['superficie_total'].'\''; ++$i;
			}
			if ( !empty($_POST['superficie_construida']) ) {
				$_POST['superficie_construida'] = preg_replace($patrones,$sustituciones,$_POST['superficie_construida']);
				$busca_sql_into[$i] = 'superficie_construida'; $busca_sql_values[$i] = '\''.$_POST['superficie_construida'].'\''; ++$i;
			}
			if ( !empty($_POST['observaciones_busqueda']) ) {
				$busca_sql_into[$i] = 'observaciones'; $busca_sql_values[$i] = '\''.trim($_POST['observaciones_busqueda']).'\''; ++$i;
			}
		endif;
		//=====================================
		
		if ( $icon == 'error' ) {
			$form = array();
			foreach ($_POST as $key => $value) {
				$form[$key] = $value;
			}
			unset($_POST, $insert_sql_into, $insert_sql_values, $busca_sql_into, $busca_sql_values);
		}
		
	endif;
endif;

//Guarda el cliente en la BD
if ($_POST['accion'] == 'Guardar') :
	
	$sql = "INSERT INTO clientes (".implode(',', $insert_sql_into).") VALUES (".implode(',', $insert_sql_values).")";
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	$msgbox = 'El Cliente ( R.U.T: '.$rut_completo.' ) se ha guardado satisfactoriamente.';
	$icon = 'info fin-ingreso';
	
	$id_cliente = $mysqli->insert_id; //Recupera el ID generado por la consulta anterior (normalmente INSERT) para una columna AUTO_INCREMENT.
	
	if ( $_POST['ingresando_propiedad'] == 'si' && $result == 1 ) :
		$_SESSION['propietario'] = array();
		$_SESSION['propietario']['nombre'] = '('.$rut_completo.') '.trim($_POST['nombre_cliente']).' '.trim($_POST['apellidos_cliente']);
		$_SESSION['propietario']['id'] = $id_cliente;
	endif;
	
	if ( $_POST['busca_propiedad'] == 'Si' && $result == 1 ) :
		$sql = "INSERT INTO clientes_buscan (
							id_cliente,
							".implode(',', $busca_sql_into).",
							fecha_ingreso
							) VALUES (
							".$id_cliente.",
							".implode(',', $busca_sql_values).",
							NOW())";
		$mysqli->query($sql) or die('Error: '.$mysqli->error);
		$msgbox .= ' | En busca de propiedad guardado correctamente.';
	endif;
	
	unset($_POST, $insert_sql_into, $insert_sql_values, $busca_sql_into, $busca_sql_values);
	
endif;

//Modificar el cliente
if ($_POST['accion'] == 'Modificar') :
	$id_cliente = $_POST['id_cliente'];
	
	if ( existe_registro_cliente($id_cliente, 'id_cliente') ) :
		$update_sql = '';
		$count = count($insert_sql_into);
		for($i=0; $i<$count; $i++) {
			$update_sql .= $insert_sql_into[$i].'='.$insert_sql_values[$i].', ';
		}
		$update_sql = substr($update_sql, 0, -2);
		$sql = "UPDATE clientes SET ".$update_sql." WHERE id_cliente=".$id_cliente;
		$mysqli->query($sql) or die('Error: '.$mysqli->error);
		
		$msgbox = 'El Cliente ( R.U.T: '.formato_rut($_POST['rut_cliente']).' ) se ha modificado satisfactoriamente.';
		$icon = 'info modificado';
		
		if ( $_POST['busca_propiedad'] == 'Si' ) :
			$sql = "INSERT INTO clientes_buscan (
								id_cliente,
								".implode(',', $busca_sql_into).",
								fecha_ingreso
								) VALUES (
								".$id_cliente.",
								".implode(',', $busca_sql_values).",
								NOW())";
			$mysqli->query($sql) or die('Error: '.$mysqli->error);
			$msgbox .= ' | En busca de propiedad guardado correctamente.';
		endif;
	else :
		$msgbox = 'No se ha encontrado el Cliente ID Nº:'.$id_cliente.'.';
		$icon = 'error';
	endif;
	unset($_POST, $insert_sql_into, $insert_sql_values, $editar_cliente, $update_sql, $busca_sql_into, $busca_sql_values);
	
endif;

//Editar cliente
if ( ($_GET['accion'] == 'editar_cliente') && !empty($_GET['id_cliente']) && !empty($_GET['rut_cliente']) ) :
	$id_cliente = $_GET['id_cliente'];
	
	$sql = "SELECT id_cliente, rut_cliente, nombre_cliente, apellidos_cliente, sexo_cliente, direccion, num_direccion, num_depa, comuna, ciudad, telefono, celular, oficina, fax, email, observaciones, fecha_ingreso
				FROM clientes
				WHERE id_cliente=".$id_cliente;
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ($form = $result->fetch_assoc()) {
		$msgbox = 'Editando el Cliente R.U.T: '.formato_rut($form['rut_cliente']);
		$icon = 'info';
	} else {
		$editar_cliente = false;
		$msgbox = 'No se ha encontrado el Cliente R.U.T: '.formato_rut($_GET['rut_cliente']);
		$icon = 'error';
	}
	$result->close();
	unset($_GET);
endif;

if ( isset($msgbox) && is_array($msgbox) ) {
	$msgbox = '<strong>ERROR:</strong><br />- '.implode('<br />- ', $msgbox);
}

?>
<script type="text/javascript">
$(document).ready(function() {
	$('#rut_cliente').focus();
	
	var msgbox = '<?php echo $msgbox; ?>';
	$("#msgbox2").addClass("<?php echo 'msgbox-'.$icon;?>").html(msgbox).show();
	
	<?php if ( empty($form['busca_propiedad']) ) { ?>
	$("#busqueda_propiedad").hide();
	<?php } ?>
	$('form#clientes-formulario').on('change', '#busca_propiedad', function(event) {
		if ( $(this).is(':checked') ) {
			$('#busqueda_propiedad').show();
		} else {
			$('#busqueda_propiedad').fadeOut("normal");
		}
		event.preventDefault();
	});
	//Al limpiar el formulario también cierra el clientes buscan en caso de estar abierto
	$('form#clientes-formulario').on('click', '#cleaner', function() {
		if ( !$(this).is(':checked') ) {
			$('#busqueda_propiedad').fadeOut("normal");
		}
	});
	
	/*---------------------------------------*/
	<?php if ( !empty($form['tipo_propiedad']) && is_array($form['tipo_propiedad']) ) {
	$propiedades = '';
	foreach ($form['tipo_propiedad'] as $tipo) {
		$propiedades .= '#tipo_propiedad option[value="'.$tipo.'"],';
	}
	$propiedades = substr($propiedades, 0, -1);
	?>
	$('<?php echo $propiedades;?>').attr("selected",true);
	<?php } ?>
	/*---------------------------------------*/
	<?php if ( !empty($form['comuna_propiedad']) && is_array($form['comuna_propiedad']) ) {
	$comuna = '';
	foreach ($form['comuna_propiedad'] as $valor) {
		$comuna .= '#comuna_propiedad option[value="'.$valor.'"],';
	}
	$comuna = substr($comuna, 0, -1);
	?>
	$('<?php echo $comuna;?>').attr("selected",true);
	<?php } ?>
	/*---------------------------------------*/
	
	$('#rut_cliente').Rut({
		digito_verificador: '#digito_verificador',
		on_error: function(){
			$('input[type="text"]#rut_cliente').css({'background-color' : '#FDD1C5', 'border' : '1px solid #E74B2F'});
			$('#digito_verificador').css({'background-color' : '#FDD1C5', 'border' : '1px solid #E74B2F'});
		},
		on_success: function(){
			$('input[type="text"]#rut_cliente').css({'background-color' : '#E7F9F8', 'border' : '1px solid #0566B7'});
			$('#digito_verificador').css({'background-color' : '#E7F9F8', 'border' : '1px solid #0566B7'});
		} 
	});
	<?php if ( $editar_cliente == true ) { ?>
	var rut = $('input[type="text"]#rut_cliente').val(); //$('#rut_cliente').val().split("-")[0];
	$('input[type="text"]#rut_cliente').val( $.Rut.formatear(rut) );
	<?php } ?>
});
</script>
				<form action="clientes-form.php" method="post" id="clientes-formulario">
				<?php if ( $editar_cliente == true ) { ?>
				<input type="hidden" name="accion" id="accion" value="Modificar" />
				<input type="hidden" name="id_cliente" id="id_cliente" value="<?php echo $form['id_cliente']; ?>" />
				<input type="hidden" name="rut_cliente" id="rut_cliente" value="<?php echo $form['rut_cliente']; ?>" />
				<?php } else { ?>
				<input type="hidden" name="accion" id="accion" value="Guardar" />
				<input type="hidden" name="ingresando_propiedad" id="ingresando_propiedad" value="<?php echo ( !empty($_GET['ingresando_propiedad']) ) ? $_GET['ingresando_propiedad'] : $form['ingresando_propiedad']; ?>" />
				<?php } ?>
					<fieldset>
						<h2>Clientes / Propietarios</h2>
							<p>
								<?php
								if ( $editar_cliente == true ) :
									list($rut, $digito) = explode('-', $form['rut_cliente']);
								else :
									$rut = $form['rut_cliente'];
									$digito = $form['digito_verificador'];
								endif;
								?>
								<label for="rut_cliente">R.U.T:<span>*</span></label>
								<input type="text" id="rut_cliente" size="11" name="rut_cliente" value="<?php echo $rut;?>" <?php echo ($editar_cliente == true) ? 'disabled="disabled"' : '' ; ?> tabindex="1" maxlength="10" />
								- <input type="text" id="digito_verificador" size="1" name="digito_verificador" value="<?php echo $digito;?>" <?php echo ($editar_cliente == true) ? 'disabled="disabled"' : '' ; ?> tabindex="1" maxlength="1" />
							</p>
							<p>
								<!--label for="nombre_cliente">Nombre:<span>*</span></label-->
								<span style="display:block;float:left;margin:5px 2px 0 0;">Nombre:<span style="color:#ff0000;">*</span><br />
									<input type="text" id="nombre_cliente" size="19" name="nombre_cliente" value="<?php echo $form['nombre_cliente'];?>" tabindex="2" maxlength="30" />
								</span>
								<span style="display:block;float:left;margin:5px 2px 0 0;">Apellidos:<span style="color:#ff0000;">*</span><br />
									<input type="text" id="apellidos_cliente" size="19" name="apellidos_cliente" value="<?php echo $form['apellidos_cliente'];?>" tabindex="2" maxlength="30" />
								</span>
								<div class="clear"></div>
							</p>
							<p>
								<!--label for="direccion">Dirección:<span>*</span></label-->
								<span style="display:block;float:left;margin:5px 2px 0 0;">Dirección: Calle<span style="color:#ff0000;">*</span><br />
									<input type="text" id="direccion" size="25" name="direccion" value="<?php echo $form['direccion'];?>" tabindex="3" maxlength="100" />
								</span>
								<span style="display:block;float:left;margin:5px 2px 0 0;">N°<span style="color:#ff0000;">*</span><br />
									<input type="text" id="num_direccion" size="4" name="num_direccion" value="<?php echo $form['num_direccion'];?>" tabindex="3" maxlength="10" />
								</span>
								<span style="display:block;float:left;margin:5px 0 0 0;">Dpto.<br />
									<input type="text" id="num_depa" size="4" name="num_depa" value="<?php echo $form['num_depa'];?>" tabindex="3" maxlength="10" />
								</span>
								<div class="clear"></div>
							</p>
							<p>
								<label for="comuna">Comuna:<span>*</span></label>
								<!--input type="text" id="comuna" size="30" name="comuna" value="<?php echo $form['comuna'];?>" tabindex="4" maxlength="20" /-->
								<select name="comuna" id="comuna" tabindex="4">
									<option value="">Seleccionar</option>
									<?php
									$sql = "SELECT COMUNA_NOMBRE FROM comuna ORDER BY COMUNA_NOMBRE ASC";
									$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
									if ($result->num_rows > 0) {
										while ($row = $result->fetch_array()) {
											if ( $form['comuna'] == $row['COMUNA_NOMBRE'] )
												echo '<option value="'.$row['COMUNA_NOMBRE'].'" selected="selected">'.$row['COMUNA_NOMBRE'].'</option>';
											else
												echo '<option value="'.$row['COMUNA_NOMBRE'].'">'.$row['COMUNA_NOMBRE'].'</option>';
										}
										$result->close();
									} ?>
								</select>
							</p>
							<p>
								<label for="ciudad">Ciudad:<span>*</span></label>
								<input type="text" id="ciudad" size="30" name="ciudad" value="<?php echo $form['ciudad'];?>" tabindex="5" maxlength="25" />
							</p>
							<p>
								<label for="sexo_cliente">Sexo:</label>
								<select name="sexo_cliente" id="sexo_cliente" tabindex="6">
                                	<option value="" <?php echo ($form['sexo_cliente'] == '')?'selected="selected"':'';?>>Seleccionar</option>
                                    <option value="Masculino" <?php echo ($form['sexo_cliente'] == 'Masculino')?'selected="selected"':'';?>>Masculino</option>
                                    <option value="Femenino" <?php echo ($form['sexo_cliente'] == 'Femenino')?'selected="selected"':'';?>>Femenino</option>
								</select>
							</p>
							<p>
								<!--label for="telefonos">Teléfono(s):</label-->
								<span style="display:block;float:left;margin:5px 2px 0 0;">Teléfono:<br />
									<input type="text" id="telefono" size="11" name="telefono" value="<?php echo $form['telefono'];?>" tabindex="7" maxlength="30" />
								</span>
								<span style="display:block;float:left;margin:5px 2px 0 0;">Celular:<br />
									<input type="text" id="celular" size="11" name="celular" value="<?php echo $form['celular'];?>" tabindex="7" maxlength="30" />
								</span>
								<span style="display:block;float:left;margin:5px 0 0 0;">Oficina:<br />
									<input type="text" id="oficina" size="10" name="oficina" value="<?php echo $form['oficina'];?>" tabindex="7" maxlength="30" />
								</span>
								<div class="clear"></div>
							</p>
							<p>
								<label for="fax">Fax:</label>
								<input type="text" id="fax" size="20" name="fax" value="<?php echo $form['fax'];?>" tabindex="8" maxlength="10" />
							</p>
							<p>
								<label for="email">E-Mail:<span>*</span></label>
								<input type="text" id="email" size="30" name="email" value="<?php echo $form['email'];?>" tabindex="9" maxlength="80" />
								<?php if ( isset($editar_cliente) && $editar_cliente == true ) { ?>
								<input type="hidden" name="email_registrado" value="<?php echo (!empty($form['email']))?$form['email']:$form['email_registrado'];?>" />
								<?php } ?>
							</p>
							<p>
								<label for="fecha_ingreso">Fecha Ing.:</label>
								<input type="text" id="fecha_ingreso" size="11" name="fecha_ingreso" value="<?php echo ($editar_cliente == true) ? mysql_to_normal($form['fecha_ingreso']) : date("d/m/Y") ;?>"<?php echo ($editar_cliente == true) ? ' readonly="readonly"' : '' ; ?> tabindex="10" maxlength="10" />
							</p>
							<?php if ( isset($editar_cliente) && $editar_cliente == true ) { ?>
							<p>
								<label class="big-name"><strong>Tipo de Cliente:</strong></label>
								<div class="clear"></div>
								<button type="button" class="button" id="arrendador" <?php echo (propiedades_cliente($form['id_cliente'],'arrendador')) ? '' : 'disabled="disabled"' ; ?>><span>Arrendador</span></button>
								<button type="button" class="button" id="arrendatario" <?php echo (propiedades_cliente($form['id_cliente'],'arrendatario')) ? '' : 'disabled="disabled"' ; ?>><span>Arrendatario</span></button>
								<button type="button" class="button" id="inversionista" <?php echo (propiedades_cliente($form['id_cliente'],'inversionista')) ? '' : 'disabled="disabled"' ; ?>><span>Inversionista</span></button>
							</p>
							<?php } ?>
							<br />
							<p>
								<label class="no_margin big-name"><strong>Busca Propiedad:</strong></label>
								<input type="checkbox" name="busca_propiedad" id="busca_propiedad" value="Si" <?php echo ($form['busca_propiedad'] == 'Si')?'checked="checked"':'';?>/>
							</p>
						<h2>Observaciones</h2>
							<p>
								<textarea name="observaciones" cols="41" rows="5" tabindex="11"><?php echo $form['observaciones'];?></textarea>
							</p>
					</fieldset>
					<fieldset id="busqueda_propiedad">
						<h2>Datos Propiedad Buscada</h2>
						<p>
							<label for="operacion">Operación:<span>*</span></label>
							<select name="operacion" id="operacion" tabindex="2">
								<option value="" <?php echo ($form['operacion'] == '')?'selected="selected"':'';?>>Todos</option>
								<option value="Venta" <?php echo ($form['operacion'] == 'Venta')?'selected="selected"':'';?>>Venta</option>
								<option value="Arriendo" <?php echo ($form['operacion'] == 'Arriendo')?'selected="selected"':'';?>>Arriendo</option>
							</select>
						</p>
						<p><strong>* CTRL + CLIC para seleccionar más de 1 tipo simultáneamente.</strong></p>
						<p class="left" style="margin-right: 10px;">
							<label for="tipo_propiedad">Tipos:<span>*</span></label>
							<select name="tipo_propiedad[]" id="tipo_propiedad" size="8" multiple="multiple" tabindex="3">
								<option value="Casa" <?php echo ($form['tipo_propiedad'] == 'Casa')?'selected="selected"':'';?>>Casa</option>
								<option value="Departamento" <?php echo ($form['tipo_propiedad'] == 'Departamento')?'selected="selected"':'';?>>Departamento</option>
								<option value="Oficina" <?php echo ($form['tipo_propiedad'] == 'Oficina')?'selected="selected"':'';?>>Oficina</option>
								<option value="Local" <?php echo ($form['tipo_propiedad'] == 'Local')?'selected="selected"':'';?>>Local</option>
								<option value="Parcela" <?php echo ($form['tipo_propiedad'] == 'Parcela')?'selected="selected"':'';?>>Parcela</option>
								<option value="Campo" <?php echo ($form['tipo_propiedad'] == 'Campo')?'selected="selected"':'';?>>Campo</option>
								<option value="Sitio" <?php echo ($form['tipo_propiedad'] == 'Sitio')?'selected="selected"':'';?>>Sitio</option>
								<option value="Bodega" <?php echo ($form['tipo_propiedad'] == 'Bodega')?'selected="selected"':'';?>>Bodega</option>
							</select>
						</p>
						<p class="left">
							<label for="comuna_propiedad">Comunas:<span>*</span></label>
							<select name="comuna_propiedad[]" id="comuna_propiedad" size="8" multiple="multiple" tabindex="4">
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
							<label for="ciudad_propiedad">Ciudad:<span>*</span></label>
							<input type="text" id="ciudad_propiedad" size="30" name="ciudad_propiedad" value="<?php echo $form['ciudad_propiedad'];?>" tabindex="6" maxlength="25" />
						</p>
						<p>
							<label for="sector">Sector:<span>*</span></label>
							<input type="text" id="sector" size="30" name="sector" value="<?php echo $form['sector'];?>" tabindex="5" maxlength="50" />
						</p>
						<p>
							<label for="valor_desde">Precio:</label>
							Desde <input type="text" id="valor_desde" size="11" name="valor_desde" value="<?php echo ($form['valor_desde'])?number_format($form['valor_desde'],0,',','.'):'';?>" maxlength="18" />
							Hasta <input type="text" id="valor_hasta" size="11" name="valor_hasta" value="<?php echo ($form['valor_hasta'])?number_format($form['valor_hasta'],0,',','.'):'';?>" maxlength="18" />
							<select name="tipo_valor" style="width: 50px;" id="tipo_valor">
								<option value="$" <?php echo ($form['tipo_valor'] == '$')?'selected="selected"':'';?>>$</option>
								<option value="U.F." <?php echo ($form['tipo_valor'] == 'U.F.')?'selected="selected"':'';?>>U.F.</option>
							</select>
						</p>
						<p>
							<label for="superficie_total" class="big-name">Superficie total:</label>
							<input type="text" id="superficie_total" size="11" name="superficie_total" value="<?php echo $form['superficie_total'];?>" tabindex="20" maxlength="16" />
							<span>M2</span>
						</p>
						<p>
							<label for="superficie_construida" class="big-name">Superficie construida:</label>
							<input type="text" id="superficie_construida" size="11" name="superficie_construida" value="<?php echo $form['superficie_construida'];?>" tabindex="21" maxlength="16" />
							<span>M2</span>
						</p>
						<h2>Detalle de la propiedad buscada</h2>
							<p>
								<textarea name="observaciones_busqueda" cols="80" rows="5" tabindex="11"><?php echo $form['observaciones_busqueda'];?></textarea>
							</p>
					</fieldset>
					<div class="clear"></div>
					<div class="botones">
						<p>
							<?php if ( $editar_cliente == true ) { ?>
							<input type="submit" name="accion" class="submit" value="Modificar" tabindex="11" />
							<input type="button" id="cancelar-ingreso" class="button" value="Cancelar" tabindex="12" />
							<?php } else { ?>
							<input type="submit" name="accion" class="submit" value="Guardar" tabindex="11" />
							<input type="reset" class="button" id="cleaner" value="Limpiar" tabindex="12" onclick="try{document.getElementById('rut_cliente').focus();}catch(e){}" />
							<?php } ?>
						</p>
					</div>
				</form>