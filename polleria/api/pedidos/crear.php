<?php
// 1. SILENCIAR ADVERTENCIAS (Evita que el JSON se rompa)
error_reporting(0); 

header('Content-Type: application/json');
require_once "../../models/Pedido.php";
session_start();

// 2. VALIDACIÓN DE TIENDA CERRADA
$archivoConfig = '../../tienda_config.json';
if (file_exists($archivoConfig)) {
    $contenido = file_get_contents($archivoConfig);
    // Verificar que el contenido no esté vacío antes de decodificar
    if($contenido) {
        $conf = json_decode($contenido, true);
        if (isset($conf['tienda_abierta']) && $conf['tienda_abierta'] === false) {
            // Retornamos JSON válido con el error
            echo json_encode([
                "ok" => false, 
                "mensaje" => " La tienda está cerrada: " . ($conf['mensaje'] ?? 'Vuelva pronto')
            ]);
            exit; 
        }
    }
}

// 3. PROCESAR EL INPUT JSON
$input = json_decode(file_get_contents('php://input'), true);

try {
    // Validar datos mínimos
    if (empty($input["nombre"]) || empty($input["telefono"]) || empty($input["productos"])) {
        throw new Exception("Faltan datos obligatorios (Nombre, Teléfono o Productos).");
    }

    $idUsuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;

    $pedido = new Pedido();
    
    // Crear el pedido
    $resultado = $pedido->crearPedido(
        $input["nombre"],
        $input["telefono"],
        $input["direccion"] ?? "Local", // Valor por defecto para evitar warnings
        $input["metodo"] ?? "Efectivo",
        $input["total"] ?? 0,
        $input["productos"],
        $idUsuario
    );

    // Devolver respuesta original del modelo
    echo json_encode($resultado);

} catch (Exception $e) {
    // Capturar cualquier error fatal y devolverlo como JSON limpio
    echo json_encode([
        "ok" => false, 
        "mensaje" => "Error en Servidor: " . $e->getMessage()
    ]);
}
?>