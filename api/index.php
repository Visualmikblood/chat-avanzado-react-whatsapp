<?php
/**
 * API Entry Point
 * Router principal para todas las peticiones de la API
 */

// Iniciar buffering de salida para evitar caracteres extraños
ob_start();

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// Manejar preflight requests (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error reporting (desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Importar controladores
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/MessageController.php';
require_once __DIR__ . '/controllers/ConversationController.php';
require_once __DIR__ . '/utils/Response.php';

// Obtener la URI y el método HTTP
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remover query string y obtener solo el path
$path = parse_url($requestUri, PHP_URL_PATH);

// Remover el prefijo /api si existe (para compatibilidad)
$path = preg_replace('#^/api#', '', $path);

// Dividir el path en segmentos
$segments = array_values(array_filter(explode('/', $path)));

// Router
try {
    // ==================== AUTH ROUTES ====================
    
    if ($segments[0] === 'auth') {
        $authController = new AuthController();
        
        // POST /auth/register
        if ($requestMethod === 'POST' && $segments[1] === 'register') {
            $authController->register();
        }
        
        // POST /auth/login
        elseif ($requestMethod === 'POST' && $segments[1] === 'login') {
            $authController->login();
        }
        
        // GET /auth/me
        elseif ($requestMethod === 'GET' && $segments[1] === 'me') {
            $authController->me();
        }
        
        // POST /auth/logout
        elseif ($requestMethod === 'POST' && $segments[1] === 'logout') {
            $authController->logout();
        }
        
        // PUT /auth/profile
        elseif ($requestMethod === 'PUT' && $segments[1] === 'profile') {
            $authController->updateProfile();
        }
        
        else {
            Response::notFound('Ruta no encontrada');
        }
    }
    
    // ==================== CONVERSATION ROUTES ====================
    
    elseif ($segments[0] === 'conversations') {
        $conversationController = new ConversationController();
        
        // GET /conversations
        if ($requestMethod === 'GET' && count($segments) === 1) {
            $conversationController->getAll();
        }
        
        // POST /conversations
        elseif ($requestMethod === 'POST' && count($segments) === 1) {
            $conversationController->create();
        }
        
        // GET /conversations/search?q=term
        elseif ($requestMethod === 'GET' && $segments[1] === 'search') {
            $conversationController->searchUsers();
        }
        
        else {
            Response::notFound('Ruta no encontrada');
        }
    }
    
    // ==================== MESSAGE ROUTES ====================
    
    elseif ($segments[0] === 'messages') {
        $messageController = new MessageController();
        
        // POST /messages (enviar mensaje)
        if ($requestMethod === 'POST' && count($segments) === 1) {
            $messageController->send();
        }
        
        // GET /messages/{conversation_id}
        elseif ($requestMethod === 'GET' && count($segments) === 2) {
            $conversationId = $segments[1];
            $messageController->getMessages($conversationId);
        }
        
        // DELETE /messages/{message_id}
        elseif ($requestMethod === 'DELETE' && count($segments) === 2) {
            $messageId = $segments[1];
            $messageController->delete($messageId);
        }
        
        // PUT /messages/{message_id}
        elseif ($requestMethod === 'PUT' && count($segments) === 2) {
            $messageId = $segments[1];
            $messageController->update($messageId);
        }
        
        else {
            Response::notFound('Ruta no encontrada');
        }
    }
    
    // ==================== DEFAULT ROUTE ====================
    
    else {
        Response::success([
            'message' => 'Chat API funcionando correctamente',
            'version' => '1.0.0',
            'endpoints' => [
                'auth' => [
                    'POST /auth/register',
                    'POST /auth/login',
                    'GET /auth/me',
                    'POST /auth/logout'
                ],
                'conversations' => [
                    'GET /conversations',
                    'POST /conversations',
                    'GET /conversations/search?q=term'
                ],
                'messages' => [
                    'POST /messages',
                    'GET /messages/{conversation_id}',
                    'PUT /messages/{message_id}',
                    'DELETE /messages/{message_id}'
                ]
            ]
        ]);
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    Response::serverError('Error interno del servidor');
}
