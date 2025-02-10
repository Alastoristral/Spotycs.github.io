<?php
// Configuración de la aplicación (como ya tienes)
define('CLIENT_ID', '9f0a8dcb6d6740cdb12a0b5ad667575c'); // Asegúrate de usar tu propio CLIENT_ID
define('CLIENT_SECRET', '684880c355894ba6a1da9bbeb8d1e21e'); // Asegúrate de usar tu propio CLIENT_SECRET
define('REDIRECT_URI', 'http://localhost:8000/spotycs/callback.php'); // La URL a la que Spotify redirige después de la autorización

// Generar la URL de autorización
$auth_url = "https://accounts.spotify.com/authorize?response_type=code&client_id=" . CLIENT_ID . "&redirect_uri=" . urlencode(REDIRECT_URI) . "&scope=user-top-read user-read-email user-read-private";


// Redirigir al usuario a la página de autorización de Spotify
header('Location: ' . $auth_url);
exit;
?>
