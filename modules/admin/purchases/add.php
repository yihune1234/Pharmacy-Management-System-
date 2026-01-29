<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if (isset($_POST['add'])) {
    $pid = $conn->real_escape_string($_POST['pid']);
    $sid = $conn->real_escape_string($_POST['sid']);
    $mid = $conn->real_escape_string($_POST['mid']);
    $qty = $conn->real_escape_string($_POST['pqty']);
    $cost = $conn->real_escape_string($_POST['pcost']);
    $pdate = $conn->real_escape_string($_POST['pdate']);
    $mdate = $conn->real_escape_string($_POST['mdate']);
    $edate = $conn->real_escape_string($_POST['edate']);

    $sql = "INSERT INTO purchase (p_id, sup_id, med_id, p_qty, p_cost, pur_date, mfg_date, exp_date) 
            VALUES ('$pid', '$sid', '$mid', '$qty', '$cost', '$pdate', '$mdate', '$edate')";
    
    if ($conn->query($sql)) {
        set_flash_message("Purchase order recorded successfully.", "success");
        header("Location: view.php");
        exit();
    } else {
        set_flash_message("Unable to record purchase. Verify record IDs.", "error");
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../../../assets/css/nav.css">
    <link rel="stylesheet" type="text/css" href="../../../assets/css/form.css">
    <title>Add Purchase - PHARMACIA</title>
</head>

<body>
    <?php render_flash_message(); ?>
    <?php require('../sidebar.php'); ?>

    <center>
        <div class="head">
            <h2> ADD PURCHASE DETAILS</h2>
        </div>
    </center>

    <div class="one row">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <div class="column">
                <p>
                    <label for="pid">Purchase ID:</label><br>
                    <input type="number" name="pid" required>
                </p>
                <p>
                    <label for="sid">Supplier ID:</label><br>
                    <input type="number" name="sid" required>
                </p>
                <p>
                    <label for="mid">Medicine ID:</label><br>
                    <input type="number" name="mid" required>
                </p>
                <p>
                    <label for="pqty">Purchase Quantity:</label><br>
                    <input type="number" name="pqty" required>
                </p>
            </div>
            <div class="column">
                <p>
                    <label for="pcost">Purchase Cost:</label><br>
                    <input type="number" step="0.01" name="pcost" required>
                </p>
                <p>
                    <label for="pdate">Date of Purchase:</label><br>
                    <input type="date" name="pdate" required>
                </p>
                <p>
                    <label for="mdate">Manufacturing Date:</label><br>
                    <input type="date" name="mdate">
                </p>
                <p>
                    <label for="edate">Expiry Date:</label><br>
                    <input type="date" name="edate">
                </p>
            </div>
            <input type="submit" name="add" value="Add Purchase">
        </form>
    </div>

</body>

<script>
    var dropdown = document.getElementsByClassName("dropdown-btn");
    var i;
    for (i = 0; i < dropdown.length; i++) {
        dropdown[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
            } else {
                dropdownContent.style.display = "block";
            }
        });
    }
</script>

</html>