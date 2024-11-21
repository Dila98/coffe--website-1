<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $menu_item_id = filter_input(INPUT_POST, 'menu_item_id', FILTER_SANITIZE_NUMBER_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
        $user_id = $_SESSION['user_id'];

        // Validate inputs
        if (!$menu_item_id || !$quantity || $quantity < 1) {
            throw new Exception("Invalid order data");
        }

        // Begin transaction
        $conn->beginTransaction();

        // Check if menu item exists and is available
        $check_item = $conn->prepare("SELECT id, price FROM menu_items WHERE id = ? AND is_available = 1");
        $check_item->execute([$menu_item_id]);
        $item = $check_item->fetch();

        if (!$item) {
            throw new Exception("Item not available");
        }

        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, menu_item_id, quantity, status) 
            VALUES (?, ?, ?, 'pending')
        ");
        
        $stmt->execute([$user_id, $menu_item_id, $quantity]);
        $conn->commit();

        $_SESSION['order_success'] = "Order placed successfully!";
        header('Location: account.php');
        exit();
    }
} catch(Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Order Error: " . $e->getMessage());
    $_SESSION['order_error'] = "Sorry, there was a problem processing your order. Please try again.";
    header('Location: index.php#menu');
    exit();
}
?>