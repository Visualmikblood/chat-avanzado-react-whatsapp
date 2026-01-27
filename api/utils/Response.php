<?php
/**
 * Response Utility Class
 * Para estandarizar las respuestas JSON de la API
 */

class Response {
    
    /**
     * Envía una respuesta JSON exitosa
     * @param mixed $data Datos a retornar
     * @param string $message Mensaje opcional
     * @param int $statusCode Código HTTP (default: 200)
     */
    public static function success($data = null, $message = 'Success', $statusCode = 200) {
        http_response_code($statusCode);
        // Limpiar cualquier output previo (como el '0' misterioso)
        if (ob_get_length()) ob_clean();
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    /**
     * Envía una respuesta JSON de error
     * @param string $message Mensaje de error
     * @param int $statusCode Código HTTP (default: 400)
     * @param mixed $errors Errores específicos (opcional)
     */
    public static function error($message = 'Error', $statusCode = 400, $errors = null) {
        http_response_code($statusCode);
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response);
        exit;
    }

    /**
     * Envía respuesta de no autorizado
     * @param string $message
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401);
    }

    /**
     * Envía respuesta de no encontrado
     * @param string $message
     */
    public static function notFound($message = 'Not Found') {
        self::error($message, 404);
    }

    /**
     * Envía respuesta de error del servidor
     * @param string $message
     */
    public static function serverError($message = 'Internal Server Error') {
        self::error($message, 500);
    }
}
