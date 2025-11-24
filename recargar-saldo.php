<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["success" => false, "error" => "NO_SESSION"]);
    exit;
}

// Incluir conexión a la base de datos
include("db.php");

// Obtener el monto de la recarga
$monto = $_POST['monto'] ?? 0;

// Validar que el monto sea válido
if (!is_numeric($monto) || $monto <= 0) {
    echo json_encode(["success" => false, "error" => "Monto inválido"]);
    exit;
}

// Obtener el ID del usuario desde la sesión
$usuario_id = $_SESSION['usuario']['id'];

// Actualizar el saldo en la base de datos
$sql = "UPDATE usuarios SET saldo = saldo + ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Error en la preparación de la consulta"]);
    exit;
}

$stmt->bind_param("di", $monto, $usuario_id);

if ($stmt->execute()) {
    // Obtener el nuevo saldo
    $sql_select = "SELECT saldo FROM usuarios WHERE id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $usuario_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nuevo_saldo = $row['saldo'];

        // Actualizar la sesión con el nuevo saldo
        $_SESSION['usuario']['saldo'] = $nuevo_saldo;

        echo json_encode([
            "success" => true,
            "data" => [
                "saldo" => number_format($nuevo_saldo, 2, '.', '')
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "No se pudo obtener el nuevo saldo"]);
    }

    $stmt_select->close();
} else {
    echo json_encode(["success" => false, "error" => "Error al actualizar el saldo"]);
}

$stmt->close();
$conn->close();
