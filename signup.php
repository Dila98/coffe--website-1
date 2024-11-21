<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Processing signup form submission");
    
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if username or email already exists
        $check_query = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_query->execute([$username, $email]);
        
        if ($check_query->rowCount() > 0) {
            $error = "Username or email already exists!";
            error_log("Signup failed: Username or email already exists");
        } else {
            // Insert new user
            $insert_query = $conn->prepare("INSERT INTO users (name, email, username, password) VALUES (?, ?, ?, ?)");
            $success = $insert_query->execute([$name, $email, $username, $hashed_password]);
            
            if ($success) {
                // Get the new user's ID
                $user_id = $conn->lastInsertId();
                error_log("New user created with ID: " . $user_id);

                // Set session variables
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['name'] = $name;
                
                error_log("Session variables set: " . print_r($_SESSION, true));
                
                // Redirect with exit
                header("Location: index.php");
                exit();
            } else {
                error_log("Failed to insert new user");
                $error = "Failed to create account. Please try again.";
            }
        }
    } catch(PDOException $e) {
        error_log("Signup error: " . $e->getMessage());
        $error = "An error occurred. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
            max-width: 500px;
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

        /* For Webkit browsers (Chrome, Safari) */
        ::-webkit-input-placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        /* For Firefox */
        ::-moz-placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .login-container p {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center">
        <div class="login-container">
            <h2 class="text-center mb-4">Create Account</h2>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form action="signup.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-login btn-lg w-100">Sign Up</button>
            </form>
            
            <p class="text-center mt-3">Already have an account? <a href="login.php" class="text-warning fw-bold">Login here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
