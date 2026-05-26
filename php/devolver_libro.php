<?php
include("conexion.php");

$id = (int)$_GET['id'];

// 1. Obtener datos del préstamo
$q = $conexion->prepare("SELECT * FROM prestamos WHERE id = ?");
$q->bind_param("i", $id);
$q->execute();
$prestamo = $q->get_result()->fetch_assoc();

if (!$prestamo || $prestamo['estado'] != 'Activo') {
    header("Location: ../prestamos.php");
    exit();
}

$hoy = date("Y-m-d");

// 2. Marcar préstamo como Devuelto con fecha de entrega real
$upQ = $conexion->prepare("
    UPDATE prestamos 
    SET estado = 'Devuelto', fecha_entrega = ?
    WHERE id = ?
");
$upQ->bind_param("si", $hoy, $id);
$upQ->execute();

// 3. Subir el stock del libro
$stockQ = $conexion->prepare("UPDATE libros SET stock = stock + 1 WHERE id = ?");
$stockQ->bind_param("i", $prestamo['id_libro']);
$stockQ->execute();

// 4. Calcular multa si hubo retraso
if ($hoy > $prestamo['fecha_devolucion']) {
    $dias  = (int)(( strtotime($hoy) - strtotime($prestamo['fecha_devolucion']) ) / 86400);
    $monto = $dias * 5;

    // Si ya existe multa para este préstamo, actualizarla
    $checkQ = $conexion->prepare("SELECT id FROM multas WHERE id_prestamo = ?");
    $checkQ->bind_param("i", $id);
    $checkQ->execute();
    $existente = $checkQ->get_result();

    if ($existente->num_rows > 0) {
        $multaId = $existente->fetch_assoc()['id'];
        $updM = $conexion->prepare("UPDATE multas SET dias = ?, monto = ? WHERE id = ?");
        $updM->bind_param("idi", $dias, $monto, $multaId);
        $updM->execute();
    } else {
        $insM = $conexion->prepare("
            INSERT INTO multas (id_prestamo, dias, monto, estado)
            VALUES (?, ?, ?, 'Pendiente')
        ");
        $insM->bind_param("iid", $id, $dias, $monto);
        $insM->execute();
    }
}

header("Location: ../prestamos.php?mensaje=devuelto");
?>
