<?php require_once('connect-mysql.php'); require_once('functions.php');
session_start(); ?>

<script type="text/javascript">
$(document).ready(function() {
	//Enable draggable functionality on any DOM element.
	//Move the draggable object by clicking on it with the mouse and dragging it anywhere within the viewport.
	//$( "#box-popup" ).draggable();
});
</script>
<div id="box-popup">
	<a href="javascript:void(0);" class="cerrar"><img src="images/close2.png" title="Cerrar" alt="Cerrar" /></a>
	<div class="content-popup"></div>
</div>
<div id="agenda-calendario">
<?php

$action_url = ''; $eventos_url = ''; $other_where = '';
if ( $_GET['accion'] == 'visitas_cliente' && !empty($_GET['id_cliente']) && !empty($_GET['rut_cliente']) && !empty($_GET['nombre_cliente']) ) :
	
	$action_url = '&accion=visitas_cliente&id_cliente='.$_GET['id_cliente'].'&rut_cliente='.$_GET['rut_cliente'].'&nombre_cliente='.$_GET['nombre_cliente'];
	$eventos_url = '&cliente='.$_GET['id_cliente'];
	$other_where = ' AND id_cliente='.$_GET['id_cliente'];
	$eventos_para = ' | Para el Cliente: '.$_GET['nombre_cliente'].' ('.formato_rut($_GET['rut_cliente']).')';
	
elseif ( $_GET['accion'] == 'visitas_propiedad' && !empty($_GET['id_propiedad']) && !empty($_GET['cod_propiedad']) ) :
	
	$action_url = '&accion=visitas_propiedad&id_propiedad='.$_GET['id_propiedad'].'&cod_propiedad='.$_GET['cod_propiedad'];
	$eventos_url = '&propiedad='.$_GET['id_propiedad'];
	$other_where = ' AND id_propiedad='.$_GET['id_propiedad'];
	$eventos_para = ' | Para la Propiedad: '.$_GET['cod_propiedad'];
	
endif;

if ( $_SESSION['tipo_sistema'] == 4 && !empty($_SESSION['id_sistema']) ) :
	
	$action_url .= '&id_vendedor='.$_SESSION['id_sistema'];
	$eventos_url .= '&vendedor='.$_SESSION['id_sistema'];
	$other_where .= ' AND id_vendedor='.$_SESSION['id_sistema'];
	
elseif ( !empty($_GET['vendedor']) ) :
	
	$action_url .= '&id_vendedor='.$_GET['vendedor'];
	$eventos_url .= '&vendedor='.$_GET['vendedor'];
	$other_where .= ' AND id_vendedor='.$_GET['vendedor'];
	
endif;

//Captura la fecha actual o la fecha consultada, y la divide en variables a usar
if ( !isset($_GET['fecha']) || empty($_GET['fecha']) ) {
	$mes_actual = intval(date("m"));
	if ( $mes_actual<=9 )
		$el_mes = '0'.$mes_actual;
	else
		$el_mes = $mes_actual;
	$el_anio = date("Y");
} elseif ( !empty($_GET['fecha']) ) {
	$fecha = explode('-', $_GET['fecha']);
	$mes_actual = intval($fecha[1]);
	if ( $mes_actual<=9 )
		$el_mes = '0'.$mes_actual;
	else
		$el_mes = $mes_actual;
	$el_anio = $fecha[0];
}

//Obtiene el numero del dia de la semana que cae primero del mes
$primero_mes = date("N",mktime(0,0,0,$mes_actual,1,$el_anio));
$fecha_hoy = date("Y-m-d");
//if (!isset($_GET["mes"])) $fecha_hoy = date("Y-m-d"); 
//else $fecha_hoy=$_GET["ano"]."-".$_GET["mes"]."-01";

$meses = array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
//Comprueba si el año es bisiesto para registrar los dias totales de cada mes
if ( ($el_anio % 4 == 0) && (($el_anio % 100 != 0) || ($el_anio % 400 == 0)) )
	$dias = array("","31","29","31","30","31","30","31","31","30","31","30","31");
