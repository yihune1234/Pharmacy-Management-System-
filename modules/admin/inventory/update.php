<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/alerts.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate admin access
require_admin();

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $qry1 = "SELECT * FROM meds WHERE med_id = '$id'";
    $result = $conn->query($qry1);
    $row = $result->fetch_row();
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
Medicines
</title>
</head>

<body>
    <?php render_flash_message(); ?>

	<?php 
require('../sidebar.php');
?>
	<center>
	<div class="head">
	<h2> UPDATE MEDICINE DETAILS</h2>
	</div>
	</center>

	<div class="one">
		<div class="row">
			<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
				<div class="column">
				<p>
					<label for="medid">Medicine ID:</label><br>
					<input type="number" name="medid" value="<?php echo $row[0]; ?>" readonly>
				</p>
				<p>
					<label for="medname">Medicine Name:</label><br>
					<input type="text" name="medname" value="<?php echo $row[1]; ?>">
				</p>
				<p>
					<label for="qty">Quantity:</label><br>
					<input type="number" name="qty" value="<?php echo $row[2]; ?>">
				</p>
				<p>
					<label for="cat">Category:</label><br>
					<input type="text" name="cat" value="<?php echo $row[3]; ?>">
				</p>
				</div>
				
				<div class="column">
				<p>
					<label for="sp">Price: </label><br>
					<input type="number" step="0.01" name="sp" value="<?php echo $row[4]; ?>">
				</p>
				<p>
					<label for="loc">Location:</label><br>
					<input type="text" name="loc" value="<?php echo $row[5]; ?>">
				</p>
				</div>
		
				<input type="submit" name="update" value="Update">
				</form>
				
	<?php

		if (isset($_POST['update'])) {
		    $id = $conn->real_escape_string($_POST['medid']);
		    $name = $conn->real_escape_string($_POST['medname']);
		    $qty = $conn->real_escape_string($_POST['qty']);
		    $cat = $conn->real_escape_string($_POST['cat']);
		    $price = $conn->real_escape_string($_POST['sp']);
		    $lcn = $conn->real_escape_string($_POST['loc']);
			 
		    $sql = "UPDATE meds SET med_name='$name', med_qty='$qty', category='$cat', med_price='$price', location_rack='$lcn' WHERE med_id='$id'";
		    
		    if ($conn->query($sql)) {
		        set_flash_message("Medicine details updated successfully.", "success");
		        header("Location: view.php");
		        exit();
		    } else {
		        set_flash_message("Unable to update medicine details.", "error");
		    }
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