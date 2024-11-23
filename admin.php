<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

// Fetch all orders with user and menu item details
try {
    $stmt = $conn->prepare("
        SELECT 
            o.id as order_id,
            o.quantity,
            o.status,
            o.created_at,
            u.name as user_name,
            u.email as user_email,
            m.name as item_name,
            m.price
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN menu_items m ON o.menu_item_id = m.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    $error = "An error occurred while fetching orders.";
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        header("Location: admin.php");
        exit();
    } catch(PDOException $e) {
        error_log("Error updating order status: " . $e->getMessage());
        $error = "Failed to update order status.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .admin-container {
            padding-top: var(--navbar-height);
            min-height: 100vh;
            background: var(--light-pink-color);
        }
        
        .order-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.2s;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
        }
        
        .status-pending { color: #ffc107; }
        .status-processing { color: #0dcaf0; }
        .status-completed { color: #198754; }
        .status-cancelled { color: #dc3545; }
        
        .admin-title {
            color: var(--dark-color);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <div class="container mt-4">
            <div class="row mb-4">
                <div class="col">
                    <h2 class="admin-title">Order Management</h2>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($orders as $order): ?>
                    <div class="col-12 mb-4">
                        <div class="order-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5>Order #<?php echo $order['order_id']; ?></h5>
                                    <p class="mb-1"><strong>Customer:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['user_email']); ?></p>
                                    <p class="mb-1"><strong>Item:</strong> <?php echo htmlspecialchars($order['item_name']); ?></p>
                                    <p class="mb-1"><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
                                    <p class="mb-1"><strong>Total:</strong> LKR <?php echo number_format($order['quantity'] * $order['price'], 2); ?></p>
                                    <p class="mb-1"><strong>Date:</strong> <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
                                </div>
                                <span class="badge bg-<?php 
                                    echo match($order['status']) {
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            
                            <form action="admin.php" method="POST" class="order-status-form">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <select name="status" class="form-select">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 