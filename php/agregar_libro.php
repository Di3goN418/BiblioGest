<?php
// Incluye la conexion
include("conexion.php");

// Datos ingresados en formulario
$titulo           = trim($_POST['titulo']           ?? '');
$isbn             = trim($_POST['isbn']             ?? '');
$anio_publicacion = trim($_POST['anio_publicacion'] ?? '');
$editorial        = trim($_POST['editorial']        ?? '');
$stock            = (int)($_POST['stock']           ?? 0);

if (!$titulo) {
    header("Location: ../libros.php?mensaje=error");
    exit();
}

// ── Imagen ────────────────────────────────────
$imagenFinal = "";
$uploadDir   = "../uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $imagen = basename($_FILES['imagen']['name']);
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $imagen))
        $imagenFinal = $imagen;
}

// ── Insertar libro ────────────────────────────
$stmt = $conexion->prepare("
    INSERT INTO libros (titulo, isbn, anio_publicacion, editorial, stock, imagen)
    VALUES (?, ?, ?, ?, ?, ?)
");
$anioVal = $anio_publicacion ?: null;
$stmt->bind_param("sssssi", $titulo, $isbn, $anioVal, $editorial, $stock, $imagenFinal);

// Necesitamos bind_param con tipos correctos — stock es int, imagen es string
$stmt = $conexion->prepare("
    INSERT INTO libros (titulo, isbn, anio_publicacion, editorial, stock, imagen)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("ssssis", $titulo, $isbn, $anioVal, $editorial, $stock, $imagenFinal);
$stmt->execute();
$id_libro = $conexion->insert_id;

// ── Nuevo autor (si escribieron uno) ──────────
if (!empty(trim($_POST['nuevo_autor'] ?? ''))) {
    $nuevoAutor = trim($_POST['nuevo_autor']);
    $stA = $conexion->prepare("INSERT INTO autores (nombre) VALUES (?)");
    $stA->bind_param("s", $nuevoAutor);
    $stA->execute();
    $_POST['autores'][] = $conexion->insert_id;
}

// ── Insertar libro_autores ────────────────────
if (!empty($_POST['autores'])) {
    $stLA = $conexion->prepare("INSERT IGNORE INTO libro_autores (id_libro, id_autor) VALUES (?, ?)");
    foreach ($_POST['autores'] as $id_autor) {
        $id_autor = (int)$id_autor;
        $stLA->bind_param("ii", $id_libro, $id_autor);
        $stLA->execute();
    }
}

// ── Nuevo género (si escribieron uno) ─────────
if (!empty(trim($_POST['nuevo_genero'] ?? ''))) {
    $nuevoGenero = trim($_POST['nuevo_genero']);
    $stG = $conexion->prepare("INSERT INTO generos (nombre) VALUES (?)");
    $stG->bind_param("s", $nuevoGenero);
    $stG->execute();
    $_POST['generos'][] = $conexion->insert_id;
}

// ── Insertar libro_generos ────────────────────
if (!empty($_POST['generos'])) {
    $stLG = $conexion->prepare("INSERT IGNORE INTO libro_generos (id_libro, id_genero) VALUES (?, ?)");
    foreach ($_POST['generos'] as $id_genero) {
        $id_genero = (int)$id_genero;
        $stLG->bind_param("ii", $id_libro, $id_genero);
        $stLG->execute();
    }
}

header("Location: ../libros.php?mensaje=guardado");
