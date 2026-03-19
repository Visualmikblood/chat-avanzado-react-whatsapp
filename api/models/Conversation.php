<?php
/**
 * Conversation Model
 * Maneja todas las operaciones relacionadas con conversaciones
 */

class Conversation {
    private $conn;
    private $table = 'conversations';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nueva conversación
     * @param string $type (individual o group)
     * @param array $participantIds IDs de los usuarios participantes
     * @param string|null $name Nombre del grupo (opcional)
     * @return int|false Conversation ID o false si falla
     */
    public function create($type, $participantIds, $name = null) {
        try {
            $this->conn->beginTransaction();

            // Crear la conversación — usando RETURNING id para compatibilidad con PostgreSQL
            $query = "INSERT INTO " . $this->table . " (type, name) VALUES (:type, :name) RETURNING id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':name', $name);

            if (!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $conversationId = $row['id'];

            // Agregar participantes
            $queryParticipants = "INSERT INTO conversation_participants (conversation_id, user_id)
                                 VALUES (:conversation_id, :user_id)";
            $stmtParticipants = $this->conn->prepare($queryParticipants);

            foreach ($participantIds as $userId) {
                $stmtParticipants->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
                $stmtParticipants->bindParam(':user_id', $userId, PDO::PARAM_INT);

                if (!$stmtParticipants->execute()) {
                    $this->conn->rollBack();
                    return false;
                }
            }

            $this->conn->commit();
            return $conversationId;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Conversation Create Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener conversaciones de un usuario
     * @param int $userId
     * @return array
     */
    public function getUserConversations($userId) {
        try {
            $query = "SELECT DISTINCT c.id, c.type, c.name, c.updated_at,
                     (SELECT content FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                     (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time,
                     (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.created_at > COALESCE(cp.last_read_at, '1970-01-01'::timestamp)) as unread_count
                     FROM conversations c
                     INNER JOIN conversation_participants cp ON c.id = cp.conversation_id
                     WHERE cp.user_id = :user_id
                     ORDER BY c.updated_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Para cada conversación, obtener información del otro participante (si es individual)
            foreach ($conversations as &$conv) {
                if ($conv['type'] === 'individual') {
                    $otherUser = $this->getOtherParticipant($conv['id'], $userId);
                    $conv['other_user'] = $otherUser;
                } else {
                    // Para grupos, obtener todos los participantes
                    $conv['participants'] = $this->getParticipants($conv['id']);
                }
            }
            
            return $conversations;
            
        } catch (PDOException $e) {
            error_log("Conversation GetUserConversations Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener el otro participante de una conversación individual
     * @param int $conversationId
     * @param int $currentUserId
     * @return array|null
     */
    public function getOtherParticipant($conversationId, $currentUserId) {
        try {
            $query = "SELECT u.id, u.username, u.email, u.avatar_url, u.status, u.last_seen
                     FROM users u
                     INNER JOIN conversation_participants cp ON u.id = cp.user_id
                     WHERE cp.conversation_id = :conversation_id AND u.id != :current_user_id
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
            $stmt->bindParam(':current_user_id', $currentUserId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Conversation GetOtherParticipant Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener todos los participantes de una conversación
     * @param int $conversationId
     * @return array
     */
    public function getParticipants($conversationId) {
        try {
            $query = "SELECT u.id, u.username, u.email, u.avatar_url, u.status
                     FROM users u
                     INNER JOIN conversation_participants cp ON u.id = cp.user_id
                     WHERE cp.conversation_id = :conversation_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Conversation GetParticipants Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si un usuario es participante de una conversación
     * @param int $conversationId
     * @param int $userId
     * @return bool
     */
    public function isParticipant($conversationId, $userId) {
        try {
            $query = "SELECT id FROM conversation_participants 
                     WHERE conversation_id = :conversation_id AND user_id = :user_id 
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("Conversation IsParticipant Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar conversación individual existente entre dos usuarios
     * @param int $user1Id
     * @param int $user2Id
     * @return int|false Conversation ID o false si no existe
     */
    public function findIndividualConversation($user1Id, $user2Id) {
        try {
            $query = "SELECT c.id 
                     FROM conversations c
                     WHERE c.type = 'individual'
                     AND (
                         SELECT COUNT(*) FROM conversation_participants cp 
                         WHERE cp.conversation_id = c.id 
                         AND cp.user_id IN (:user1_id, :user2_id)
                     ) = 2
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user1_id', $user1Id, PDO::PARAM_INT);
            $stmt->bindParam(':user2_id', $user2Id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : false;
            
        } catch (PDOException $e) {
            error_log("Conversation FindIndividualConversation Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar conversación como leída
     * @param int $conversationId
     * @param int $userId
     * @return bool
     */
    public function markAsRead($conversationId, $userId) {
        try {
            $query = "UPDATE conversation_participants 
                     SET last_read_at = NOW() 
                     WHERE conversation_id = :conversation_id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Conversation MarkAsRead Error: " . $e->getMessage());
            return false;
        }
    }
}
