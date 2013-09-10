<?php require('connect-mysql.php'); ?>
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