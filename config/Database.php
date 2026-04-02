<?php
class Database {
    private static $instance = null;
    private $connection;
    
    // Configuración de base de datos
    // IMPORTANTE: Actualiza estos valores con los de HostGator
    private $host = 'localhost'; // Generalmente 'localhost' en HostGator
    private $db_name = 'gsfilms_db'; // Nombre de tu base de datos
    private $username = 'tu_usuario_db'; // Tu usuario de MySQL
    private $password = 'tu_contraseña_db'; // Tu contraseña de MySQL
    
    // Alternativa: Usar variables de entorno (descomenta si tienes configurado)
    /*
    private $host = getenv('DB_HOST') ?: 'localhost';
    private $db_name = getenv('DB_NAME') ?: 'gsfilms_db';
    private $username = getenv('DB_USER') ?: 'root';
    private $password = getenv('DB_PASS') ?: '';
    */
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            throw new Exception("Query failed");
        }
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
