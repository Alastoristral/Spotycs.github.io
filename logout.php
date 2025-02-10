<?php
session_start(); // Iniciar sesión

// Destruir todas las variables de sesión
$_SESSION = [];

// Invalidar la cookie de sesión
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir al usuario a la página de inicio o login
header("Location: index.html");
exit;
?>