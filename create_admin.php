<?php
require_once 'config.php';

// Change these values for your admin account
$admin_name = "Admin User";
$username = 'admin';
$password = 'admin123';
$email = 'admin@coffee.com';

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);
    
    if ($check->rowCount() > 0) {
        echo "Admin account already exists!";
    } else {
        // Create admin user
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, is_admin) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$admin_name, $username, $email, $hash]);
        echo "Admin user created successfully!<br>";
        echo "Username: " . $username . "<br>";
        echo "Password: " . $password . "<br>";
        echo "<a href='login.php'>Go to Login</a>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 