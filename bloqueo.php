<?php
include("db.php"); // Usar conexión centralizada

// Recibir datos del modal
$dni = $_POST['dni'] ?? '';
$numeroCuenta = $_POST['numero_cuenta'] ?? '';
$motivo = $_POST['motivo'] ?? '';
$detalle = $_POST['detalle'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

// Validación 
if (empty($dni) || empty($numeroCuenta) || empty($motivo) || empty($contrasena)) {
    echo("Error: faltan datos.");
    exit;
}

// Encriptar contraseña
$contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

// Insertar en la tabla bloqueo
$sql = "INSERT INTO bloqueo_cuenta (id_usuario, numero_cuenta, motivo, detalle, contrasena_bloqueo)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error en prepare(): " . $conn->error);
}

$stmt->bind_param("sssss", $id_usuario, $numeroCuenta, $motivo, $detalle, $contrasena);

if ($stmt->execute()) {
    echo "Bloqueo registrado con éxito.";
} else {
    echo "Error al registrar: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
