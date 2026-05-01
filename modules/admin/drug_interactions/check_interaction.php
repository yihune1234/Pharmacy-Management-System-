<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$interaction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = isset($_GET['edit']) && $_GET['edit'] == 1;
$interaction = null;

// Load existing interaction if editing
if ($interaction_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM drug_interactions WHERE interaction_id = ?");
    $stmt->bind_param("i", $interaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $interaction = $result->fetch_assoc();
    $stmt->close();
}

// Get medicines for dropdown
$medicines = $conn->query("SELECT Med_ID, Med_Name FROM meds ORDER BY Med_Name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $med_id_1 = (int)$_POST['med_id_1'];
    $med_id_2 = (int)$_POST['med_id_2'];
    $severity = $conn->real_escape_string($_POST['severity']);
    $description = $conn->real_escape_string($_POST['description']);
    $recommendation = $conn->real_escape_string($_POST['recommendation']);

    // Validate inputs
    if (empty($med_id_1) || empty($med_id_2) || empty($description)) {
        set_flash_message("Please fill in all required fields.", "error");
    } elseif ($med_id_1 === $med_id_2) {
        set_flash_message("Please select two different medicines.", "error");
    } else {
        if ($is_edit && $interaction_id > 0) {
            // Update existing interaction
            $sql = "UPDATE drug_interactions SET 
                    med_id_1 = $med_id_1,
                    med_id_2 = $med_id_2,
                    severity = '$severity',
                    description = '$description',
                    recommendation = '$recommendation'
                    WHERE interaction_id = $interaction_id";
            
            if ($conn->query($sql)) {
                set_flash_message("Interaction updated successfully!", "success");
                header("Location: checker.php");
                exit();
            } else {
                set_flash_message("Error updating interaction: " . $conn->error, "error");
            }
        } else {
            // Check if interaction already exists
            $check = $conn->query("SELECT interaction_id FROM drug_interactions WHERE (med_id_1 = $med_id_1 AND med_id_2 = $med_id_2) OR (med_id_1 = $med_id_2 AND med_id_2 = $med_id_1)");
            
            if ($check && $check->num_rows > 0) {
                set_flash_message("This interaction already exists in the database.", "error");
            } else {
                // Insert new interaction
                $stmt = $conn->prepare("INSERT INTO drug_interactions (med_id_1, med_id_2, severity, description, recommendation) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $med_id_1, $med_id_2, $severity, $description, $recommendation);
                
                if ($stmt->execute()) {
                    set_flash_message("Interaction added successfully!", "success");
                    header("Location: checker.php");
                    exit();
                } else {
                    set_flash_message("Error adding interaction: " . $stmt->error, "error");
                }
                $stmt->close();
            }
        }
    }
}

// Handle check request (from POST form)
$check_result = null;
if (isset($_POST['check_interaction'])) {
    $med_id_1 = (int)$_POST['med_id_1'];
    $med_id_2 = (int)$_POST['med_id_2'];
    
    if ($med_id_1 > 0 && $med_id_2 > 0) {
        $stmt = $conn->prepare("
            SELECT di.*, m1.Med_Name as med_1_name, m2.Med_Name as med_2_name
            FROM drug_interactions di
            JOIN meds m1 ON di.med_id_1 = m1.Med_ID
            JOIN meds m2 ON di.med_id_2 = m2.Med_ID
            WHERE (di.med_id_1 = ? AND di.med_id_2 = ?) OR (di.med_id_1 = ? AND di.med_id_2 = ?)
        ");
        $stmt->bind_param("iiii", $med_id_1, $med_id_2, $med_id_2, $med_id_1);
        $stmt->execute();
        $result = $stmt->get_result();
        $check_result = $result->fetch_assoc();
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Drug Interaction - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Title Header -->
    <div class="mb-10 flex items-center justify-between">
        <div>
            <p class="subheading-premium">Safety Management</p>
            <h1 class="heading-premium"><?php echo $is_edit ? 'Edit' : 'Add New'; ?> Drug Interaction</h1>
        </div>
        <a href="checker.php" class="btn-primary btn-slate">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form -->
        <div class="lg:col-span-2 premium-card">
            <form method="POST" class="space-y-8">
                <!-- Medicine 1 -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Medicine 1 <span class="text-rose-500">*</span></label>
                    <select name="med_id_1" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                        <option value="">Select First Medicine</option>
                        <?php 
                        $medicines->data_seek(0);
                        while($med = $medicines->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $med['Med_ID']; ?>" 
                                    <?php echo ($interaction && $interaction['med_id_1'] == $med['Med_ID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($med['Med_Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Medicine 2 -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Medicine 2 <span class="text-rose-500">*</span></label>
                    <select name="med_id_2" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                        <option value="">Select Second Medicine</option>
                        <?php 
                        $medicines->data_seek(0);
                        while($med = $medicines->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $med['Med_ID']; ?>" 
                                    <?php echo ($interaction && $interaction['med_id_2'] == $med['Med_ID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($med['Med_Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Severity -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Severity <span class="text-rose-500">*</span></label>
                    <select name="severity" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                        <option value="Low" <?php echo (!$interaction || $interaction['severity'] === 'Low') ? 'selected' : ''; ?>>Low</option>
                        <option value="Moderate" <?php echo ($interaction && $interaction['severity'] === 'Moderate') ? 'selected' : ''; ?>>Moderate</option>
                        <option value="High" <?php echo ($interaction && $interaction['severity'] === 'High') ? 'selected' : ''; ?>>High</option>
                        <option value="Critical" <?php echo ($interaction && $interaction['severity'] === 'Critical') ? 'selected' : ''; ?>>Critical</option>
                    </select>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Description <span class="text-rose-500">*</span></label>
                    <textarea name="description" rows="4" required
                              placeholder="Describe the interaction and its effects..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700"><?php echo $interaction ? htmlspecialchars($interaction['description']) : ''; ?></textarea>
                </div>

                <!-- Recommendation -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Recommendation</label>
                    <textarea name="recommendation" rows="3"
                              placeholder="Recommended action or precaution..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700"><?php echo $interaction ? htmlspecialchars($interaction['recommendation']) : ''; ?></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4">
                    <button type="submit" name="save_interaction" class="flex-1 btn-primary btn-blue">
                        <i class="fas fa-save mr-2"></i> <?php echo $is_edit ? 'Update' : 'Add'; ?> Interaction
                    </button>
                    <a href="checker.php" class="flex-1 btn-primary btn-slate text-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Sidebar -->
        <div class="space-y-8">
            <!-- Severity Guide -->
            <div class="premium-card">
                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                    Severity Levels
                </h3>
                <div class="space-y-3">
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl">
                        <p class="text-xs font-black text-blue-600 uppercase tracking-widest">Low</p>
                        <p class="text-sm text-blue-700 mt-1">Minor interaction, monitor</p>
                    </div>
                    <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl">
                        <p class="text-xs font-black text-amber-600 uppercase tracking-widest">Moderate</p>
                        <p class="text-sm text-amber-700 mt-1">Significant interaction, caution</p>
                    </div>
                    <div class="p-3 bg-orange-50 border border-orange-100 rounded-xl">
                        <p class="text-xs font-black text-orange-600 uppercase tracking-widest">High</p>
                        <p class="text-sm text-orange-700 mt-1">Serious interaction, avoid</p>
                    </div>
                    <div class="p-3 bg-rose-50 border border-rose-100 rounded-xl">
                        <p class="text-xs font-black text-rose-600 uppercase tracking-widest">Critical</p>
                        <p class="text-sm text-rose-700 mt-1">Contraindicated, do not use</p>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="premium-card">
                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
                    <i class="fas fa-lightbulb text-amber-600 mr-3"></i>
                    Tips
                </h3>
                <ul class="space-y-2 text-sm text-slate-600">
                    <li class="flex items-start">
                        <i class="fas fa-check text-emerald-600 mr-2 mt-1 flex-shrink-0"></i>
                        <span>Be specific in descriptions</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-emerald-600 mr-2 mt-1 flex-shrink-0"></i>
                        <span>Include clinical evidence</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-emerald-600 mr-2 mt-1 flex-shrink-0"></i>
                        <span>Provide clear recommendations</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-emerald-600 mr-2 mt-1 flex-shrink-0"></i>
                        <span>Update regularly with new data</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Closing Sidebar Tags -->
    </main>
    </div>
    </div>
</body>
</html>
