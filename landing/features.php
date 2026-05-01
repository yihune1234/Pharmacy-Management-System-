<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Features - PHARMACIA Pharmacy Management System</title>
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
            margin-bottom: 40px;
            text-align: center;
        }

        .container-max {
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .feature-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .feature-box i {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 15px;
        }

        .feature-box h3 {
            color: #333;
            font-size: 1.3rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .feature-box p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .feature-list {
            list-style: none;
            margin: 15px 0;
        }

        .feature-list li {
            padding: 8px 0;
            color: #666;
            font-size: 0.9rem;
        }

        .feature-list li:before {
            content: "✓ ";
            color: #667eea;
            font-weight: bold;
            margin-right: 8px;
        }

        .category-section {
            margin-bottom: 60px;
        }

        .category-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
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

            .section-title {
                font-size: 1.5rem;
            }

            .category-title {
                font-size: 1.3rem;
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

    <!-- Page Header -->
    <section class="page-header">
        <div class="container-max">
            <h1>Powerful Features</h1>
            <p>Everything you need to manage your pharmacy efficiently</p>
        </div>
    </section>

    <!-- Features Content -->
    <section class="section">
        <div class="container-max">
            <!-- Inventory Management -->
            <div class="category-section">
                <h2 class="category-title"><i class="fas fa-boxes"></i> Inventory Management</h2>
                <div class="feature-grid">
                    <div class="feature-box">
                        <i class="fas fa-plus-circle"></i>
                        <h3>Add & Manage Medicines</h3>
                        <p>Easily add new medicines with detailed information including name, price, quantity, and category.</p>
                        <ul class="feature-list">
                            <li>Batch tracking</li>
                            <li>Expiry date management</li>
                            <li>Cost price tracking</li>
                            <li>Supplier linking</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-chart-bar"></i>
                        <h3>Real-Time Stock Tracking</h3>
                        <p>Monitor stock levels in real-time with automatic updates on every transaction.</p>
                        <ul class="feature-list">
                            <li>Live stock updates</li>
                            <li>Stock history</li>
                            <li>Batch-wise tracking</li>
                            <li>Stock valuation</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-bell"></i>
                        <h3>Smart Alerts</h3>
                        <p>Automatic alerts for low stock and expiring medicines to prevent stockouts.</p>
                        <ul class="feature-list">
                            <li>Low stock alerts (≤10 units)</li>
                            <li>Expiry alerts (30-day warning)</li>
                            <li>Overstock alerts</li>
                            <li>Dead stock identification</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-calendar-times"></i>
                        <h3>Expiry Management</h3>
                        <p>FIFO/FEFO implementation to prevent selling expired medicines.</p>
                        <ul class="feature-list">
                            <li>FIFO/FEFO tracking</li>
                            <li>Expiry reports</li>
                            <li>Batch archival</li>
                            <li>Waste tracking</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-exchange-alt"></i>
                        <h3>Purchase Management</h3>
                        <p>Streamlined purchase order creation and supplier management.</p>
                        <ul class="feature-list">
                            <li>Create purchase orders</li>
                            <li>Supplier tracking</li>
                            <li>Payment status</li>
                            <li>Batch management</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-search"></i>
                        <h3>Advanced Search</h3>
                        <p>Quickly find medicines with powerful search and filtering options.</p>
                        <ul class="feature-list">
                            <li>Search by name</li>
                            <li>Filter by category</li>
                            <li>Filter by supplier</li>
                            <li>Stock level filters</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Sales & POS -->
            <div class="category-section">
                <h2 class="category-title"><i class="fas fa-shopping-cart"></i> Sales & Point of Sale</h2>
                <div class="feature-grid">
                    <div class="feature-box">
                        <i class="fas fa-cash-register"></i>
                        <h3>Advanced POS System</h3>
                        <p>Multi-step POS workflow for efficient and accurate sales processing.</p>
                        <ul class="feature-list">
                            <li>Customer selection</li>
                            <li>Medicine selection</li>
                            <li>Cart management</li>
                            <li>Real-time validation</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-receipt"></i>
                        <h3>Receipt Generation</h3>
                        <p>Professional receipts with all transaction details and pharmacy information.</p>
                        <ul class="feature-list">
                            <li>Detailed receipts</li>
                            <li>Print functionality</li>
                            <li>Email receipts</li>
                            <li>Receipt history</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-undo"></i>
                        <h3>Refund Processing</h3>
                        <p>Easy refund management with automatic stock and loyalty point reversal.</p>
                        <ul class="feature-list">
                            <li>Full refunds</li>
                            <li>Partial refunds</li>
                            <li>Refund reasons</li>
                            <li>Refund history</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-tag"></i>
                        <h3>Discount Management</h3>
                        <p>Flexible discount system with multiple discount types and rules.</p>
                        <ul class="feature-list">
                            <li>Percentage discounts</li>
                            <li>Fixed amount discounts</li>
                            <li>Bulk pricing</li>
                            <li>Loyalty discounts</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-credit-card"></i>
                        <h3>Multiple Payment Methods</h3>
                        <p>Support for various payment methods to accommodate all customers.</p>
                        <ul class="feature-list">
                            <li>Cash payments</li>
                            <li>Card payments</li>
                            <li>Mobile money</li>
                            <li>Credit/Check</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-history"></i>
                        <h3>Sales History</h3>
                        <p>Complete sales records with detailed transaction information.</p>
                        <ul class="feature-list">
                            <li>Transaction logs</li>
                            <li>Sales reports</li>
                            <li>Customer history</li>
                            <li>Item details</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Pharmacy Features -->
            <div class="category-section">
                <h2 class="category-title"><i class="fas fa-prescription-bottle"></i> Pharmacy Features</h2>
                <div class="feature-grid">
                    <div class="feature-box">
                        <i class="fas fa-file-medical"></i>
                        <h3>Prescription Management</h3>
                        <p>Upload and manage prescriptions with file storage and tracking.</p>
                        <ul class="feature-list">
                            <li>Upload prescriptions</li>
                            <li>Multiple file formats</li>
                            <li>Link to sales</li>
                            <li>Prescription history</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-flask"></i>
                        <h3>Drug Interaction Checker</h3>
                        <p>Real-time drug interaction checking for patient safety.</p>
                        <ul class="feature-list">
                            <li>10+ interactions</li>
                            <li>Severity levels</li>
                            <li>Real-time alerts</li>
                            <li>Interaction database</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-user-md"></i>
                        <h3>Customer Medical Profiles</h3>
                        <p>Store and manage customer medical information for better service.</p>
                        <ul class="feature-list">
                            <li>Medical history</li>
                            <li>Allergy tracking</li>
                            <li>Chronic conditions</li>
                            <li>Medication list</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Safety Alerts</h3>
                        <p>Automatic alerts for allergies and contraindications during sales.</p>
                        <ul class="feature-list">
                            <li>Allergy alerts</li>
                            <li>Interaction warnings</li>
                            <li>Dosage alerts</li>
                            <li>Contraindication checks</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Customer Management -->
            <div class="category-section">
                <h2 class="category-title"><i class="fas fa-users"></i> Customer Management</h2>
                <div class="feature-grid">
                    <div class="feature-box">
                        <i class="fas fa-user-plus"></i>
                        <h3>Customer Registration</h3>
                        <p>Easy customer registration with comprehensive profile management.</p>
                        <ul class="feature-list">
                            <li>Quick registration</li>
                            <li>Profile management</li>
                            <li>Contact information</li>
                            <li>Address tracking</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-star"></i>
                        <h3>Loyalty Program</h3>
                        <p>Reward loyal customers with points and tier-based benefits.</p>
                        <ul class="feature-list">
                            <li>Points system</li>
                            <li>Loyalty tiers</li>
                            <li>Tier benefits</li>
                            <li>Points redemption</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-chart-line"></i>
                        <h3>Customer Analytics</h3>
                        <p>Analyze customer behavior and purchase patterns.</p>
                        <ul class="feature-list">
                            <li>Purchase history</li>
                            <li>Spending analysis</li>
                            <li>Frequency tracking</li>
                            <li>Preferences</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-envelope"></i>
                        <h3>Customer Communication</h3>
                        <p>Send notifications and reminders to customers.</p>
                        <ul class="feature-list">
                            <li>Email notifications</li>
                            <li>SMS reminders</li>
                            <li>Refill reminders</li>
                            <li>Promotional messages</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Reporting & Analytics -->
            <div class="category-section">
                <h2 class="category-title"><i class="fas fa-chart-pie"></i> Reporting & Analytics</h2>
                <div class="feature-grid">
                    <div class="feature-box">
                        <i class="fas fa-file-alt"></i>
                        <h3>Comprehensive Reports</h3>
                        <p>Generate detailed reports for business insights and decision making.</p>
                        <ul class="feature-list">
                            <li>Sales reports</li>
                            <li>Stock reports</li>
                            <li>Expiry reports</li>
                            <li>Supplier reports</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-chart-line"></i>
                        <h3>Revenue Analytics</h3>
                        <p>Track revenue trends and performance metrics.</p>
                        <ul class="feature-list">
                            <li>Daily revenue</li>
                            <li>Monthly trends</li>
                            <li>Yearly analysis</li>
                            <li>Growth metrics</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-calculator"></i>
                        <h3>Profit & Loss</h3>
                        <p>Detailed profit and loss analysis for financial management.</p>
                        <ul class="feature-list">
                            <li>Revenue tracking</li>
                            <li>Cost analysis</li>
                            <li>Profit calculation</li>
                            <li>Margin analysis</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-download"></i>
                        <h3>Export Options</h3>
                        <p>Export reports in multiple formats for further analysis.</p>
                        <ul class="feature-list">
                            <li>PDF export</li>
                            <li>Excel export</li>
                            <li>Print functionality</li>
                            <li>Email reports</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Security & Performance -->
            <div class="category-section">
                <h2 class="category-title"><i class="fas fa-shield-alt"></i> Security & Performance</h2>
                <div class="feature-grid">
                    <div class="feature-box">
                        <i class="fas fa-lock"></i>
                        <h3>Two-Factor Authentication</h3>
                        <p>Enhanced security with TOTP-based two-factor authentication.</p>
                        <ul class="feature-list">
                            <li>TOTP support</li>
                            <li>QR code setup</li>
                            <li>Backup codes</li>
                            <li>Device management</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-key"></i>
                        <h3>Data Encryption</h3>
                        <p>AES-256-GCM encryption for sensitive data protection.</p>
                        <ul class="feature-list">
                            <li>AES-256 encryption</li>
                            <li>Secure storage</li>
                            <li>HTTPS support</li>
                            <li>SSL certificates</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-history"></i>
                        <h3>Audit Logging</h3>
                        <p>Comprehensive audit trail for compliance and security.</p>
                        <ul class="feature-list">
                            <li>Activity logging</li>
                            <li>Change tracking</li>
                            <li>User actions</li>
                            <li>IP tracking</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-tachometer-alt"></i>
                        <h3>High Performance</h3>
                        <p>Optimized for speed with 80% performance improvements.</p>
                        <ul class="feature-list">
                            <li>Query optimization</li>
                            <li>Database indexing</li>
                            <li>Caching system</li>
                            <li>Pagination</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-database"></i>
                        <h3>Backup & Recovery</h3>
                        <p>Automated backup system for data protection and recovery.</p>
                        <ul class="feature-list">
                            <li>Daily backups</li>
                            <li>Backup restoration</li>
                            <li>Backup verification</li>
                            <li>Disaster recovery</li>
                        </ul>
                    </div>

                    <div class="feature-box">
                        <i class="fas fa-users-cog"></i>
                        <h3>Role-Based Access</h3>
                        <p>Granular access control with role-based permissions.</p>
                        <ul class="feature-list">
                            <li>Admin role</li>
                            <li>Pharmacist role</li>
                            <li>Cashier role</li>
                            <li>Custom permissions</li>
                        </ul>
                    </div>
                </div>
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