else
	$dias = array("","31","28","31","30","31","30","31","31","30","31","30","31");

//Obtiene la fecha de los dias del mes los cuales tienen eventos registrados
$sql = "SELECT fecha_visita FROM agenda_visitas
		WHERE MONTH(fecha_visita)='".$el_mes."' AND YEAR(fecha_visita)='".$el_anio."'".$other_where;
$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
if ($result->num_rows > 0) {
	$eventos = array();
	$i = 0;
	do {
		$eventos[$i] = $row['fecha_visita'];
		++$i;
	} while ($row = $result->fetch_assoc());
}

//Calcula el total de filas/total de semanas para crear el calendario
$dias_antes = $primero_mes-1;
$dias_despues = 42;
$tope = $dias[$mes_actual]+$dias_antes;
if ( $tope%7!=0 )
	$total_filas = intval(($tope/7)+1);
else
	$total_filas = intval(($tope/7));

$mes_anterior = date("n",mktime(0,0,0,$mes_actual-1,01,$el_anio));
$mes_despues = date("n",mktime(0,0,0,$mes_actual+1,01,$el_anio));
$dias_mes_anterior = $dias[$mes_anterior]-($dias_antes-1);
$dias_mes_despues = 1;
?>

<?php if ( $_GET['msgbox'] == 'orden_visita' && $_GET['estado'] == 'ok' ) { ?>
<div class="msgbox-info">
	La orden de visita se ha registrado correctamente y se ha enviado un correo con la información de la visita al cliente y al anfitrión.<br />
	- Cliente: <?php nombre_cliente($_GET['cliente']); ?><br />
	- Vendedor: <?php nombre_vendedor($_GET['vendedor']); ?>
</div><br />
<?php } elseif ( $_GET['msgbox'] == 'orden_visita' && $_GET['estado'] == 'no' ) { ?>
<div class="msgbox-info">
	La orden de visita se ha registrado correctamente, pero ha ocurrido un error al enviar el correo con la información de la visita.<br />
	- Cliente: <?php nombre_cliente($_GET['cliente']); ?><br />
	- Vendedor: <?php nombre_vendedor($_GET['vendedor']); ?>
</div><br />
<?php } ?>

<h2>Agenda de Visitas: <?php echo $meses[$mes_actual].' del '.$el_anio.''.$eventos_para;?></h2><br />
<?php
$fecha_mes_anterior = date("Y-m-d",mktime(0,0,0,$mes_actual-1,01,$el_anio));
$fecha_mes_siguiente = date("Y-m-d",mktime(0,0,0,$mes_actual+1,01,$el_anio));

function total_visitas_mes($fecha,$other_where) {
	global $mysqli;
	
	list($anio, $mes, $dia) = explode('-', $fecha);
	$sql = "SELECT COUNT(id_visita) AS visitas_total FROM agenda_visitas
			WHERE MONTH(fecha_visita)='".$mes."' AND YEAR(fecha_visita)='".$anio."'".$other_where;
	$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
	if ($row = $result->fetch_assoc()) {
		return $row['visitas_total'];
	} 
}
?>
<p>&laquo; <span title="Ordenes de visita">(<?php echo total_visitas_mes($fecha_mes_anterior,$other_where);?>)</span> <a href="agenda_visitas.php?fecha=<?php echo $fecha_mes_anterior.$action_url;?>">Mes Anterior</a> - <a href="agenda_visitas.php?fecha=<?php echo $fecha_mes_siguiente.$action_url;?>">Mes Siguiente</a> <span title="Ordenes de visita">(<?php echo total_visitas_mes($fecha_mes_siguiente,$other_where);?>)</span> &raquo;</p>

<table class="calendario" cellspacing="0" cellpadding="0">
<tr>
	<th>Lunes</th>
	<th>Martes</th>
	<th>Miércoles</th>
	<th>Jueves</th>
	<th>Viernes</th>
	<th>Sábado</th>
	<th>Domingo</th>
</tr>

<?php
$j = 1;
$filita = 0;

