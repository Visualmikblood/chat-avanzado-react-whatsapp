<?php
/**
 * Conversation Controller
 * Maneja la creación y obtención de conversaciones
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/Response.php';

class ConversationController {
    private $db;
    private $conversationModel;
    private $userModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->conversationModel = new Conversation($this->db);
        $this->userModel = new User($this->db);
    }

    /**
     * Obtener todas las conversaciones del usuario autenticado
     * GET /api/conversations
     * Header: Authorization: Bearer <token>
     */
    public function getAll() {
        // Autenticar usuario
        $userId = AuthMiddleware::getUserId();

        // Obtener conversaciones
        $conversations = $this->conversationModel->getUserConversations($userId);

        Response::success($conversations);
    }

    /**
     * Crear conversación individual o buscar si ya existe
     * POST /api/conversations
     * Body: { user_id } (ID del otro usuario)
     * Header: Authorization: Bearer <token>
     */
    public function create() {
        // Autenticar usuario
        $userId = AuthMiddleware::getUserId();

        // Obtener datos del request
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id'])) {
            Response::error('user_id es requerido', 400);
        }

        $otherUserId = (int) $data['user_id'];

        // Verificar que no intente crear conversación consigo mismo
        if ($userId === $otherUserId) {
            Response::error('No puedes crear una conversación contigo mismo', 400);
        }

        // Verificar que el otro usuario exista
        $otherUser = $this->userModel->findById($otherUserId);
        if (!$otherUser) {
            Response::notFound('Usuario no encontrado');
        }

        // Buscar si ya existe una conversación individual entre estos usuarios
        $existingConversation = $this->conversationModel->findIndividualConversation($userId, $otherUserId);

        if ($existingConversation) {
            // Si ya existe, retornar los detalles de esa conversación
            $conversation = [
                'id' => $existingConversation,
                'type' => 'individual',
                'other_user' => $otherUser,
                'already_exists' => true
            ];

            Response::success($conversation, 'Conversación ya existe');
        }

        // Crear nueva conversación individual
        $conversationId = $this->conversationModel->create('individual', [$userId, $otherUserId]);

        if (!$conversationId) {
            Response::serverError('Error al crear la conversación');
        }

        // Retornar la nueva conversación
        unset($otherUser['password_hash']);
        
        $conversation = [
            'id' => $conversationId,
            'type' => 'individual',
            'other_user' => $otherUser,
            'created' => true
        ];

        Response::success($conversation, 'Conversación creada exitosamente', 201);
    }

    /**
     * Buscar usuarios para iniciar conversación
     * GET /api/conversations/search?q=searchTerm
     * Header: Authorization: Bearer <token>
     */
    public function searchUsers() {
        // Autenticar usuario
        $userId = AuthMiddleware::getUserId();

        // Obtener término de búsqueda
        $searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

        // DEBUG: Log search term
        file_put_contents(__DIR__ . '/../../debug_search.log', "Search Term: [" . $searchTerm . "] - UserID: " . $userId . "\n", FILE_APPEND);

        if (strlen($searchTerm) < 2) {
            Response::error('El término de búsqueda debe tener al menos 2 caracteres', 400);
        }

        // Buscar usuarios (excluyendo al usuario actual)
        $users = $this->userModel->search($searchTerm, $userId);

        Response::success($users);
    }
}
