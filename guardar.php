<?php
include("db.php");

// Sanitizar entradas
$nombre = $conn->real_escape_string($_POST['nombre']);
$apellido = $conn->real_escape_string($_POST['apellido']);
$fecha_nacimiento = $conn->real_escape_string($_POST['fecha_nacimiento']);
$dni = $conn->real_escape_string($_POST['dni']);
$telefono = $conn->real_escape_string($_POST['telefono']);
$correo = $conn->real_escape_string($_POST['correo']);
$direccion = $conn->real_escape_string($_POST['direccion']);
$contrasena = $conn->real_escape_string($_POST['contrasena']);

// -------------------------------------------------------
// GENERAR NUMERO DE CUENTA A PARTIR DEL DNI
// -------------------------------------------------------
$codigoBanco = "5687";
$codigoSucursal = "2579";

// calcular dígito verificador
$suma = array_sum(str_split($dni));
$digito = $suma % 10;

$numero_cuenta = $codigoBanco . "-" . $codigoSucursal . "-" . $dni . "-" . $digito;

// -------------------------------------------------------
// Insertar registro
// IMPORTANTE: ordenar los valores igual que los campos
// -------------------------------------------------------
$sql = "INSERT INTO usuarios
(nombre, apellido, fecha_nacimiento, dni, telefono, correo, direccion, contrasena, numero_cuenta)
VALUES
('$nombre', '$apellido', '$fecha_nacimiento', '$dni', '$telefono', '$correo', '$direccion', '$contrasena', '$numero_cuenta')";

if ($conn->query($sql) === TRUE) {
    echo "Usuario Registrado. Número de cuenta: $numero_cuenta";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
