<?php
header('Content-Type: application/json');
session_start();

require_once '../../models/Pedido.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['ok' => false, 'message' => 'No autorizado']);
    exit;
}

$pedidoModel = new Pedido();
$pedidos = $pedidoModel->obtenerPedidosPorUsuario($_SESSION['id_usuario']);

echo json_encode(['ok' => true, 'pedidos' => $pedidos]);
