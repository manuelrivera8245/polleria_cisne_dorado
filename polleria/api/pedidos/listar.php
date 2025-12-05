<?php
header('Content-Type: application/json');
require_once "../../models/Pedido.php";

// Seguridad: Solo Admins pueden ver esto
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Acceso denegado']);
    exit;
}

try {
    $pedido = new Pedido();
    $lista = $pedido->listarPedidos(); // Asegúrate de tener este método en tu modelo
    echo json_encode($lista);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Error del servidor']);
}
?>