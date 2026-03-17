<?php
/**
 * JWT Helper Class
 * Implementación simple de JSON Web Tokens (HS256)
 * Sin dependencias externas para facilitar despliegue en Vercel
 */

class JWT {
    
    /**
     * Genera un token JWT
     */
    public static function encode($payload) {
        $secret = $_ENV['JWT_SECRET'] ?? 'default_secret_key_change_me';
        
        // Header
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // Payload (agregamos expiración por defecto 24h)
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + (60 * 60 * 24);
        }
        $payloadJson = json_encode($payload);
        
        // Base64Url Encode
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadJson));
        
        // Firma
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Decodifica y valida un token JWT
     */
    public static function decode($token) {
        $secret = $_ENV['JWT_SECRET'] ?? 'default_secret_key_change_me';
        $parts = explode('.', $token);
        
        if (count($parts) != 3) {
            return null;
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
        
        // Verificar firma
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignatureCheck = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if (!hash_equals($base64UrlSignature, $base64UrlSignatureCheck)) {
            return null;
        }
        
        // Decodificar payload
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload)), true);
        
        // Verificar expiración
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    }
    
    /**
     * Obtiene el token del header Authorization
     */
    public static function getBearerToken() {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}