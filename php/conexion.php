<?php
// Conexion al sevicio de MySQL
$conexion = new mysqli(
    "localhost", 
    "root", 
    "", 
    "bibliotecanew"
    );

// Verifica si ocurrió un error en la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>