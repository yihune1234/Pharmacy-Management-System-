<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHARMACIA - Professional Pharmacy Management System</title>
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
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 20px;
            text-align: center;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.95;
        }

        .btn-primary {
            background: white;
            color: #667eea;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .btn-secondary:hover {
            background: white;
            color: #667eea;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin: 20px;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .feature-card i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        .section {
            padding: 80px 20px;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 50px;
            color: #333;
        }

        .container-max {
            max-width: 1200px;
            margin: 0 auto;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        footer {
            background: #1a1a1a;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 900;
            color: #667eea;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: #333;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .auth-buttons {
            display: flex;
            gap: 15px;
        }

        .btn-login {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        .btn-register {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background: #667eea;
            color: white;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .stat-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 900;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            margin-top: 10px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .nav-links {
                flex-direction: column;
                gap: 15px;
            }

            .section-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container-max">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 0;">
                <a href="index.php" class="logo">
                    <i class="fas fa-pills"></i> PHARMACIA
                </a>
                
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="about.php">About</a>
                    <a href="features.php">Features</a>
                    <a href="contact.php">Contact</a>
                </div>

                <div class="auth-buttons">
                    <a href="login.php" class="btn-login">Login</a>
                    <a href="register.php" class="btn-register">Try Demo</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container-max">
            <h1>Professional Pharmacy Management</h1>
            <p>Complete solution for inventory, sales, and customer management</p>
            <div>
                <a href="register.php" class="btn-primary">Get Started</a>
                <a href="about.php" class="btn-secondary">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section" style="background: #f8f9fa;">
        <div class="container-max">
            <h2 class="section-title">Why Choose PHARMACIA?</h2>
            <div class="grid-3">
                <div class="feature-card">
                    <i class="fas fa-lock"></i>
                    <h3>Enterprise Security</h3>
                    <p>Two-factor authentication, data encryption, and comprehensive audit logging for complete peace of mind.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-tachometer-alt"></i>
                    <h3>Lightning Fast</h3>
                    <p>Optimized database queries and intelligent caching deliver 80% faster performance.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Advanced Analytics</h3>
                    <p>Real-time dashboards, profit & loss reports, and revenue trend analysis.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-pills"></i>
                    <h3>Pharmacy Features</h3>
                    <p>Drug interaction checker, expiry management, and prescription tracking.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h3>Role-Based Access</h3>
                    <p>Admin, Pharmacist, and Cashier roles with customized permissions.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bell"></i>
                    <h3>Smart Alerts</h3>
                    <p>Real-time notifications for low stock, expiry dates, and system events.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="section">
        <div class="container-max">
            <h2 class="section-title">By The Numbers</h2>
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-number">10,000+</div>
                    <div class="stat-label">Lines of Code</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">33</div>
                    <div class="stat-label">Features</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">80%</div>
                    <div class="stat-label">Faster Performance</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support Ready</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center;">
        <div class="container-max">
            <h2 style="font-size: 2.5rem; font-weight: 900; margin-bottom: 20px;">Ready to Transform Your Pharmacy?</h2>
            <p style="font-size: 1.1rem; margin-bottom: 30px; opacity: 0.95;">Join hundreds of pharmacies using PHARMACIA for efficient management.</p>
            <a href="register.php" class="btn-primary">Start Your Free Trial</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container-max">
            <p>&copy; 2024 PHARMACIA. All rights reserved.</p>
            <p style="margin-top: 10px; opacity: 0.7;">Professional Pharmacy Management System</p>
        </div>
    </footer>
</body>
</html>
