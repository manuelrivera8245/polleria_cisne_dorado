<?php
header('Content-Type: application/json');
require_once "../../models/Pedido.php";

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id'])) {
    $pedido = new Pedido();
    $ok = $pedido->eliminarPedido($data['id']);
    echo json_encode(["ok" => $ok]);
} else {
    echo json_encode(["ok" => false]);
}
?>