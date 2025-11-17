<?php
// Incluir conexión centralizada
include("db.php");

// RECIBIR DATOS DEL FORM -----------------------
$numero_cuenta = $_POST['numero_cuenta'] ?? '';
$dni = $_POST['dni'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

// DEBUG (remover en producción)
// error_log("Login intento - Cuenta: $numero_cuenta, DNI: $dni");

if (empty($numero_cuenta) || empty($dni) || empty($contrasena)) {
    die("Faltan datos");
}

// CONSULTA SEGURA ------------------------------
$sql = "SELECT * FROM usuarios WHERE numero_cuenta = ? AND dni = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error en prepare(): " . $conn->error);
}

$stmt->bind_param("ss", $numero_cuenta, $dni);
$stmt->execute();
$result = $stmt->get_result();

// VALIDAR USUARIO ------------------------------
if ($result->num_rows == 1) {
    $usuario = $result->fetch_assoc();

    // Detectar si la contraseña está hasheada o en texto plano
    $password_stored = $usuario['contrasena'];
    $password_valid = false;

    // Si comienza con $ (formato bcrypt hash), usar password_verify
    if (strpos($password_stored, '$') === 0) {
        $password_valid = password_verify($contrasena, $password_stored);
    } else {
        // Si es texto plano, comparar directamente
        $password_valid = ($contrasena === $password_stored);
    }

    if ($password_valid) {
        // Crear sesión
        session_start();
        $_SESSION['usuario'] = $usuario;

        echo "OK";
        // header("Location: usuario.php");
        exit();
    } else {
        echo "Contraseña incorrecta";
    }
} else {
    echo "Usuario no encontrado";
}

$stmt->close();
$conn->close();
