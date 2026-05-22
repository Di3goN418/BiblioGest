<?php
session_start();
include("php/conexion.php");

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

$limite   = 8;
$pagina   = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio   = ($pagina - 1) * $limite;
$busqueda = isset($_GET['buscar']) ? $conexion->real_escape_string($_GET['buscar']) : "";

$where = $busqueda
    ? "WHERE u.nombre LIKE '%$busqueda%' OR l.titulo LIKE '%$busqueda%'"
    : "";

$resultado = $conexion->query("
    SELECT p.*, u.nombre AS usuario_nombre, l.titulo AS libro_titulo, l.imagen AS libro_imagen
    FROM prestamos p
    JOIN usuarios u ON p.id_usuario = u.id
    JOIN libros   l ON p.id_libro   = l.id
    $where ORDER BY p.id DESC LIMIT $inicio, $limite
");

$totalQ       = $conexion->query("SELECT COUNT(*) as t FROM prestamos p JOIN usuarios u ON p.id_usuario=u.id JOIN libros l ON p.id_libro=l.id $where");
$total        = $totalQ->fetch_assoc()['t'];
$totalPaginas = ceil($total / $limite);

$activos   = $conexion->query("SELECT COUNT(*) as t FROM prestamos WHERE estado='Activo'")->fetch_assoc()['t'];
$devueltos = $conexion->query("SELECT COUNT(*) as t FROM prestamos WHERE estado='Devuelto'")->fetch_assoc()['t'];
$vencidos  = $conexion->query("SELECT COUNT(*) as t FROM prestamos WHERE estado='Activo' AND fecha_devolucion < CURDATE()")->fetch_assoc()['t'];

$usuarios = $conexion->query("SELECT id, nombre FROM usuarios ORDER BY nombre");
$libros   = $conexion->query("SELECT id, titulo, stock FROM libros WHERE stock > 0 ORDER BY titulo");

$paginaActiva = 'prestamos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Préstamos — BiblioGest</title>
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
        'creado'         => ['success', '✅ Préstamo registrado correctamente'],
        'devuelto'       => ['success', '📚 Libro devuelto exitosamente'],
        'eliminado'      => ['warning', '🗑️ Préstamo eliminado'],
        'error_stock'    => ['danger',  '❌ El libro no tiene stock disponible'],
        'error_prestamo' => ['danger',  '❌ El usuario ya tiene un préstamo activo'],
      ]; ?>
      <?php [$tipo, $texto] = $msgs[$_GET['mensaje']] ?? ['info','Operación completada']; ?>
      <div class="alert alert-<?= $tipo ?> alert-dismissible alert-autohide fade show">
        <?= $texto ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">
        <i class="fa-solid fa-handshake me-2" style="color:var(--brand)"></i>Préstamos
      </h4>
      <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#modalNuevo">
        <i class="fa-solid fa-plus me-1"></i> Nuevo préstamo
      </button>
    </div>

    <!-- STATS -->
    <div class="row g-3 mb-4">
      <div class="col-4">
        <div class="card border-0 shadow-sm stat-card" style="border-left-color:#4e73df!important">
          <div class="card-body py-2">
            <p class="text-muted small mb-0">Activos</p>
            <h4 class="fw-bold mb-0"><?= $activos ?></h4>
          </div>
        </div>
      </div>
      <div class="col-4">
        <div class="card border-0 shadow-sm stat-card" style="border-left-color:#1cc88a!important">
          <div class="card-body py-2">
            <p class="text-muted small mb-0">Devueltos</p>
            <h4 class="fw-bold mb-0"><?= $devueltos ?></h4>
          </div>
        </div>
      </div>
      <div class="col-4">
        <div class="card border-0 shadow-sm stat-card" style="border-left-color:#e74a3b!important">
          <div class="card-body py-2">
            <p class="text-muted small mb-0">Vencidos</p>
            <h4 class="fw-bold mb-0"><?= $vencidos ?></h4>
          </div>
        </div>
      </div>
    </div>

    <form method="GET" class="mb-3">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
        <input type="text" name="buscar" class="form-control"
               placeholder="Buscar por usuario o libro..."
               value="<?= htmlspecialchars($busqueda) ?>">
      </div>
    </form>

    <div class="card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Libro</th>
              <th>Usuario</th>
              <th>Préstamo</th>
              <th>Límite</th>
              <th>Retraso</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($resultado && $resultado->num_rows > 0):
            while ($row = $resultado->fetch_assoc()):
              $hoy  = date("Y-m-d");
              $dias = 0;
              if ($row['estado'] == 'Activo' && $hoy > $row['fecha_devolucion'])
                  $dias = (int)((strtotime($hoy) - strtotime($row['fecha_devolucion'])) / 86400);
              if ($row['estado'] == 'Devuelto')                   { $badge = 'bg-success'; $label = 'Devuelto'; }
              elseif ($row['estado'] == 'Activo' && $dias > 0)    { $badge = 'bg-danger';  $label = 'Vencido';  }
              else                                                 { $badge = 'bg-primary'; $label = 'Activo';   }
          ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="uploads/<?= htmlspecialchars($row['libro_imagen'] ?? '') ?>"
                       class="img-libro-sm"
                       onerror="this.src='https://via.placeholder.com/35x48?text=📖'">
                  <span class="small fw-semibold"><?= htmlspecialchars($row['libro_titulo']) ?></span>
                </div>
              </td>
              <td><?= htmlspecialchars($row['usuario_nombre']) ?></td>
              <td><small><?= date("d/m/Y", strtotime($row['fecha_prestamo'])) ?></small></td>
              <td><small><?= date("d/m/Y", strtotime($row['fecha_devolucion'])) ?></small></td>
              <td>
                <?php if ($dias > 0): ?>
                  <span class="text-danger fw-bold small">+<?= $dias ?> días</span>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
              <td>
                <?php if ($row['estado'] == 'Activo'): ?>
                  <a href="php/devolver_libro.php?id=<?= $row['id'] ?>"
                     onclick="return confirm('¿Marcar como devuelto?')"
                     class="btn btn-sm btn-outline-success me-1" title="Devolver">
                    <i class="fa-solid fa-rotate-left"></i>
                  </a>
                <?php endif; ?>
                <a href="php/eliminar_prestamo.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('¿Eliminar este préstamo?')"
                   class="btn btn-sm btn-outline-danger" title="Eliminar">
                  <i class="fa-solid fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-center text-muted py-5">
              <i class="fa-solid fa-handshake-slash fa-2x mb-2 d-block"></i>
              No se encontraron préstamos
            </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

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

