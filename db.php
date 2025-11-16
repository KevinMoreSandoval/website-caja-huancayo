<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "contabilidad";

$conn = new mysqli($servername, $username, $password, $database);
if($conn->connect_error){
    die("Error de Conexión");
}

?>