<?php

// Cierra sesion completamente
session_start();
session_destroy();

// Redirige al login
header("Location: ../login.php");
?>