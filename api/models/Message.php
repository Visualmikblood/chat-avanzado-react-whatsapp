<?php
/**
 * Message Model
 * Maneja todas las operaciones relacionadas con mensajes
 */

class Message {
    private $conn;
    private $table = 'messages';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nuevo mensaje
     * @param int $conversationId
     * @param int $senderId
     * @param string $content
     * @param string $type (text, image, file)
     * @return array|false Mensaje creado o false si falla
     */
    public function create($conversationId, $senderId, $content, $type = 'text') {
        try {
            $query = "INSERT INTO " . $this->table . " 
                     (conversation_id, sender_id, content, type) 
                     VALUES (:conversation_id, :sender_id, :content, :type)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
            $stmt->bindParam(':sender_id', $senderId, PDO::PARAM_INT);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':type', $type);
            
            if ($stmt->execute()) {
                $messageId = $this->conn->lastInsertId();
                
                // Retornar el mensaje completo con todos los campos
                return $this->findById($messageId);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Message Create Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener mensaje por ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        try {
            $query = "SELECT m.*, u.username as sender_username, u.avatar_url as sender_avatar 
                     FROM " . $this->table . " m
                     INNER JOIN users u ON m.sender_id = u.id
                     WHERE m.id = :id LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Message FindById Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener mensajes de una conversación
     * @param int $conversationId
     * @param int $limit Cantidad de mensajes a obtener
     * @param int $offset Offset para paginación
     * @return array
     */
    public function getByConversation($conversationId, $limit = 50, $offset = 0) {
        try {
            $query = "SELECT m.*, u.username as sender_username, u.avatar_url as sender_avatar 
                     FROM " . $this->table . " m
                     INNER JOIN users u ON m.sender_id = u.id
                     WHERE m.conversation_id = :conversation_id
                     ORDER BY m.created_at DESC
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Invertir el orden para que los más recientes estén al final
            return array_reverse($messages);
        } catch (PDOException $e) {
            error_log("Message GetByConversation Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Eliminar mensaje
     * @param int $id
     * @param int $userId ID del usuario (para verificar que sea el autor)
     * @return bool
     */
    public function delete($id, $userId) {
        try {
            $query = "DELETE FROM " . $this->table . " 
                     WHERE id = :id AND sender_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Message Delete Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Editar mensaje
     * @param int $id
     * @param int $userId ID del usuario (para verificar que sea el autor)
     * @param string $newContent
     * @return bool
     */
    public function update($id, $userId, $newContent) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET content = :content, is_edited = 1 
                     WHERE id = :id AND sender_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':content', $newContent);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Message Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar mensaje como entregado
     * @param int $id
     * @return bool
     */
    public function markAsDelivered($id) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET delivered_at = NOW() 
                     WHERE id = :id AND delivered_at IS NULL";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Message MarkAsDelivered Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar mensaje como leído
     * @param int $id
     * @return bool
     */
    public function markAsRead($id) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET read_at = NOW(), delivered_at = COALESCE(delivered_at, NOW())
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Message MarkAsRead Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar todos los mensajes de una conversación como leídos
     * @param int $conversationId
     * @param int $userId ID del usuario que lee (para no marcar sus propios mensajes)
     * @return bool
     */
    public function markConversationAsRead($conversationId, $userId) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET read_at = NOW(), delivered_at = COALESCE(delivered_at, NOW())
                     WHERE conversation_id = :conversation_id 
                     AND sender_id != :user_id 
                     AND read_at IS NULL";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Message MarkConversationAsRead Error: " . $e->getMessage());
            return false;
        }
    }
}
