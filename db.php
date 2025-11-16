<?php
$servername = "localhost";
$username = "root";
$passwordname = "";
$database = "contabilidad";

$conn = new mysqli($servername, $username, $passwordname, $database);
if($conn->connect_error){
    die("Error de Conexión");
}

?>