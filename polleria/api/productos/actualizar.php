<?php
// polleria/api/productos/actualizar.php
header('Content-Type: application/json');
require_once '../../config/db.php';

// Usamos $_POST porque los datos vienen como FormData (con imagen)
$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre'] ?? '';
$precio = $_POST['precio'] ?? '';
$categoria = $_POST['id_categoria'] ?? '';

// Validación básica
if(!$id || empty($nombre)) {
    echo json_encode(['ok' => false, 'error' => 'Falta ID o Nombre del producto']);
    exit;
}

$db = (new Database())->connect();

try {
    // CASO 1: Si subieron una nueva imagen
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $directorio = "../../public/img/producto/";
        
        // Crear carpeta si no existe
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid() . "." . $extension;
        $destino = $directorio . $nombreArchivo;

        if(move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
            $nuevaRuta = "public/img/producto/" . $nombreArchivo;
            
            // Actualizar datos INCLUYENDO la imagen
            $sql = "UPDATE productos SET nombre=?, precio=?, id_categoria=?, imagen=? WHERE id_producto=?";
            $stmt = $db->prepare($sql);
            $res = $stmt->execute([$nombre, $precio, $categoria, $nuevaRuta, $id]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Error al subir la imagen al servidor']);
            exit;
        }
    } 
    // CASO 2: Si NO subieron imagen (solo actualizar textos)
    else {
        $sql = "UPDATE productos SET nombre=?, precio=?, id_categoria=? WHERE id_producto=?";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([$nombre, $precio, $categoria, $id]);
    }

    if($res) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se pudo actualizar en la base de datos']);
    }

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'Error SQL: ' . $e->getMessage()]);
}
?>