<?php
// Archivo: api/usuarios/repartidores.php
header('Content-Type: application/json');

// IMPORTANTE: Al estar en 'api/usuarios/', debemos subir 2 niveles para llegar a 'config'
$ruta_db = __DIR__ . '/../../config/db.php'; 

if (file_exists($ruta_db)) {
    require_once $ruta_db;
    try {
        $db = (new Database())->connect();
        $sql = "SELECT id_usuario, nombre FROM usuarios WHERE rol = 'Repartidor'";
        $stmt = $db->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo json_encode([]); 
    }
} else {
    // Si la ruta está mal, devuelve array vacío para no romper el JS
    echo json_encode([]); 
}
?>