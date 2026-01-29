<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if (isset($_POST['add'])) {
    $id = $conn->real_escape_string($_POST['cid']);
    $fname = $conn->real_escape_string($_POST['cfname']);
    $lname = $conn->real_escape_string($_POST['clname']);
    $age = $conn->real_escape_string($_POST['age']);
    $sex = $conn->real_escape_string($_POST['sex']);
    $phno = $conn->real_escape_string($_POST['phno']);
    $mail = $conn->real_escape_string($_POST['emid']);

    $sql = "INSERT INTO customer (C_ID, C_Fname, C_Lname, C_Age, C_Sex, C_Phno, C_Mail) 
            VALUES ('$id', '$fname', '$lname', '$age', '$sex', '$phno', '$mail')";
    
    if ($conn->query($sql)) {
        set_flash_message("Customer '$fname' registered successfully.", "success");
        header("Location: view.php");
        exit();
    } else {
        set_flash_message("Unable to register customer. Check for duplicate ID.", "error");
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
    <title>Add Customer - PHARMACIA</title>
</head>

<body>
    <?php render_flash_message(); ?>
    <?php require('../sidebar.php'); ?>

    <center>
        <div class="head">
            <h2> ADD CUSTOMER DETAILS</h2>
        </div>
    </center>

    <div class="one">
        <div class="row">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="column">
                    <p>
                        <label for="cid">Customer ID:</label><br>
                        <input type="number" name="cid" required>
                    </p>
                    <p>
                        <label for="cfname">First Name:</label><br>
                        <input type="text" name="cfname" required>
                    </p>
                    <p>
                        <label for="clname">Last Name:</label><br>
                        <input type="text" name="clname" required>
                    </p>
                    <p>
                        <label for="age">Age:</label><br>
                        <input type="number" name="age">
                    </p>
                    <p>
                        <label for="sex">Sex: </label><br>
                        <select id="sex" name="sex">
                            <option value="">Select</option>
                            <option>Female</option>
                            <option>Male</option>
                            <option>Others</option>
                        </select>
                    </p>
                </div>
                <div class="column">
                    <p>
                        <label for="phno">Phone Number: </label><br>
                        <input type="number" name="phno" required>
                    </p>
                    <p>
                        <label for="emid">Email ID:</label><br>
                        <input type="text" name="emid">
                    </p>
                </div>
                <input type="submit" name="add" value="Add Customer">
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