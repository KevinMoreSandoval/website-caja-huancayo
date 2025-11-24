<?php
include("db.php");

// Recibir datos del modal
$dni = $_POST['dni'] ?? '';
$numeroCuenta = $_POST['numero_cuenta'] ?? '';
$motivo = $_POST['motivo'] ?? '';
$detalle = $_POST['detalle'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

$exito = false;
$mensaje = '';
$detalles = [];

// Validación 
if (empty($dni) || empty($numeroCuenta) || empty($motivo) || empty($contrasena)) {
    $mensaje = 'Faltan datos obligatorios para completar el bloqueo de la cuenta.';
} else {
    // Buscar el id_usuario por DNI
    $sqlUsuario = "SELECT id FROM usuarios WHERE dni = ?";
    $stmtUsuario = $conn->prepare($sqlUsuario);

    if (!$stmtUsuario) {
        $mensaje = 'Error del sistema al preparar la consulta.';
    } else {
        $stmtUsuario->bind_param("s", $dni);
        $stmtUsuario->execute();
        $resultUsuario = $stmtUsuario->get_result();

        if ($resultUsuario->num_rows === 0) {
            $mensaje = 'No se encontró ningún usuario con el DNI proporcionado.';
            $stmtUsuario->close();
        } else {
            $usuario = $resultUsuario->fetch_assoc();
            $id_usuario = $usuario['id'];
            $stmtUsuario->close();

            // Encriptar contraseña
            $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

            // Insertar en la tabla bloqueo
            $sql = "INSERT INTO bloqueos_usuarios (id_usuario, numero_cuenta, motivo, detalle, contrasena_bloqueo)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $mensaje = 'Error del sistema al procesar el bloqueo.';
            } else {
                $stmt->bind_param("sssss", $id_usuario, $numeroCuenta, $motivo, $detalle, $contrasenaHash);

                if ($stmt->execute()) {
                    // Actualizar el estado del usuario a 'bloqueado'
                    $sqlUpdateEstado = "UPDATE usuarios SET estado = 'bloqueado' WHERE id = ?";
                    $stmtUpdate = $conn->prepare($sqlUpdateEstado);

                    if ($stmtUpdate) {
                        $stmtUpdate->bind_param("i", $id_usuario);
                        $stmtUpdate->execute();
                        $stmtUpdate->close();
                    }

                    $exito = true;
                    $mensaje = 'La cuenta ha sido bloqueada correctamente en el sistema.';
                    $detalles = [
                        'Cuenta' => $numeroCuenta,
                        'DNI' => $dni,
                        'Motivo' => $motivo,
                        'Detalle' => $detalle
                    ];
                } else {
                    $mensaje = 'No se pudo completar el registro del bloqueo.';
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $exito ? 'Bloqueo Exitoso' : 'Error en el Proceso'; ?></title>
    <link rel="stylesheet" href="styles/bloqueo.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="icon-container <?php echo $exito ? 'success' : 'error'; ?>">
                <div class="icon">
                    <?php echo $exito ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M8 0a4 4 0 0 1 4 4v2.05a2.5 2.5 0 0 1 2 2.45v5a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 2 13.5v-5a2.5 2.5 0 0 1 2-2.45V4a4 4 0 0 1 4-4M4.5 7A1.5 1.5 0 0 0 3 8.5v5A1.5 1.5 0 0 0 4.5 15h7a1.5 1.5 0 0 0 1.5-1.5v-5A1.5 1.5 0 0 0 11.5 7zM8 1a3 3 0 0 0-3 3v2h6V4a3 3 0 0 0-3-3"/>
</svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
</svg>'; ?>
                </div>
            </div>

            <h1 class="title"><?php echo $exito ? 'Bloqueo Registrado' : 'Error en el Proceso'; ?></h1>

            <p class="message"><?php echo htmlspecialchars($mensaje); ?></p>

            <?php if ($exito && !empty($detalles)): ?>
                <div class="details">
                    <?php foreach ($detalles as $label => $valor): ?>
                        <?php if (!empty($valor)): ?>
                            <div class="detail-row">
                                <span class="detail-label"><?php echo htmlspecialchars($label); ?>:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($valor); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="actions">
                <?php if ($exito): ?>
                    <button class="btn btn-primary" onclick="window.location.href='index.html'">
                        <span>Ir al Inicio</span>
                    </button>
                <?php else: ?>
                    <button class="btn btn-primary" onclick="history.back()">
                        <span>Reintentar</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>