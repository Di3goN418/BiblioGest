<?php
include("conexion.php");

$id               = (int)$_POST['id'];
$titulo           = trim($_POST['titulo']           ?? '');
$isbn             = trim($_POST['isbn']             ?? '');
$anio_publicacion = trim($_POST['anio_publicacion'] ?? '') ?: null;
$editorial        = trim($_POST['editorial']        ?? '');
$stock            = (int)($_POST['stock']           ?? 0);

// ── Imagen ────────────────────────────────────
$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Obtener imagen actual
$imgActual = $conexion->query("SELECT imagen FROM libros WHERE id=$id")->fetch_assoc()['imagen'] ?? '';
$imagenFinal = $imgActual;

if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $nombre = basename($_FILES['imagen']['name']);
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $nombre))
        $imagenFinal = $nombre;
}

// ── Actualizar libro ──────────────────────────
$stmt = $conexion->prepare("
    UPDATE libros
    SET titulo=?, isbn=?, anio_publicacion=?, editorial=?, stock=?, imagen=?
    WHERE id=?
");
$stmt->bind_param("ssssisi", $titulo, $isbn, $anio_publicacion, $editorial, $stock, $imagenFinal, $id);
$stmt->execute();

// ── Nuevo autor (si escribieron uno) ──────────
if (!empty(trim($_POST['nuevo_autor'] ?? ''))) {
    $nuevoAutor = trim($_POST['nuevo_autor']);
    $stA = $conexion->prepare("INSERT INTO autores (nombre) VALUES (?)");
    $stA->bind_param("s", $nuevoAutor);
    $stA->execute();
    $_POST['autores_edit'][] = $conexion->insert_id;
}

// ── Reemplazar libro_autores ──────────────────
$conexion->query("DELETE FROM libro_autores WHERE id_libro=$id");
if (!empty($_POST['autores_edit'])) {
    $stLA = $conexion->prepare("INSERT IGNORE INTO libro_autores (id_libro, id_autor) VALUES (?, ?)");
    foreach ($_POST['autores_edit'] as $id_autor) {
        $id_autor = (int)$id_autor;
        $stLA->bind_param("ii", $id, $id_autor);
        $stLA->execute();
    }
}

// ── Nuevo género (si escribieron uno) ─────────
if (!empty(trim($_POST['nuevo_genero'] ?? ''))) {
    $nuevoGenero = trim($_POST['nuevo_genero']);
    $stG = $conexion->prepare("INSERT INTO generos (nombre) VALUES (?)");
    $stG->bind_param("s", $nuevoGenero);
    $stG->execute();
    $_POST['generos_edit'][] = $conexion->insert_id;
}

// ── Reemplazar libro_generos ──────────────────
$conexion->query("DELETE FROM libro_generos WHERE id_libro=$id");
if (!empty($_POST['generos_edit'])) {
    $stLG = $conexion->prepare("INSERT IGNORE INTO libro_generos (id_libro, id_genero) VALUES (?, ?)");
    foreach ($_POST['generos_edit'] as $id_genero) {
        $id_genero = (int)$id_genero;
        $stLG->bind_param("ii", $id, $id_genero);
        $stLG->execute();
    }
}

header("Location: ../libros.php?mensaje=editado");
