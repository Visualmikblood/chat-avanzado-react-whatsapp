<?php
require 'api/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "Connected successfully\n";
    
    $stmt = $conn->query("SELECT id, username, email, password_hash FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total users found: " . count($users) . "\n";
    
    foreach ($users as $user) {
        echo "User: " . $user['username'] . " | Email: " . $user['email'] . "\n";
        echo "Hash: " . substr($user['password_hash'], 0, 20) . "...\n";
        
        // Test verify 'password123'
        $verify = password_verify('password123', $user['password_hash']) ? 'VALID' : 'INVALID';
        echo "Password check ('password123'): " . $verify . "\n\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
