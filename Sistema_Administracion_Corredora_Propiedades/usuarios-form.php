<?php require_once('connect-mysql.php'); require_once('functions.php'); ?>
<?php
//echo '<pre>'; print_r($_POST); echo '</pre>';

//Variable para comprobar si se esta editando el registro
if ( $_GET['accion'] == 'editar_usuario' || $_POST['accion'] == 'Modificar' ) {
	$editar_usuario = true;
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
		
		if ( !empty($_POST['rut_usuario']) && ( !isset($editar_usuario) && $editar_usuario != true ) ) {
			
			$rut_completo = $_POST['rut_usuario'].'-'.$_POST['digito_verificador'];
			if ( preg_match('/[0-9]{1,2}[.]?[0-9]{3}[.]?[0-9]{3}[-][0-9kK]{1}/', $rut_completo) ) {
				
				$rut_buscar = preg_replace('/[.]/', '', $rut_completo);
				if ( !existe_registro_usuario($rut_buscar, 'rut_usuario') ) {
					$insert_sql_into[$i] = 'rut_usuario'; $insert_sql_values[$i] = '\''.$rut_buscar.'\''; ++$i;
				} else {
					$msgbox[] = '<strong>El RUT ('.$rut_completo.') ya existe en la base de datos.</strong>';
					$icon = 'error';
				}
				
			} else {
				$msgbox[] = 'Formato de R.U.T incorrecto.';
				$icon = 'error';
			}
			
		} elseif ( !isset($editar_usuario) && $editar_usuario != true ) {
			$msgbox[] = 'Especifique el R.U.T del usuario.';
			$icon = 'error';
		}
		
		if ( !empty($_POST['nombre']) && !empty($_POST['apellido']) ) {
			$insert_sql_into[$i] = 'nombre'; $insert_sql_values[$i] = '\''.trim($_POST['nombre']).'\''; ++$i;
			$insert_sql_into[$i] = 'apellido'; $insert_sql_values[$i] = '\''.trim($_POST['apellido']).'\''; ++$i;
		} else {
			$msgbox[] = 'Especifique el nombre y apellido del usuario.';
			$icon = 'error';
		}
		
		if ( !empty($_POST['telefono']) ) {
			$insert_sql_into[$i] = 'telefono'; $insert_sql_values[$i] = '\''.trim($_POST['telefono']).'\''; ++$i;
		}
		if ( !empty($_POST['celular']) ) {
			$insert_sql_into[$i] = 'celular'; $insert_sql_values[$i] = '\''.trim($_POST['celular']).'\''; ++$i;
		}
		
		if ( !empty($_POST['sexo_usuario']) ) {
			$insert_sql_into[$i] = 'sexo_usuario'; $insert_sql_values[$i] = '\''.$_POST['sexo_usuario'].'\''; ++$i;
		}
		
		if ( !isset($editar_usuario) && $editar_usuario != true ) {
			if ( !empty($_POST['pass']) ) {
				$passFORmail = $_POST['pass'];
				$insert_sql_into[$i] = 'password'; $insert_sql_values[$i] = '\''.md5($_POST['pass']).'\''; ++$i;
			} else {
				$msgbox[] = 'Debe introducir una contraseña válida.';
				$icon = 'error';
			}
			unset($_POST['pass']);
		}
		
		if ( !empty($_POST['email']) && preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $_POST['email']) ) {
			
			if ( $_POST['email_registrado'] != $_POST['email'] ) {
				$_POST['email'] = trim($_POST['email']);
				if ( !existe_registro_usuario($_POST['email'], 'email') ) {
					$insert_sql_into[$i] = 'email'; $insert_sql_values[$i] = '\''.$_POST['email'].'\''; ++$i;
				} else {
					$msgbox[] = '<strong>El E-mail del Usuario ya existe en la base de datos.</strong>';
					$icon = 'error';
				}
			}
			
		} else {
			$msgbox[] = 'Especifique correctamente el correo electrónico del usuario.';
			$icon = 'error';
		}
		
		if ( !empty($_POST['tipo_usuario']) ) {
			$insert_sql_into[$i] = 'tipo_usuario'; $insert_sql_values[$i] = '\''.$_POST['tipo_usuario'].'\''; ++$i;
		} else {
			$msgbox[] = 'Debe especificar el rol del usuario.';
			$icon = 'error';
		}
		
		if ( !empty($_POST['privilegios_opcionales']) ) {
			$serialize_array = serialize($_POST['privilegios_opcionales']); //serializo el array para guardarlo en la base de datos
			$insert_sql_into[$i] = 'privilegios_opcionales'; $insert_sql_values[$i] = '\''.$serialize_array.'\''; ++$i;
			unset($serialize_array);
		} elseif ( empty($_POST['privilegios_opcionales']) && $editar_usuario == true ) {
			$insert_sql_into[$i] = 'privilegios_opcionales'; $insert_sql_values[$i] = 'NULL'; ++$i;
		}
		
		if ( !isset($editar_usuario) && $editar_usuario != true ) {
			if ( $_POST['fecha_ingreso'] == date("d/m/Y") ) {
				$insert_sql_into[$i] = 'fecha_ingreso'; $insert_sql_values[$i] = 'NOW()';
			} elseif ( !empty($_POST['fecha_ingreso']) && preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $_POST['fecha_ingreso']) ) {
				$insert_sql_into[$i] = 'fecha_ingreso'; $insert_sql_values[$i] = '\''.$_POST['fecha_ingreso'].'\'';
			} else {
				$msgbox[] = 'Especifique correctamente la fecha de ingreso.';
				$icon = 'error';
			}
		}
		
		if ( $icon == 'error' ) {
			$form = array();
			foreach ($_POST as $key => $value) {
				$form[$key] = $value;
			}
			unset($_POST, $insert_sql_into, $insert_sql_values);
		}
		
	endif;
