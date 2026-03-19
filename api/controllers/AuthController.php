<?php
/**
 * Auth Controller
 * Maneja registro, login y logout
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthController {
    private $db;
    private $userModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
    }

    /**
     * Registro de nuevo usuario
     * POST /api/auth/register
     * Body: { username, email, password }
     */
    public function register() {
        // Obtener datos del request
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar datos requeridos
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            Response::error('Todos los campos son requeridos', 400);
        }

        $username = trim($data['username']);
        $email = trim($data['email']);
        $password = $data['password'];

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Email inválido', 400);
        }

        // Validar longitud de contraseña
        if (strlen($password) < 6) {
            Response::error('La contraseña debe tener al menos 6 caracteres', 400);
        }

        // Verificar si el email ya existe
        if ($this->userModel->emailExists($email)) {
            Response::error('El email ya está registrado', 409);
        }

        // Verificar si el username ya existe
        if ($this->userModel->usernameExists($username)) {
            Response::error('El nombre de usuario ya está en uso', 409);
        }

        // Crear usuario
        $userId = $this->userModel->create($username, $email, $password);

        if (!$userId) {
            Response::serverError('Error al crear el usuario');
        }

        // Obtener datos del usuario creado
        $user = $this->userModel->findById($userId);

        // Generar token JWT
        $tokenData = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ];

        $token = JWT::encode($tokenData);

        // Eliminar campos sensibles
        unset($user['password_hash']);

        Response::success([
            'user' => $user,
            'token' => $token
        ], 'Usuario registrado exitosamente', 201);
    }

    /**
     * Login de usuario
     * POST /api/auth/login
     * Body: { email, password }
     */
    public function login() {
        // Obtener datos del request
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar datos requeridos
        if (empty($data['email']) || empty($data['password'])) {
            Response::error('Email y contraseña son requeridos', 400);
        }

        $email = trim($data['email']);
        $password = $data['password'];

        // Buscar usuario por email
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            Response::error('Credenciales inválidas', 401);
        }

        // Verificar contraseña
        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            Response::error('Credenciales inválidas', 401);
        }

        // Actualizar estado a online
        $this->userModel->updateStatus($user['id'], 'online');

        // Generar token JWT
        $tokenData = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ];

        $token = JWT::encode($tokenData);

        // Eliminar campos sensibles
        unset($user['password_hash']);

        Response::success([
            'user' => $user,
            'token' => $token
        ], 'Login exitoso');
    }

    /**
     * Obtener información del usuario autenticado
     * GET /api/auth/me
     * Header: Authorization: Bearer <token>
     */
    public function me() {
        require_once __DIR__ . '/../middleware/auth.php';
        
        $userId = AuthMiddleware::getUserId();

        $user = $this->userModel->findById($userId);

        if (!$user) {
            Response::notFound('Usuario no encontrado');
        }

        Response::success($user);
    }

    /**
     * Logout (marcar usuario como offline)
     * POST /api/auth/logout
     * Header: Authorization: Bearer <token>
     */
    public function logout() {
        require_once __DIR__ . '/../middleware/auth.php';
        
        $userId = AuthMiddleware::getUserId();

        // Actualizar estado a offline
        $this->userModel->updateStatus($userId, 'offline');

        Response::success(null, 'Logout exitoso');
    }

    /**
     * Actualizar perfil del usuario
     * PUT /api/auth/profile
     * Body: { username?, avatar_url? }
     * Header: Authorization: Bearer <token>
     */
    public function updateProfile() {
        require_once __DIR__ . '/../middleware/auth.php';
        
        $userId = AuthMiddleware::getUserId();

        // Obtener datos del request
        $data = json_decode(file_get_contents("php://input"), true);

        // DEBUG: Log received data
        file_put_contents(__DIR__ . '/../../debug_profile.log', "Received data: " . json_encode($data) . "\n", FILE_APPEND);

        // Validar que al menos un campo esté presente
        if (empty($data['username']) && empty($data['avatar_url'])) {
            Response::error('Debes proporcionar al menos un campo para actualizar', 400);
        }

        // Si se está actualizando el username, verificar que no exista
        if (!empty($data['username'])) {
            $username = trim($data['username']);
            
            // Verificar que no sea el mismo username actual
            $currentUser = $this->userModel->findById($userId);
            if ($currentUser['username'] !== $username && $this->userModel->usernameExists($username)) {
                Response::error('El nombre de usuario ya está en uso', 409);
            }
            
            $data['username'] = $username;
        }

        // Actualizar perfil
        $success = $this->userModel->updateProfile($userId, $data);

        if (!$success) {
            Response::serverError('Error al actualizar el perfil');
        }

        // Obtener usuario actualizado
        $user = $this->userModel->findById($userId);
        unset($user['password_hash']);

        Response::success($user, 'Perfil actualizado exitosamente');
    }
}
