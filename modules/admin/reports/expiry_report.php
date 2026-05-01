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
		$result=mysqli_query($conn,"SELECT P_ID, Sup_ID, Med_ID, P_Qty, P_Cost, Pur_Date, Mfg_Date, Exp_Date FROM purchase WHERE Exp_Date <= DATE_ADD(CURDATE(), INTERVAL 6 MONTH) AND Exp_Date >= CURDATE();");
		if ($result->num_rows > 0) { 

		while($row = $result->fetch_assoc()) {
			
		echo "<tr>";
			echo "<td>" . $row["P_ID"]. "</td>";
			echo "<td>" . $row["Sup_ID"]. "</td>";
			echo "<td>" . $row["Med_ID"]. "</td>";
			echo "<td>" . $row["P_Qty"]. "</td>";
			echo "<td>" . $row["P_Cost"]. "</td>";
			echo "<td>" . $row["Pur_Date"]. "</td>";
			echo "<td>" . $row["Mfg_Date"] . "</td>";
			echo "<td style='color:red;'>" . $row["Exp_Date"]. "</td>";
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