endif;

//Guarda el usuario en la BD
if ($_POST['accion'] == 'Guardar') :
	
	$sql = "INSERT INTO usuarios (".implode(',', $insert_sql_into).") VALUES (".implode(',', $insert_sql_values).")";
	$mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	/*===================== INICIO ENVIAR CORREO =====================*/
	include('configuracion_correo.php');
	$contenidoHTML = 'Estimado(a) '.$_POST['nombre'].' '.$_POST['apellido'].'<br />
	<br />
	Se le ha otorgado acceso a nuestro sistema de administración de propiedades.<br/>
	<br/>
	La información de su cuenta es la siguiente:<br/>
	Usuario: '.$rut_completo.'<br/>
	Contraseña: '.$passFORmail.'<br/>
	<br/>
	<br/>
	Correo generado automáticamente por el sistema de administración {{Nombre Empresa}}.
	<br/>
	<br/>
	Los correos de respuesta a esta dirección no son leídos.<br/>
	Para comunicarse con nosotros por favor hacerlo al siguiente correo: contacto@admin.com';
	
	$mail->Subject = 'Su cuenta FácilFood';
	$mail->AddAddress($_POST['email'], $_POST['nombre'].' '.$_POST['apellido']);
	$mail->AddBCC('admin@admin.com');
	$mail->Body = $contenidoHTML;
	//$mail->Timeout = 50;
	if ( !$mail->Send() ) {
		$msgbox = 'El Usuario ( R.U.T: '.$rut_completo.' ) se ha guardado satisfactoriamente.<br />
					Ha ocurrido un error al enviar al usuario el correo electrónico con la información de acceso a su cuenta.';
		$icon = 'info fin-ingreso';
	} else {
		$msgbox = 'El Usuario ( R.U.T: '.$rut_completo.' ) se ha guardado satisfactoriamente.<br />
					Se le ha enviado un correo electrónico al nuevo usuario con la información de acceso a su cuenta.';
		$icon = 'info fin-ingreso';
	}
	$mail->ClearAddresses();
	$mail->SmtpClose();
	/*===================== FIN ENVIAR CORREO =====================*/
	
	unset($_POST, $insert_sql_into, $insert_sql_values);
	
endif;

//Modificar el usuario
if ($_POST['accion'] == 'Modificar') :
	$id_usuario = $_POST['id_usuario'];
	
	if ( existe_registro_usuario($id_usuario, 'id_usuario') ) :
		$update_sql = '';
		$count = count($insert_sql_into);
		for($i=0; $i<$count; $i++) {
			$update_sql .= $insert_sql_into[$i].'='.$insert_sql_values[$i].', ';
		}
		$update_sql = substr($update_sql, 0, -2);
		$sql = "UPDATE usuarios SET ".$update_sql." WHERE id_usuario=".$id_usuario;
		$mysqli->query($sql) or die('Error: '.$mysqli->error);
		
		$msgbox = 'El Usuario ( R.U.T: '.formato_rut($_POST['rut_usuario']).' ) se ha modificado satisfactoriamente.';
		$icon = 'info modificado';
	else :
		$msgbox = 'No se ha encontrado el Usuario ID Nº:'.$id_usuario.'.';
		$icon = 'error';
	endif;
	unset($_POST, $insert_sql_into, $insert_sql_values, $editar_usuario, $update_sql);
	
endif;

