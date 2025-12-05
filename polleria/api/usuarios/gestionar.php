<?php
// api/usuarios/gestionar.php
header('Content-Type: application/json');
require_once '../../config/db.php';

$data = json_decode(file_get_contents("php://input"));

// Validación básica
if (!isset($data->rol)) {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Faltan datos requeridos"]);
    exit;
}

$db = (new Database())->connect();

try {
    // ==========================================
    // MODO EDICIÓN (UPDATE)
    // ==========================================
    if (!empty($data->id_usuario)) {
        
        // Verificar duplicidad de email (excluyendo al propio usuario)
        if (!empty($data->email)) {
            $stmtCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?");
            $stmtCheck->execute([$data->email, $data->id_usuario]);
            if ($stmtCheck->fetch()) {
                http_response_code(409); 
                echo json_encode(["ok" => false, "error" => "El email ya está registrado por otro usuario."]);
                exit;
            }
        }

        $campos = [];
        $params = [];

        // Construcción dinámica del SQL
        if (isset($data->nombre)) { $campos[] = "nombre = ?"; $params[] = $data->nombre; }
        if (isset($data->email)) { $campos[] = "email = ?"; $params[] = $data->email; }
        if (isset($data->telefono)) { $campos[] = "telefono = ?"; $params[] = $data->telefono; }
        if (isset($data->rol)) { $campos[] = "rol = ?"; $params[] = $data->rol; }
        if (isset($data->estado_repartidor)) { $campos[] = "estado_repartidor = ?"; $params[] = $data->estado_repartidor; }
        
        // Solo actualizamos contraseña si el usuario escribió algo
        if (!empty($data->password)) {
            $campos[] = "password = ?";
            // NOTA: Para producción usa password_hash. Aquí lo dejamos plano según tu ejemplo anterior, 
            // pero lo ideal es: $params[] = password_hash($data->password, PASSWORD_DEFAULT);
            $params[] = $data->password; 
        }

        if (empty($campos)) {
            echo json_encode(["ok" => true, "mensaje" => "Nada que actualizar."]);
            exit;
        }

        $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id_usuario = ?";
        $params[] = $data->id_usuario;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(["ok" => true, "mensaje" => "Actualizado correctamente."]);

    } 
    // ==========================================
    // MODO CREACIÓN (INSERT) - ¡NUEVO!
    // ==========================================
    else {
        // Validar campos obligatorios para creación
        if (empty($data->nombre) || empty($data->email) || empty($data->password)) {
            echo json_encode(["ok" => false, "error" => "Nombre, Email y Contraseña son obligatorios."]);
            exit;
        }

        // Verificar si el email ya existe
        $stmtCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmtCheck->execute([$data->email]);
        if ($stmtCheck->fetch()) {
            echo json_encode(["ok" => false, "error" => "El email ya está registrado."]);
            exit;
        }

        // Preparar datos por defecto
        $telefono = $data->telefono ?? '';
        $estadoRep = $data->estado_repartidor ?? 'Inactivo';
        // Encriptar pass (o dejar plano según tu preferencia actual)
        $passFinal = $data->password; 

        $sql = "INSERT INTO usuarios (nombre, email, password, telefono, rol, estado_repartidor) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $exito = $stmt->execute([
            $data->nombre, 
            $data->email, 
            $passFinal, 
            $telefono, 
            $data->rol, 
            $estadoRep
        ]);

        if ($exito) {
            echo json_encode(["ok" => true, "mensaje" => "Usuario creado exitosamente."]);
        } else {
            echo json_encode(["ok" => false, "error" => "Error al insertar en BD."]);
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Error DB: " . $e->getMessage()]);
}
?>