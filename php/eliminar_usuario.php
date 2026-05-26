<?php
include("conexion.php");

$id = (int)$_GET['id'];

// Bloquear si tiene CUALQUIER préstamo, no solo activos
$check = $conexion->prepare("SELECT id FROM prestamos WHERE id_usuario = ? LIMIT 1");
$check->bind_param("i", $id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    header("Location: ../usuarios.php?mensaje=error_prestamo");
    exit();
}

$stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../usuarios.php?mensaje=eliminado");
?>