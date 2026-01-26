<!DOCTYPE html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="nav2.css">
<link rel="stylesheet" type="text/css" href="form4.css">
<title>
Employees
</title>
</head>

<body>

	<?php 
require('./admin_sidebar.php');
?>
	
	<center>
	<div class="head">
	<h2> ADD EMPLOYEE DETAILS</h2>
	</div>
	</center>
	
	
	<br><br><br><br><br><br><br><br>
	
	
	<div class="one row">
	
	<?php
	
		include "config.php";
		 
		if(isset($_POST['add']))
		{
		$id = mysqli_real_escape_string($conn, $_REQUEST['eid']);
		$fname = mysqli_real_escape_string($conn, $_REQUEST['efname']);
		$lname = mysqli_real_escape_string($conn, $_REQUEST['elname']);
		$bdate = mysqli_real_escape_string($conn, $_REQUEST['ebdate']);
		$age = mysqli_real_escape_string($conn, $_REQUEST['eage']);
		$sex = mysqli_real_escape_string($conn, $_REQUEST['esex']);
		$etype = mysqli_real_escape_string($conn, $_REQUEST['etype']);
		$jdate = mysqli_real_escape_string($conn, $_REQUEST['ejdate']);
		$sal = mysqli_real_escape_string($conn, $_REQUEST['esal']);
		$phno = mysqli_real_escape_string($conn, $_REQUEST['ephno']);
		$mail = mysqli_real_escape_string($conn, $_REQUEST['e_mail']);
		$add = mysqli_real_escape_string($conn, $_REQUEST['eadd']);

		 
		$sql = "INSERT INTO employee VALUES ($id, '$fname','$lname','$bdate',$age,'$sex','$etype','$jdate','$sal',$phno, '$mail','$add')";
		if(mysqli_query($conn, $sql)){
			echo "<p style='font-size:8;'>Employee successfully added!</p>";
		} else{
			echo "<p style='font-size:8; color:red;'>Error! Check details.</p>";
		}
		
	}
		 
		$conn->close();
	?>
		
			<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
				<div class="column">
					<p>
						<label for="eid">Employee ID:</label><br>
						<input type="number" name="eid">
					</p>
					<p>
						<label for="efname">First Name:</label><br>
						<input type="text" name="efname">
					</p>
					<p>
						<label for="elname">Last Name:</label><br>
						<input type="text" name="elname">
					</p>
					<p>
						<label for="ebdate">Date of Birth:</label><br>
						<input type="date" name="ebdate">
					</p>
					<p>
						<label for="eage">Age:</label><br>
						<input type="number" name="eage">
					</p>
					<p>
						<label for="esex">Sex:</label><br>
						<select id="esex" name="esex">
								<option value="selected">Select</option>
								<option>Female</option>
								<option>Male</option>
								<option>Others</option>
						</select>
					</p>
				</div>
				<div class="column">
					<p>
						<label for="etype">Employee Type:</label><br>
						<select id="etype" name="etype">
							<option value="selected">Select</option>
								<option>Pharmacist</option>
								<option>Manager</option>
						</select>
					</p>
					<p>
						<label for="ejdate">Date of Joining:</label><br>
						<input type="date" name="ejdate">
					</p>
					<p>
						<label for="esal">Salary:</label><br>
						<input type="number" step="0.01" name="esal">
					</p>
					<p>
						<label for="ephno">Phone Number:</label><br>
						<input type="number" name="ephno">
					</p>
					
					<p>
						<label for="e_mail">Email ID:</label><br>
						<input type="text" name="e_mail">
					</p>
					<p>
						<label for="eadd">Address:</label><br>
						<input type="text" name="eadd">
					</p>
					
				</div>
				
			
			<input type="submit" name="add" value="Add Employee">
			</form>
		<br>
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