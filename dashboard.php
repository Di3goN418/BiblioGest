<?php
// inicia una sesion para mantener el login 
session_start();

// incluye la conexion a la BD y la funcionpara calcular multas
include("php/conexion.php");
include("php/calcular_multas.php");

// verifica si no hay una sesion inciada 
if (!isset($_SESSION['usuario'])) {
    // redirige el Login si no hay sesion activa
    header("Location: login.php");
    // finaliza la ejecucion del script
    exit();
}

$totalUsuarios  = $conexion->query("SELECT COUNT(*) as t FROM usuarios")->fetch_assoc()['t']; // obtiene el total de usuarios registrados
$totalLibros    = $conexion->query("SELECT COUNT(*) as t FROM libros")->fetch_assoc()['t']; // obtiene el total de libros registrados
$totalPrestamos = $conexion->query("SELECT COUNT(*) as t FROM prestamos WHERE estado='Activo'")->fetch_assoc()['t']; // obtiene el total de prestamos activos
$totalMultas    = $conexion->query("SELECT COUNT(*) as t FROM multas WHERE estado='Pendiente'")->fetch_assoc()['t']; // obtiene el total de multas pendientes

// define la pagina activa 
$paginaActiva = 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard — BiblioGest</title>
  <!-- importa Bootstrap y Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- conecta con los estilos especificos -->
  <link rel="stylesheet" href="css/app.css">
</head>
<body class="bg-light">

<!-- barra de navegación para dispositivos moviles (prueba)-->
<nav class="navbar d-md-none mobile-nav px-3 py-2">
  <button class="btn btn-link text-white p-0" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
    <i class="fa-solid fa-bars fa-lg"></i>
  </button>
  <span class="text-white fw-bold"><i class="fa-solid fa-book me-2"></i>BiblioGest</span>
</nav>

