<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["error" => "NO_SESSION"]);
    exit;
}

echo json_encode($_SESSION['usuario']);
?>
