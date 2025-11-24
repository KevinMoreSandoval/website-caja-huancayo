<?php
session_start();
include("db.php");

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["success" => false, "error" => "NO_SESSION"]);
    exit;
}

$id_usuario = $_POST['id_usuario'] ?? null;

if (!$id_usuario) {
    echo json_encode(["success" => false, "error" => "ID de usuario no proporcionado"]);
    exit;
}

try {
    // Obtener información de bloqueo más reciente del usuario
    $sql = "SELECT motivo, detalle, fecha_bloqueo 
            FROM bloqueos_usuarios 
            WHERE id_usuario = ? 
            ORDER BY fecha_bloqueo DESC 
            LIMIT 1";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "Error al preparar la consulta"]);
        exit;
    }

    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $blockData = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "data" => $blockData
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "No se encontró información de bloqueo"
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error al obtener información de bloqueo: " . $e->getMessage()
    ]);
}

$conn->close();
