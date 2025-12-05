<?php
// polleria/api/pedidos/actualizar.php
header('Content-Type: application/json');
require_once '../../config/db.php';

// 1. Leer el JSON que envía Javascript
$input = json_decode(file_get_contents('php://input'), true);

$id = $input['id'] ?? null;

// Validar que exista el ID del pedido
if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'Falta el ID del pedido']);
    exit;
}

$db = (new Database())->connect();

try {
    // CASO 1: Asignar Repartidor (Viene 'id_repartidor')
    if (isset($input['id_repartidor'])) {
        // Al asignar repartidor, actualizamos el ID y cambiamos estado a 'En Camino' automáticamente
        $sql = "UPDATE pedidos SET id_repartidor = ?, estado = 'En Camino' WHERE id_pedido = ?";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$input['id_repartidor'], $id]);
    } 
    // CASO 2: Cambiar solo el Estado (Viene 'estado')
    elseif (isset($input['estado'])) {
        $sql = "UPDATE pedidos SET estado = ? WHERE id_pedido = ?";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$input['estado'], $id]);
    } 
    else {
        echo json_encode(['ok' => false, 'error' => 'No se enviaron datos válidos (estado o id_repartidor)']);
        exit;
    }

    if ($res) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se pudo actualizar la base de datos']);
    }

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'Error SQL: ' . $e->getMessage()]);
}
?>