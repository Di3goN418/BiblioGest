<?php
include("conexion.php");

$id = (int)$_GET['id'];

$stmt = $conexion->prepare("UPDATE multas SET estado='Pagada' WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../multas.php?mensaje=pagada");
