<?php
// php/sidebar.php
// Requiere: $paginaActiva (string) definida antes del include
// Ejemplo:  $paginaActiva = 'libros';

$nav = [
    ['dashboard.php',  'house',        'Inicio'],
    ['usuarios.php',   'users',        'Usuarios'],
    ['libros.php',     'book-open',    'Libros'],
    ['prestamos.php',  'handshake',    'Préstamos'],
    ['multas.php',     'dollar-sign',  'Multas'],
];

function navItems(array $nav, string $activa): string {
    $html = '<ul class="nav flex-column gap-1 flex-grow-1">';
    foreach ($nav as [$href, $icon, $label]) {
        $key    = explode('.', $href)[0];
        $active = ($activa === $key) ? ' active' : '';
        $html  .= "<li class='nav-item'>
            <a href='$href' class='nav-link$active'>
                <i class='fa-solid fa-$icon me-2'></i>$label
            </a></li>";
    }
    $html .= '</ul>';
    return $html;
}

$activa = $paginaActiva ?? '';
?>

<!-- ── OFFCANVAS MOBILE ───────────────────── -->
<div class="offcanvas offcanvas-start sidebar d-md-none"
     id="sidebarOffcanvas" tabindex="-1">
  <div class="offcanvas-header pb-0">
    <span class="text-white fw-bold fs-5">
      <i class="fa-solid fa-book me-2"></i>BiblioGest
    </span>
    <button class="btn-close btn-close-white"
            data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body px-2 d-flex flex-column">
    <?= navItems($nav, $activa) ?>
    <a href="php/logout.php" class="nav-link mt-3">
      <i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar sesión
    </a>
  </div>
</div>

<!-- ── SIDEBAR DESKTOP ───────────────────── -->
<nav class="sidebar d-none d-md-flex flex-column p-3">
  <a href="dashboard.php" class="brand mb-4">
    <i class="fa-solid fa-book me-2"></i>BiblioGest
  </a>
  <?= navItems($nav, $activa) ?>
  <a href="php/logout.php" class="nav-link mt-3">
    <i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar sesión
  </a>
</nav>
