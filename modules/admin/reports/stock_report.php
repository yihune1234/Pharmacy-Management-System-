<!DOCTYPE html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="../../../assets/css/nav.css">
<link rel="stylesheet" type="text/css" href="../../../assets/css/table.css">
<title>
Reports
</title>
</head>

<body>

	<?php require('../sidebar.php'); ?>
	
	<center>
	<div class="head">
	<h2> MEDICINES LOW ON STOCK(LESS THAN 50)</h2>
	</div>
	</center>
	
	<table align="right" id="table1" style="margin-right:100px;">
		<tr>
			<th>Medicine ID</th>
			<th>Medicine Name</th>
			<th>Quantity Available</th>
			<th>Category</th>
			<th>Price</th>
		</tr>
		
	<?php
	
	include "../../../config/config.php";
	$result=mysqli_query($conn,"SELECT med_id, med_name, med_qty, category, med_price FROM meds WHERE med_qty < 50;");
	if ($result->num_rows > 0) {
	
	while($row = $result->fetch_assoc()) {

	echo "<tr>";
		echo "<td>" . $row["med_id"]. "</td>";
		echo "<td>" . $row["med_name"] . "</td>";
		echo "<td style='color:red;'>" . $row["med_qty"]. "</td>";
		echo "<td>" . $row["category"]. "</td>";
		echo "<td>" . $row["med_price"] . "</td>";
	echo "</tr>";
	}
	echo "</table>";
	} 

	$conn->close();
	?>
	
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
