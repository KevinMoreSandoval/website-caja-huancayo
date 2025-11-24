<?php
session_start();
include("db.php");

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["success" => false, "error" => "NO_SESSION"]);
    exit;
}

// Obtener el ID del usuario de la sesión
$usuario_id = $_SESSION['usuario']['id'];

// Recibir datos del formulario
$direccion = trim($_POST['direccion'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');

// Validaciones básicas
if (empty($direccion) || empty($telefono) || empty($correo)) {
    echo json_encode(["success" => false, "error" => "Todos los campos son requeridos"]);
    exit;
}

// Validar formato de correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "error" => "Formato de correo inválido"]);
    exit;
}

// Actualizar en la base de datos
$sql = "UPDATE usuarios SET direccion = ?, telefono = ?, correo = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Error preparando consulta"]);
    exit;
}

$stmt->bind_param("sssi", $direccion, $telefono, $correo, $usuario_id);

if ($stmt->execute()) {
    // Actualizar la sesión con los nuevos datos
    $_SESSION['usuario']['direccion'] = $direccion;
    $_SESSION['usuario']['telefono'] = $telefono;
    $_SESSION['usuario']['correo'] = $correo;

    echo json_encode([
        "success" => true,
        "message" => "Datos actualizados correctamente",
        "data" => [
            "direccion" => $direccion,
            "telefono" => $telefono,
            "correo" => $correo
        ]
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Error al actualizar los datos"]);
}

$stmt->close();
$conn->close();
