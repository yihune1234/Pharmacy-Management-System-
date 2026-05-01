<?php
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');

    if ($name && $email) {
        // Create guest session with demo access to all roles
        $_SESSION['guest_mode'] = true;
        $_SESSION['guest_name'] = $name;
        $_SESSION['guest_email'] = $email;
        $_SESSION['guest_roles'] = ['admin', 'pharmacist', 'cashier'];
        $_SESSION['current_role'] = 'admin'; // Default to admin view
        $_SESSION['guest_login_time'] = time();

        $success = 'Welcome! You now have access to all features as a demo user.';
        
        // Redirect to demo dashboard
        header("Location: guest-dashboard.php");
        exit();
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Access - PHARMACIA Pharmacy Management System</title>
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
            padding: 20px;
        }

        .register-container {
            width: 100%;
            max-width: 500px;
        }

        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
        }

        .logo {
            font-size: 2.5rem;
            color: #667eea;
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 900;
            color: #333;
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #666;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 10px;
        }

        .badge {
            display: inline-block;
            background: #e3f2fd;
            color: #667eea;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            width: 100%;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
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

        .btn-register {
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

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
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

        .login-link {
            color: #666;
            font-size: 0.9rem;
            text-align: center;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
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

        .features-box {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #555;
        }

        .features-box strong {
            color: #667eea;
            display: block;
            margin-bottom: 10px;
        }

        .features-list {
            list-style: none;
        }

        .features-list li {
            padding: 5px 0;
        }

        .features-list li:before {
            content: "✓ ";
            color: #667eea;
            font-weight: bold;
            margin-right: 8px;
        }

        @media (max-width: 480px) {
            .register-card {
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
    <div class="register-container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <div class="register-card">
            <div class="logo">
                <i class="fas fa-eye"></i>
            </div>
            <div class="logo-text">PHARMACIA</div>
            <p class="subtitle">Try All Features - Demo Access</p>
            <div class="badge">
                <i class="fas fa-star"></i> Full Access to All Roles
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Your Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            placeholder="Enter your name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="your@email.com"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-register">
                        <i class="fas fa-play-circle"></i> Start Demo
                    </button>
                </form>

                <div class="divider">
                    <span>OR</span>
                </div>

                <p class="login-link">
                    Have an account? <a href="login.php">Login here</a>
                </p>

                <div class="features-box">
                    <strong><i class="fas fa-unlock"></i> Demo Access Includes:</strong>
                    <ul class="features-list">
                        <li>Admin Dashboard & Controls</li>
                        <li>Pharmacist Features</li>
                        <li>Cashier POS System</li>
                        <li>Inventory Management</li>
                        <li>Sales & Reporting</li>
                        <li>Customer Management</li>
                        <li>All Advanced Features</li>
                        <li>Switch Between All Roles</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
