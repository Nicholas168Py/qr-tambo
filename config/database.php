<?php
/**
 * QR Tambo - Database Connection
 * Singleton PDO connection for XAMPP MySQL
 */
class Database {
    private static $instance = null;
    private $connection;

    private $host = 'sql301.infinityfree.com';
    private $dbname = 'if0_42506388_qr_tambo';
    private $username = 'if0_42506388';
    private $password = 'B0T3u5l7sC';

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            die(json_encode([
                'success' => false,
                'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
            ]));
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