<!-- MODAL NUEVO PRÉSTAMO -->
<div class="modal fade" id="modalNuevo" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(90deg,#ff7a00,#ffae42)">
        <h5 class="modal-title text-white"><i class="fa-solid fa-handshake me-2"></i>Nuevo préstamo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="php/crear_prestamo.php" method="POST">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Usuario <span class="text-danger">*</span></label>
              <select name="id_usuario" class="form-select" required>
                <option value="">— Seleccionar usuario —</option>
                <?php while ($u = $usuarios->fetch_assoc()): ?>
                  <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Libro <span class="text-danger">*</span></label>
              <select name="id_libro" id="selectLibro" class="form-select" required onchange="mostrarInfoLibro(this)">
                <option value="">— Seleccionar libro —</option>
                <?php while ($l = $libros->fetch_assoc()): ?>
                  <option value="<?= $l['id'] ?>"
                          data-stock="<?= $l['stock'] ?>"
                          data-titulo="<?= htmlspecialchars($l['titulo']) ?>">
                    <?= htmlspecialchars($l['titulo']) ?> (Stock: <?= $l['stock'] ?>)
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-12 d-none" id="libroInfo">
              <div class="alert alert-info py-2 mb-0 small">
                <strong id="libroNombre"></strong> — Disponibles: <strong id="libroStock"></strong>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Fecha de préstamo</label>
              <input type="date" name="fecha_prestamo" class="form-control"
                     value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Fecha límite devolución</label>
              <input type="date" name="fecha_devolucion" id="fechaDev" class="form-control" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand">Registrar préstamo</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
<script src="js/prestamos.js"></script>
</body>
</html>
