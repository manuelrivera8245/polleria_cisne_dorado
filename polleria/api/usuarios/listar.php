<?php
// polleria/api/usuarios/listar.php
header('Content-Type: application/json');
require_once '../../config/db.php';

try {
    $db = (new Database())->connect();
    
    // Seleccionamos todos los campos relevantes, incluyendo el estado de repartidor
    $sql = "SELECT 
                id_usuario, 
                nombre, 
                email, 
                telefono, 
                rol, 
                estado_repartidor 
            FROM usuarios 
            ORDER BY rol DESC, nombre ASC"; 
            
    $stmt = $db->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($usuarios);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]);
}
?>