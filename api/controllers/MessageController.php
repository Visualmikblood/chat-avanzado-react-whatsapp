<?php
/**
 * Message Controller
 * Maneja el envío y recepción de mensajes con Pusher
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/pusher.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/Response.php';
// IMPLEMENTACIÓN VERCEL: Importar adaptador de almacenamiento
require_once dirname(__DIR__) . '/vercel_compatibility/StorageAdapter.php';

class MessageController {
    private $db;
    private $messageModel;
    private $conversationModel;
    private $pusher;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->messageModel = new Message($this->db);
        $this->conversationModel = new Conversation($this->db);
        $this->pusher = PusherConfig::getInstance();
    }

    /**
     * Enviar mensaje
     * POST /api/messages
     * Body: { conversation_id, content, type }
     * Header: Authorization: Bearer <token>
     */
    public function send() {
        // Autenticar usuario
        $userId = AuthMiddleware::getUserId();
        
        $conversationId = null;
        $content = null;
        $type = 'text';

        // Detectar tipo de contenido request
        $contentType = $_SERVER["CONTENT_TYPE"] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            // Manejar JSON (Texto normal)
            $data = json_decode(file_get_contents("php://input"), true);
            $conversationId = $data['conversation_id'] ?? null;
            $content = isset($data['content']) ? trim($data['content']) : null;
            $type = $data['type'] ?? 'text';
        } else {
            // Manejar Multipart (Archivos)
            $conversationId = $_POST['conversation_id'] ?? null;
            $type = $_POST['type'] ?? 'file';

            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                // Procesar subida de archivo
                
                // ============================================================
                // IMPLEMENTACIÓN VERCEL/SUPABASE: Uso de StorageAdapter
                // Esta línea maneja automáticamente Local vs Nube
                // ============================================================
                $uploadedUrl = StorageAdapter::upload($_FILES['file']);
                // ============================================================

                if ($uploadedUrl) {
                    $content = $uploadedUrl;
                    
                    // Determinar tipo si no se especificó
                    if ($type === 'file') {
                        $fileName = $_FILES['file']['name'];
                        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        if (in_array($extension, $imageExtensions)) {
                            $type = 'image';
                        }
                    }
                } else {
                    Response::serverError('Error al subir el archivo');
                }
            } else {
               // Si es multipart pero sin archivo, verificar si trae content (texto)
               $content = isset($_POST['content']) ? trim($_POST['content']) : null;
            }
        }

        // Validaciones comunes
        if (empty($conversationId)) {
            Response::error('conversation_id es requerido', 400);
        }

        $conversationId = (int) $conversationId;

        // Verificar permisos
        if (!$this->conversationModel->isParticipant($conversationId, $userId)) {
            Response::unauthorized('No tienes permiso para enviar mensajes en esta conversación');
        }

        // Validar contenido (si no hay archivo ni texto)
        if (empty($content)) {
            Response::error('El mensaje no puede estar vacío', 400);
        }

        // Crear mensaje en DB
        $newMessage = $this->messageModel->create($conversationId, $userId, $content, $type);

        if (!$newMessage) {
            Response::serverError('Error al crear el mensaje');
        }

        // Marcar como entregado
        $this->messageModel->markAsDelivered($newMessage['id']);

        // Disparar evento a Pusher
        $channelName = "conversation-{$conversationId}";
        $eventName = "new-message";
        
        $eventData = [
            'id' => $newMessage['id'],
            'conversation_id' => $newMessage['conversation_id'],
            'sender_id' => $newMessage['sender_id'],
            'sender_username' => $newMessage['sender_username'],
            'sender_avatar' => $newMessage['sender_avatar'],
            'content' => $newMessage['content'],
            'type' => $newMessage['type'],
            'created_at' => $newMessage['created_at'],
            'delivered_at' => $newMessage['delivered_at'],
            'read_at' => $newMessage['read_at'],
            'is_edited' => (bool) $newMessage['is_edited']
        ];

        $this->pusher->trigger($channelName, $eventName, $eventData);

        Response::success($newMessage, 'Mensaje enviado exitosamente', 201);
    }

    /**
     * Obtener mensajes de una conversación
     * GET /api/messages/{conversation_id}
     * Query params: ?limit=50&offset=0
     * Header: Authorization: Bearer <token>
     */
    public function getMessages($conversationId) {
        // Autenticar usuario
        $userId = AuthMiddleware::getUserId();

        // Validar conversation_id
        if (!$conversationId || !is_numeric($conversationId)) {
            Response::error('ID de conversación inválido', 400);
        }

        $conversationId = (int) $conversationId;

        // Verificar que el usuario sea participante de la conversación
        if (!$this->conversationModel->isParticipant($conversationId, $userId)) {
            Response::unauthorized('No tienes permiso para ver esta conversación');
        }

        // Marcar mensajes como leídos
        $this->conversationModel->markAsRead($conversationId, $userId);
        
        // También marcar mensajes como leídos individualmente
        $this->messageModel->markConversationAsRead($conversationId, $userId);

        // Obtener parámetros de paginación
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

        // Limitar el máximo de mensajes por request
        if ($limit > 100) {
            $limit = 100;
        }

        // Obtener mensajes
        $messages = $this->messageModel->getByConversation($conversationId, $limit, $offset);

        Response::success([
            'messages' => $messages,
            'count' => count($messages),
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Eliminar mensaje
     * DELETE /api/messages/{message_id}
     * Header: Authorization: Bearer <token>
     */
    public function delete($messageId) {
        // Autenticar usuario
        $userId = AuthMiddleware::getUserId();

        // Validar message_id
        if (!$messageId || !is_numeric($messageId)) {
            Response::error('ID de mensaje inválido', 400);
        }

        $messageId = (int) $messageId;

        // Obtener el mensaje antes de borrarlo para tener el conversation_id
        $message = $this->messageModel->findById($messageId);

        if (!$message) {
            Response::error('Mensaje no encontrado', 404);
        }

        // Eliminar mensaje (solo si el usuario es el autor)
        $success = $this->messageModel->delete($messageId, $userId);

        if (!$success) {
            Response::error('No se pudo eliminar el mensaje o no tienes permiso', 403);
        }

        // Disparar evento a Pusher
        $channelName = "conversation-{$message['conversation_id']}";
        $eventName = "message-deleted";
        
        $eventData = [
            'id' => $messageId,
            'conversation_id' => $message['conversation_id']
        ];

        $this->pusher->trigger($channelName, $eventName, $eventData);

        Response::success(null, 'Mensaje eliminado exitosamente');
    }

    /**
     * Editar mensaje
     * PUT /api/messages/{message_id}
     * Body: { content }
     * Header: Authorization: Bearer <token>
     */
    public function update($messageId) {
        // Autenticar usuario
        $userId = AuthMiddleware::getUserId();

        // Validar message_id
        if (!$messageId || !is_numeric($messageId)) {
            Response::error('ID de mensaje inválido', 400);
        }

        $messageId = (int) $messageId;

        // Obtener datos del request
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['content'])) {
            Response::error('El contenido es requerido', 400);
        }

        $newContent = trim($data['content']);

        if (strlen($newContent) === 0) {
            Response::error('El mensaje no puede estar vacío', 400);
        }

        // Actualizar mensaje (solo si el usuario es el autor)
        $success = $this->messageModel->update($messageId, $userId, $newContent);

        if (!$success) {
            Response::error('No se pudo editar el mensaje o no tienes permiso', 403);
        }

        Response::success(null, 'Mensaje editado exitosamente');
    }
}
