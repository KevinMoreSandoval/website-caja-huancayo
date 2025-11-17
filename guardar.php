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
$checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE numero_cuenta = ?");
if (!$checkStmt) {
    echo "Error preparando consulta: " . $conn->error;
    $conn->close();
    exit;
}

$i = 1;
while (true) {
    $checkStmt->bind_param('s', $candidate);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['total'];

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
    // Mostrar página de bienvenida con número de cuenta y contraseña
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>¡Bienvenido a Caja Huancayo!</title>
        <link href="css/bootstrap.min.css" rel="stylesheet" />
        <link href="iconAndFonts/bootstrap-icons.css" rel="stylesheet" />
        <link rel="stylesheet" href="styles/modalSave.css" />
    </head>

    <body>
        <script src="js/modalSave.js"></script>
        <div class="welcome-container">
            <div class="icon-success">
                <i class="bi bi-check-circle"></i>
            </div>

            <h1 class="welcome-title">¡Bienvenido!</h1>
            <p class="welcome-subtitle">Tu cuenta ha sido creada exitosamente</p>

            <div class="user-greeting">
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre . ' ' . $apellido); ?></p>
                <p><strong>Correo:</strong> <?php echo htmlspecialchars($correo); ?></p>
                <p><strong>Fecha de registro:</strong> <?php echo date('d/m/Y H:i'); ?></p>
            </div>

            <div class="alert-info-custom">
                <i class="bi bi-info-circle"></i> Guarda esta información en un lugar seguro
            </div>

            <div class="credential-box">
                <label class="credential-label">Número de Cuenta</label>
                <div class="credential-value">
                    <span id="numeroCuenta"><?php echo htmlspecialchars($candidate); ?></span>
                    <button class="copy-btn" onclick="copiar('numeroCuenta')" title="Copiar">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>

            <div class="credential-box">
                <label class="credential-label">Contraseña</label>
                <div class="credential-value">
                    <span id="contraseña"><?php echo htmlspecialchars($contrasena); ?></span>
                    <button class="copy-btn" onclick="copiar('contraseña')" title="Copiar">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>

            <a href="index.html" class="btn-login">
                <i class="bi bi-arrow-right"></i> Volver al inicio
            </a>
        </div>
    </body>

    </html>
<?php
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