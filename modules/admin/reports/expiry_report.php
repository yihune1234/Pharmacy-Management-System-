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
<?php 
require('../sidebar.php');
?>
	<center>
	<div class="head">
	<h2> STOCK EXPIRING WITHIN 6 MONTHS</h2>
	</div>
	</center>
	
	<table align="right" id="table1" style="margin-right:100px;">
		<tr>
			<th>Purchase ID</th>
			<th>Supplier ID</th>
			<th>Medicine ID</th>
			<th>Quantity</th>
			<th>Cost of Purchase</th>
			<th>Date of Purchase</th>
			<th>Manufacturing Date</th>
			<th>Expiry Date</th>
		</tr>
		
	<?php
	
		include "../../../config/config.php";
		$result=mysqli_query($conn,"SELECT p_id, sup_id, med_id, p_qty, p_cost, pur_date, mfg_date, exp_date FROM purchase WHERE exp_date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH) AND exp_date >= CURDATE();");
		if ($result->num_rows > 0) { 

		while($row = $result->fetch_assoc()) {
			
		echo "<tr>";
			echo "<td>" . $row["p_id"]. "</td>";
			echo "<td>" . $row["sup_id"]. "</td>";
			echo "<td>" . $row["med_id"]. "</td>";
			echo "<td>" . $row["p_qty"]. "</td>";
			echo "<td>" . $row["p_cost"]. "</td>";
			echo "<td>" . $row["pur_date"]. "</td>";
			echo "<td>" . $row["mfg_date"] . "</td>";
			echo "<td style='color:red;'>" . $row["exp_date"]. "</td>";
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
