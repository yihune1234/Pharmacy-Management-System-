<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" type="text/css" href="login1.css">
<div class="header">
<h1>Login </h1>
<p style="margin-top:-20px;line-height:1;font-size:30px;">Pharmacy Management System</p>
</div>
<title>
pharmacy
</title>
</head>

<body>

	<br><br><br><br>
	<div class="container">
		<form method="post" action="">
			<div id="div_login">
				<h1>Pharmacist Login</h1>
				<center>
				<div>
					<input type="text" class="textbox" id="uname" name="uname" placeholder="Username" />
				</div>
				<div>
					<input type="password" class="textbox" id="pwd" name="pwd" placeholder="Password"/>
				</div>
				<div>
					<input type="submit" value="Submit" name="submit" id="submit" />
					<input type="submit" value="Click here for Admin Login" name="psubmit" id="submit" />
				</div>
			 
				
	<?php
include "config.php";

if(isset($_POST['submit'])){

    $uname = mysqli_real_escape_string($conn,$_POST['uname']);
    $password = mysqli_real_escape_string($conn,$_POST['pwd']);

    if ($uname != "" && $password != ""){

        // Fetch user details along with role
        $sql="SELECT e_id, role FROM emplogin WHERE e_username='$uname' AND e_pass='$password'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc(); // fetch_assoc to get role by key

        if(!$row) {
            echo "<p style='color:red;'>Invalid username or password!</p>";
        }
        else {
            $emp = $row['e_id'];
            $role = $row['role']; // assuming your table has a 'role' column

            session_start();
            $_SESSION['user'] = $emp;
            $_SESSION['role'] = $role;

            // Redirect based on role
            if($role == "pharmacist"){
                header("location:pharmmainpage.php");
                exit();
            } elseif($role == "admin") {
                header("location:adminmainpage.php");
                exit();
            } else {
                echo "<p style='color:red;'>Unknown role!</p>";
            }
        }
    }
}

if(isset($_POST['psubmit']))
{
    header("location:mainpage.php");
}
?>

				</center> 
			</div>
		</form>
	</div>
	<div class=footer>
	<br>
	Powered by VE Technologies. 
	<br><br>
	</div>

</body>

</html>