<?php
/**
 * Authentication Middleware
 * Verifica el token JWT en cada request protegido
 */

require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthMiddleware {
    
    /**
     * Verifica que el usuario esté autenticado
     * @return object Payload del JWT si es válido
     */
    public static function authenticate() {
        // Obtener el header Authorization
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            Response::unauthorized('Token no proporcionado');
        }
        
        // Extraer el token del header "Bearer <token>"
        $authHeader = $headers['Authorization'];
        $arr = explode(' ', $authHeader);
        
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
