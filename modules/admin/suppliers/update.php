<?php
		include "../../../config/config.php";
	
		if(isset($_GET['id']))
		{
			$id=$_GET['id'];
			$qry1="SELECT * FROM suppliers WHERE sup_id='$id'";
			$result = $conn->query($qry1);
			$row = $result -> fetch_row();
		}
?>

<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="../../../assets/css/nav.css">
<link rel="stylesheet" type="text/css" href="../../../assets/css/form.css">
<title>
Suppliers
</title>
</head>

<body>

	<?php require('../sidebar.php'); ?>
	
	<center>
	<div class="head">
	<h2> UPDATE SUPPLIER DETAILS</h2>
	</div>
	</center>


	<div class="one">
		<div class="row">
			<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
				<div class="column">
					<p>
						<label for="sid">Supplier ID:</label><br>
						<input type="number" name="sid" value="<?php echo $row[0]; ?>" readonly>
					</p>
					<p>
						<label for="sname">Supplier Company Name:</label><br>
						<input type="text" name="sname" value="<?php echo $row[1]; ?>">
					</p>
					<p>
						<label for="sadd">Address:</label><br>
						<input type="text" name="sadd" value="<?php echo $row[2]; ?>">
					</p>
					
					
				</div>
				<div class="column">
					<p>
						<label for="sphno">Phone Number:</label><br>
						<input type="number" name="sphno" value="<?php echo $row[3]; ?>">
					</p>
					
					<p>
						<label for="smail">Email Address </label><br>
						<input type="text" name="smail" value="<?php echo $row[4]; ?>">
					</p>
					
				</div>
				
			
			<input type="submit" name="update" value="Update">
			</form>
			
	<?php
		 if( isset($_POST['update']))
		 {
			$id = $_POST['sid'];
			$name = $_POST['sname'];
			$add = $_POST['sadd'];
			$phno = $_POST['sphno'];
			$mail = $_POST['smail'];
			 
		$sql="UPDATE suppliers SET sup_name='$name',sup_add='$add',sup_phno='$phno',sup_mail='$mail' where sup_id='$id'";
		if ($conn->query($sql))
		header("location:view.php");
		else
		echo "<p style='font-size:8; color:red;'>Error! Unable to update.</p>";
		}

	?>
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