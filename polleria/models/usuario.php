<?php
require_once __DIR__ . '/../config/db.php';

class Usuario {
    private $db;

    /**
     * Constructor. Inicia conexión a base de datos.
     */
    public function __construct() {
        $this->db = (new Database())->connect();
    }

    /**
     * Valida las credenciales de un usuario para el inicio de sesión.
     * @param string $email Correo electrónico.
     * @param string $password Contraseña.
     * @return array|false Datos del usuario si es correcto, o false si falla.
     */
    public function login($email, $password) {
        try {
            $sql = "SELECT id_usuario, nombre, email, password, rol, telefono FROM usuarios WHERE email = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['password']) { 
            unset($user['password']);
            return $user;
        }
        return false;
    } catch (PDOException $e) {
        return false;
    }
}
    /**
     * Registra un nuevo usuario en el sistema.
     * @param string $nombre Nombre completo.
     * @param string $email Correo electrónico.
     * @param string $password Contraseña (texto plano, nota: debería hashearse).
     * @param string $telefono Teléfono de contacto.
     * @return array Resultado con 'ok' y mensaje o id_usuario.
     */
    public function registrar($nombre, $email, $password, $telefono) {
        try {
            // Verificar si el email ya existe
            $sqlCheck = "SELECT id_usuario FROM usuarios WHERE email = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->execute([$email]);
            if ($stmtCheck->fetch()) {
                return ['ok' => false, 'message' => 'El correo electrónico ya está registrado.'];
            }

            // Insertar nuevo usuario
            $sql = "INSERT INTO usuarios (nombre, email, password, telefono, rol, estado_repartidor) VALUES (?, ?, ?, ?, 'Cliente', 'Inactivo')";
            $stmt = $this->db->prepare($sql);
            $exito = $stmt->execute([$nombre, $email, $password, $telefono]);

            if ($exito) {
                return ['ok' => true, 'id_usuario' => $this->db->lastInsertId()];
            }
            return ['ok' => false, 'message' => 'Error al registrar el usuario.'];

        } catch (PDOException $e) {
            error_log("Error registro: " . $e->getMessage());
            return ['ok' => false, 'message' => 'Error de base de datos.'];
        }
    }
}