<div class="d-flex">
  <!-- coloca/incluye el menu lateral -->
  <?php include "php/sidebar.php"; ?>

  <!-- MAIN -->
  <!-- contenido principal -->
  <main class="flex-grow-1 p-4">

  <!-- mensaje de bienvenida -->
    <h4 class="fw-bold mb-1">Hola, Bienvenido a BiblioGest  </h4>
    <p class="text-muted mb-4">Resumen de la biblioteca.</p>

    <!-- CARDS -->
    <div class="row g-3 mb-4">

      <!-- CARDS con las estadisticas de usuarios registrados -->
      <div class="col-6 col-md-3">
        <div class="card stat-card border-0 shadow-sm h-100" style="border-left-color:#4e73df!important">
          <div class="card-body">
            <p class="text-muted small mb-1">Usuarios</p>
            <h2 class="fw-bold mb-0"><?= $totalUsuarios ?></h2>
            <small class="text-muted">Registrados</small>
          </div>
        </div>
      </div>

      <!-- CARDS con las estadisticas de libros en catalogo -->
      <div class="col-6 col-md-3">
        <div class="card stat-card border-0 shadow-sm h-100" style="border-left-color:#1cc88a!important">
          <div class="card-body">
            <p class="text-muted small mb-1">Libros</p>
            <h2 class="fw-bold mb-0"><?= $totalLibros ?></h2>
            <small class="text-muted">En catálogo</small>
          </div>
        </div>
      </div>

      <!-- CARDS con las estadisticas de los prestamos activos -->
      <div class="col-6 col-md-3">
        <div class="card stat-card border-0 shadow-sm h-100" style="border-left-color:#f6c23e!important">
          <div class="card-body">
            <p class="text-muted small mb-1">Préstamos</p>
            <h2 class="fw-bold mb-0"><?= $totalPrestamos ?></h2>
            <small class="text-muted">Activos</small>
          </div>
        </div>
      </div>

      <!-- CARDS con las estadisticas de multas pendientes -->
      <div class="col-6 col-md-3">
        <div class="card stat-card border-0 shadow-sm h-100" style="border-left-color:#e74a3b!important">
          <div class="card-body">
            <p class="text-muted small mb-1">Multas</p>
            <h2 class="fw-bold mb-0"><?= $totalMultas ?></h2>
            <small class="text-muted">Pendientes</small>
          </div>
        </div>
      </div>

    </div>
  <!-- /cards -->


    <!-- GRID INFERIOR -->
    <div class="row g-3">

      <!-- ACTIVIDAD RECIENTE -->
      <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Actividad reciente</h6>

          <!-- consulta de los ultimos 5 prestamos registrados (actividad reciente)-->
            <?php
            $act = $conexion->query("
              SELECT p.*, u.nombre AS usuario, l.titulo AS libro, l.imagen
              FROM prestamos p
              JOIN usuarios u ON p.id_usuario = u.id
              JOIN libros   l ON p.id_libro   = l.id
              ORDER BY p.id DESC LIMIT 5
            ");

            // Recorre y muestra cada prestamo registrado 
            if ($act && $act->num_rows > 0):
              while ($a = $act->fetch_assoc()):
                $fecha = $a['fecha_prestamo'];
                $texto = 'Préstamo';
                $badge = 'bg-primary';
                // Verifica si el libro fue entregado
                if (!empty($a['fecha_entrega'])) {
                    $fecha = $a['fecha_entrega'];
                    $texto = 'Devuelto';
                    $badge = 'bg-success';
                }
            ?>
              <div class="d-flex align-items-center gap-3 mb-3">
                <!-- Verifica si el libro tiene imagen registrada, sino pone un icono generico -->
                <?php if (!empty($a['imagen']) && file_exists("uploads/".$a['imagen'])): ?>
                  <img src="uploads/<?= $a['imagen'] ?>" class="img-libro-sm">
                <?php else: ?>
                  <div class="img-libro-sm bg-secondary rounded d-flex align-items-center justify-content-center">
                    <i class="fa-solid fa-book text-white small"></i>
                  </div>
                <?php endif; ?>
                <div class="flex-grow-1">
                  <!-- Protege y muestra texto seguro -->
                  <p class="mb-0 small fw-semibold"><?= htmlspecialchars($a['usuario']) ?></p>
                  <p class="mb-0 text-muted" style="font-size:.8rem"><?= htmlspecialchars($a['libro']) ?></p>
                </div>
                <div class="text-end">
                  <span class="badge <?= $badge ?> mb-1"><?= $texto ?></span>
                  <p class="mb-0 text-muted" style="font-size:.75rem"><?= date("d M", strtotime($fecha)) ?></p>
                </div>
              </div>
            <?php endwhile; else: ?>
              <p class="text-muted small">No hay actividad reciente.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- PRÓXIMOS A VENCER -->
      <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="fw-bold mb-3">Préstamos próximos a vencer</h6>
            <!-- Consulta los prestamos proximos a vencer -->
            <?php
            $prox = $conexion->query("
              SELECT p.*, u.nombre AS usuario, l.titulo AS libro, l.imagen
              FROM prestamos p
              JOIN usuarios u ON p.id_usuario = u.id
              JOIN libros   l ON p.id_libro   = l.id
              WHERE p.estado='Activo'
              ORDER BY p.fecha_devolucion ASC
            ");
            if ($prox && $prox->num_rows > 0):
              while ($p = $prox->fetch_assoc()):
                // Calcula los dias de retraso del prestamo
                $dias = (int)((strtotime(date("Y-m-d")) - strtotime($p['fecha_devolucion'])) / 86400);
                // Define el color segun el estado del prestamo
                $dias = max(0, $dias);
                if ($dias === 0)      { $color = 'success'; }
                elseif ($dias <= 2)   { $color = 'warning'; }
                else                  { $color = 'danger';  }
            ?>
              <div class="d-flex align-items-center gap-3 mb-3">
                <?php if (!empty($p['imagen']) && file_exists("uploads/".$p['imagen'])): ?>
                  <img src="uploads/<?= $p['imagen'] ?>" class="img-libro-sm">
                <?php else: ?>
                  <div class="img-libro-sm bg-secondary rounded d-flex align-items-center justify-content-center">
                    <i class="fa-solid fa-book text-white small"></i>
                  </div>
                <?php endif; ?>
                <span class="rounded-circle bg-<?= $color ?>" style="width:10px;height:10px;flex-shrink:0"></span>
                <div class="flex-grow-1">
                  <p class="mb-0 small fw-semibold"><?= htmlspecialchars($p['libro']) ?></p>
                  <p class="mb-0 text-muted" style="font-size:.8rem"><?= htmlspecialchars($p['usuario']) ?></p>
                </div>
                <div class="text-end">
                  <p class="mb-0 small"><?= date("d M", strtotime($p['fecha_devolucion'])) ?></p>
                  <?php if ($dias > 0): ?>
                    <span class="text-danger fw-bold" style="font-size:.8rem">+<?= $dias ?> días</span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endwhile; else: ?>
              <p class="text-muted small">No hay préstamos activos.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  <!-- /grid -->
  </main>
</div>

<!-- Libreria JavaScript de Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Carga el JS necesaio para esta pagina -->
<script src="js/main.js"></script>
</body>
</html>
