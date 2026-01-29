<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $qry1 = "SELECT * FROM employee WHERE e_id = '$id'";
    $result = $conn->query($qry1);
    $row = $result->fetch_row();
}

if (isset($_POST['update'])) {
    $id = $conn->real_escape_string($_POST['eid']);
    $fname = $conn->real_escape_string($_POST['efname']);
    $lname = $conn->real_escape_string($_POST['elname']);
    $bdate = $conn->real_escape_string($_POST['ebdate']);
    $age = $conn->real_escape_string($_POST['eage']);
    $sex = $conn->real_escape_string($_POST['esex']);
    $etype = $conn->real_escape_string($_POST['etype']);
    $jdate = $conn->real_escape_string($_POST['ejdate']);
    $sal = $conn->real_escape_string($_POST['esal']);
    $phno = $conn->real_escape_string($_POST['ephno']);
    $mail = $conn->real_escape_string($_POST['e_mail']);
    $add = $conn->real_escape_string($_POST['eadd']);
    
    $sql = "UPDATE employee
            SET e_fname='$fname', e_lname='$lname', bdate='$bdate', e_age='$age', e_sex='$sex',
            e_type='$etype', e_jdate='$jdate', e_sal='$sal', e_phno='$phno', e_mail='$mail', e_add='$add' 
            WHERE e_id='$id'";
    
    if ($conn->query($sql)) {
        set_flash_message("Employee details updated successfully.", "success");
        header("Location: view.php");
        exit();
    } else {
        set_flash_message("Unable to update employee details.", "error");
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
    <title>Update Employee - PHARMACIA</title>
</head>

<body>
    <?php render_flash_message(); ?>
    <?php require('../sidebar.php'); ?>

    <center>
        <div class="head">
            <h2> UPDATE EMPLOYEE DETAILS</h2>
        </div>
    </center>

    <div class="one">
        <div class="row">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="column">
                    <p>
                        <label for="eid">Employee ID:</label><br>
                        <input type="number" name="eid" value="<?php echo $row[0]; ?>" readonly>
                    </p>
                    <p>
                        <label for="efname">First Name:</label><br>
                        <input type="text" name="efname" value="<?php echo $row[1]; ?>">
                    </p>
                    <p>
                        <label for="elname">Last Name:</label><br>
                        <input type="text" name="elname" value="<?php echo $row[2]; ?>">
                    </p>
                    <p>
                        <label for="ebdate">Date of Birth:</label><br>
                        <input type="date" name="ebdate" value="<?php echo $row[3]; ?>">
                    </p>
                    <p>
                        <label for="eage">Age:</label><br>
                        <input type="number" name="eage" value="<?php echo $row[4]; ?>">
                    </p>
                    <p>
                        <label for="esex">Sex:</label><br>
                        <input type="text" name="esex" value="<?php echo $row[5]; ?>">
                    </p>
                </div>
                <div class="column">
                    <p>
                        <label for="etype">Employee Type:</label><br>
                        <input type="text" name="etype" value="<?php echo $row[6]; ?>">
                    </p>
                    <p>
                        <label for="ejdate">Date of Joining:</label><br>
                        <input type="date" name="ejdate" value="<?php echo $row[7]; ?>">
                    </p>
                    <p>
                        <label for="esal">Salary:</label><br>
                        <input type="number" step="0.01" name="esal" value="<?php echo $row[8]; ?>">
                    </p>
                    <p>
                        <label for="ephno">Phone Number:</label><br>
                        <input type="number" name="ephno" value="<?php echo $row[9]; ?>">
                    </p>
                    <p>
                        <label for="e_mail">Email ID:</label><br>
                        <input type="text" name="e_mail" value="<?php echo $row[10]; ?>">
                    </p>
                    <p>
                        <label for="eadd">Address:</label><br>
                        <input type="text" name="eadd" value="<?php echo $row[11]; ?>">
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