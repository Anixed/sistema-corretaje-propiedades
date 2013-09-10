<?php
require('connect-mysql.php'); require('functions.php');
echo cod_correlativo_propiedades($_POST['tipo_propiedad'],$_POST['operacion'],$_POST['cod_propiedad']);