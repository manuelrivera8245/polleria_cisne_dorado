<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$db = (new Database())->connect();

// Traemos productos y el nombre de su categoría
$sql = "SELECT p.*, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
        ORDER BY p.id_producto DESC";

$stmt = $db->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['data' => $data]);
?>