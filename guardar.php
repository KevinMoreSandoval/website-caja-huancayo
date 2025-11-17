<?php
include("db.php");

// Recoger y sanitizar entradas (usar coalescing para evitar "undefined index")
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
$dni = trim($_POST['dni'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$contrasena = trim($_POST['contrasena'] ?? '');

// Validaciones básicas
if (empty($nombre) || empty($apellido) || empty($dni) || empty($contrasena)) {
    echo "Error: faltan datos requeridos";
    $conn->close();
    exit;
}

// Hashear contraseña (recomendado)
$hash = password_hash($contrasena, PASSWORD_DEFAULT);

// -------------------------------------------------------
// GENERAR NUMERO DE CUENTA A PARTIR DEL DNI (base)
// -------------------------------------------------------
$codigoBanco = "5687";
$codigoSucursal = "2579";

// calcular dígito verificador simple
$suma = array_sum(str_split($dni));
$digito = $suma % 10;

$base_numero = $codigoBanco . "-" . $codigoSucursal . "-" . $dni . "-" . $digito;

// Asegurar unicidad: si ya existe, añadimos sufijo incremental
$candidate = $base_numero;
$checkStmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE numero_cuenta = ?");
if (!$checkStmt) {
    echo "Error preparando consulta: " . $conn->error;
    $conn->close();
    exit;
}

$i = 1;
while (true) {
    $checkStmt->bind_param('s', $candidate);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->store_result();
    // Si no existe, salimos
    if ($count == 0) break;
    // Generar candidato con sufijo
    $candidate = $base_numero . '-' . $i;
    $i++;
    // protección por si algo falla no quedar en loop infinito
    if ($i > 1000) break;
}
$checkStmt->close();

// -------------------------------------------------------
// Insertar registro con sentencia preparada
// -------------------------------------------------------
$insert = $conn->prepare("INSERT INTO usuarios (nombre, apellido, fecha_nacimiento, dni, telefono, correo, direccion, contrasena, numero_cuenta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$insert) {
    echo "Error preparando insert: " . $conn->error;
    $conn->close();
    exit;
}

$insert->bind_param('sssssssss', $nombre, $apellido, $fecha_nacimiento, $dni, $telefono, $correo, $direccion, $hash, $candidate);

if ($insert->execute()) {
    echo "Usuario Registrado. Número de cuenta: $candidate";
} else {
    // Manejo más amable de errores (no volcar todo el error SQL en producción)
    if ($conn->errno === 1062) {
        echo "Error: número de cuenta duplicado. Intente nuevamente.";
    } else {
        echo "Error registrando usuario.";
    }
}

$insert->close();
$conn->close();
?>
