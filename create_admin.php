<?php
require_once 'config.php';

try {
    // Generate a new password hash
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Delete existing admin
    $stmt = $conn->prepare("DELETE FROM users WHERE username = 'admin'");
    $stmt->execute();
    
    // Create new admin
    $stmt = $conn->prepare("
        INSERT INTO users (name, username, email, password, is_admin) 
        VALUES ('Admin User', 'admin', 'admin@coffee.com', ?, TRUE)
    ");
    $stmt->execute([$hash]);
    
    echo "Admin user created successfully with hash: " . $hash;
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 