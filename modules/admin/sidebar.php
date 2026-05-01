<?php
// Determine the relative path to the admin root
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path = ($current_dir == 'admin') ? '' : '../';

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/alerts.php';
require_once __DIR__ . '/../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

// Get dashboard statistics for sidebar badges
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customer")->fetch_assoc()['count'] ?? 0;
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employee")->fetch_assoc()['count'] ?? 0;
$total_suppliers = $conn->query("SELECT COUNT(*) as count FROM suppliers")->fetch_assoc()['count'] ?? 0;

// Low stock check
$low_stock_result = $conn->query("SELECT COUNT(*) as count FROM meds WHERE Med_Qty <= 10");
$low_stock = $low_stock_result->fetch_assoc()['count'] ?? 0;
?>

<style>
    .glass-effect {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 12px;
        color: #64748b;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    
    .nav-link:hover {
        background-color: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        transform: translateX(4px);
    }
    
    .nav-link-active {
        background-color: #3b82f6;
        color: white;
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
    }
    
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<div style="display: flex; height: 100vh; background-color: #f8fafc; overflow: hidden;">
    <!-- Sidebar -->
    <aside id="sidebar" style="position: fixed; top: 0; left: 0; z-index: 50; width: 288px; height: 100vh; background-color: white; border-right: 1px solid rgba(226, 232, 240, 0.6); transition: transform 0.3s ease; transform: translateX(0); display: flex; flex-direction: column;">
        <div style="display: flex; flex-direction: column; height: 100%;">
            <!-- Brand Logo -->
            <div style="padding: 32px;">
                <div style="display: flex; align-items: center; gap: 12px; cursor: pointer;" onclick="location.href='<?php echo $path; ?>dashboard.php'">
                    <div style="width: 48px; height: 48px; background-color: #3b82f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.2); transition: transform 0.3s ease;" onmouseover="this.style.transform='rotate(6deg)'" onmouseout="this.style.transform='rotate(0)'">
                        <i class="fas fa-prescription" style="color: white; font-size: 20px;"></i>
                    </div>
                    <div>
                        <h1 style="font-size: 18px; font-weight: 900; color: #1e293b; letter-spacing: -0.5px; margin: 0; line-height: 1;">PHARMACIA</h1>
                        <span style="font-size: 10px; font-weight: 700; color: #3b82f6; text-transform: uppercase; letter-spacing: 1px; display: block;">Medical System</span>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav style="flex: 1; padding: 0 16px 16px; overflow-y: auto;" class="custom-scrollbar">
                <div style="display: flex; flex-direction: column; gap: 32px;">
                    <!-- Dashboard Section -->
                    <div>
                        <p style="padding: 0 16px; font-size: 10px; font-weight: 900; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px;">Intelligence</p>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <a href="<?php echo $path; ?>dashboard.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'nav-link-active' : ''; ?>">
                                <i class="fas fa-home-alt" style="font-size: 16px;"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="<?php echo $path; ?>sales/pos_new.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'pos_new.php') ? 'nav-link-active' : ''; ?>">
                                <i class="fas fa-cash-register" style="font-size: 16px;"></i>
                                <span>Point of Sale</span>
                            </a>
                        </div>
                    </div>

                    <!-- Operations Section -->
                    <div>
                        <p style="padding: 0 16px; font-size: 10px; font-weight: 900; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px;">Operations</p>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <a href="<?php echo $path; ?>inventory/view.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'view.php' && strpos($_SERVER['PHP_SELF'], 'inventory') !== false) ? 'nav-link-active' : ''; ?>" style="justify-content: space-between;">
                                <span style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-boxes-stacked" style="font-size: 16px;"></i>
                                    <span>Stock Inventory</span>
                                </span>
                                <?php if($low_stock > 0): ?>
                                    <span style="background-color: rgba(244, 63, 94, 0.1); color: #f43f5e; font-size: 10px; font-weight: 900; padding: 4px 8px; border-radius: 9999px; border: 1px solid rgba(244, 63, 94, 0.2);"><?php echo $low_stock; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="<?php echo $path; ?>purchases/view_new.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'view_new.php' && strpos($_SERVER['PHP_SELF'], 'purchases') !== false) ? 'nav-link-active' : ''; ?>">
                                <i class="fas fa-cart-flatbed" style="font-size: 16px;"></i>
                                <span>Procurement</span>
                            </a>
                        </div>
                    </div>

                    <!-- Directory Section -->
                    <div>
                        <p style="padding: 0 16px; font-size: 10px; font-weight: 900; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px;">Core Directory</p>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <a href="<?php echo $path; ?>customers/view_new.php" class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'customers') !== false) ? 'nav-link-active' : ''; ?>" style="justify-content: space-between;">
                                <span style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-fingerprint" style="font-size: 16px;"></i>
                                    <span>Patient Database</span>
                                </span>
                                <span style="font-size: 10px; font-weight: 900; opacity: 0.5;"><?php echo $total_customers; ?></span>
                            </a>
                            <a href="<?php echo $path; ?>suppliers/view_new.php" class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'suppliers') !== false) ? 'nav-link-active' : ''; ?>">
                                <i class="fas fa-handshake-angle" style="font-size: 16px;"></i>
                                <span>Partner Network</span>
                            </a>
                            <a href="<?php echo $path; ?>employees/view_new.php" class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'employees') !== false) ? 'nav-link-active' : ''; ?>">
                                <i class="fas fa-id-badge" style="font-size: 16px;"></i>
                                <span>Staff Management</span>
                            </a>
                        </div>
                    </div>

                    <!-- Pharmacy Features Section -->
                    <div>
                        <p style="padding: 0 16px; font-size: 10px; font-weight: 900; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px;">Pharmacy</p>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <a href="<?php echo $path; ?>prescriptions/prescriptions.php" class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'prescriptions') !== false) ? 'nav-link-active' : ''; ?>">
                                <i class="fas fa-file-medical" style="font-size: 16px;"></i>
                                <span>Prescriptions</span>
                            </a>
                            <a href="<?php echo $path; ?>drug_interactions/checker.php" class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'drug_interactions') !== false) ? 'nav-link-active' : ''; ?>">
                                <i class="fas fa-flask-vial" style="font-size: 16px;"></i>
                                <span>Drug Interactions</span>
                            </a>
                            <a href="<?php echo $path; ?>inventory/expiry_management.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'expiry_management.php') ? 'nav-link-active' : ''; ?>">
                                <i class="fas fa-calendar-times" style="font-size: 16px;"></i>
                                <span>Expiry Management</span>
                            </a>
                            <a href="<?php echo $path; ?>sales/payment_methods.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'payment_methods.php') ? 'nav-link-active' : ''; ?>">
                                <i class="fas fa-credit-card" style="font-size: 16px;"></i>
                                <span>Payment Methods</span>
                            </a>
                        </div>
                    </div>

                    <!-- Support Section -->
                    <div>
                        <p style="padding: 0 16px; font-size: 10px; font-weight: 900; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px;">Analysis</p>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <a href="<?php echo $path; ?>reports/reports_dashboard.php" class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'reports') !== false) ? 'nav-link-active' : ''; ?>">
                                <i class="fas fa-chart-line" style="font-size: 16px;"></i>
                                <span>Visual Insights</span>
                            </a>
                            <a href="<?php echo $path; ?>alerts/alerts.php" class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'alerts') !== false) ? 'nav-link-active' : ''; ?>">
                                <i class="fas fa-shield-virus" style="font-size: 16px;"></i>
                                <span>Security Alerts</span>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- User Status Card -->
            <div style="padding: 16px; border-top: 1px solid #e2e8f0; background-color: rgba(248, 250, 252, 0.5);">
                <div style="background-color: white; border-radius: 16px; padding: 16px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); border: 1px solid rgba(226, 232, 240, 0.6); display: flex; align-items: center; gap: 12px;">
                    <div style="position: relative;">
                        <div style="width: 40px; height: 40px; background-color: rgba(59, 130, 246, 0.1); color: #3b82f6; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                            <?php echo strtoupper(substr($_SESSION['name'] ?? 'A', 0, 1)); ?>
                        </div>
                        <div style="position: absolute; bottom: -4px; right: -4px; width: 12px; height: 12px; background-color: #10b981; border: 2px solid white; border-radius: 50%;"></div>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <p style="font-size: 12px; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0;"><?php echo htmlspecialchars($_SESSION['name']); ?></p>
                        <p style="font-size: 9px; font-weight: 900; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">Chief Administrator</p>
                    </div>
                    <a href="<?php echo $path; ?>../auth/logout.php" style="color: #cbd5e1; text-decoration: none; transition: color 0.3s ease;" onmouseover="this.style.color='#f43f5e'" onmouseout="this.style.color='#cbd5e1'">
                        <i class="fas fa-power-off" style="font-size: 12px;"></i>
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content wrapper -->
    <div style="flex: 1; margin-left: 288px; display: flex; flex-direction: column; min-height: 100vh;">
        <!-- Top Navigation Bar -->
        <header style="height: 80px; background-color: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.6); display: flex; align-items: center; justify-content: space-between; padding: 0 32px; position: sticky; top: 0; z-index: 40;">
            <!-- Left Header -->
            <div style="display: flex; align-items: center; gap: 16px;">
                <button id="sidebarToggle" style="display: none; width: 40px; height: 40px; border-radius: 8px; background-color: #f1f5f9; color: #64748b; border: none; cursor: pointer; font-size: 16px;">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2 style="font-size: 10px; font-weight: 900; color: #cbd5e1; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 4px 0;">Application</h2>
                    <h1 style="font-size: 18px; font-weight: 700; color: #1e293b; letter-spacing: -0.5px; margin: 0; line-height: 1;">Command Center</h1>
                </div>
            </div>

            <!-- Global Search -->
            <div style="display: none; flex: 1; max-width: 448px; margin: 0 48px;">
                <div style="position: relative; width: 100%;">
                    <span style="position: absolute; top: 0; left: 0; bottom: 0; display: flex; align-items: center; padding-left: 16px; color: #cbd5e1; transition: color 0.3s ease;">
                        <i class="fas fa-magnifying-glass" style="font-size: 12px;"></i>
                    </span>
                    <input type="text" placeholder="Global search console..." 
                           style="width: 100%; background-color: rgba(241, 245, 249, 0.5); border: 1px solid rgba(226, 232, 240, 0.6); border-radius: 16px; padding-left: 44px; padding-right: 16px; padding-top: 10px; padding-bottom: 10px; font-size: 14px; font-weight: 500; outline: none; transition: all 0.3s ease;"
                           onfocus="this.style.outline='none'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)'; this.style.borderColor='#3b82f6'; this.style.backgroundColor='white'"
                           onblur="this.style.boxShadow='none'; this.style.borderColor='rgba(226, 232, 240, 0.6)'; this.style.backgroundColor='rgba(241, 245, 249, 0.5)'">
                </div>
            </div>

            <!-- Right Header -->
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button style="width: 40px; height: 40px; border-radius: 8px; background-color: transparent; color: #64748b; border: none; cursor: pointer; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'">
                        <i class="fas fa-plus-square" style="font-size: 16px;"></i>
                    </button>
                    <button style="width: 40px; height: 40px; border-radius: 8px; background-color: transparent; color: #64748b; border: none; cursor: pointer; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'">
                        <i class="fas fa-envelope-open" style="font-size: 16px;"></i>
                    </button>
                    <button style="width: 40px; height: 40px; border-radius: 8px; background-color: #1e293b; color: white; border: none; cursor: pointer; box-shadow: 0 10px 25px -5px rgba(30, 41, 59, 0.2); transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <i class="fas fa-gear" style="font-size: 16px;"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Body -->
        <main style="flex: 1; padding: 32px; overflow-y: auto; width: 100%; max-width: 1600px; margin: 0 auto;">

<script>
    // Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    
    toggle?.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.style.transform = sidebar.style.transform === 'translateX(0)' ? 'translateX(-100%)' : 'translateX(0)';
    });

    document.addEventListener('click', (e) => {
        if (window.innerWidth < 1024 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.style.transform = 'translateX(-100%)';
        }
    });
</script>
<?php render_flash_message(); ?>
