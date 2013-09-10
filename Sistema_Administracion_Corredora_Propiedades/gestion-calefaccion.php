<?php require('connect-mysql.php'); //require('functions.php');

if ( $_GET['accion'] == 'Guardar' && !empty($_GET['calefaccion_nombre']) ) :
	$sql = "INSERT INTO calefaccion (calefaccion_nombre) VALUES ('".trim($_GET['calefaccion_nombre'])."')";
	$mysqli->query($sql) or die('Error: '.$mysqli->error);
	unset($_GET);
endif;

if ( $_GET['accion'] == 'Eliminar' && !empty($_GET['id_calefaccion']) ) :
	$sql = "DELETE FROM calefaccion WHERE id=".$_GET['id_calefaccion'];
	$mysqli->query($sql) or die('Error: '.$mysqli->error);
	unset($_GET);
endif;

?>
<script type="text/javascript">
$(document).ready(function() {
	/* Cebreado con jQuery */
	$(".grilla tbody tr:odd").addClass("alt");
	$("#calefaccion_nombre").focus();
});
</script>
				<div id="content-calefaccion" class="content-clientes-grilla">
				<form action="gestion-calefaccion.php" method="get" id="calefaccion-grilla">
				<input type="hidden" name="accion" id="accion" value="Guardar" />
					<div class="box-top-grilla left" style="margin-left:0;">
						<p>
							<label for="calefaccion_nombre" class="big-name">Nombre Calefacción:<span>*</span></label><br />
							<input type="text" id="calefaccion_nombre" name="calefaccion_nombre" maxlength="100" style="margin-left:0;" />
					        <input id="guardar-calefaccion" class="submit" type="submit" value="Nueva Opción" />
						</p>
					</div>
					<div class="box-top-grilla right">
					</div>
				</form>
					<div class="clear"></div>
			        <table cellpadding="0" cellspacing="0" class="grilla" style="width:400px;">
						<thead>
							<tr>
								<th colspan="2">Opciones</th>
								<th>ID</th>
								<th>Nombre</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$sql = "SELECT id, calefaccion_nombre FROM calefaccion ORDER BY id DESC";
							$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
							
							if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) : ?>
									<tr id="calefaccion-<?php echo $row['id'];?>">
									<td><a href="gestion-calefaccion.php?id_calefaccion=<?php echo $row['id'];?>&calefaccion_nombre=<?php echo $row['calefaccion_nombre'];?>" class="seleccionar-calefaccion"><img src="images/add2.png" title="Seleccionar Calefacción" alt="Seleccionar" /></a></td>
									<td><a href="gestion-calefaccion.php?accion=Eliminar&id_calefaccion=<?php echo $row['id'];?>&calefaccion_nombre=<?php echo $row['calefaccion_nombre'];?>" class="eliminar-calefaccion"><img src="images/delete.png" title="Eliminar Calefacción" alt="Eliminar" /></a></td>
									<td><?php echo $row['id'];?></td>
									<td><?php echo $row['calefaccion_nombre'];?></td>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="4">No se han encontrando registros existentes en la base de datos...</td></tr>';
							endif; ?>
						</tbody>
					</table>
			    </div><!--/content-clientes-grilla-->