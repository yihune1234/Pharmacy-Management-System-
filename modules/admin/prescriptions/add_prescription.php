<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();
validate_role_area('admin');

$prescription_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$prescription = null;
$is_edit = false;

// Load existing prescription if editing
if ($prescription_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM prescriptions WHERE prescription_id = ?");
    $stmt->bind_param("i", $prescription_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prescription = $result->fetch_assoc();
    $is_edit = true;
    $stmt->close();
}

// Get customers for dropdown
$customers = $conn->query("SELECT C_ID, C_Fname, C_Lname FROM customer ORDER BY C_Fname, C_Lname");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)$_POST['customer_id'];
    $doctor_name = $conn->real_escape_string($_POST['doctor_name']);
    $prescription_date = $conn->real_escape_string($_POST['prescription_date']);
    $status = $conn->real_escape_string($_POST['status']);
    $notes = $conn->real_escape_string($_POST['notes']);
    $file_path = null;

    // Validate inputs
    if (empty($customer_id) || empty($doctor_name) || empty($prescription_date)) {
        set_flash_message("Please fill in all required fields.", "error");
    } else {
        // Handle file upload
        if (isset($_FILES['prescription_file']) && $_FILES['prescription_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../uploads/prescriptions/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = basename($_FILES['prescription_file']['name']);
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

            if (!in_array(strtolower($file_ext), $allowed_ext)) {
                set_flash_message("Invalid file type. Allowed: PDF, JPG, PNG, DOC, DOCX", "error");
            } else {
                $new_file_name = 'RX_' . time() . '_' . uniqid() . '.' . $file_ext;
                $file_path = 'uploads/prescriptions/' . $new_file_name;

                if (move_uploaded_file($_FILES['prescription_file']['tmp_name'], $upload_dir . $new_file_name)) {
                    // File uploaded successfully
                } else {
                    set_flash_message("Error uploading file.", "error");
                    $file_path = null;
                }
            }
        }

        // Insert or update prescription
        if ($is_edit && $prescription_id > 0) {
            // Update existing prescription
            $update_file = $file_path ? ", file_path = '$file_path'" : "";
            $sql = "UPDATE prescriptions SET 
                    customer_id = $customer_id,
                    doctor_name = '$doctor_name',
                    prescription_date = '$prescription_date',
                    status = '$status',
                    notes = '$notes'
                    $update_file
                    WHERE prescription_id = $prescription_id";
            
            if ($conn->query($sql)) {
                set_flash_message("Prescription updated successfully!", "success");
                header("Location: prescriptions.php");
                exit();
            } else {
                set_flash_message("Error updating prescription: " . $conn->error, "error");
            }
        } else {
            // Insert new prescription
            $stmt = $conn->prepare("INSERT INTO prescriptions (customer_id, doctor_name, prescription_date, file_path, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $customer_id, $doctor_name, $prescription_date, $file_path, $status, $notes);
            
            if ($stmt->execute()) {
                set_flash_message("Prescription added successfully!", "success");
                header("Location: prescriptions.php");
                exit();
            } else {
                set_flash_message("Error adding prescription: " . $stmt->error, "error");
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Prescription - PHARMACIA</title>
</head>
<body class="bg-[#f8fafc]">
    <?php require('../sidebar.php'); ?>

    <!-- Title Header -->
    <div class="mb-10 flex items-center justify-between">
        <div>
            <p class="subheading-premium">Medical Records</p>
            <h1 class="heading-premium"><?php echo $is_edit ? 'Edit' : 'Add New'; ?> Prescription</h1>
        </div>
        <a href="prescriptions.php" class="btn-primary btn-slate">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <!-- Form Card -->
    <div class="premium-card max-w-2xl">
        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            <!-- Customer Selection -->
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Patient <span class="text-rose-500">*</span></label>
                <select name="customer_id" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    <option value="">Select Patient</option>
                    <?php while($customer = $customers->fetch_assoc()): ?>
                        <option value="<?php echo $customer['C_ID']; ?>" 
                                <?php echo ($prescription && $prescription['customer_id'] == $customer['C_ID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['C_Fname'] . ' ' . $customer['C_Lname']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Doctor Name -->
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Doctor Name <span class="text-rose-500">*</span></label>
                <input type="text" name="doctor_name" required
                       placeholder="Dr. John Smith"
                       value="<?php echo $prescription ? htmlspecialchars($prescription['doctor_name']) : ''; ?>"
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
            </div>

            <!-- Prescription Date -->
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Prescription Date <span class="text-rose-500">*</span></label>
                <input type="date" name="prescription_date" required
                       value="<?php echo $prescription ? $prescription['prescription_date'] : date('Y-m-d'); ?>"
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Status</label>
                <select name="status"
                        class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700">
                    <option value="Active" <?php echo (!$prescription || $prescription['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Completed" <?php echo ($prescription && $prescription['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="Expired" <?php echo ($prescription && $prescription['status'] === 'Expired') ? 'selected' : ''; ?>>Expired</option>
                    <option value="Archived" <?php echo ($prescription && $prescription['status'] === 'Archived') ? 'selected' : ''; ?>>Archived</option>
                </select>
            </div>

            <!-- Prescription File -->
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Prescription File (PDF, Image, or Document)</label>
                <div class="relative">
                    <input type="file" name="prescription_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                           class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                </div>
                <?php if ($prescription && $prescription['file_path']): ?>
                    <p class="text-sm text-slate-600 mt-2">
                        <i class="fas fa-file mr-2"></i>Current file: <a href="../../<?php echo htmlspecialchars($prescription['file_path']); ?>" target="_blank" class="text-blue-600 hover:underline">View</a>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Notes</label>
                <textarea name="notes" rows="4"
                          placeholder="Additional notes about the prescription..."
                          class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none font-semibold text-slate-700"><?php echo $prescription ? htmlspecialchars($prescription['notes']) : ''; ?></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 btn-primary btn-blue">
                    <i class="fas fa-save mr-2"></i> <?php echo $is_edit ? 'Update' : 'Add'; ?> Prescription
                </button>
                <a href="prescriptions.php" class="flex-1 btn-primary btn-slate text-center">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Closing Sidebar Tags -->
    </main>
    </div>
    </div>
</body>
</html>
