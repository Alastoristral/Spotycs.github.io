<?php
session_start();
require 'vendor/autoload.php';

// Configuración de la app de Spotify
$session = new SpotifyWebAPI\Session(
    "9f0a8dcb6d6740cdb12a0b5ad667575c", 
    "684880c355894ba6a1da9bbeb8d1e21e", 
    "http://localhost:8000/spotycs/callback.php"
);
$api = new SpotifyWebAPI\SpotifyWebAPI();

// Definir los scopes necesarios
$scopes = [
    'user-top-read',
    'user-read-email',
    'user-read-private'
];
 
// Lógica de autorización y refresco de token
if (!isset($_SESSION['access_token']) || time() > $_SESSION['access_token_expires']) {
    if (isset($_SESSION['refresh_token'])) {
        // Intentamos refrescar el token si tenemos un refresh_token guardado
        try {
            $session->refreshAccessToken($_SESSION['refresh_token']);
            $_SESSION['access_token'] = $session->getAccessToken();
            $_SESSION['access_token_expires'] = time() + 3600; // Actualizar el tiempo de expiración
            $_SESSION['refresh_token'] = $session->getRefreshToken() ?? $_SESSION['refresh_token']; // Mantener el refresh_token
        } catch (SpotifyWebAPI\SpotifyWebAPIException $e) {
            echo "Error al refrescar el token: " . $e->getMessage();
            exit;
        }
    } elseif (isset($_GET['code'])) {
        // Primera vez obteniendo el token de acceso
        try {
            $session->requestAccessToken($_GET['code']);
            $_SESSION['access_token'] = $session->getAccessToken();
            $_SESSION['access_token_expires'] = time() + 3600; // Actualizar el tiempo de expiración
            $_SESSION['refresh_token'] = $session->getRefreshToken(); // Guardar el refresh_token
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?')); // Evitar que el código quede en la URL
            exit;
        } catch (SpotifyWebAPI\SpotifyWebAPIException $e) {
            echo "Error al obtener el token: " . $e->getMessage();
            exit;
        }
    } else {
        // Si no tenemos token, redirigir a la URL de autorización de Spotify
        $authorizeUrl = $session->getAuthorizeUrl([
            'scope' => implode(' ', $scopes)
        ]);
        header('Location: ' . $authorizeUrl);
        exit;
    }
}

// Configurar el token actualizado en la API
$api->setAccessToken($_SESSION['access_token']);

try {
    // Obtener artistas y canciones más escuchadas
    $topArtists = $api->getMyTop('artists', ['limit' => 5]);
    $topTracks = $api->getMyTop('tracks', ['limit' => 5]);

    // Extraer géneros de los artistas
    $topGenres = [];
    foreach ($topArtists->items as $artist) {
        $topGenres = array_merge($topGenres, $artist->genres);
    }
    $topGenres = array_unique($topGenres); // Eliminar géneros duplicados
    $topGenres = array_slice($topGenres, 0, 5); // Limitar a los 5 géneros más populares

    // Guardar los datos obtenidos en la sesión
    $_SESSION['topArtists'] = $topArtists;
    $_SESSION['topTracks'] = $topTracks;
    $_SESSION['topGenres'] = $topGenres;

    // Mostrar la información del usuario y aplicar CSS
    $userData = $api->me();
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Registrarse - SPOTYCS</title>
        <link rel='stylesheet' href='styles.css'>
        <link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap' rel='stylesheet'>
    </head>
    <body>
        <header>
            <div class='logo-container'>
                <img src='image.png' alt='Logo SPOTYCS' class='logo-image'>
                <span class='logo-text'>SPOTYCS</span>
            </div>
            <button class='menu-toggle' id='menu-toggle' aria-label='Abrir menú de navegación'>☰</button>
            <nav id='nav-menu' class='nav' aria-label='Menú de navegación'>
                <ul>
                    <li><a href='statistics.php' class='nav-link' aria-label='Ver estadísticas'>Estadísticas</a></li>
                    <li><a href='friends.html' class='nav-link' aria-label='Ver amigos'>Amigos</a></li>
                    <li><a href='quizzes.html' class='nav-link' aria-label='Ver quizzes'>Quizzes</a></li>
                    <li><a href='concerts.html' class='nav-link' aria-label='Ver conciertos'>Conciertos</a></li>
                    <li><a href='logout.php' class='nav-link' aria-label='Salir al inicio'>Inicio</a></li>
                </ul>
            </nav>
        </header>

        <div class='profile-container'>
            <h1>Bienvenido, {$userData->display_name}!</h1>
            <p>Email: {$userData->email}</p>
            <img src='{$userData->images[0]->url}' width='100' alt='Imagen del perfil'>
            <br><a href='logout.php' class='logout-btn'>Cerrar sesión</a>
        </div>
        <script src='script.js'></script> <!-- Aquí incluimos el script.js -->
    </body>
    </html>";

} catch (SpotifyWebAPI\SpotifyWebAPIException $e) {
    echo "Error al obtener la información: " . $e->getMessage();
}
?>
