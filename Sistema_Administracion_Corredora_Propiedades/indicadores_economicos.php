<?php require('connect-mysql.php');

//http://indicador.eof.cl/ <- webservice que provee los indicadores económicos
//Los indicadores económicos que se muestran son los que aparecen en http://www.bcentral.cl
if ( $xml_indicadores = simplexml_load_file("http://indicador.eof.cl/xml") ) {
	$uf = 0;
	foreach($xml_indicadores as $i){
		$nombre = $i->attributes();
		if ( preg_match("/UF/", $nombre) ) {
			$uf = $i;
			break;
		}
	}
	unset($xml_indicadores, $i);
	
	$search = array('.', ','); $replace = array('', '.');
	$uf = str_replace($search, $replace, $uf);
	$sql = "UPDATE administracion SET UF='".$uf."', ultima_modificacion=NOW() WHERE id=1";
	$mysqli->query($sql) or die('Error: '.$mysqli->error);
	echo "Valor U.F. obtenido y actualizado correctamente de http://www.bcentral.cl<br />Valor Captado: ".number_format($uf,2,',','.');
}