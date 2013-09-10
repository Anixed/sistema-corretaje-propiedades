<?php
require('connect-mysql.php'); //require('functions.php');
//echo '<pre>'; print_r($_GET); echo '</pre>';

$address = ''; $msgbox = '';
if ( $_GET['accion'] == 'mapa_propiedad_consultar' && !empty($_GET['id_propiedad']) && !empty($_GET['cod_propiedad']) ) :
	
	$sql = "SELECT CONCAT(direccion,' ',num_direccion) AS direccion, sector, comuna, ciudad FROM propiedades WHERE id_propiedad=".$_GET['id_propiedad'];
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	if ($row = $result->fetch_assoc()) :
		$row['direccion'] = str_replace('#','',$row['direccion']); //preg_replace('/[#]/','',$row['direccion']);
		$address = str_replace(' ', '+', $row['direccion'].','.$row['comuna'].',Chile');
		$msgbox = 'Mapa Propiedad ( Código: '.$_GET['cod_propiedad'].' )';
	endif;
	
elseif ( $_GET['accion'] == 'mapa_propiedad_buscar' && !empty($_GET['cod_propiedad']) ) :
	
	if ( !empty($_GET['address']) ) {
		$address = $_GET['address'];
		$msgbox = 'Mapa Propiedad ( Código: '.$_GET['cod_propiedad'].' )';
	} else {
		$msgbox = 'ERROR: Especificar dirección y comuna.';
	}
	
else:
	$msgbox = 'ERROR: Propiedad no encontrada.';
endif;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Google Maps, Propiedad: <?php echo $address; ?> | Sistema Corretaje de Propiedades</title>
	<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
	<script type="text/javascript" src="js/gmaps.js"></script>
	<link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
	<script type="text/javascript">
	$(document).ready(function(){
		var map = new GMaps({
			div: '#map',
			/*zoom: 16,*/
			lat: -38.73383,
			lng: -72.584496
		});
		var direccion = $('#address').val();
		GMaps.geocode({
			address: direccion,
			callback: function(results, status) {
			if (status == 'OK') {
				var latlng = results[0].geometry.location;
				map.setCenter(latlng.lat(), latlng.lng());
				map.addMarker({
					lat: latlng.lat(),
					lng: latlng.lng(),
					title: direccion/*,
					click: function(e) {
						alert('You clicked in this marker');
					}*/
				});
			} else if (status == 'ZERO_RESULTS' && direccion != '' ) {
				alert('Dirección ('+direccion+') no encontrada.');
			}
			}
		});
	});
</script>
</head>
<body>
<div id="wrap">
	<h2><?php echo $msgbox; ?></h2>
	<div id="content">
		<div id="content-page">
			<div id="content-googlemaps">
				<input type="hidden" id="address" name="address" value="<?php echo $address; ?>" />
				<div id="map"></div>
			</div>
		</div><!--/content-page-->
	</div><!--/content-->
</div><!--/wrap-->
</body>
</html>