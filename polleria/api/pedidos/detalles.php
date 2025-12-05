<?php
// polleria/api/pedidos/detalles.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db.php';

if (!isset($_GET['id'])) {
    echo json_encode([]);
    exit;
}

$id_pedido = $_GET['id'];

$database = new Database();
$db = $database->connect();

// Consultamos el detalle uniendo con la tabla de productos para saber el nombre
$query = "SELECT 
            p.nombre,
            dp.cantidad,
            dp.precio_unitario,
            (dp.cantidad * dp.precio_unitario) as subtotal
          FROM detalle_pedidos dp
          INNER JOIN productos p ON dp.id_producto = p.id_producto
          WHERE dp.id_pedido = :id_pedido";

$stmt = $db->prepare($query);
$stmt->bindParam(':id_pedido', $id_pedido);
$stmt->execute();

$productos = [];

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $productos[] = $row;
}

echo json_encode($productos);
?>