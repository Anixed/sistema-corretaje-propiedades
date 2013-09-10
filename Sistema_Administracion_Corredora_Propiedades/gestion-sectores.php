<?php require('connect-mysql.php'); //require('functions.php');

if ( $_GET['accion'] == 'Guardar' && !empty($_GET['sector_nombre']) ) :
	$sql = "INSERT INTO sectores (sector_nombre) VALUES ('".trim($_GET['sector_nombre'])."')";
	$mysqli->query($sql) or die('Error: '.$mysqli->error);
	unset($_GET);
endif;

if ( $_GET['accion'] == 'Eliminar' && !empty($_GET['id_sector']) ) :
	$sql = "DELETE FROM sectores WHERE id=".$_GET['id_sector'];
	$mysqli->query($sql) or die('Error: '.$mysqli->error);
	unset($_GET);
endif;

?>
<script type="text/javascript">
$(document).ready(function() {
	/* Cebreado con jQuery */
	$(".grilla tbody tr:odd").addClass("alt");
	$("#sector_nombre").focus();
});
</script>
				<div id="content-sectores" class="content-clientes-grilla">
				<form action="gestion-sectores.php" method="get" id="sectores-grilla">
				<input type="hidden" name="accion" id="accion" value="Guardar" />
					<div class="box-top-grilla left" style="margin-left:0;">
						<!--span class="buscar_por">Sectores:</span><br /-->
						<p>
							<label for="sector_nombre" class="big-name">Nombre Sector:<span>*</span></label><br />
							<input type="text" id="sector_nombre" name="sector_nombre" maxlength="100" style="margin-left:0;" />
					        <input id="guardar-sector" class="submit" type="submit" value="Nueva Opción" />
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
							$sql = "SELECT id, sector_nombre FROM sectores ORDER BY id DESC";
							$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
							
							if ($result->num_rows > 0) :
								while ($row = $result->fetch_assoc()) : ?>
									<tr id="sector-<?php echo $row['id'];?>">
									<td><a href="gestion-sectores.php?id_sector=<?php echo $row['id'];?>&sector_nombre=<?php echo $row['sector_nombre'];?>" class="seleccionar-sector"><img src="images/add2.png" title="Seleccionar Sector" alt="Seleccionar" /></a></td>
									<td><a href="gestion-sectores.php?accion=Eliminar&id_sector=<?php echo $row['id'];?>&sector_nombre=<?php echo $row['sector_nombre'];?>" class="eliminar-sector"><img src="images/delete.png" title="Eliminar Sector" alt="Eliminar" /></a></td>
									<td><?php echo $row['id'];?></td>
									<td><?php echo $row['sector_nombre'];?></td>
									</tr>
							<?php endwhile;
								$result->close();
							else :
								echo '<tr><td colspan="4">No se han encontrando registros existentes en la base de datos...</td></tr>';
							endif; ?>
						</tbody>
					</table>
			    </div><!--/content-clientes-grilla-->