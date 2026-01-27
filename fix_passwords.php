<?php
require 'api/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "Connected successfully\n";
    
    // Generar hash válido para 'password123'
    $newHash = password_hash('password123', PASSWORD_BCRYPT);
    echo "New valid hash generated\n";
    
    // Actualizar todos los usuarios
    $sql = "UPDATE users SET password_hash = :hash";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':hash', $newHash);
    
    if ($stmt->execute()) {
        echo "Successfully updated passwords for all users to 'password123'\n";
    } else {
        echo "Failed to update passwords\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
