// prestamos.js — lógica de la vista de préstamos

document.addEventListener('DOMContentLoaded', function () {

  // ── Fecha mínima de devolución = mañana ───
  const fechaDev = document.getElementById('fechaDev');
  if (fechaDev) {
    const manana = new Date();
    manana.setDate(manana.getDate() + 1);
    const iso = manana.toISOString().split('T')[0];
    fechaDev.min   = iso;
    fechaDev.value = iso;
  }

});

// ── Mostrar info del libro al seleccionarlo en el modal ──
function mostrarInfoLibro(sel) {
  const opt  = sel.options[sel.selectedIndex];
  const info = document.getElementById('libroInfo');
  if (!opt || !opt.value) {
    info.classList.add('d-none');
    return;
  }
  document.getElementById('libroNombre').textContent = opt.dataset.titulo;
  document.getElementById('libroStock').textContent  = opt.dataset.stock;
  info.classList.remove('d-none');
}
