<?php
include("conexion.php");

$id       = (int)$_POST['id'];
$nombre   = trim($_POST['nombre']   ?? '');
$correo   = trim($_POST['correo']   ?? '') ?: null;
$telefono = trim($_POST['telefono'] ?? '') ?: null;

$stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, correo=?, telefono=? WHERE id=?");
$stmt->bind_param("sssi", $nombre, $correo, $telefono, $id);
$stmt->execute();

header("Location: ../usuarios.php?mensaje=editado");
