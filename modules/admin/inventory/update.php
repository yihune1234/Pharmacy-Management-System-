<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$id = $_GET['id'] ?? null;

if (!$id) {
    set_flash_message("Operational failure: Asset ID missing.", "error");
    header("Location: view.php");
    exit();
}

// Fetch medicine details
$stmt = $conn->prepare("SELECT * FROM meds WHERE Med_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    set_flash_message("Protocol breach: Asset not found in matrix.", "error");
    header("Location: view.php");
    exit();
}

// Handle form submission
if (isset($_POST['update'])) {
    $name = $conn->real_escape_string($_POST['medname']);
    $qty = (int)$_POST['qty'];
    $cat = $conn->real_escape_string($_POST['cat']);
    $price = (float)$_POST['sp'];
    $lcn = $conn->real_escape_string($_POST['loc']);
     
    $sql = "UPDATE meds SET Med_Name='$name', Med_Qty=$qty, Category='$cat', Med_Price=$price, Location_Rack='$lcn' WHERE Med_ID=$id";
    
    if ($conn->query($sql)) {
        set_flash_message("Asset synchronization successful for ID #$id.", "success");
        header("Location: view.php");
        exit();
    } else {
        set_flash_message("Operational failure during asset update.", "error");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Calibration - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Header -->
    <div class="mb-12 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <h2 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-1">Asset Calibration</h2>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Edit Medicine</h1>
            <p class="text-slate-500 font-medium mt-1">Synchronize parameters for ID #<?php echo str_pad($id, 3, '0', STR_PAD_LEFT); ?>.</p>
        </div>
        <a href="view.php" class="bg-white border border-slate-200 px-6 py-3 rounded-2xl text-sm font-bold text-slate-600 shadow-sm hover:bg-slate-50 transition-all flex items-center">
            <i class="fas fa-arrow-left-long mr-2"></i> Return to Matrix
        </a>
    </div>

    <!-- Main Calibration Console -->
    <div class="max-w-6xl">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $id; ?>" method="post" class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Data Entry Block -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 relative overflow-hidden">
                    <h3 class="text-xl font-black text-slate-900 uppercase italic mb-10 flex items-center">
                        <span class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center mr-4 text-xs font-black shadow-lg shadow-blue-200">ID</span>
                        Asset Signature
                    </h3>
                    
                    <div class="space-y-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Official Asset Name</label>
                            <input type="text" name="medname" value="<?php echo htmlspecialchars($row['Med_Name']); ?>" required
                                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Classification</label>
                                <select name="cat" required
                                    class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700 appearance-none">
                                    <option value="Tablet" <?php echo ($row['Category'] == 'Tablet') ? 'selected' : ''; ?>>Tablet</option>
                                    <option value="Capsule" <?php echo ($row['Category'] == 'Capsule') ? 'selected' : ''; ?>>Capsule</option>
                                    <option value="Syrup" <?php echo ($row['Category'] == 'Syrup') ? 'selected' : ''; ?>>Syrup</option>
                                    <option value="Injection" <?php echo ($row['Category'] == 'Injection') ? 'selected' : ''; ?>>Injection</option>
                                    <option value="Ointment" <?php echo ($row['Category'] == 'Ointment') ? 'selected' : ''; ?>>Ointment</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Current Stockpile</label>
                                <input type="number" name="qty" value="<?php echo $row['Med_Qty']; ?>"
                                    class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white transition-all outline-none font-bold text-slate-700">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Block -->
            <div class="space-y-8">
                <div class="bg-slate-900 p-10 rounded-[3rem] shadow-2xl relative overflow-hidden h-full">
                    <h3 class="text-xl font-black text-white uppercase italic mb-10 flex items-center">
                        <span class="w-10 h-10 bg-white/10 text-white rounded-xl flex items-center justify-center mr-4 text-xs font-black backdrop-blur">VAL</span>
                        Valuation
                    </h3>
                    
                    <div class="space-y-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Target Price (Rs)</label>
                            <div class="relative">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 text-blue-500 font-extrabold">Rs.</span>
                                <input type="number" step="0.01" name="sp" value="<?php echo $row['Med_Price']; ?>" required
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl pl-14 pr-6 py-5 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white/10 transition-all outline-none font-black text-white text-2xl">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Logical Storage</label>
                            <div class="relative">
                                <i class="fas fa-location-arrow absolute left-5 top-1/2 -translate-y-1/2 text-slate-600"></i>
                                <input type="text" name="loc" value="<?php echo htmlspecialchars($row['Location_Rack']); ?>"
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl pl-12 pr-6 py-5 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white/10 transition-all outline-none font-bold text-white">
                            </div>
                        </div>

                        <div class="pt-10">
                            <button type="submit" name="update" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-6 rounded-2xl shadow-xl shadow-blue-500/20 transition-all active:scale-95 flex items-center justify-center text-lg">
                                <i class="fas fa-sync-alt mr-3"></i> Synchronize Asset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Layout Closes -->
    </main>
    </div>
    </div>
</body>
</html>