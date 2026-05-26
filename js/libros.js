// Lógica de la vista de libros

document.addEventListener('DOMContentLoaded', function () {

  // Preview imagen al AGREGAR 
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

  // Preview imagen al EDITAR 
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

  // Validar que al menos un autor y un género estén seleccionados
  // Formulario agregar
  const formAgregar = document.querySelector('#modalAgregar form');
  if (formAgregar) {
    formAgregar.addEventListener('submit', function (e) {
      const autores = document.querySelectorAll('#modalAgregar input[name="autores[]"]:checked');
      const generos = document.querySelectorAll('#modalAgregar input[name="generos[]"]:checked');
      const nuevoAutor = document.querySelector('#modalAgregar input[name="nuevo_autor"]').value.trim();
      const nuevoGenero = document.querySelector('#modalAgregar input[name="nuevo_genero"]').value.trim();

      if (autores.length === 0 && !nuevoAutor) {
        e.preventDefault();
        alert('Selecciona o escribe al menos un autor.');
        return;
      }
      if (generos.length === 0 && !nuevoGenero) {
        e.preventDefault();
        alert('Selecciona o escribe al menos un género.');
      }
    });
  }

  // Formulario editar
  const formEditar = document.querySelector('#modalEditar form');
  if (formEditar) {
    formEditar.addEventListener('submit', function (e) {
      const autores = document.querySelectorAll('#modalEditar input[name="autores_edit[]"]:checked');
      const generos = document.querySelectorAll('#modalEditar input[name="generos_edit[]"]:checked');
      const nuevoAutor = document.getElementById('edit_nuevo_autor').value.trim();
      const nuevoGenero = document.getElementById('edit_nuevo_genero').value.trim();

      if (autores.length === 0 && !nuevoAutor) {
        e.preventDefault();
        alert('Selecciona o escribe al menos un autor.');
        return;
      }
      if (generos.length === 0 && !nuevoGenero) {
        e.preventDefault();
        alert('Selecciona o escribe al menos un género.');
      }
    });
  }

});

// Rellenar modal editar con datos del libro 
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


