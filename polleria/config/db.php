<?php
class Database {
    private $host = "localhost";
    private $port = "3307";
    private $dbname = "cisne_dorado_delivery"; // Nombre de tu DB
    private $user = "root";
    private $pass = "admin"; // Tu contraseña de Workbench

    /**
     * Establece la conexión con la base de datos MySQL.
     * @return PDO Objeto de conexión PDO.
     * @throws Exception Si hay error de conexión.
     */
    public function connect() {
        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->dbname . ";charset=utf8";

            $pdo = new PDO($dsn, $this->user, $this->pass);
            
            // Configurar el manejo de errores
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $pdo;
        } catch (PDOException $e) {
            // Lanza la excepción para que pueda ser capturada por el archivo que llame a esta clase
            throw new Exception("Error de conexión a BD: " . $e->getMessage());
        }
    }
}
?>