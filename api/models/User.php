<?php
/**
 * User Model
 * Maneja todas las operaciones relacionadas con usuarios
 */

class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear nuevo usuario
     * @param string $username
     * @param string $email
     * @param string $password
     * @return int|false User ID o false si falla
     */
    public function create($username, $email, $password) {
        try {
            $query = "INSERT INTO " . $this->table . "
                     (username, email, password_hash)
                     VALUES (:username, :email, :password_hash) RETURNING id";

            $stmt = $this->conn->prepare($query);

            // Hash de la contraseña
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $password_hash);

            if ($stmt->execute()) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row['id'];
            }

            return false;
        } catch (PDOException $e) {
            error_log("User Create Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar usuario por email
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email) {
        try {
            $query = "SELECT id, username, email, password_hash, avatar_url, status, created_at 
                     FROM " . $this->table . " 
                     WHERE email = :email LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("User FindByEmail Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar usuario por ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        try {
            $query = "SELECT id, username, email, bio, avatar_url, status, created_at, last_seen 
                     FROM " . $this->table . " 
                     WHERE id = :id LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("User FindById Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si el email ya existe
     * @param string $email
     * @return bool
     */
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Verificar si el username ya existe
     * @param string $username
     * @return bool
     */
    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Verificar contraseña
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Actualizar estado del usuario
     * @param int $userId
     * @param string $status (online, offline, away)
     * @return bool
     */
    public function updateStatus($userId, $status) {
        try {
            $query = "UPDATE " . $this->table . " 
                     SET status = :status, last_seen = NOW() 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User UpdateStatus Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar usuarios (para agregar a conversaciones)
     * @param string $searchTerm
     * @param int $currentUserId ID del usuario actual (para excluirlo)
     * @return array
     */
    public function search($searchTerm, $currentUserId = null) {
        try {
            $query = "SELECT id, username, email, avatar_url, status 
                     FROM " . $this->table . " 
                     WHERE (username LIKE :search_user OR email LIKE :search_email)";
            
            if ($currentUserId !== null) {
                $query .= " AND id != :currentUserId";
            }
            
            $query .= " LIMIT 20";
            
            $stmt = $this->conn->prepare($query);
            
            $searchParam = "%" . $searchTerm . "%";
            $stmt->bindParam(':search_user', $searchParam);
            $stmt->bindParam(':search_email', $searchParam);
            
            // DEBUG: Log before execution
            file_put_contents(__DIR__ . '/../../debug_search_sql.log', "Executing query for: [$searchParam]\n", FILE_APPEND);
            
            if ($currentUserId !== null) {
                $stmt->bindParam(':currentUserId', $currentUserId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // DEBUG: Log results count
            file_put_contents(__DIR__ . '/../../debug_search_sql.log', "Found: " . count($results) . " results\n", FILE_APPEND);
            
            return $results;
        } catch (PDOException $e) {
            // DEBUG: Log error
            file_put_contents(__DIR__ . '/../../debug_search_sql.log', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            error_log("User Search Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar perfil de usuario
     * @param int $userId
     * @param array $data (username, bio, avatar_url)
     * @return bool
     */
    public function updateProfile($userId, $data) {
        try {
            $fields = [];
            $params = [':id' => $userId];

            if (isset($data['username'])) {
                $fields[] = "username = :username";
                $params[':username'] = $data['username'];
            }

            if (isset($data['bio'])) {
                $fields[] = "bio = :bio";
                $params[':bio'] = $data['bio'];
            }

            if (isset($data['avatar_url'])) {
                $fields[] = "avatar_url = :avatar_url";
                $params[':avatar_url'] = $data['avatar_url'];
            }

            if (empty($fields)) {
                return false;
            }

            $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("User UpdateProfile Error: " . $e->getMessage());
            return false;
        }
    }
}
