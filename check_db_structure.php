<?php
require 'api/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "=== ESTRUCTURA DE LA TABLA users ===\n";
    $stmt = $conn->query("DESCRIBE users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    echo "\n=== DATOS DEL USUARIO alice ===\n";
    $stmt = $conn->query("SELECT id, username, bio, avatar_url FROM users WHERE username = 'alice'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($user);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
