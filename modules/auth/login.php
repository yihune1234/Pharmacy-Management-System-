<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../../assets/css/login.css">
    <div class="header">
        <h1>Login</h1>
        <p style="margin-top:-20px;line-height:1;font-size:30px;">Pharmacy Management System</p>
    </div>
    <title>Pharmacy</title>
</head>

<body>
    <br><br><br><br>
    <div class="container">
        <form method="post" action="">
            <div id="div_login">
                <h1>Login</h1>
                <center>
                    <div>
                        <input type="text" class="textbox" name="uname" placeholder="Username" required />
                    </div>
                    <div>
                        <input type="password" class="textbox" name="pwd" placeholder="Password" required />
                    </div>
                    <div>
                        <input type="submit" value="Login" name="submit" id="submit" />
                    </div>
                </center>
            </div>
        </form>
    </div>

<?php
include "../../config/config.php";
session_start();

if(isset($_POST['submit'])){

    $uname = mysqli_real_escape_string($conn, $_POST['uname']);
    $password = mysqli_real_escape_string($conn, $_POST['pwd']);

    if($uname != "" && $password != ""){

        // Fetch user info along with role name
        $sql = "
            SELECT e.E_ID, e.username, e.password, r.role_name
            FROM employee e
            LEFT JOIN roles r ON e.role_id = r.role_id
            WHERE e.username = '$uname' AND e.password = '$password'
        ";
        $result = $conn->query($sql);

        if($result && $result->num_rows == 1){
            $row = $result->fetch_assoc();

            $_SESSION['user'] = $row['E_ID'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role_name'];

            // Redirect based on role
            switch(strtolower($row['role_name'])){
                case "admin":
                    header("Location: ../admin/dashboard.php");
                    exit();
                case "pharmacist":
                    header("Location: ../pharmacist/dashboard.php");
                    exit();
                case "cashier":
                    header("Location: ../cashier/dashboard.php");
                    exit();
                default:
                    echo "<p style='color:red;'>Unknown role!</p>";
            }

        } else {
            echo "<p style='color:red;'>Invalid username or password!</p>";
        }

    } else {
        echo "<p style='color:red;'>Please enter both username and password!</p>";
    }
}
?>

    <div class="footer">
        <br>
        Powered by VE Technologies. 
        <br><br>
    </div>
</body>
</html>
