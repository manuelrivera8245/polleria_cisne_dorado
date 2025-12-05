<?php
// api/ajustes/obtener.php
session_start(); // <--- IMPORTANTE: ESTO DEBE SER LA PRIMERA LÍNEA
header('Content-Type: application/json');

// 1. Cargar Configuración del JSON
$archivo = '../../tienda_config.json';
$config = [
    'tienda_abierta' => true,
    'mensaje' => ''
];

if (file_exists($archivo)) {
    $contenido = file_get_contents($archivo);
    $dataJson = json_decode($contenido, true);
    if($dataJson) $config = $dataJson;
}

// 2. Cargar Datos del Admin desde la Sesión
// Usamos ?? '' para evitar errores si el dato no existe
$adminData = [
    'nombre' => $_SESSION['nombre'] ?? 'Admin',
    'email'  => $_SESSION['email'] ?? 'Sin email',
    'rol'    => $_SESSION['rol'] ?? 'Staff'
];

echo json_encode([
    'ok' => true, 
    'config' => $config, 
    'admin' => $adminData
]);
?>