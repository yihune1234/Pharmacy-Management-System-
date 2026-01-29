<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if (isset($_GET['pid']) && isset($_GET['sid']) && isset($_GET['mid'])) {
    $pid = $conn->real_escape_string($_GET['pid']);
    $sid = $conn->real_escape_string($_GET['sid']);
    $mid = $conn->real_escape_string($_GET['mid']);
    $qry1 = "SELECT * FROM purchase WHERE p_id='$pid' AND sup_id='$sid' AND med_id='$mid'";
    $result = $conn->query($qry1);
    $row = $result->fetch_row();
}

if (isset($_POST['update'])) {
    $pid = $conn->real_escape_string($_POST['pid']);
    $sid = $conn->real_escape_string($_POST['sid']);
    $mid = $conn->real_escape_string($_POST['mid']);
    $qty = $conn->real_escape_string($_POST['pqty']);
    $cost = $conn->real_escape_string($_POST['pcost']);
    $pdate = $conn->real_escape_string($_POST['pdate']);
    $mdate = $conn->real_escape_string($_POST['mdate']);
    $edate = $conn->real_escape_string($_POST['edate']);
    
    $sql = "UPDATE purchase SET p_cost='$cost', p_qty='$qty', pur_date='$pdate', mfg_date='$mdate', exp_date='$edate' 
            WHERE p_id='$pid' AND sup_id='$sid' AND med_id='$mid'";
            
    if ($conn->query($sql)) {
        set_flash_message("Purchase details updated successfully.", "success");
        header("Location: view.php");
        exit();
    } else {
        set_flash_message("Unable to update purchase details.", "error");
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
    <title>Update Purchase - PHARMACIA</title>
</head>

<body>
    <?php render_flash_message(); ?>
    <?php require('../sidebar.php'); ?>

    <center>
        <div class="head">
            <h2> UPDATE PURCHASE DETAILS</h2>
        </div>
    </center>

    <div class="one">
        <div class="row">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="column">
                    <p>
                        <label for="pid">Purchase ID:</label><br>
                        <input type="number" name="pid" value="<?php echo $row[0]; ?>" readonly>
                    </p>
                    <p>
                        <label for="sid">Supplier ID:</label><br>
                        <input type="number" name="sid" value="<?php echo $row[1]; ?>" readonly>
                    </p>
                    <p>
                        <label for="mid">Medicine ID:</label><br>
                        <input type="number" name="mid" value="<?php echo $row[2]; ?>" readonly>
                    </p>
                    <p>
                        <label for="pqty">Purchase Quantity:</label><br>
                        <input type="number" name="pqty" value="<?php echo $row[3]; ?>">
                    </p>
                </div>
                <div class="column">
                    <p>
                        <label for="pcost">Purchase Cost:</label><br>
                        <input type="number" step="0.01" name="pcost" value="<?php echo $row[4]; ?>">
                    </p>
                    <p>
                        <label for="pdate">Date of Purchase:</label><br>
                        <input type="date" name="pdate" value="<?php echo $row[5]; ?>">
                    </p>
                    <p>
                        <label for="mdate">Manufacturing Date:</label><br>
                        <input type="date" name="mdate" value="<?php echo $row[6]; ?>">
                    </p>
                    <p>
                        <label for="edate">Expiry Date:</label><br>
                        <input type="date" name="edate" value="<?php echo $row[7]; ?>">
                    </p>
                </div>
                <input type="submit" name="update" value="Update">
            </form>
        </div>
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