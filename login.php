<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BiblioGest — Login</title>

  <!-- Imports Bootstrap, Font Awesome y los estilos CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> 
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/login.css">
  <link rel="stylesheet" href="css/app.css">
</head>
<body>

<!-- Contenedor principal de la página -->
<div class="container-fluid p-0">
  <div class="row g-0 min-vh-100">

    <!-- Imagen de la Izquierda -->
    <div class="col-md-6 left-panel d-flex align-items-center justify-content-center">
      <img src="img/libreria3.avif" alt="Biblioteca" class="w-100 float-img">
    </div>

    <!-- Parte de la derecha con el formulario -->
    <div class="col-md-6 d-flex align-items-center justify-content-center bg-white">
      <div class="login-card bg-white p-4 p-md-5 w-100 mx-3">

        <!-- Nombre y logo del sistema -->
        <div class="text-center mb-4">
          <h2 class="fw-bold" style="color:var(--brand)">
            <i class="fa-solid fa-book me-2"></i>Biblioteca
          </h2>
          <p class="text-muted small mb-0">Sistema de Control de Biblioteca</p>
        </div>

        <!-- Mensaje de bienvenida -->
        <h5 class="fw-bold text-center">¡Bienvenido!</h5>
        <p class="text-muted text-center small mb-3">Inicia sesión para continuar</p>

        <!-- Verifica si ocurrió un error de autenticación -->
        <?php if (isset($_GET['error'])): ?>
          <!-- Mensaje de error de inicio de sesión -->
          <div class="alert alert-danger py-2 text-center small">
            Usuario o contraseña incorrectos
          </div>
        <?php endif; ?>


        <!-- Formulario de autenticación -->
        <form action="php/login.php" method="POST">

        <!-- Campo para ingresar el usuario -->
          <div class="input-group mb-3">
            <span class="input-group-text bg-light border-end-0">
              <i class="fa-solid fa-user text-muted"></i>
            </span>
            <input type="text" name="usuario" class="form-control border-start-0 ps-0"
                   placeholder="Usuario" required>
          </div>

          <!-- Campo para ingresar la contraseña -->
          <div class="input-group mb-3">
            <span class="input-group-text bg-light border-end-0">
              <i class="fa-solid fa-lock text-muted"></i>
            </span>
            <input type="password" name="password" class="form-control border-start-0 ps-0"
                   placeholder="Contraseña" required>
          </div>

          <!-- Botón para iniciar sesión -->
          <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold">
            Iniciar sesión
          </button>

        </form>
      </div>
    </div>

  </div>
</div>

<!-- Libreria JavaScript de Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
