<?php
header('Content-Type: application/json');
session_start();

// Seguridad: Solo admin puede guardar
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$archivo = '../../tienda_config.json';

// Preparamos los datos a guardar
$nuevoEstado = [
    'tienda_abierta' => (bool)$data['tienda_abierta'],
    'mensaje' => $data['mensaje'] ?? ''
];

// Guardamos en el archivo JSON
if (file_put_contents($archivo, json_encode($nuevoEstado, JSON_PRETTY_PRINT))) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'error' => 'No se pudo escribir el archivo JSON. Verifica permisos.']);
}
?>