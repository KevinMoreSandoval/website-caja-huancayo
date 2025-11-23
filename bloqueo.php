<?php
include("db.php"); // Usar conexión centralizada

// Recibir datos del modal
$dni = $_POST['dni'] ?? '';
$numeroCuenta = $_POST['numero_cuenta'] ?? '';
$motivo = $_POST['motivo'] ?? '';
$detalle = $_POST['detalle'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

// Validación sencilla
if (empty($dni) || empty($numeroCuenta) || empty($motivo) || empty($contrasena)) {
    die("Error: faltan datos.");
}

// Insertar en la tabla "bloqueo" (o el nombre exacto que usaste)
$sql = "INSERT INTO bloqueo_cuenta (dni, numero_cuenta, motivo, detalle, contrasena)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error en prepare(): " . $conn->error);
}

$stmt->bind_param("sssss", $dni, $numeroCuenta, $motivo, $detalle, $contrasena);

if ($stmt->execute()) {
    echo "Bloqueo registrado con éxito.";
} else {
    echo "Error al registrar: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
