<?php
$db_host = 'localhost';
$db_name = 'coffee_shop';
$db_user = 'root';
$db_pass = '';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    error_log("Database connection successful");
} catch(PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}
?> 