//Editar usuario
if ( ($_GET['accion'] == 'editar_usuario') && !empty($_GET['id_usuario']) && !empty($_GET['rut_usuario']) ) :
	$id_usuario = $_GET['id_usuario'];
	
	$sql = "SELECT id_usuario, rut_usuario, nombre, apellido, sexo_usuario, telefono, celular, email, tipo_usuario, privilegios_opcionales, fecha_ingreso
				FROM usuarios
				WHERE id_usuario=".$id_usuario;
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	
	if ($form = $result->fetch_assoc()) {
		
		if ( isset($form['privilegios_opcionales']) ) {
			$form['privilegios_opcionales'] = unserialize($form['privilegios_opcionales']);
		}
		
		$msgbox = 'Editando al Usuario R.U.T: '.formato_rut($form['rut_usuario']);
		$icon = 'info';
	} else {
		$editar_usuario = false;
		$msgbox = 'No se ha encontrado el Usuario R.U.T: '.formato_rut($_GET['rut_usuario']);
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
	$('#rut_usuario').focus();
	
	var msgbox = '<?php echo $msgbox; ?>';
	$("#msgbox2").addClass("<?php echo 'msgbox-'.$icon;?>").html(msgbox).show();
	
	$('#rut_usuario').Rut({
		digito_verificador: '#digito_verificador',
		on_error: function(){
			$('input[type="text"]#rut_usuario').css({'background-color' : '#FDD1C5', 'border' : '1px solid #E74B2F'});
			$('#digito_verificador').css({'background-color' : '#FDD1C5', 'border' : '1px solid #E74B2F'});
		},
		on_success: function(){
			$('input[type="text"]#rut_usuario').css({'background-color' : '#E7F9F8', 'border' : '1px solid #0566B7'});
			$('#digito_verificador').css({'background-color' : '#E7F9F8', 'border' : '1px solid #0566B7'});
		} 
	});
	<?php if ( $editar_usuario == true ) { ?>
	var rut = $('input[type="text"]#rut_usuario').val(); //$('#rut_usuario').val().split("-")[0];
	$('input[type="text"]#rut_usuario').val( $.Rut.formatear(rut) );
	<?php } ?>
});
</script>
				<form action="usuarios-form.php" method="post" id="usuarios-formulario">
				<?php if ( $editar_usuario == true ) { ?>
				<input type="hidden" name="accion" id="accion" value="Modificar" />
				<input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $form['id_usuario']; ?>" />
				<input type="hidden" name="rut_usuario" id="rut_usuario" value="<?php echo $form['rut_usuario']; ?>" />
				<?php } else { ?>
				<input type="hidden" name="accion" id="accion" value="Guardar" />
				<?php } ?>
					<fieldset>
						<h2>Usuario del Sistema</h2>
							<p>
								<?php
								if ( $editar_usuario == true ) :
									list($rut, $digito) = explode('-', $form['rut_usuario']);
								else :
									$rut = $form['rut_usuario'];
									$digito = $form['digito_verificador'];
								endif;
								?>
								<label for="rut_usuario">R.U.T:<span>*</span></label>
								<input type="text" id="rut_usuario" size="11" name="rut_usuario" value="<?php echo $rut;?>" <?php echo ($editar_usuario == true) ? 'disabled="disabled"' : '' ; ?> tabindex="1" maxlength="10" />
								- <input type="text" id="digito_verificador" size="1" name="digito_verificador" value="<?php echo $digito;?>" <?php echo ($editar_usuario == true) ? 'disabled="disabled"' : '' ; ?> tabindex="1" maxlength="1" />
							</p>
							<p>
								<!--label for="nombre">Nombre:<span>*</span></label-->
								<span style="display:block;float:left;margin:5px 2px 0 0;">Nombre:<span style="color:#ff0000;">*</span><br />
									<input type="text" id="nombre" size="19" name="nombre" value="<?php echo $form['nombre'];?>" tabindex="2" maxlength="30" />
								</span>
								<span style="display:block;float:left;margin:5px 2px 0 0;">Apellidos:<span style="color:#ff0000;">*</span><br />
									<input type="text" id="apellido" size="19" name="apellido" value="<?php echo $form['apellido'];?>" tabindex="2" maxlength="30" />
								</span>
								<div class="clear"></div>
							</p>
							<p>
								<!--label for="telefonos">Teléfono(s):</label-->
								<span style="display:block;float:left;margin:5px 2px 0 0;">Teléfono:<br />
									<input type="text" id="telefono" size="19" name="telefono" value="<?php echo $form['telefono'];?>" tabindex="5" maxlength="30" />
								</span>
								<span style="display:block;float:left;margin:5px 2px 0 0;">Celular:<br />
									<input type="text" id="celular" size="19" name="celular" value="<?php echo $form['celular'];?>" tabindex="5" maxlength="30" />
								</span>
								<div class="clear"></div>
							</p>
							<p>
								<label for="sexo_usuario">Sexo:</label>
								<select name="sexo_usuario" id="sexo_usuario" tabindex="6">
                                	<option value="" <?php echo ($form['sexo_usuario'] == '')?'selected="selected"':'';?>>Seleccionar</option>
                                    <option value="Masculino" <?php echo ($form['sexo_usuario'] == 'Masculino')?'selected="selected"':'';?>>Masculino</option>
                                    <option value="Femenino" <?php echo ($form['sexo_usuario'] == 'Femenino')?'selected="selected"':'';?>>Femenino</option>
								</select>
							</p>
							<p>
								<label for="pass">Contraseña:<span>*</span></label>
								<input type="password" id="pass" size="30" name="pass" value="<?php echo $form['pass'];?>" tabindex="7" maxlength="18" />
							</p>
							<p>
								<label for="email">E-Mail:<span>*</span></label>
								<input type="text" id="email" size="30" name="email" value="<?php echo $form['email'];?>" tabindex="9" maxlength="80" />
								<?php if ( isset($editar_usuario) && $editar_usuario == true ) { ?>
								<input type="hidden" name="email_registrado" value="<?php echo (!empty($form['email']))?$form['email']:$form['email_registrado'];?>" />
								<?php } ?>
							</p>
							<p>
								<label for="fecha_ingreso">Fecha Ing.:</label>
								<input type="text" id="fecha_ingreso" size="11" name="fecha_ingreso" value="<?php echo ($editar_usuario == true) ? mysql_to_normal($form['fecha_ingreso']) : date("d/m/Y") ;?>"<?php echo ($editar_usuario == true) ? ' readonly="readonly"' : '' ; ?> tabindex="10" maxlength="10" />
							</p>
							<br />
						<h2>Tipo &amp; Privilegios</h2>
							<p>
								<label for="tipo_usuario">Rol Usuario:<span>*</span></label>
								<select name="tipo_usuario" id="tipo_usuario" tabindex="11">
                                	<option value="" <?php echo ($form['tipo_usuario'] == '')?'selected="selected"':'';?>>Seleccionar</option>
                                	<option value="" disabled="disabled">----------------------------</option>
                                    <option value="3" <?php echo ($form['tipo_usuario'] == '3')?'selected="selected"':'';?>>Secretaria</option>
                                    <option value="4" <?php echo ($form['tipo_usuario'] == '4')?'selected="selected"':'';?>>Vendedor</option>
                                    <option value="2" <?php echo ($form['tipo_usuario'] == '2')?'selected="selected"':'';?>>Administrativo</option>
                                    <option value="1" <?php echo ($form['tipo_usuario'] == '1')?'selected="selected"':'';?>>Administrador</option>
								</select>
							</p>
							<br />
							<p>
								<span><strong>Privilegio total a los módulos: (Opcional)</strong></span>
								<div class="clear"></div>
								<table style="margin-top: 5px;">
									<tr>
										<td><input type="checkbox" name="privilegios_opcionales[0]" value="Propiedades" <?php echo ($form['privilegios_opcionales'][0] == 'Propiedades')?'checked="checked"':'';?>/></td>
										<td style="padding: 0 7px;"><span>Propiedades</span></td>
										<td><input type="checkbox" name="privilegios_opcionales[1]" value="Agenda Anfitriones" <?php echo ($form['privilegios_opcionales'][1] == 'Agenda Anfitriones')?'checked="checked"':'';?>/></td>
										<td style="padding: 0 7px;"><span>Agenda Anfitriones</span></td>
									</tr>
									<tr>
										<td><input type="checkbox" name="privilegios_opcionales[2]" value="Clientes" <?php echo ($form['privilegios_opcionales'][2] == 'Clientes')?'checked="checked"':'';?>/></td>
										<td style="padding: 0 7px;"><span>Clientes</span></td>
										<td><input type="checkbox" name="privilegios_opcionales[3]" value="Administración" <?php echo ($form['privilegios_opcionales'][3] == 'Administración')?'checked="checked"':'';?>/></td>
										<td style="padding: 0 7px;"><span>Administración</span></td>
									</tr>
								</table>
								<div class="clear"></div>
								<br />
							</p>
					</fieldset>
					<div class="clear"></div>
					<div class="botones">
						<p>
							<?php if ( $editar_usuario == true ) { ?>
							<input type="submit" name="accion" class="submit" value="Modificar" tabindex="11" />
							<input type="button" id="cancelar-ingreso" class="button" value="Cancelar" tabindex="12" />
							<?php } else { ?>
							<input type="submit" name="accion" class="submit" value="Guardar" tabindex="11" />
							<input type="reset" class="button" value="Limpiar" tabindex="12" onclick="try{document.getElementById('rut_usuario').focus();}catch(e){}" />
							<?php } ?>
						</p>
					</div>
				</form>