<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" type="text/css" href="../../../assets/css/nav.css">
<link rel="stylesheet" type="text/css" href="../../../assets/css/form.css">
<title>
Customers
</title>
</head>

<body>
<?php 
require('../sidebar.php');
?>
	
	<center>
	<div class="head">
	<h2> UPDATE CUSTOMER DETAILS</h2>
	</div>
	</center>


	<div class="one">
		<div class="row">
		
	<?php
		
		include "../../../config/config.php";
		
		if(isset($_GET['id']))
		{
			$id=$_GET['id'];
			$qry1="SELECT * FROM customer WHERE c_id='$id'";
			$result = $conn->query($qry1);
			$row = $result -> fetch_row();
		}

		if( isset($_POST['update']))
		 {
			$id = $_POST['cid'];
			$fname = $_POST['cfname'];
			$lname = $_POST['clname'];
			$age = $_POST['age'];
			$sex = $_POST['sex'];
			$phno = $_POST['phno'];
			$mail = $_POST['emid'];
			 
		$sql="UPDATE customer SET c_fname='$fname',c_lname='$lname',c_age='$age',c_sex='$sex',c_phno='$phno',c_mail='$mail' where c_id='$id'";
		if ($conn->query($sql))
		header("location:view.php");
		else
		echo "<p style='font-size:8; color:red;'>Error! Unable to update.</p>";
		}

	?>
			<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
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