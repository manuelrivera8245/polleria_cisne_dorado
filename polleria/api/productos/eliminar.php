<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id)) {
    $db = (new Database())->connect();
    
    // Borramos el producto (OJO: Si ya se vendió, esto podría dar error por FK.
    // Lo ideal sería solo marcarlo como 'Inactivo', pero para este ejemplo lo borramos).
    $sql = "DELETE FROM productos WHERE id_producto = ?";
    $stmt = $db->prepare($sql);
    
    try {
        $stmt->execute([$data->id]);
        echo json_encode(['ok' => true]);
    } catch(Exception $e) {
        echo json_encode(['ok' => false, 'message' => 'No se puede eliminar si ya tiene ventas.']);
    }
}
?>