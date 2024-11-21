<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=coffee_shop", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the form data
        $menu_item_id = filter_input(INPUT_POST, 'menu_item_id', FILTER_SANITIZE_NUMBER_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
        $user_id = $_SESSION['user_id'];

        // Validate the data
        if (!$menu_item_id || !$quantity || $quantity < 1) {
            throw new Exception("Invalid order data");
        }

        // Insert the order
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, menu_item_id, quantity, status) 
            VALUES (?, ?, ?, 'pending')
        ");
        
        $stmt->execute([$user_id, $menu_item_id, $quantity]);

        // Set success message
        $_SESSION['order_success'] = "Order placed successfully!";
        
        // Redirect to account page
        header('Location: account.php');
        exit();

    } else {
        // If not POST request, redirect to menu
        header('Location: index.php#menu');
        exit();
    }

} catch(Exception $e) {
    // Log the error
    error_log("Order Error: " . $e->getMessage());
    
    // Set error message
    $_SESSION['order_error'] = "Sorry, there was a problem processing your order. Please try again.";
    
    // Redirect back to menu
    header('Location: index.php#menu');
    exit();
}
?>