<?php
// Incluye la conexion
include("conexion.php");

// Datos ingresados en el formulario 
$nombre   = trim($_POST['nombre']   ?? '');
$correo   = trim($_POST['correo']   ?? '') ?: null;
$telefono = trim($_POST['telefono'] ?? '') ?: null;

if (!$nombre) { header("Location: ../usuarios.php"); exit(); }


// Agregar usuario a la BD
$stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, telefono) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nombre, $correo, $telefono);
$stmt->execute();

header("Location: ../usuarios.php?mensaje=agregado");
