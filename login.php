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

<div class="container-fluid p-0">
  <div class="row g-0 min-vh-100">

    <!-- IZQUIERDA -->
    <div class="col-md-6 left-panel d-flex align-items-center justify-content-center">
      <img src="img/libreria3.avif" alt="Biblioteca" class="w-100 float-img">
    </div>

    <!-- DERECHA -->
    <div class="col-md-6 d-flex align-items-center justify-content-center bg-white">
      <div class="login-card bg-white p-4 p-md-5 w-100 mx-3">

        <div class="text-center mb-4">
          <h2 class="fw-bold" style="color:var(--brand)">
            <i class="fa-solid fa-book me-2"></i>Biblioteca
          </h2>
          <p class="text-muted small mb-0">Sistema de Control de Biblioteca</p>
        </div>

        <h5 class="fw-bold text-center">¡Bienvenido!</h5>
        <p class="text-muted text-center small mb-3">Inicia sesión para continuar</p>

        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger py-2 text-center small">
            Usuario o contraseña incorrectos
          </div>
        <?php endif; ?>

        <form action="php/login.php" method="POST">

          <div class="input-group mb-3">
            <span class="input-group-text bg-light border-end-0">
              <i class="fa-solid fa-user text-muted"></i>
            </span>
            <input type="text" name="usuario" class="form-control border-start-0 ps-0"
                   placeholder="Usuario" required>
          </div>

          <div class="input-group mb-3">
            <span class="input-group-text bg-light border-end-0">
              <i class="fa-solid fa-lock text-muted"></i>
            </span>
            <input type="password" name="password" class="form-control border-start-0 ps-0"
                   placeholder="Contraseña" required>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-4 small">
            <div class="form-check mb-0">
              <input class="form-check-input" type="checkbox" id="recuerdame">
              <label class="form-check-label" for="recuerdame">Recordarme</label>
            </div>
            <a href="#" class="text-decoration-none" style="color:var(--brand)">
              ¿Olvidaste tu contraseña?
            </a>
          </div>

          <button type="submit" class="btn btn-brand w-100 py-2 fw-semibold">
            Iniciar sesión
          </button>

        </form>

        <p class="text-center text-muted small mt-4 mb-0">
          ¿No tienes cuenta?
          <span style="color:var(--brand); cursor:pointer">Contacta al administrador</span>
        </p>

      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
