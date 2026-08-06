<?php
/**
 * QR Tambo - Database Connection
 * Singleton PDO connection
 *
 * Soporte dual de entorno:
 *  - Local (XAMPP): intenta primero 127.0.0.1 / qr_tambo / root
 *  - Producción (InfinityFree): usa las credenciales de config.php
 * El primer perfil que conecta correctamente es el que se usa.
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        // Perfil local (XAMPP) — solo en servidores de desarrollo.
        // Evita intentar conectar a 127.0.0.1 en producción (InfinityFree).
        $serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
        $httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $isLocalServer = (
            $serverName === 'localhost' ||
            $serverName === '127.0.0.1' ||
            strpos($httpHost, '127.0.0.1') === 0 ||
            $serverName === '' || $serverName === '::1'
        );

        $profiles = [];
        if ($isLocalServer && !in_array(DB_HOST, ['127.0.0.1', 'localhost'], true)) {
            $profiles[] = ['127.0.0.1', 'qr_tambo', 'root', ''];
        }
        $profiles[] = [DB_HOST, DB_NAME, DB_USER, DB_PASS];

        $lastError = null;
        foreach ($profiles as $profile) {
            list($host, $name, $user, $pass) = $profile;
            try {
                $this->connection = new PDO(
                    "mysql:host=" . $host . ";dbname=" . $name . ";charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_TIMEOUT => 3
                    ]
                );
                return;
            } catch (PDOException $e) {
                $lastError = $e->getMessage();
            }
        }

        http_response_code(500);
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        die(json_encode([
            'success' => false,
            'message' => 'Error de conexión a la base de datos: ' . $lastError
        ]));
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
