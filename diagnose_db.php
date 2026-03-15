<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'api/config/database.php';

try {
    echo "Testing connection...\n";
    $db = new Database();
    
    // Explicitly try PDO to see exact error
    $host = '127.0.0.1'; // Changed from localhost
    $db_name = 'chat_app';
    $username = 'root';
    $password = '';
    
    // Check if .env values are being loaded
    $envFile = __DIR__ . '/api/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($key, $value) = explode('=', $line, 2);
            if (trim($key) == 'DB_HOST') $host = trim($value);
            if (trim($key) == 'DB_NAME') $db_name = trim($value);
            if (trim($key) == 'DB_USER') $username = trim($value);
            if (trim($key) == 'DB_PASS') $password = trim($value);
        }
    }
    
    echo "Parameters: host=$host, db=$db_name, user=$username, pass=" . ($password ? '***' : '(empty)') . "\n";
    
    $dsn = "mysql:host=$host;charset=utf8mb4"; // Connect without DB first
    $pdo = new PDO($dsn, $username, $password);
    echo "Connection to host successful!\n";
    
    $stmt = $pdo->query("SHOW DATABASES LIKE '$db_name'");
    if ($stmt->fetch()) {
        echo "Database '$db_name' EXISTS.\n";
        $pdo->query("USE $db_name");
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->fetch()) {
            echo "Table 'users' EXISTS.\n";
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "Total users: " . $row['count'] . "\n";
            
            $stmt = $pdo->query("SELECT id, username, email FROM users");
            while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo " - " . $user['username'] . " (" . $user['email'] . ")\n";
            }
        } else {
            echo "Table 'users' DOES NOT EXIST.\n";
        }
    } else {
        echo "Database '$db_name' DOES NOT EXIST.\n";
    }

} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
