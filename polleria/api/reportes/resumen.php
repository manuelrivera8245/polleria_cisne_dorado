<?php
// api/reportes/resumen.php
header('Content-Type: application/json');
include_once '../../config/db.php';

// 1. IMPORTANTE: Definir Zona Horaria de Perú
date_default_timezone_set('America/Lima');

$db = (new Database())->connect();
$response = [];

try {
    // 2. PEDIDOS DE HOY (Todo lo que entró hoy, excepto rechazados)
    $stmt = $db->query("SELECT COUNT(*) as total FROM pedidos 
                        WHERE DATE(fecha_hora) = CURDATE() 
                        AND estado != 'Rechazado'");
    $response['pedidos_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 3. VENTAS DE HOY (Dinero real: Solo entregados hoy)
    $stmt = $db->query("SELECT SUM(total) as total FROM pedidos 
                        WHERE DATE(fecha_hora) = CURDATE() 
                        AND estado = 'Entregado'");
    $totalVentas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $response['ventas_hoy'] = $totalVentas ? $totalVentas : 0;

    // 4. PLATO ESTRELLA (Ranking histórico global)
    $sqlTop = "SELECT p.nombre, SUM(dp.cantidad) as cant 
               FROM detalle_pedidos dp 
               JOIN productos p ON dp.id_producto = p.id_producto 
               JOIN pedidos ped ON dp.id_pedido = ped.id_pedido
               WHERE ped.estado = 'Entregado' 
               GROUP BY dp.id_producto 
               ORDER BY cant DESC 
               LIMIT 1";
    $stmt = $db->query($sqlTop);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['top_producto'] = $row ? $row['nombre'] : "-";

    // 5. PENDIENTES (¡CORRECCIÓN!: Contar TODOS los pendientes, no solo los de hoy)
    // Esto arreglará la tarjeta roja del Dashboard
    $stmt = $db->query("SELECT COUNT(*) as total FROM pedidos 
                        WHERE estado IN ('Pendiente', 'En Preparacion', 'Listo', 'En Camino', 'En Puerta')");
    $response['pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 6. REPARTIDORES LIBRES
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'Repartidor' AND estado_repartidor = 'Disponible'");
    $response['repartidores'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>