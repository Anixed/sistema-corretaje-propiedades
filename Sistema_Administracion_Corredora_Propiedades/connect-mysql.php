<?php
/*$server = "localhost";
$bd_user = "root";
$bd_pass = "mypass";
$bd_name = "sistema_corretaje";*/
define("DB_NAME","sistema_corretaje");    // nombre DB
define("DB_HOST","localhost");             // host MySQL
define("DB_USER","root");                 // usuario MySQL
define("DB_PASS","mypass");              // pass MySQL

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
	die('Error de Conexión (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
$mysqli->query("SET NAMES 'utf8'");