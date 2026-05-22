<?php
session_start();
include("php/conexion.php");

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

$limite   = 8;
$pagina   = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio   = ($pagina - 1) * $limite;
$busqueda = isset($_GET['buscar']) ? $conexion->real_escape_string($_GET['buscar']) : "";

$where = $busqueda
    ? "WHERE nombre LIKE '%$busqueda%' OR correo LIKE '%$busqueda%' OR telefono LIKE '%$busqueda%'"
    : "";

$resultado = $conexion->query("SELECT * FROM usuarios $where ORDER BY nombre LIMIT $inicio, $limite");
$totalQ    = $conexion->query("SELECT COUNT(*) as t FROM usuarios $where");
$total     = $totalQ->fetch_assoc()['t'];
$totalPags = ceil($total / $limite);

$paginaActiva = 'usuarios';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Usuarios — BiblioGest</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/app.css">
</head>
<body class="bg-light">

<nav class="navbar d-md-none mobile-nav px-3 py-2">
  <button class="btn btn-link text-white p-0" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
    <i class="fa-solid fa-bars fa-lg"></i>
  </button>
  <span class="text-white fw-bold"><i class="fa-solid fa-book me-2"></i>BiblioGest</span>
</nav>

<div class="d-flex">
  <?php include "php/sidebar.php"; ?>

  <main class="flex-grow-1 p-4">

    <?php if (isset($_GET['mensaje'])): ?>
      <?php $msgs = [
        'agregado'        => ['success', 'Usuario agregado correctamente'],
        'editado'         => ['success', 'Usuario actualizado'],
        'eliminado'       => ['warning', 'Usuario eliminado'],
        'error_prestamo'  => ['danger',  'No se puede eliminar: tiene préstamos activos'],
      ]; ?>
      <?php [$tipo, $texto] = $msgs[$_GET['mensaje']] ?? ['info','Operación completada']; ?>
      <div class="alert alert-<?= $tipo ?> alert-dismissible alert-autohide fade show">
        <?= $texto ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">
        <i class="fa-solid fa-users me-2" style="color:var(--brand)"></i>Usuarios
      </h4>
      <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalAgregar">
        <i class="fa-solid fa-plus me-1"></i> Nuevo usuario
      </button>
    </div>

    <form method="GET" class="mb-3">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
        <input type="text" name="buscar" class="form-control"
               placeholder="Buscar por nombre, correo o teléfono..."
               value="<?= htmlspecialchars($busqueda) ?>">
      </div>
    </form>

    <div class="card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th></th>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Teléfono</th>
              <th>Préstamo activo</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($resultado && $resultado->num_rows > 0):
            while ($u = $resultado->fetch_assoc()):
              $pq = $conexion->prepare("SELECT id FROM prestamos WHERE id_usuario=? AND estado='Activo' LIMIT 1");
              $pq->bind_param("i", $u['id']);
              $pq->execute();
              $tieneActivo = $pq->get_result()->num_rows > 0;
          ?>
            <tr>
              <td><div class="avatar"><?= strtoupper(substr($u['nombre'], 0, 1)) ?></div></td>
              <td class="fw-semibold"><?= htmlspecialchars($u['nombre']) ?></td>
              <td><?= htmlspecialchars($u['correo'] ?? '—') ?></td>
              <td><?= htmlspecialchars($u['telefono'] ?? '—') ?></td>
              <td>
                <span class="badge <?= $tieneActivo ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                  <?= $tieneActivo ? 'Sí' : 'No' ?>
                </span>
              </td>
              <td>
                <button class="btn btn-sm btn-outline-primary me-1"
                  onclick="editarUsuario(<?= $u['id'] ?>,'<?= addslashes($u['nombre']) ?>','<?= addslashes($u['correo']??'') ?>','<?= addslashes($u['telefono']??'') ?>')"
                  data-bs-toggle="modal" data-bs-target="#modalEditar">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <a href="php/eliminar_usuario.php?id=<?= $u['id'] ?>"
                   onclick="return confirm('¿Eliminar este usuario?')"
                   class="btn btn-sm btn-outline-danger">
                  <i class="fa-solid fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-center text-muted py-5">
              <i class="fa-solid fa-users-slash fa-2x mb-2 d-block"></i>
              No se encontraron usuarios
            </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($totalPags > 1): ?>
    <nav class="mt-3">
      <ul class="pagination pagination-sm">
        <?php for ($i = 1; $i <= $totalPags; $i++): ?>
          <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
            <a class="page-link" href="?pagina=<?= $i ?>&buscar=<?= urlencode($busqueda) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>

  </main>
</div>

<!-- MODAL AGREGAR -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(90deg,#ff7a00,#ffae42)">
        <h5 class="modal-title text-white"><i class="fa-solid fa-plus me-2"></i>Nuevo usuario</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="php/agregar_usuario.php" method="POST">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
              <input type="text" name="nombre" class="form-control" placeholder="Ej: Ana Torres" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Correo</label>
              <input type="email" name="correo" class="form-control" placeholder="correo@ejemplo.com">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Teléfono</label>
              <input type="text" name="telefono" class="form-control" placeholder="Ej: 5512345678">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(90deg,#ff7a00,#ffae42)">
        <h5 class="modal-title text-white"><i class="fa-solid fa-pen me-2"></i>Editar usuario</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="php/editar_usuario.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Nombre completo</label>
              <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Correo</label>
              <input type="email" name="correo" id="edit_correo" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Teléfono</label>
              <input type="text" name="telefono" id="edit_telefono" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
<script>
function editarUsuario(id, nombre, correo, telefono) {
  document.getElementById('edit_id').value       = id;
  document.getElementById('edit_nombre').value   = nombre;
  document.getElementById('edit_correo').value   = correo;
  document.getElementById('edit_telefono').value = telefono;
}
</script>
</body>
</html>
