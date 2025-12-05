<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

// Usamos POST directo porque viene de FormData
$nombre = $_POST['nombre'] ?? '';
$precio = $_POST['precio'] ?? '';
$categoria = $_POST['id_categoria'] ?? '';

if(empty($nombre) || empty($precio)) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

$rutaImagen = null;

// Procesar imagen si existe
if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
    $directorio = "../../public/img/producto/";
    // Crear carpeta si no existe
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    // Nombre único para evitar sobrescribir
    $nombreArchivo = uniqid() . "." . $extension;
    $destino = $directorio . $nombreArchivo;

    if(move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
        // Guardamos la ruta relativa para usarla en el HTML
        $rutaImagen = "public/img/producto/" . $nombreArchivo;
    }
}

$db = (new Database())->connect();

// Insertar con imagen
$sql = "INSERT INTO productos (nombre, precio, id_categoria, imagen, estado) VALUES (?, ?, ?, ?, 'Disponible')";
$stmt = $db->prepare($sql);

if($stmt->execute([$nombre, $precio, $categoria, $rutaImagen])) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'error' => 'Error SQL']);
}
?>