for ($i=1; $i<=$dias_despues; $i++) {
	if ( $filita<$total_filas ) {
		
		if ($i==1 || $i==8 || $i==15 || $i==22 || $i==29 || $i==36) {
			echo '<tr>';
		}
		
		//Entramos a listar los dias del mes consultado
		if ( $i>=$primero_mes && $i<=$tope ) {
			echo '<td';
			if ($j<10) 	$el_dia = '0'.$j;
			else 		$el_dia = $j;
			$fecha_consultada = $el_anio.'-'.$el_mes.'-'.$el_dia;
			
			//Si existen eventos en esta fecha entonces se especifica que existen eventos este dia
			if ( count($eventos) > 0 && in_array($fecha_consultada, $eventos, true) ) {
				echo ' class="activa-con-evento';
				$existe_eventos = true;
			} else {
				echo ' class="activa-sin-evento';
				$existe_eventos = false;
			}
			
			if ( $fecha_hoy == $fecha_consultada ) {
				echo ' hoy';
				$hoy = ' (Hoy)';
			} else {
				$hoy = '';
			}
			echo "\">";
			
			if ( $existe_eventos == false ) { ?>
			<div class="calendar-top">
				<span class="left"><?php echo $j.$hoy;?></span>
				<a href="agenda_visitas-eventos.php?agregar_evento=<?php echo $fecha_consultada.$eventos_url;?>" title="Crear un Evento el <?php echo mysql_to_normal($fecha_consultada);?>" class="add-evento">Agregar Evento</a>
			</div>
			<div class="calendar-content">
			</div>
			<?php } else { //$existe_eventos == true ?>
			<div class="calendar-top">
				<span class="left"><?php echo $j.$hoy;?></span>
				<a href="agenda_visitas-eventos.php?agregar_evento=<?php echo $fecha_consultada.$eventos_url;?>" title="Crear un Evento el <?php echo mysql_to_normal($fecha_consultada);?>" class="add-evento">Agregar Evento</a>
				<a href="agenda_visitas-registros.php?ver_evento=<?php echo $fecha_consultada.$eventos_url;?>" title="Ver Eventos del <?php echo mysql_to_normal($fecha_consultada);?>" class="ver-evento">Ver Eventos</a>
			</div>
			<div class="calendar-content"><?php
			//$k = 1;
			$sql = "SELECT id_visita, TIME_FORMAT(hora_in, '%H:%i') AS hora_in, TIME_FORMAT(hora_out, '%H:%i') AS hora_out FROM agenda_visitas
			WHERE fecha_visita='".$fecha_consultada."'".$other_where." ORDER BY hora_in ASC";
			$result = $mysqli->query($sql) or die('Error: '.$mysqli->error);
			if ($result->num_rows > 0) {
				echo '<p><strong>'.$result->num_rows.' ORDENES DE VISITAS</strong></p>';
				while ($row = $result->fetch_assoc()) {
					echo '<p><strong>'.$row['id_visita']/*$k*/.'.</strong> De '.$row['hora_in'].' hasta '.$row['hora_out'].'</p>';
					//++$k;
				}
			} ?>
			</div>
			<?php }
			
			echo '</td>';
			++$j; //$j+=1;
		} else {
			if ( $i <= $tope) { ?>
			<td class="desactivada">
				<div class="calendar-top"><span class="left"><?php echo $dias_mes_anterior.' de '.$meses[$mes_anterior];?></span></div>
				<div class="calendar-content"></div>
			</td>
			<?php ++$dias_mes_anterior;
			} elseif ( $i >= $primero_mes ) { ?>
			<td class="desactivada">
				<div class="calendar-top"><span class="left"><?php echo $dias_mes_despues.' de '.$meses[$mes_despues];?></span></div>
				<div class="calendar-content"></div>
			</td>
			<?php ++$dias_mes_despues;
			}
		}
		
		if ($i==7 || $i==14 || $i==21 || $i==28 || $i==35 || $i==42) {
			echo '</tr>';
			$filita+=1;
		}
	}
}
?>
</table>
</div>