<?php
// 1. CONEXIÓN A LA BD -----------------------------
$conexion = new mysqli("localhost", "root", "", "usuarios");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 2. RECIBIR DATOS DEL FORM -----------------------
$numero_cuenta = $_POST['numero_cuenta'];
$dni = $_POST['dni'];
$contrasena = $_POST['contrasena'];


if (empty($numero_cuenta) || empty($dni) || empty($contrasena)) {
    die("Faltan datos");
}

// 3. CONSULTA SEGURA ------------------------------
$sql = "SELECT * FROM usuarios WHERE numero_cuenta = ? AND dni = ?";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error en prepare(): " . $conexion->error);
}

$stmt->bind_param("ss", $numero_cuenta, $dni);
$stmt->execute();
$result = $stmt->get_result();

// 4. VALIDAR USUARIO ------------------------------
if ($result->num_rows == 1) {
    $usuario = $result->fetch_assoc();

    // Si la contraseña está sin hash:
    if ($contrasena === $usuario['contrasena']) {

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
$conexion->close();
