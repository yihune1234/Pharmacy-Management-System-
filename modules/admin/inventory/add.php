<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="../../../assets/css/nav.css">
<link rel="stylesheet" type="text/css" href="../../../assets/css/form.css">
<title>
Medicines
</title>
</head>

<body>
<?php 
require('../sidebar.php');
?>
	<center>
	<div class="head">
	<h2> ADD MEDICINE DETAILS</h2>
	</div>
	</center>
	
	
	<br><br><br><br><br><br><br><br>
	
	
	<div class="one row" >
		<div class="row">
			<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
				<div class="column">
					<p>
						<label for="medid">Medicine ID:</label><br>
						<input type="number" name="medid">
					</p>
					<p>
						<label for="medname">Medicine Name:</label><br>
						<input type="text" name="medname">
					</p>
					<p>
						<label for="qty">Quantity:</label><br>
						<input type="number" name="qty">
					</p>
					<p>
						<label for="cat">Category:</label><br>
						<select id="cat" name="cat">
								<option>Tablet</option>
								<option>Capsule</option>
								<option>Syrup</option>
						</select>
					</p>
					
				</div>
				<div class="column">
					
					<p>
						<label for="sp">Price: </label><br>
						<input type="number" step="0.01" name="sp">
					</p>
					<p>
						<label for="loc">Location:</label><br>
						<input type="text" name="loc">
					</p>
				</div>
				
			
			<input type="submit" name="add" value="Add Medicine">
			</form>
		<br>
		
	</div>	
	<?php
	
		include "../../../config/config.php";
		 
		if(isset($_POST['add']))
		{
		$id = mysqli_real_escape_string($conn, $_REQUEST['medid']);
		$name = mysqli_real_escape_string($conn, $_REQUEST['medname']);
		$qty = mysqli_real_escape_string($conn, $_REQUEST['qty']);
		$category = mysqli_real_escape_string($conn, $_REQUEST['cat']);
		$sprice = mysqli_real_escape_string($conn, $_REQUEST['sp']);
		$location = mysqli_real_escape_string($conn, $_REQUEST['loc']);

		 
		$sql = "INSERT INTO meds VALUES ($id, '$name', $qty,'$category',$sprice, '$location')";
		if(mysqli_query($conn, $sql)){
			echo "<p style='font-size:8;'>Medicine details successfully added!</p>";
		} else{
			echo "<p style='font-size:8; color:red;'>Error! Check details.</p>";
		}
		}
		 
		$conn->close();
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


