<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "contabilidad";

$conn = new mysqli($servername, $username, $passwor, $database);
if($conn->connect_error){
    die("Error de Conexión");
}

?>