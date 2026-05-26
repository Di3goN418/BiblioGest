// utilerías compartidas en todo el sistema

document.addEventListener('DOMContentLoaded', function () {

  // Auto-ocultar alertas de Bootstrap después de 3 s
  document.querySelectorAll('.alert-autohide').forEach(function (el) {
    setTimeout(function () {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
      bsAlert.close();
    }, 3000);
  });

});
