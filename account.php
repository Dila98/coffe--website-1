<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    // Fetch user details
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Add debugging for user ID
    error_log("Checking orders for user_id: " . $_SESSION['user_id']);

    // Fetch user orders with menu item details
    $stmt = $conn->prepare("
        SELECT 
            o.id as order_id,
            o.quantity,
            o.status,
            o.created_at,
            m.name as item_name,
            m.price,
            m.image_path,
            c.name as category_name
        FROM orders o
        JOIN menu_items m ON o.menu_item_id = m.id
        JOIN categories c ON m.category_id = c.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Add debugging for query results
    $orders = $stmt->fetchAll();
    error_log("Number of orders found: " . count($orders));
    
    // If no orders found, let's check if the order exists at all
    if (empty($orders)) {
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $check_stmt->execute([$_SESSION['user_id']]);
        $order_count = $check_stmt->fetchColumn();
        error_log("Raw orders count in database: " . $order_count);
    }

} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    $error = "An error occurred. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Coffee Website</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header / Navbar -->
    <header>
        <nav class="navbar">
            <a href="index.php" class="nav-logo">
                <h2 class="logo-text">☕ Coffee</h2>
            </a>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">Logout</a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="account-container">
            <h2 class="section-title text-center">My Account</h2>
            
            <?php if (isset($_SESSION['order_success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['order_success'];
                        unset($_SESSION['order_success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['order_error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['order_error'];
                        unset($_SESSION['order_error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php else: ?>
                <div class="user-info">
                    <h3 class="h5 mb-3">Account Information</h3>
                    <p class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="mb-0"><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                </div>

                <div class="order-history">
                    <h3 class="h5 mb-3">Order History</h3>
                    <?php if (empty($orders)): ?>
                        <p class="text-muted">No orders yet.</p>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach ($orders as $order): ?>
                                <div class="order-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <?php if ($order['image_path']): ?>
                                                <img src="images/<?php echo htmlspecialchars($order['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($order['item_name']); ?>"
                                                     class="img-fluid rounded">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h4 class="h6 mb-1"><?php echo htmlspecialchars($order['item_name']); ?></h4>
                                                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($order['category_name']); ?></p>
                                                    <p class="mb-1">Status: <span class="badge bg-<?php echo $order['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span></p>
                                                </div>
                                                <span class="badge bg-secondary">Order #<?php echo $order['order_id']; ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <div>
                                                    <p class="mb-0">Quantity: <?php echo $order['quantity']; ?></p>
                                                    <p class="mb-0">Price per item: LKR<?php echo number_format($order['price'], 2); ?></p>
                                                    <p class="mb-0"><strong>Total: LKR <?php echo number_format($order['quantity'] * $order['price'], 2); ?></strong></p>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html> 