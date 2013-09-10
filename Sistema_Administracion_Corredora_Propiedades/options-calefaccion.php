<?php require('connect-mysql.php'); ?>
<option value="">Ninguna</option>
<?php
$sql = "SELECT calefaccion_nombre FROM calefaccion ORDER BY calefaccion_nombre ASC";
$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
if ($result->num_rows > 0) {
	while ($row = $result->fetch_array()) {
		echo '<option value="'.$row['calefaccion_nombre'].'">'.$row['calefaccion_nombre'].'</option>';
	}
	$result->close();
} ?>
<option value="Otra">Otra</option>