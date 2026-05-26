<?php
include("conexion.php");

$id = (int)$_GET['id'];

// Bloquear si tiene cualquier préstamo (activo o devuelto)
$check = $conexion->prepare("SELECT id FROM prestamos WHERE id_libro = ? LIMIT 1");
$check->bind_param("i", $id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    header("Location: ../libros.php?mensaje=error_libro");
    exit();
}

$stmt = $conexion->prepare("DELETE FROM libros WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../libros.php?mensaje=eliminado");
?>