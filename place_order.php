<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Get form data
        $user_id = $_SESSION['user_id'];
        $menu_item_id = filter_input(INPUT_POST, 'menu_item_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        $unit_price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        
        // Calculate subtotal
        $subtotal = $quantity * $unit_price;

        // Create new order
        $order_stmt = $conn->prepare("
            INSERT INTO orders (user_id, total_amount, status) 
            VALUES (?, ?, 'pending')
        ");
        $order_stmt->execute([$user_id, $subtotal]);
        $order_id = $conn->lastInsertId();

        // Create order item
        $order_item_stmt = $conn->prepare("
            INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, subtotal) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $order_item_stmt->execute([
            $order_id,
            $menu_item_id,
            $quantity,
            $unit_price,
            $subtotal
        ]);

        $conn->commit();
        header('Location: account.php?order_success=1');
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        error_log($e->getMessage());
        header('Location: index.php?error=order_failed');
        exit();
    }
}
?> 