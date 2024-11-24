<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    try {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        $_SESSION['success_message'] = "Order status updated successfully!";
        header('Location: admin.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error_message'] = "Failed to update order status.";
    }
}

// Fetch all orders with user details
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
            m.price,
            m.image_path
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN menu_items m ON o.menu_item_id = m.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .admin-container {
            padding-top: 80px;
        }
        .order-card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-5px);
        }
        .search-container {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container admin-container">
        <h1 class="mb-4">Admin Dashboard</h1>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="search-container bg-light p-3 rounded">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" id="orderSearch" class="form-control" placeholder="Search orders...">
                </div>
                <div class="col-md-6">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="row">
            <?php foreach ($orders as $order): ?>
                <div class="col-md-6 mb-4">
                    <div class="card order-card">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="images/<?php echo htmlspecialchars($order['image_path']); ?>" 
                                     class="img-fluid rounded-start" 
                                     alt="<?php echo htmlspecialchars($order['item_name']); ?>">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title">Order #<?php echo $order['order_id']; ?></h5>
                                        <span class="badge bg-<?php 
                                            echo match($order['status']) {
                                                'completed' => 'success',
                                                'processing' => 'warning',
                                                'cancelled' => 'danger',
                                                default => 'info'
                                            };
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?>
                                        </small>
                                    </p>
                                    <p class="card-text"><strong>Customer:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
                                    <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($order['user_email']); ?></p>
                                    <p class="card-text"><strong>Item:</strong> <?php echo htmlspecialchars($order['item_name']); ?></p>
                                    <p class="card-text"><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
                                    <p class="card-text"><strong>Total:</strong> LKR <?php echo number_format($order['price'] * $order['quantity'], 2); ?></p>
                                    
                                    <!-- Status Update Form -->
                                    <form action="admin.php" method="POST" class="mt-3">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <div class="input-group">
                                            <select name="status" class="form-select">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary">Update Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('orderSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterOrders(searchTerm, document.getElementById('statusFilter').value);
        });

        // Status filter functionality
        document.getElementById('statusFilter').addEventListener('change', function(e) {
            const searchTerm = document.getElementById('orderSearch').value.toLowerCase();
            filterOrders(searchTerm, e.target.value);
        });

        function filterOrders(searchTerm, statusFilter) {
            const orders = document.querySelectorAll('.order-card');
            
            orders.forEach(order => {
                const orderText = order.textContent.toLowerCase();
                const orderStatus = order.querySelector('.badge').textContent.toLowerCase();
                
                const matchesSearch = orderText.includes(searchTerm);
                const matchesStatus = !statusFilter || orderStatus === statusFilter.toLowerCase();
                
                order.closest('.col-md-6').style.display = 
                    (matchesSearch && matchesStatus) ? 'block' : 'none';
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 