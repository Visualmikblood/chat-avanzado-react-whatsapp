<?php
/**
 * Database Configuration
 * Conexión PDO a MySQL con manejo de errores
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $charset = 'utf8mb4';
    private $conn;

    public function __construct() {
        // Cargar variables de entorno desde .env
        $this->loadEnv();
        
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'chat_app';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
        $this->port = $_ENV['DB_PORT'] ?? '5432';
    }

    /**
     * Carga variables de entorno desde archivo .env
     */
    private function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue; // Ignorar comentarios
                }
                
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }

    /**
     * Establece la conexión con la base de datos
     * @return PDO
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // ============================================================
            // IMPLEMENTACIÓN VERCEL/SUPABASE: Detección de Driver
            // ============================================================
            $driver = $_ENV['DB_CONNECTION'] ?? 'mysql'; // Por defecto mysql para local
            
            if ($driver === 'pgsql') {
                // Configuración para PostgreSQL (Supabase)
                // Supabase requiere SSL mode
                $dsn = "pgsql:host={$this->host};dbname={$this->db_name};port={$this->port}";
                // En Postgres/Supabase el charset se maneja diferente, pero UTF8 es default
            } else {
                // Configuración Original (Localhost MySQL)
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            }
            // ============================================================
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new Exception("No se pudo conectar a la base de datos", 500);
        }

        return $this->conn;
    }

    /**
     * Cierra la conexión
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
