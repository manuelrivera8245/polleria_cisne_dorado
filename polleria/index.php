<?php
// index.php

if (session_status() === PHP_SESSION_NONE) session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// --- CONFIGURACIÓN CLAVE ---
// Solo 'admin' se carga sin el diseño web público.
// El resto (login, registro, repartidor) se cargarán CON header y footer.
$paginas_sin_layout = ['admin']; 

if (in_array($page, $paginas_sin_layout)) {
    // MODO PANEL (Solo para Admin)
    if (file_exists("pages/{$page}.php")) {
        include "pages/{$page}.php";
    } else {
        echo "Error 404";
    }
} else {
    // MODO WEB (Para todo lo demás: Login, Home, Carta, Repartidor...)
    include 'includes/header.php';
    
    if (file_exists("pages/{$page}.php")) {
        include "pages/{$page}.php";
    } else {
        include "pages/home.php";
    }
    
    include 'includes/footer.php';
}
?>