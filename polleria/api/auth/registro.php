<?php
header('Content-Type: application/json');
session_start();

require_once '../../models/usuario.php';

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del body
$data = json_decode(file_get_contents('php://input'), true);

$nombre = $data['nombre'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$telefono = $data['telefono'] ?? '';

// Validaciones básicas
if (empty($nombre) || empty($email) || empty($password) || empty($telefono)) {
    echo json_encode(['ok' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'message' => 'Correo electrónico inválido']);
    exit;
}

$usuarioModel = new Usuario();
$resultado = $usuarioModel->registrar($nombre, $email, $password, $telefono);

if ($resultado['ok']) {
    // Iniciar sesión automáticamente
    $_SESSION['id_usuario'] = $resultado['id_usuario'];
    $_SESSION['nombre'] = $nombre;
    $_SESSION['email'] = $email;
    $_SESSION['rol'] = 'Cliente';
    
    echo json_encode(['ok' => true, 'message' => 'Registro exitoso']);
} else {
    echo json_encode(['ok' => false, 'message' => $resultado['message']]);
}
