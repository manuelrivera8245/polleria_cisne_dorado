<?php
// api/auth/login.php
header('Content-Type: application/json');
require_once '../../models/Usuario.php';

// Iniciamos sesión para poder guardar las variables $_SESSION
session_start();

// 1. Verificar que sea método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
    exit;
}

// 2. Obtener los datos (Soporta JSON y Form-Data)
$input = json_decode(file_get_contents('php://input'), true);
// Si no viene como JSON, intentamos leer de $_POST estándar
$email = $input['email'] ?? $_POST['email'] ?? '';
$password = $input['password'] ?? $_POST['password'] ?? '';

// 3. Validar campos vacíos
if (empty($email) || empty($password)) {
    echo json_encode(['ok' => false, 'message' => 'Correo y contraseña son obligatorios']);
    exit;
}

// 4. Instanciar modelo y verificar credenciales
try {
    $usuarioModel = new Usuario();
    $user = $usuarioModel->login($email, $password);

    if ($user) {
        // --- ÉXITO: Guardamos datos en sesión ---
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['nombre']     = $user['nombre'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['rol']        = $user['rol'];
        
        // Importante: Guardamos el teléfono (si existe en la BD)
        if (isset($user['telefono'])) {
            $_SESSION['telefono'] = $user['telefono'];
        }

        // --- REDIRECCIÓN SEGÚN ROL ---
        $redirect = 'index.php?page=home'; // Por defecto para Cliente

        if ($user['rol'] === 'Administrador') {
            $redirect = 'index.php?page=admin';
        } elseif ($user['rol'] === 'Repartidor') {
            $redirect = 'index.php?page=repartidor'; // <--- ESTA ES LA RUTA NUEVA
        }

        echo json_encode(['ok' => true, 'redirect' => $redirect]);
    } else {
        // --- ERROR: Datos incorrectos ---
        echo json_encode(['ok' => false, 'message' => 'Correo o contraseña incorrectos']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>