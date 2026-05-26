<?php
session_start();
include("conexion.php");

// Datos del formulario
$usuario  = $_POST['usuario'];
$password = $_POST['password'];

// Query de acceso con los parametros usuario y password como ?
$stmt = $conexion->prepare("SELECT * FROM admins WHERE usuario = ? AND password = ?");
$stmt->bind_param("ss", $usuario, $password);
$stmt->execute();
$resultado = $stmt->get_result();

// Verificacion del usuario y acceso al sistema
if ($resultado && $resultado->num_rows > 0) {
    $_SESSION['usuario'] = $usuario;
    header("Location: ../dashboard.php");
    exit();
} else {
    header("Location: ../login.php?error=1");
    exit();
}
?>

