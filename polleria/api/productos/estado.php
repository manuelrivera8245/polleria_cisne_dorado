<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id) && !empty($data->estado)) {
    $db = (new Database())->connect();
    
    $sql = "UPDATE productos SET estado = ? WHERE id_producto = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$data->estado, $data->id]);
    
    echo json_encode(['ok' => true]);
}
?>