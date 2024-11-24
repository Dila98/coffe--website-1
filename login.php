<?php
session_start();
require_once 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    error_log("Login attempt - Username: " . $username);
    error_log("Password (length): " . strlen($password));

    try {
        $stmt = $conn->prepare("
            SELECT * FROM users 
            WHERE username = :username OR email = :email
        ");
        
        $stmt->execute([
            ':username' => $username,
            ':email' => $username
        ]);
        
        $user = $stmt->fetch();

        error_log("User found: " . ($user ? 'Yes' : 'No'));
        if ($user) {
            error_log("User ID: " . $user['id']);
            error_log("Username: " . $user['username']);
            error_log("Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No'));
            error_log("Stored Password Hash: " . $user['password']);
            error_log("Password Verification Result: " . (password_verify($password, $user['password']) ? 'True' : 'False'));
        }
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            
            error_log("Login successful - Session data: " . print_r($_SESSION, true));
            
            if ($_SESSION['is_admin']) {
                error_log("Redirecting to admin.php");
                header("Location: admin.php");
                exit();
            } else {
                error_log("Redirecting to index.php");
                header("Location: index.php");
                exit();
            }
        } else {
            $error = "Invalid username/email or password!";
            error_log("Login failed - Invalid credentials");
        }
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $error = "An error occurred. Please try again later.";
    }
}

try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn();

    if (!$adminExists) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        error_log("Creating admin user with password hash: " . $adminPassword);
        
        $stmt = $conn->prepare("
            INSERT INTO users (name, username, email, password, is_admin) 
            VALUES ('Admin User', 'admin', 'admin@coffee.com', ?, TRUE)
        ");
        $stmt->execute([$adminPassword]);
        error_log("Admin user created successfully");
    }
} catch(PDOException $e) {
    error_log("Error checking/creating admin user: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: url('images/pexels-chuck-2149537.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 3rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .btn-login {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .form-label {
            color: #fff;
            font-weight: 500;
            margin-bottom: 8px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .text-warning {
            color: #fff !important;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .text-warning:hover {
            color: rgba(255, 255, 255, 0.8) !important;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        .login-container p {
            color: white;
        }

        .alert {
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 20px;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
        }

        .alert .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .alert .btn-close:hover {
            opacity: 1;
        }

        .alert-dismissible .btn-close {
            padding: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center">
        <div class="login-container">
            <h2 class="text-center mb-4">Welcome Back!</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-login btn-lg w-100">Login</button>
            </form>
            
            <p class="text-center mt-3">Don't have an account? <a href="signup.php" class="text-warning fw-bold">Sign up here</a></p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 