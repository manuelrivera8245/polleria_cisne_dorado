<?php
header('Content-Type: application/json');
require_once "../../config/db.php";
session_start();

// Validar que sea Repartidor
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Repartidor') {
    echo json_encode([]);
    exit;
}

$idRepartidor = $_SESSION['id_usuario'];

try {
    $db = (new Database())->connect();
    // Traemos los pedidos asignados que NO estén "Entregados" (para que no se llene la lista)
    // Opcional: Si quieres ver historial, quita el "AND estado != 'Entregado'"
    $sql = "SELECT p.*, u.nombre as nombre_cliente 
            FROM pedidos p
            LEFT JOIN usuarios u ON p.id_usuario_cliente = u.id_usuario
            WHERE p.id_repartidor = ? 
            AND p.estado != 'Entregado'
            ORDER BY p.fecha_hora DESC";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$idRepartidor]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo json_encode([]);
}
?>