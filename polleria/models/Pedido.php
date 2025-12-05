<?php
require_once __DIR__ . '/../config/db.php';

class Pedido {

    private $db;

    /**
     * Constructor de la clase.
     * Inicia la conexión a la base de datos.
     */
    public function __construct() {
        $this->db = (new Database())->connect();
    }

    /**
     * Crea un nuevo pedido en la base de datos.
     * @param string $nombre Nombre del cliente (si es invitado).
     * @param string $telefono Teléfono de contacto.
     * @param string $direccion Dirección de entrega.
     * @param string $metodo Método de pago.
     * @param float $total Monto total del pedido.
     * @param array $productos Lista de productos.
     * @param int|null $idUsuario ID del usuario registrado (opcional).
     * @return array Resultado de la operación con tracking o mensaje de error.
     */
    public function crearPedido($nombre, $telefono, $direccion, $metodo, $total, $productos, $idUsuario = null) {
        try {
            $this->db->beginTransaction();

            $tracking = "TRK-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

            $tracking = "TRK-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

            $nombreInvitado = $idUsuario ? null : $nombre;

        $sql = "INSERT INTO pedidos (id_usuario_cliente, nombre_invitado, telefono_contacto, direccion_entrega, metodo_pago, total, codigo_tracking, estado, fecha_hora) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente', NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idUsuario, $nombreInvitado, $telefono, $direccion, $metodo, $total, $tracking]);
        
        $idPedido = $this->db->lastInsertId();

        // Insertar Detalles
        $sqlDetalle = "INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
        $stmtDetalle = $this->db->prepare($sqlDetalle);

        foreach ($productos as $prod) {
            $stmtDetalle->execute([$idPedido, $prod['id'], $prod['cantidad'], $prod['precio']]);
        }

        $this->db->commit();
        return ["ok" => true, "tracking" => $tracking, "id_pedido" => $idPedido];

    } catch (PDOException $e) {
        $this->db->rollBack();
        error_log("Error CrearPedido: " . $e->getMessage());
        return ["ok" => false, "message" => "Error en BD: " . $e->getMessage()];
    }
}

    /**
     * Lista todos los pedidos, incluyendo datos del usuario registrado.
     * @return array Lista de pedidos.
     */
    public function listarPedidos() {
        try {
            $sql = "SELECT p.*, u.nombre as nombre_registrado 
                    FROM pedidos p
                    LEFT JOIN usuarios u ON p.id_usuario_cliente = u.id_usuario
                    ORDER BY p.fecha_hora DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al listar: " . $e->getMessage());
        return [];
    }
}
    /**
     * Obtiene los pedidos realizados por un usuario específico.
     * @param int $idUsuario ID del usuario.
     * @return array Lista de pedidos del usuario.
     */
    public function obtenerPedidosPorUsuario($idUsuario) {
        try {
            $sql = "SELECT * FROM pedidos WHERE id_usuario_cliente = ? ORDER BY fecha_hora DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idUsuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener pedidos por usuario: " . $e->getMessage());
            return [];
        }
    }
    // ... (Métodos anteriores: connect, crearPedido, listarPedidos) ...

    /**
     * Actualiza el estado de un pedido.
     * @param int $id ID del pedido.
     * @param string $nuevoEstado Nuevo estado a asignar.
     * @return bool Verdadero si se actualizó correctamente, falso en caso contrario.
     */
    public function actualizarEstado($id, $nuevoEstado) {
        try {
            $sql = "UPDATE pedidos SET estado = ? WHERE id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$nuevoEstado, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un pedido y sus detalles de la base de datos.
     * @param int $id ID del pedido.
     * @return bool Verdadero si se eliminó correctamente, falso en caso contrario.
     */
    public function eliminarPedido($id) {
        try {
            $sqlDetalle = "DELETE FROM detalle_pedidos WHERE id_pedido = ?";
            $stmtDetalle = $this->db->prepare($sqlDetalle);
            $stmtDetalle->execute([$id]);

            $sql = "DELETE FROM pedidos WHERE id_pedido = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

