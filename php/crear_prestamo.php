<?php
include("conexion.php");

$id_usuario      = (int)$_POST['id_usuario'];
$id_libro        = (int)$_POST['id_libro'];
$fecha_prestamo  = $_POST['fecha_prestamo'];
$fecha_devolucion = $_POST['fecha_devolucion'];

// 1. Verificar que el libro tenga stock
$libroQ = $conexion->prepare("SELECT stock FROM libros WHERE id = ?");
$libroQ->bind_param("i", $id_libro);
$libroQ->execute();
$libro = $libroQ->get_result()->fetch_assoc();

if (!$libro || $libro['stock'] <= 0) {
    header("Location: ../prestamos.php?mensaje=error_stock");
    exit();
}

// 2. Verificar que el usuario NO tenga ya un préstamo activo
$activoQ = $conexion->prepare("SELECT id FROM prestamos WHERE id_usuario = ? AND estado = 'Activo'");
$activoQ->bind_param("i", $id_usuario);
$activoQ->execute();
$activo = $activoQ->get_result();

if ($activo->num_rows > 0) {
    header("Location: ../prestamos.php?mensaje=error_prestamo");
    exit();
}

// 3. Insertar el préstamo
$stmt = $conexion->prepare("
    INSERT INTO prestamos (id_usuario, id_libro, fecha_prestamo, fecha_devolucion, estado)
    VALUES (?, ?, ?, ?, 'Activo')
");
$stmt->bind_param("iiss", $id_usuario, $id_libro, $fecha_prestamo, $fecha_devolucion);
$stmt->execute();
$id_prestamo = $conexion->insert_id;

// 4. Bajar el stock del libro en 1
$stockQ = $conexion->prepare("UPDATE libros SET stock = stock - 1 WHERE id = ?");
$stockQ->bind_param("i", $id_libro);
$stockQ->execute();

// 5. Si la fecha de devolución ya pasó (caso raro, pero cubrimos), generar multa
$hoy = date("Y-m-d");
if ($hoy > $fecha_devolucion) {
    $dias  = (int)(( strtotime($hoy) - strtotime($fecha_devolucion) ) / 86400);
    $monto = $dias * 5;

    $multaQ = $conexion->prepare("
        INSERT INTO multas (id_prestamo, dias, monto, estado)
        VALUES (?, ?, ?, 'Pendiente')
    ");
    $multaQ->bind_param("iid", $id_prestamo, $dias, $monto);
    $multaQ->execute();
}

header("Location: ../prestamos.php?mensaje=creado");
?>
