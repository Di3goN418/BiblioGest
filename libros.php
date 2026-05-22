<?php
session_start();
include("php/conexion.php");

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

$limite   = 8;
$pagina   = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio   = ($pagina - 1) * $limite;
$busqueda = isset($_GET['buscar']) ? $conexion->real_escape_string($_GET['buscar']) : "";

$where = $busqueda
    ? "WHERE l.titulo LIKE '%$busqueda%'
       OR l.isbn LIKE '%$busqueda%'
       OR l.editorial LIKE '%$busqueda%'
       OR a.nombre LIKE '%$busqueda%'
       OR g.nombre LIKE '%$busqueda%'"
    : "";

$resultado = $conexion->query("
    SELECT l.*,
        GROUP_CONCAT(DISTINCT a.nombre  ORDER BY a.nombre  SEPARATOR ', ') AS autores,
        GROUP_CONCAT(DISTINCT a.id                                          SEPARATOR ',') AS autor_ids,
        GROUP_CONCAT(DISTINCT g.nombre  ORDER BY g.nombre  SEPARATOR ', ') AS generos,
        GROUP_CONCAT(DISTINCT g.id                                          SEPARATOR ',') AS genero_ids
    FROM libros l
    LEFT JOIN libro_autores la ON l.id = la.id_libro
    LEFT JOIN autores        a  ON la.id_autor  = a.id
    LEFT JOIN libro_generos  lg ON l.id = lg.id_libro
    LEFT JOIN generos        g  ON lg.id_genero = g.id
    $where
    GROUP BY l.id
    ORDER BY l.titulo
    LIMIT $inicio, $limite
");

$totalQ   = $conexion->query("
    SELECT COUNT(DISTINCT l.id) AS t FROM libros l
    LEFT JOIN libro_autores la ON l.id = la.id_libro
    LEFT JOIN autores        a  ON la.id_autor  = a.id
    LEFT JOIN libro_generos  lg ON l.id = lg.id_libro
    LEFT JOIN generos        g  ON lg.id_genero = g.id
    $where
");
$total        = $totalQ->fetch_assoc()['t'];
$totalPaginas = ceil($total / $limite);

// Para los checkboxes de los modales
$todosAutores = $conexion->query("SELECT * FROM autores ORDER BY nombre");
$todosGeneros = $conexion->query("SELECT * FROM generos ORDER BY nombre");

$paginaActiva = 'libros';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Libros — BiblioGest</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/app.css">
</head>
<body class="bg-light">

<!-- NAVBAR MOBILE -->
<nav class="navbar d-md-none mobile-nav px-3 py-2">
  <button class="btn btn-link text-white p-0" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
    <i class="fa-solid fa-bars fa-lg"></i>
  </button>
  <span class="text-white fw-bold"><i class="fa-solid fa-book me-2"></i>BiblioGest</span>
</nav>

<div class="d-flex">
  <?php include "php/sidebar.php"; ?>

  <main class="flex-grow-1 p-4">

    <!-- ALERTA -->
    <?php if (isset($_GET['mensaje'])): ?>
      <?php $msgs = ['guardado'=>['success','Libro agregado'], 'editado'=>['success','Libro actualizado'], 'eliminado'=>['warning','Libro eliminado']]; ?>
      <?php [$tipo, $texto] = $msgs[$_GET['mensaje']] ?? ['info','Operación completada']; ?>
      <div class="alert alert-<?= $tipo ?> alert-dismissible alert-autohide fade show" role="alert">
        <?= $texto ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- TOP -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0"><i class="fa-solid fa-book-open me-2" style="color:var(--brand)"></i>Libros</h4>
      <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalAgregar">
        <i class="fa-solid fa-plus me-1"></i> Nuevo libro
      </button>
    </div>

    <!-- BUSCADOR -->
    <form method="GET" class="mb-3">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
        <input type="text" name="buscar" class="form-control"
               placeholder="Buscar por título, autor, género, ISBN..."
               value="<?= htmlspecialchars($busqueda) ?>">
      </div>
    </form>

    <!-- TABLA -->
    <div class="card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Imagen</th>
              <th>Título</th>
              <th>Autor(es)</th>
              <th>Género(s)</th>
              <th>ISBN</th>
              <th>Año</th>
              <th>Editorial</th>
              <th class="text-center">Stock</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($resultado && $resultado->num_rows > 0):
            while ($row = $resultado->fetch_assoc()):
              $autorIds  = $row['autor_ids']  ? array_map('intval', explode(',', $row['autor_ids']))  : [];
              $generoIds = $row['genero_ids'] ? array_map('intval', explode(',', $row['genero_ids'])) : [];
          ?>
            <tr>
              <td>
                <img src="uploads/<?= htmlspecialchars($row['imagen'] ?? '') ?>"
                     class="img-libro-sm"
                     onerror="this.src='https://via.placeholder.com/35x48?text=📖'">
              </td>
              <td class="fw-semibold"><?= htmlspecialchars($row['titulo']) ?></td>
              <td><small><?= htmlspecialchars($row['autores'] ?? '—') ?></small></td>
              <td>
                <?php foreach (explode(', ', $row['generos'] ?? '') as $g): ?>
                  <?php if (trim($g)): ?>
                    <span class="badge rounded-pill" style="background:var(--brand-light);color:var(--brand);font-size:.72rem">
                      <?= htmlspecialchars(trim($g)) ?>
                    </span>
                  <?php endif; ?>
                <?php endforeach; ?>
              </td>
              <td><small class="text-muted"><?= htmlspecialchars($row['isbn'] ?? '—') ?></small></td>
              <td><?= $row['anio_publicacion'] ?? '—' ?></td>
              <td><?= htmlspecialchars($row['editorial'] ?? '—') ?></td>
              <td class="text-center">
                <span class="badge <?= ($row['stock'] > 0) ? 'bg-success' : 'bg-danger' ?>">
                  <?= $row['stock'] ?>
                </span>
              </td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1"
                  onclick="editarLibro(
                    <?= $row['id'] ?>,
                    '<?= addslashes($row['titulo']) ?>',
                    '<?= addslashes($row['isbn'] ?? '') ?>',
                    '<?= $row['anio_publicacion'] ?? '' ?>',
                    '<?= addslashes($row['editorial'] ?? '') ?>',
                    <?= $row['stock'] ?>,
                    <?= json_encode($autorIds) ?>,
                    <?= json_encode($generoIds) ?>
                  )"
                  data-bs-toggle="modal" data-bs-target="#modalEditar">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <a href="php/eliminar_libro.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('¿Eliminar este libro?')"
                   class="btn btn-sm btn-outline-danger">
                  <i class="fa-solid fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="9" class="text-center text-muted py-5">
              <i class="fa-solid fa-book-open fa-2x mb-2 d-block"></i>
              No se encontraron libros
            </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PAGINACIÓN -->
    <?php if ($totalPaginas > 1): ?>
    <nav class="mt-3">
      <ul class="pagination pagination-sm">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
          <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
            <a class="page-link" href="?pagina=<?= $i ?>&buscar=<?= urlencode($busqueda) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>

  </main>
</div>

<!-- ══════════════════════════════════════════
     MODAL AGREGAR
══════════════════════════════════════════ -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(90deg,#ff7a00,#ffae42)">
        <h5 class="modal-title text-white"><i class="fa-solid fa-plus me-2"></i>Nuevo libro</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="php/agregar_libro.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row g-3">

            <div class="col-md-8">
              <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
              <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Año de publicación</label>
              <input type="number" name="anio_publicacion" class="form-control"
                     min="1000" max="<?= date('Y') ?>" placeholder="Ej: 1984">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">ISBN</label>
              <input type="text" name="isbn" class="form-control" placeholder="978-XX-XXXX-XXX-X">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Editorial</label>
              <input type="text" name="editorial" class="form-control">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Stock</label>
              <input type="number" name="stock" class="form-control" min="0" value="1">
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Imagen</label>
              <input type="file" name="imagen" id="imagenInput" class="form-control" accept="image/*">
              <img id="preview" class="mt-2 w-100">
            </div>

            <!-- AUTORES -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Autores</label>
              <div class="border rounded p-2" style="max-height:130px;overflow-y:auto">
                <?php $todosAutores->data_seek(0); while ($a = $todosAutores->fetch_assoc()): ?>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="autores[]" value="<?= $a['id'] ?>" id="autor_<?= $a['id'] ?>">
                    <label class="form-check-label small" for="autor_<?= $a['id'] ?>">
                      <?= htmlspecialchars($a['nombre']) ?>
                    </label>
                  </div>
                <?php endwhile; ?>
              </div>
              <input type="text" name="nuevo_autor" class="form-control form-control-sm mt-2"
                     placeholder="+ Nuevo autor (opcional)">
            </div>

            <!-- GÉNEROS -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Géneros</label>
              <div class="border rounded p-2" style="max-height:130px;overflow-y:auto">
                <?php $todosGeneros->data_seek(0); while ($g = $todosGeneros->fetch_assoc()): ?>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="generos[]" value="<?= $g['id'] ?>" id="genero_<?= $g['id'] ?>">
                    <label class="form-check-label small" for="genero_<?= $g['id'] ?>">
                      <?= htmlspecialchars($g['nombre']) ?>
                    </label>
                  </div>
                <?php endwhile; ?>
              </div>
              <input type="text" name="nuevo_genero" class="form-control form-control-sm mt-2"
                     placeholder="+ Nuevo género (opcional)">
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand">Guardar libro</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL EDITAR
══════════════════════════════════════════ -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(90deg,#ff7a00,#ffae42)">
        <h5 class="modal-title text-white"><i class="fa-solid fa-pen me-2"></i>Editar libro</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="php/editar_libro.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <div class="row g-3">

            <div class="col-md-8">
              <label class="form-label fw-semibold">Título</label>
              <input type="text" name="titulo" id="edit_titulo" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Año de publicación</label>
              <input type="number" name="anio_publicacion" id="edit_anio" class="form-control"
                     min="1000" max="<?= date('Y') ?>">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">ISBN</label>
              <input type="text" name="isbn" id="edit_isbn" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Editorial</label>
              <input type="text" name="editorial" id="edit_editorial" class="form-control">
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Stock</label>
              <input type="number" name="stock" id="edit_stock" class="form-control" min="0">
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Nueva imagen</label>
              <input type="file" name="imagen" id="edit_imagen" class="form-control" accept="image/*">
              <img id="edit_preview" class="mt-2 w-100">
            </div>

            <!-- AUTORES EDITAR -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Autores</label>
              <div class="border rounded p-2" style="max-height:130px;overflow-y:auto">
                <?php $todosAutores->data_seek(0); while ($a = $todosAutores->fetch_assoc()): ?>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="autores_edit[]" value="<?= $a['id'] ?>" id="edit_autor_<?= $a['id'] ?>">
                    <label class="form-check-label small" for="edit_autor_<?= $a['id'] ?>">
                      <?= htmlspecialchars($a['nombre']) ?>
                    </label>
                  </div>
                <?php endwhile; ?>
              </div>
              <input type="text" name="nuevo_autor" id="edit_nuevo_autor"
                     class="form-control form-control-sm mt-2"
                     placeholder="+ Nuevo autor (opcional)">
            </div>

            <!-- GÉNEROS EDITAR -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Géneros</label>
              <div class="border rounded p-2" style="max-height:130px;overflow-y:auto">
                <?php $todosGeneros->data_seek(0); while ($g = $todosGeneros->fetch_assoc()): ?>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="generos_edit[]" value="<?= $g['id'] ?>" id="edit_genero_<?= $g['id'] ?>">
                    <label class="form-check-label small" for="edit_genero_<?= $g['id'] ?>">
                      <?= htmlspecialchars($g['nombre']) ?>
                    </label>
                  </div>
                <?php endwhile; ?>
              </div>
              <input type="text" name="nuevo_genero" id="edit_nuevo_genero"
                     class="form-control form-control-sm mt-2"
                     placeholder="+ Nuevo género (opcional)">
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand">Actualizar libro</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
<script src="js/libros.js"></script>
</body>
</html>
