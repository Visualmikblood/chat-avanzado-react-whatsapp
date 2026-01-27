<?php
/**
 * Pusher Configuration
 * Configuración del SDK de Pusher para eventos en tiempo real
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Pusher\Pusher;

class PusherConfig {
    private static $instance = null;
    private $pusher;

    private function __construct() {
        // Cargar variables de entorno
        $this->loadEnv();

        $options = [
            'cluster' => $_ENV['PUSHER_CLUSTER'] ?? 'eu',
            'useTLS' => true
        ];

        try {
            $this->pusher = new Pusher(
                $_ENV['PUSHER_APP_KEY'] ?? '',
                $_ENV['PUSHER_APP_SECRET'] ?? '',
                $_ENV['PUSHER_APP_ID'] ?? '',
                $options
            );
        } catch (Exception $e) {
            error_log("Pusher Configuration Error: " . $e->getMessage());
            throw new Exception("No se pudo configurar Pusher", 500);
        }
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
                    continue;
                }
                
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }

    /**
     * Singleton pattern para obtener instancia única
     * @return PusherConfig
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new PusherConfig();
        }
        return self::$instance;
    }

    /**
     * Obtiene el cliente de Pusher
     * @return Pusher
     */
    public function getPusher() {
        return $this->pusher;
    }

    /**
     * Dispara un evento a Pusher
     * @param string $channel Canal de Pusher
     * @param string $event Nombre del evento
     * @param array $data Datos a enviar
     * @return bool
     */
    public function trigger($channel, $event, $data) {
        try {
            $this->pusher->trigger($channel, $event, $data);
            return true;
        } catch (Exception $e) {
            error_log("Pusher Trigger Error: " . $e->getMessage());
            return false;
        }
    }
}
