<?php
/**
 * JWT Configuration
 * Funciones para generar y validar JSON Web Tokens
 */

class JWT {
    private static $secret_key;
    private static $encrypt = 'HS256';
    private static $aud = null;

    /**
     * Inicializa la configuración JWT
     */
    public static function init() {
        self::loadEnv();
        self::$secret_key = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-this-in-production';
        self::$aud = self::getAudience();
    }

    /**
     * Carga variables de entorno
     */
    private static function loadEnv() {
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
     * Genera un token JWT
     * @param array $data Datos a incluir en el payload
     * @param int $expiration Tiempo de expiración en segundos (default: 24 horas)
     * @return string
     */
    public static function encode($data, $expiration = 86400) {
        self::init();
        
        $time = time();
        
        $token = [
            'iat' => $time, // Issued at
            'exp' => $time + $expiration, // Expiration
            'aud' => self::$aud,
            'data' => $data
        ];

        $header = json_encode(['typ' => 'JWT', 'alg' => self::$encrypt]);
        $payload = json_encode($token);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Decodifica y valida un token JWT
     * @param string $token
     * @return object|false Retorna el objeto decodificado o false si es inválido
     */
    public static function decode($token) {
        self::init();
        
        if (empty($token)) {
            return false;
        }

        $tokenParts = explode('.', $token);
        
        if (count($tokenParts) !== 3) {
            return false;
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $tokenParts;

        // Verificar firma
        $signature = self::base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);

        if (!hash_equals($expectedSignature, $signature)) {
            return false; // Firma inválida
        }

        $payload = json_decode(self::base64UrlDecode($base64UrlPayload));

        if (!$payload) {
            return false; // JSON inválido
        }

        // Verificar expiración
        if (isset($payload->exp) && $payload->exp < time()) {
            return false; // Token expirado
        }

        return $payload;
    }

    /**
     * Codifica en Base64 URL-safe
     */
    private static function base64UrlEncode($text) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }

    /**
     * Decodifica desde Base64 URL-safe
     */
    private static function base64UrlDecode($text) {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $text));
    }

    /**
     * Obtiene la audiencia (URL base del servidor)
     */
    private static function getAudience() {
        $aud = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }

        return $aud;
    }
}
