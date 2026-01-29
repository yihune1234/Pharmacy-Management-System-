<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $qry1 = "SELECT * FROM customer WHERE c_id = '$id'";
    $result = $conn->query($qry1);
    $row = $result->fetch_row();
}

if (isset($_POST['update'])) {
    $id = $conn->real_escape_string($_POST['cid']);
    $fname = $conn->real_escape_string($_POST['cfname']);
    $lname = $conn->real_escape_string($_POST['clname']);
    $age = $conn->real_escape_string($_POST['age']);
    $sex = $conn->real_escape_string($_POST['sex']);
    $phno = $conn->real_escape_string($_POST['phno']);
    $mail = $conn->real_escape_string($_POST['emid']);
    
    $sql = "UPDATE customer SET c_fname='$fname', c_lname='$lname', c_age='$age', c_sex='$sex', c_phno='$phno', c_mail='$mail' WHERE c_id='$id'";
    
    if ($conn->query($sql)) {
        set_flash_message("Customer details updated successfully.", "success");
        header("Location: view.php");
        exit();
    } else {
        set_flash_message("Unable to update customer details.", "error");
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
    <title>Update Customer - PHARMACIA</title>
</head>

<body>
    <?php render_flash_message(); ?>
    <?php require('../sidebar.php'); ?>

    <center>
        <div class="head">
            <h2> UPDATE CUSTOMER DETAILS</h2>
        </div>
    </center>

    <div class="one">
        <div class="row">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="column">
                    <p>
                        <label for="cid">Customer ID:</label><br>
                        <input type="number" name="cid" value="<?php echo $row[0]; ?>" readonly>
                    </p>
                    <p>
                        <label for="cfname">First Name:</label><br>
                        <input type="text" name="cfname" value="<?php echo $row[1]; ?>">
                    </p>
                    <p>
                        <label for="clname">Last Name:</label><br>
                        <input type="text" name="clname" value="<?php echo $row[2]; ?>">
                    </p>
                    <p>
                        <label for="age">Age:</label><br>
                        <input type="number" name="age" value="<?php echo $row[3]; ?>">
                    </p>
                    <p>
                        <label for="sex">Sex: </label><br>
                        <input type="text" name="sex" value="<?php echo $row[4]; ?>">
                    </p>
                </div>
                <div class="column">
                    <p>
                        <label for="phno">Phone Number: </label><br>
                        <input type="number" name="phno" value="<?php echo $row[5]; ?>">
                    </p>
                    <p>
                        <label for="emid">Email ID:</label><br>
                        <input type="text" name="emid" value="<?php echo $row[6]; ?>">
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