<?php
include("conexion.php");

$id = (int)$_GET['id'];

// Si el préstamo estaba activo, devolver el stock antes de eliminar
$q = $conexion->prepare("SELECT id_libro, estado FROM prestamos WHERE id = ?");
$q->bind_param("i", $id);
$q->execute();
$prestamo = $q->get_result()->fetch_assoc();

if ($prestamo && $prestamo['estado'] == 'Activo') {
    $stockQ = $conexion->prepare("UPDATE libros SET stock = stock + 1 WHERE id = ?");
    $stockQ->bind_param("i", $prestamo['id_libro']);
    $stockQ->execute();
}

// Eliminar multa relacionada si existe
$delM = $conexion->prepare("DELETE FROM multas WHERE id_prestamo = ?");
$delM->bind_param("i", $id);
$delM->execute();

// Eliminar el préstamo
$delP = $conexion->prepare("DELETE FROM prestamos WHERE id = ?");
$delP->bind_param("i", $id);
$delP->execute();

header("Location: ../prestamos.php?mensaje=eliminado");
?>
