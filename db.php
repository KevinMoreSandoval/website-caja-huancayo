<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "usuarios";

$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    // En producción, no mostrar detalles del error al usuario
    error_log("Error de Conexión a BD: " . $conn->connect_error);
    die("Error: No se pudo conectar a la base de datos. Intente más tarde.");
}

// Establecer charset UTF-8
$conn->set_charset("utf8mb4");
?>
