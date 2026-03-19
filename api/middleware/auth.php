<?php
/**
 * Authentication Middleware
 * Verifica el token JWT en cada request protegido
 */

require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthMiddleware {

    /**
     * Obtiene el header Authorization de forma robusta.
     * En Vercel serverless PHP, getallheaders() a veces no funciona,
     * por lo que también se revisan las variables $_SERVER como fallback.
     * @return string|null El valor del header Authorization o null si no existe
     */
    private static function getAuthorizationHeader() {
        // 1. Intentar con getallheaders() (funciona en la mayoría de entornos)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            // Probar con capitalización estándar y en minúsculas
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
            if (isset($headers['authorization'])) {
                return $headers['authorization'];
            }
        }

        // 2. Fallback: $_SERVER['HTTP_AUTHORIZATION'] (Vercel serverless, Apache mod_rewrite)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        // 3. Fallback: $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] (Apache con .htaccess)
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        return null;
    }

    /**
     * Verifica que el usuario esté autenticado
     * @return object Payload del JWT si es válido
     */
    public static function authenticate() {
        $authHeader = self::getAuthorizationHeader();

        if (!$authHeader) {
            Response::unauthorized('Token no proporcionado');
        }

        // Extraer el token del header "Bearer <token>"
        $arr = explode(' ', trim($authHeader));

        if (count($arr) !== 2 || $arr[0] !== 'Bearer') {
            Response::unauthorized('Formato de token inválido');
        }

        $token = $arr[1];

        // Decodificar y validar el token
        $decoded = JWT::decode($token);

        if (!$decoded) {
            Response::unauthorized('Token inválido o expirado');
        }

        return $decoded;
    }

    /**
     * Extrae el ID del usuario del token
     * @return int User ID
     */
    public static function getUserId() {
        $decoded = self::authenticate();
        return $decoded->data->id ?? null;
    }
}
