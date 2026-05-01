<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Check if guest is registered
if (!isset($_SESSION['guest_customer_id'])) {
    header("Location: register.php");
    exit();
}

$customer_id = $_SESSION['guest_customer_id'];

// Get customer information
$stmt = $conn->prepare("SELECT * FROM customer WHERE C_ID = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get customer purchase history
$history_stmt = $conn->prepare("
    SELECT s.Sale_ID, s.S_Date, s.Total_Amt, COUNT(si.Med_ID) as item_count
    FROM sales s
    LEFT JOIN sales_items si ON s.Sale_ID = si.Sale_ID
    WHERE s.C_ID = ?
    GROUP BY s.Sale_ID
    ORDER BY s.S_Date DESC
    LIMIT 10
");
$history_stmt->bind_param("i", $customer_id);
$history_stmt->execute();
$purchase_history = $history_stmt->get_result();
$history_stmt->close();

// Get customer statistics
$stats_stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT s.Sale_ID) as total_purchases,
        SUM(s.Total_Amt) as total_spent,
        AVG(s.Total_Amt) as avg_purchase
    FROM sales s
    WHERE s.C_ID = ?
");
$stats_stmt->bind_param("i", $customer_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Profile - PHARMACIA</title>
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
            padding: 40px 20px;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .section {
            padding: 40px 20px;
        }

        .container-max {
            max-width: 1000px;
            margin: 0 auto;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-card h3 {
            color: #667eea;
            font-size: 1.3rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .profile-info {
            margin-bottom: 15px;
        }

        .profile-info label {
            color: #999;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            display: block;
            margin-bottom: 5px;
        }

        .profile-info value {
            color: #333;
            font-size: 1rem;
            font-weight: 600;
            display: block;
        }

        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 900;
        }

        .stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-top: 5px;
        }

        .loyalty-tier {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .loyalty-tier.bronze {
            background: #cd7f32;
            color: white;
        }

        .loyalty-tier.silver {
            background: #c0c0c0;
            color: #333;
        }

        .loyalty-tier.gold {
            background: #ffd700;
            color: #333;
        }

        .purchase-history {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .purchase-history h3 {
            color: #667eea;
            font-size: 1.3rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .purchase-table {
            width: 100%;
            border-collapse: collapse;
        }

        .purchase-table thead {
            background: #f8f9fa;
        }

        .purchase-table th {
            padding: 12px;
            text-align: left;
            color: #666;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            border-bottom: 2px solid #e0e0e0;
        }

        .purchase-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            color: #555;
        }

        .purchase-table tr:hover {
            background: #f8f9fa;
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

        .btn-logout {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        footer {
            background: #1a1a1a;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .purchase-table {
                font-size: 0.85rem;
            }

            .purchase-table th,
            .purchase-table td {
                padding: 8px;
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
                </div>

                <div class="auth-buttons">
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container-max">
            <h1>Welcome, <?php echo htmlspecialchars($customer['C_Fname']); ?>!</h1>
            <p>Your Guest Profile & Purchase History</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="section">
        <div class="container-max">
            <!-- Profile Grid -->
            <div class="profile-grid">
                <!-- Personal Information -->
                <div class="profile-card">
                    <h3><i class="fas fa-user"></i> Personal Information</h3>
                    
                    <div class="profile-info">
                        <label>Full Name</label>
                        <value><?php echo htmlspecialchars($customer['C_Fname'] . ' ' . $customer['C_Lname']); ?></value>
                    </div>

                    <div class="profile-info">
                        <label>Email Address</label>
                        <value><?php echo htmlspecialchars($customer['C_Email']); ?></value>
                    </div>

                    <div class="profile-info">
                        <label>Phone Number</label>
                        <value><?php echo htmlspecialchars($customer['C_Phone']); ?></value>
                    </div>

                    <div class="profile-info">
                        <label>Address</label>
                        <value><?php echo htmlspecialchars($customer['C_Address']); ?></value>
                    </div>

                    <div class="profile-info">
                        <label>Member Since</label>
                        <value><?php echo date('F j, Y', strtotime($customer['C_RegDate'] ?? 'now')); ?></value>
                    </div>
                </div>

                <!-- Loyalty & Statistics -->
                <div class="profile-card">
                    <h3><i class="fas fa-star"></i> Loyalty Program</h3>
                    
                    <div class="stat-box">
                        <div class="stat-number"><?php echo number_format($customer['Loyalty_Points']); ?></div>
                        <div class="stat-label">Loyalty Points</div>
                    </div>

                    <div class="profile-info">
                        <label>Loyalty Tier</label>
                        <value>
                            <span class="loyalty-tier <?php echo strtolower($customer['Loyalty_Tier']); ?>">
                                <?php echo ucfirst($customer['Loyalty_Tier']); ?>
                            </span>
                        </value>
                    </div>

                    <hr style="margin: 20px 0; border: none; border-top: 1px solid #e0e0e0;">

                    <h3 style="margin-top: 20px;"><i class="fas fa-chart-bar"></i> Statistics</h3>

                    <div class="stat-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin-top: 15px;">
                        <div class="stat-number"><?php echo $stats['total_purchases'] ?? 0; ?></div>
                        <div class="stat-label">Total Purchases</div>
                    </div>

                    <div class="stat-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin-top: 15px;">
                        <div class="stat-number">Rs. <?php echo number_format($stats['total_spent'] ?? 0, 0); ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>

                    <div class="stat-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin-top: 15px;">
                        <div class="stat-number">Rs. <?php echo number_format($stats['avg_purchase'] ?? 0, 0); ?></div>
                        <div class="stat-label">Average Purchase</div>
                    </div>
                </div>
            </div>

            <!-- Purchase History -->
            <div class="purchase-history">
                <h3><i class="fas fa-history"></i> Purchase History</h3>

                <?php if ($purchase_history && $purchase_history->num_rows > 0): ?>
                    <table class="purchase-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($purchase = $purchase_history->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#S-<?php echo $purchase['Sale_ID']; ?></strong></td>
                                <td><?php echo date('M j, Y', strtotime($purchase['S_Date'])); ?></td>
                                <td><?php echo $purchase['item_count']; ?> item(s)</td>
                                <td><strong>Rs. <?php echo number_format($purchase['Total_Amt'], 2); ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <p>No purchase history yet.</p>
                        <p style="font-size: 0.9rem; margin-top: 10px;">Visit our pharmacy to make your first purchase!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Benefits Section -->
            <div class="profile-card" style="margin-top: 30px;">
                <h3><i class="fas fa-gift"></i> Guest Benefits</h3>
                <ul style="list-style: none; margin-top: 20px;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                        <i class="fas fa-check" style="color: #667eea; margin-right: 10px;"></i>
                        <strong>Earn Loyalty Points</strong> - Get 1 point for every Rs. 10 spent
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                        <i class="fas fa-check" style="color: #667eea; margin-right: 10px;"></i>
                        <strong>Loyalty Tiers</strong> - Bronze, Silver, and Gold with exclusive benefits
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                        <i class="fas fa-check" style="color: #667eea; margin-right: 10px;"></i>
                        <strong>Track Purchases</strong> - View your complete purchase history
                    </li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #e0e0e0;">
                        <i class="fas fa-check" style="color: #667eea; margin-right: 10px;"></i>
                        <strong>Special Offers</strong> - Exclusive discounts for loyal customers
                    </li>
                    <li style="padding: 10px 0;">
                        <i class="fas fa-check" style="color: #667eea; margin-right: 10px;"></i>
                        <strong>Priority Service</strong> - Fast checkout and personalized recommendations
                    </li>
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
