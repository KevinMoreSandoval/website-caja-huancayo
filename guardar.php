<?php
include('db.php');

$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$dni = $_POST['dni'];
$contrasena = $_POST['contrasena'];
 
//Insertar registros a tabla de la base de datos
$sql="INSERT INTO usuarios(nombre,apellido,fecha_nacimiento,dni,contrasena) VALUES('$nombre', '$apellido', '$fecha_nacimiento', '$dni', '$contrasena')";
 if($conn->query($sql) === TRUE) {
    echo ('Usuario Registrado');
 }else{
    echo "Error";
 }
 $conn->close();
?>