<?php
session_start();
require_once __DIR__ . '/../config/config.php';

$error = '';
$success = '';
$login_attempted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_attempted = true;
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            // Query database for user with role information
            $query = "
                SELECT e.E_ID, e.E_Fname, e.E_Username, e.E_Password, e.role_id, r.role_name 
                FROM employee e 
                LEFT JOIN roles r ON e.role_id = r.role_id 
                WHERE e.E_Username = ?
            ";
            
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                $error = 'Database error: ' . $conn->error;
            } else {
                $stmt->bind_param("s", $username);
                
                if (!$stmt->execute()) {
                    $error = 'Database query failed: ' . $stmt->error;
                } else {
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        
                        // Verify password
                        if (password_verify($password, $user['E_Password'])) {
                            // Create session
                            $_SESSION['user'] = $user['E_ID'];
                            $_SESSION['username'] = $user['E_Username'];
                            $_SESSION['name'] = $user['E_Fname'];
                            $_SESSION['role'] = $user['role_name'] ?? 'admin';
                            $_SESSION['role_id'] = $user['role_id'];
                            $_SESSION['last_activity'] = time();

                            // Determine redirect URL based on role
                            $role = strtolower(trim($user['role_name'] ?? 'admin'));
                            $redirect_url = '';
                            
                            switch ($role) {
                                case 'admin':
                                    $redirect_url = '../modules/admin/dashboard.php';
                                    break;
                                case 'pharmacist':
                                    $redirect_url = '../modules/pharmacist/dashboard.php';
                                    break;
                                case 'cashier':
                                    $redirect_url = '../modules/cashier/dashboard.php';
                                    break;
                                default:
                                    $redirect_url = '../modules/admin/dashboard.php';
                            }
                            
                            // Perform redirect
                            header('Location: ' . $redirect_url);
                            exit();
                        } else {
                            $error = 'Invalid username or password.';
                        }
                    } else {
                        $error = 'Invalid username or password.';
                    }
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $error = 'Login error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PHARMACIA Pharmacy Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            text-align: center;
        }

        .logo {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 10px;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 900;
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            font-size: 0.9rem;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            font-size: 0.9rem;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #999;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            padding: 0 10px;
            font-size: 0.85rem;
        }

        .register-link {
            color: #666;
            font-size: 0.9rem;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #764ba2;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            transform: translateX(-5px);
        }

        .demo-info {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: left;
            font-size: 0.85rem;
            color: #555;
        }

        .demo-info strong {
            color: #667eea;
        }

        .demo-info code {
            background: #e8ecff;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #667eea;
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }

            .logo-text {
                font-size: 1.5rem;
            }

            .form-group input {
                padding: 10px 12px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <div class="login-card">
            <div class="logo">
                <i class="fas fa-pills"></i>
            </div>
            <div class="logo-text">PHARMACIA</div>
            <p class="subtitle">Professional Pharmacy Management System</p>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your username"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="divider">
                <span>OR</span>
            </div>

            <p class="register-link">
                Don't have an account? <a href="register.php">Try Demo</a>
            </p>

            <div class="demo-info">
                <strong>Demo Credentials:</strong><br>
                <div style="margin-top: 10px; padding: 10px; background: white; border-radius: 5px;">
                    <div style="margin-bottom: 8px;">
                        <strong style="color: #667eea;">Admin Account:</strong><br>
                        Username: <code>admin</code><br>
                        Password: <code>admin123</code>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong style="color: #667eea;">Pharmacist Account:</strong><br>
                        Username: <code>pharmacist</code><br>
                        Password: <code>pharmacist123</code>
                    </div>
                    <div>
                        <strong style="color: #667eea;">Cashier Account:</strong><br>
                        Username: <code>cashier</code><br>
                        Password: <code>cashier123</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
