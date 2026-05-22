// libros.js — lógica de la vista de libros

document.addEventListener('DOMContentLoaded', function () {

  // ── Preview imagen al AGREGAR ──────────────
  const imagenInput = document.getElementById('imagenInput');
  const preview     = document.getElementById('preview');

  if (imagenInput && preview) {
    imagenInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = e => {
          preview.src           = e.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // ── Preview imagen al EDITAR ───────────────
  const editImagen  = document.getElementById('edit_imagen');
  const editPreview = document.getElementById('edit_preview');

  if (editImagen && editPreview) {
    editImagen.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = e => {
          editPreview.src           = e.target.result;
          editPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  }

});

// ── Rellenar modal editar con datos del libro ──
// autorIds y generoIds llegan como arrays PHP → JSON
function editarLibro(id, titulo, isbn, anio, editorial, stock, autorIds, generoIds) {

  document.getElementById('edit_id').value        = id;
  document.getElementById('edit_titulo').value    = titulo;
  document.getElementById('edit_isbn').value      = isbn;
  document.getElementById('edit_anio').value      = anio;
  document.getElementById('edit_editorial').value = editorial;
  document.getElementById('edit_stock').value     = stock;

  // Marcar checkboxes de autores
  document.querySelectorAll('input[name="autores_edit[]"]').forEach(cb => {
    cb.checked = autorIds.includes(parseInt(cb.value));
  });

  // Marcar checkboxes de géneros
  document.querySelectorAll('input[name="generos_edit[]"]').forEach(cb => {
    cb.checked = generoIds.includes(parseInt(cb.value));
  });

  // Limpiar campo "nuevo autor / género"
  const na = document.getElementById('edit_nuevo_autor');
  const ng = document.getElementById('edit_nuevo_genero');
  if (na) na.value = '';
  if (ng) ng.value = '';
}
