<?php
// inicia una sesion para mantener el login e incluye la conexion 
session_start();
include("php/conexion.php");

// verifica si no hay una sesion inciada 
if (!isset($_SESSION['usuario'])) { 
    // redirige el Login si no hay sesion activa
  header("Location: login.php"); 
    // finaliza la ejecucion del script
  exit(); }

// Consulta la información completa de las multas
// Renombra columnas para facilitar su uso
// Relaciona las multas con los prestamos
// Relaciona los prestamos con los usuarios
// Ordena las multas de mayor a menor monto
$resultado = $conexion->query("
    SELECT m.*, p.fecha_devolucion, p.fecha_entrega,
           u.nombre AS usuario, u.telefono,
           l.titulo AS libro
    FROM multas m
    JOIN prestamos p ON m.id_prestamo = p.id
    JOIN usuarios  u ON p.id_usuario  = u.id
    JOIN libros    l ON p.id_libro    = l.id
    ORDER BY m.estado ASC, m.monto DESC
");

$totalPendiente = $conexion->query("SELECT COALESCE(SUM(monto),0) as t FROM multas WHERE estado='Pendiente'")->fetch_assoc()['t']; // Calcula el monto total pendiente de pago
$countPendiente = $conexion->query("SELECT COUNT(*) as t FROM multas WHERE estado='Pendiente'")->fetch_assoc()['t']; // Cuenta la cantidad de multas pendientes
$countPagada    = $conexion->query("SELECT COUNT(*) as t FROM multas WHERE estado='Pagada'")->fetch_assoc()['t']; // Cuenta la cantidad de multas pagadas

// Define la pagina actual
$paginaActiva = 'multas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Multas — BiblioGest</title>
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

  <!-- Contenedor principal del modulo de multas -->
  <main class="flex-grow-1 p-4">

    <?php 
    // Verifica si existe un mensaje del sistema
    if (isset($_GET['mensaje'])): ?>
    <!-- Alerta de operación exitosa -->
      <div class="alert alert-success alert-dismissible alert-autohide fade show">
        ✅ Multa marcada como pagada
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <h4 class="fw-bold mb-4">
      <i class="fa-solid fa-dollar-sign me-2" style="color:var(--brand)"></i>Multas por retraso
    </h4>

    <!-- STATS -->
    <!-- Tarjetas con estadisticas de multas -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card" style="border-left-color:#e74a3b!important">
          <div class="card-body">
            <!-- Muestra la cantidad de multas pendientes -->
            <p class="text-muted small mb-1">Pendientes</p>
            <h3 class="fw-bold mb-0"><?= $countPendiente ?></h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card" style="border-left-color:#f6c23e!important">
          <div class="card-body">
            <!-- Muestra la cantidad de multas pendientes y la formatea -->
            <p class="text-muted small mb-1">Total por cobrar</p>
            <h3 class="fw-bold mb-0">$<?= number_format($totalPendiente, 2) ?></h3>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card" style="border-left-color:#1cc88a!important">
          <div class="card-body">
            <!-- Muestra la cantidad de multas pagadas -->
            <p class="text-muted small mb-1">Pagadas</p>
            <h3 class="fw-bold mb-0"><?= $countPagada ?></h3>
          </div>
        </div>
      </div>
    </div>

    <!-- TABLA -->
    <!-- Tabla principal de multas -->
    <div class="card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Usuario</th>
              <th>Teléfono</th>
              <th>Libro</th>
              <th>Fecha límite</th>
              <th class="text-center">Días retraso</th>
              <th>Monto</th>
              <th>Estado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
          <?php 
          // Verifica si existen multas para mostrar
          if ($resultado && $resultado->num_rows > 0):
            // Recorre los registros de multas
            while ($row = $resultado->fetch_assoc()):
          ?>
            <tr>
              <td class="fw-semibold">
                  <?= // Muestra texto de forma segura 
                  htmlspecialchars($row['usuario'])  ?></td>
              <td><small class="text-muted"><?= htmlspecialchars($row['telefono'] ?? '—') ?></small></td>
              <td><?= htmlspecialchars($row['libro']) ?></td>
              <td><small><?= 
                        // Formatea la fecha de devolución
                        date("d/m/Y", strtotime($row['fecha_devolucion'])) ?></small></td>
              <td class="text-center">
                <span class="badge bg-danger"><?= 
                                              // Muestra el tiempo de retraso
                                              $row['dias'] ?> días</span>
              </td>
              <td>
                <span class="fw-bold text-success">$<?= number_format($row['monto'], 2) ?></span>
              </td>
              <td>
              <!-- Verifica y muestra el estado de la multa -->
                <?php if ($row['estado'] == 'Pendiente'): ?>
                  <span class="badge bg-warning text-dark">Pendiente</span>
                <?php else: ?>
                  <span class="badge bg-success">Pagada</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($row['estado'] == 'Pendiente'): ?>
                  <!-- Enlace para registrar el pago de la multa -->
                  <!-- Solicita confirmación antes de realizar la acción -->
                  <a href="php/pagar_multa.php?id=<?= $row['id'] ?>"
                    onclick="return confirm('¿Marcar esta multa como pagada?')"
                    class="btn btn-sm btn-success">
                    <i class="fa-solid fa-check me-1"></i>Cobrada
                  </a>
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="8" class="text-center text-muted py-5">
              <i class="fa-solid fa-circle-check fa-2x mb-2 d-block text-success"></i>
              No hay multas registradas
            </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<!-- Libreria JavaScript de Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Archivo JS principal -->
<script src="js/main.js"></script>
</body>
</html>
