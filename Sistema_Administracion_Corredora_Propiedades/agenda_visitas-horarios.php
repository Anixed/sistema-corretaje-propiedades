<?php require('connect-mysql.php'); require('functions.php'); ?>
<?php
echo horario_propiedad($_GET['propiedad'], $_GET['ver_evento'], $_GET['vendedor'], $_GET['cliente']);