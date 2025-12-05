<?php
// polleria/api/usuarios/eliminar.php
header('Content-Type: application/json');
require_once '../../config/db.php';
session_start();

// Validar que quien solicita sea Administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id)) {
    // Evitar que el admin se elimine a sí mismo
    if($data->id == $_SESSION['id_usuario']) {
        echo json_encode(['ok' => false, 'error' => 'No puedes eliminar tu propia cuenta mientras estás logueado.']);
        exit;
    }

    $db = (new Database())->connect();
    
    // La base de datos está configurada para poner NULL en pedidos si se borra el usuario,
    // así que es seguro borrar directamente.
    $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
    $stmt = $db->prepare($sql);
    
    try {
        if($stmt->execute([$data->id])) {
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'No se pudo eliminar el registro.']);
        }
    } catch(Exception $e) {
        echo json_encode(['ok' => false, 'error' => 'Error SQL: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['ok' => false, 'error' => 'Falta el ID']);
}
?>