<?php
// polleria/api/productos/top.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db.php';

$database = new Database();
$db = $database->connect();

// CONSULTA SQL CLAVE:
// 1. Unimos productos con detalle_pedidos.
// 2. Sumamos la cantidad vendida de cada uno.
// 3. Ordenamos de mayor a menor (DESC).
// 4. Tomamos solo los 3 primeros.

$query = "SELECT 
            p.id_producto,
            p.nombre,
            p.precio,
            SUM(dp.cantidad) as total_vendido
          FROM detalle_pedidos dp
          INNER JOIN productos p ON dp.id_producto = p.id_producto
          GROUP BY dp.id_producto
          ORDER BY total_vendido DESC
          LIMIT 3";

$stmt = $db->prepare($query);
$stmt->execute();

$productos = [];

if($stmt->rowCount() > 0) {
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $productos[] = $row;
    }
} else {
    // FALLBACK: Si no hay ventas todavía, muestra 3 productos al azar para que no quede vacío
    $queryFallback = "SELECT * FROM productos LIMIT 3";
    $stmtFallback = $db->prepare($queryFallback);
    $stmtFallback->execute();
    while($row = $stmtFallback->fetch(PDO::FETCH_ASSOC)) {
        $productos[] = $row;
    }
}

echo json_encode($productos);
?>