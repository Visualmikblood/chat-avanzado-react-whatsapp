-- ============================================
-- CHAT APPLICATION - DATABASE SCHEMA
-- ============================================
-- Diseñado para soportar chats individuales
-- Optimizado con índices para consultas rápidas
-- ============================================

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS chat_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE chat_app;

-- ============================================
-- TABLA: users
-- Almacena información de usuarios
-- ============================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(255) DEFAULT NULL,
    status ENUM('online', 'offline', 'away') DEFAULT 'offline',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: conversations
-- Almacena las conversaciones (individuales o grupos)
-- ============================================
CREATE TABLE conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('individual', 'group') DEFAULT 'individual',
    name VARCHAR(100) DEFAULT NULL,  -- Solo para grupos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: conversation_participants
-- Relación muchos a muchos entre usuarios y conversaciones
-- ============================================
CREATE TABLE conversation_participants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_read_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant (conversation_id, user_id),
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: messages
-- Almacena todos los mensajes
-- ============================================
CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    type ENUM('text', 'image', 'file') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_edited BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_created_at (created_at),
    INDEX idx_conversation_created (conversation_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS DE PRUEBA (OPCIONAL - PARA DESARROLLO)
-- ============================================

-- Insertar usuarios de prueba
-- Contraseña para todos: "password123" (hash generado con password_hash en PHP)
INSERT INTO users (username, email, password_hash, status) VALUES
('alice', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'offline'),
('bob', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'offline'),
('charlie', 'charlie@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'offline');

-- Crear una conversación individual entre Alice y Bob
INSERT INTO conversations (type) VALUES ('individual');

-- Agregar participantes a la conversación
INSERT INTO conversation_participants (conversation_id, user_id) VALUES
(1, 1),  -- Alice
(1, 2);  -- Bob

-- Insertar mensajes de prueba
INSERT INTO messages (conversation_id, sender_id, content) VALUES
(1, 1, '¡Hola Bob! ¿Cómo estás?'),
(1, 2, 'Hola Alice, muy bien gracias. ¿Y tú?'),
(1, 1, 'Excelente! ¿Te gustaría probar esta nueva app de chat?'),
(1, 2, '¡Claro! Se ve increíble 🚀');

-- ============================================
-- CONSULTAS ÚTILES PARA VERIFICACIÓN
-- ============================================

-- Ver todos los usuarios
-- SELECT * FROM users;

-- Ver conversaciones con sus participantes
-- SELECT c.id, c.type, u.username 
-- FROM conversations c
-- INNER JOIN conversation_participants cp ON c.id = cp.conversation_id
-- INNER JOIN users u ON cp.user_id = u.id;

-- Ver mensajes de una conversación específica
-- SELECT m.id, u.username as sender, m.content, m.created_at
-- FROM messages m
-- INNER JOIN users u ON m.sender_id = u.id
-- WHERE m.conversation_id = 1
-- ORDER BY m.created_at ASC;
