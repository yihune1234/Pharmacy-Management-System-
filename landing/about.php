<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About PHARMACIA - Pharmacy Management System</title>
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
            background: #f8f9fa;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        .section {
            padding: 60px 20px;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 900;
            color: #333;
            margin-bottom: 30px;
        }

        .container-max {
            max-width: 1000px;
            margin: 0 auto;
        }

        .content-block {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            line-height: 1.8;
        }

        .content-block h3 {
            color: #667eea;
            font-size: 1.5rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .content-block p {
            color: #555;
            margin-bottom: 15px;
        }

        .feature-list {
            list-style: none;
            margin: 20px 0;
        }

        .feature-list li {
            padding: 10px 0;
            color: #555;
            border-bottom: 1px solid #eee;
        }

        .feature-list li:before {
            content: "✓ ";
            color: #667eea;
            font-weight: bold;
            margin-right: 10px;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 30px 0;
        }

        .team-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .team-card i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }

        .team-card h4 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .team-card p {
            color: #666;
            font-size: 0.9rem;
        }

        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline-item {
            padding: 20px;
            margin-bottom: 20px;
            background: white;
            border-left: 4px solid #667eea;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .timeline-item h4 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .timeline-item p {
            color: #666;
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

        footer {
            background: #1a1a1a;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.8rem;
            }

            .nav-links {
                flex-direction: column;
                gap: 15px;
            }

            .content-block {
                padding: 20px;
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
                    <a href="register.php" class="btn-register">Register as Guest</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container-max">
            <h1>About PHARMACIA</h1>
            <p>Transforming Pharmacy Management with Technology</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="section">
        <div class="container-max">
            <div class="content-block">
                <h3>Our Mission</h3>
                <p>
                    PHARMACIA is dedicated to revolutionizing pharmacy management through innovative technology solutions. 
                    We believe that every pharmacy, regardless of size, deserves access to enterprise-grade management tools 
                    that are secure, fast, and easy to use.
                </p>
                <p>
                    Our mission is to empower pharmacists and pharmacy staff with the tools they need to focus on what matters most: 
                    patient care and business growth.
                </p>
            </div>

            <div class="content-block">
                <h3>Our Vision</h3>
                <p>
                    To be the leading pharmacy management system trusted by thousands of pharmacies worldwide, 
                    setting the standard for security, performance, and user experience in the industry.
                </p>
            </div>

            <div class="content-block">
                <h3>Core Values</h3>
                <ul class="feature-list">
                    <li><strong>Security First:</strong> Your data is protected with enterprise-grade encryption and security measures</li>
                    <li><strong>Performance:</strong> Lightning-fast operations with 80% performance improvements</li>
                    <li><strong>Innovation:</strong> Continuous improvement with cutting-edge features and technology</li>
                    <li><strong>User-Centric:</strong> Intuitive design that makes pharmacy management effortless</li>
                    <li><strong>Reliability:</strong> 24/7 availability with comprehensive backup and recovery systems</li>
                    <li><strong>Support:</strong> Dedicated support team ready to help you succeed</li>
                </ul>
            </div>

            <div class="content-block">
                <h3>Key Features</h3>
                <ul class="feature-list">
                    <li>Complete Inventory Management with real-time stock tracking</li>
                    <li>Advanced Point of Sale (POS) system with multi-step workflow</li>
                    <li>Comprehensive Reporting and Analytics with profit & loss tracking</li>
                    <li>Drug Interaction Checker for patient safety</li>
                    <li>Prescription Management with file upload support</li>
                    <li>Two-Factor Authentication for enhanced security</li>
                    <li>Role-Based Access Control (Admin, Pharmacist, Cashier)</li>
                    <li>Customer Loyalty Program with tier-based rewards</li>
                    <li>Expiry Management with FIFO/FEFO implementation</li>
                    <li>Multi-Channel Notifications (Email, SMS, In-app)</li>
                </ul>
            </div>

            <div class="content-block">
                <h3>Technology Stack</h3>
                <p>
                    PHARMACIA is built on modern, proven technologies:
                </p>
                <ul class="feature-list">
                    <li><strong>Backend:</strong> PHP 7.4+ with prepared statements for security</li>
                    <li><strong>Database:</strong> MySQL/MariaDB with 45+ optimized indexes</li>
                    <li><strong>Frontend:</strong> HTML5, Tailwind CSS, JavaScript with Chart.js</li>
                    <li><strong>Security:</strong> AES-256-GCM encryption, TOTP 2FA, CSRF protection</li>
                    <li><strong>Performance:</strong> File-based caching, query optimization, pagination</li>
                </ul>
            </div>

            <div class="content-block">
                <h3>Our Journey</h3>
                <div class="timeline">
                    <div class="timeline-item">
                        <h4>Phase 1: Foundation (2024)</h4>
                        <p>Launched core pharmacy management features with basic inventory and sales tracking.</p>
                    </div>
                    <div class="timeline-item">
                        <h4>Phase 2: Security & Performance (2024)</h4>
                        <p>Implemented enterprise security with 2FA, encryption, and achieved 80% performance improvements.</p>
                    </div>
                    <div class="timeline-item">
                        <h4>Phase 3: Pharmacy Features (2024)</h4>
                        <p>Added prescription management, drug interaction checker, and expiry management.</p>
                    </div>
                    <div class="timeline-item">
                        <h4>Phase 4: Advanced Features (2024)</h4>
                        <p>Launched discounts, medical profiles, advanced reporting, and notification system.</p>
                    </div>
                    <div class="timeline-item">
                        <h4>Phase 5: Production Ready (2024)</h4>
                        <p>System is now production-ready with comprehensive testing and documentation.</p>
                    </div>
                </div>
            </div>

            <div class="content-block">
                <h3>Why Choose PHARMACIA?</h3>
                <div class="team-grid">
                    <div class="team-card">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Enterprise Security</h4>
                        <p>2FA, encryption, audit logging, and compliance-ready architecture</p>
                    </div>
                    <div class="team-card">
                        <i class="fas fa-rocket"></i>
                        <h4>High Performance</h4>
                        <p>80% faster queries, intelligent caching, and optimized database</p>
                    </div>
                    <div class="team-card">
                        <i class="fas fa-headset"></i>
                        <h4>Expert Support</h4>
                        <p>Dedicated support team ready to help you succeed</p>
                    </div>
                    <div class="team-card">
                        <i class="fas fa-cogs"></i>
                        <h4>Easy Integration</h4>
                        <p>Seamless integration with existing systems and workflows</p>
                    </div>
                    <div class="team-card">
                        <i class="fas fa-chart-line"></i>
                        <h4>Advanced Analytics</h4>
                        <p>Real-time dashboards and comprehensive reporting</p>
                    </div>
                    <div class="team-card">
                        <i class="fas fa-users"></i>
                        <h4>Role-Based Access</h4>
                        <p>Customized permissions for Admin, Pharmacist, and Cashier</p>
                    </div>
                </div>
            </div>

            <div class="content-block">
                <h3>Statistics</h3>
                <ul class="feature-list">
                    <li>10,000+ lines of production-ready code</li>
                    <li>33 comprehensive features implemented</li>
                    <li>23 database tables with 45+ optimized indexes</li>
                    <li>11 database views for advanced reporting</li>
                    <li>80% improvement in query performance</li>
                    <li>38% reduction in memory usage</li>
                    <li>24/7 system availability with automated backups</li>
                </ul>
            </div>
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
