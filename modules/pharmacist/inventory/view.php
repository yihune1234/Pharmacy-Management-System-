<?php

	// load central config (creates $conn)
	require_once __DIR__ . '/../../../config/config.php';

	// if no working DB connection, send user to installer
	if (!isset($conn) || !($conn instanceof mysqli) || ($conn instanceof mysqli && $conn->connect_error)) {
		header('Location: /Pharmacy-Management-System/database/install.php');
		exit();
	}

	if(isset($_POST['search'])) {
		
		$search=$_POST['valuetosearch'] ?? '';
		$query="SELECT med_id as medid, med_name as medname, med_qty as medqty, category as medcategory, med_price as medprice, location_rack as medlocation FROM meds WHERE med_name LIKE '%$search%' OR med_id LIKE '%$search%'";
		$search_result=filtertable($query);
	}
	else {
			$query="SELECT med_id as medid, med_name as medname,med_qty as medqty,category as medcategory,med_price as medprice,location_rack as medlocation FROM meds";
			$search_result=filtertable($query);
	}
	
	function filtertable($query)
	{
		global $conn;
		$filter_result = $conn->query($query);
		return $filter_result;
	}
	
?>

<!DOCTYPE html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="../../../assets/css/table.css">
<link rel="stylesheet" type="text/css" href="../../../assets/css/nav.css">
<link rel="stylesheet" type="text/css" href="../../../assets/css/form.css">
<title>
Inventory
</title>
</head>

<body>

	<?php
	require('../sidebar.php');
	?>

	
	<center>
	
	<div class="head">
	<h2> MEDICINE INVENTORY </h2>
	</div>
	
	<form method="post">
	<input type="text" name="valuetosearch" placeholder="Enter any value to Search" style="width:400px; margin-left:250px;">&nbsp;&nbsp;&nbsp;
	<input type="submit" name="search" value="Search">
	<br><br>
	</form>
	
	</center>
	

	<table align="right" id="table1" style="margin-top:20px; margin-right:100px;">
		<tr>
			<th>Medicine ID</th>
			<th>Medicine Name</th>
			<th>Quantity Available</th>
			<th>Category</th>
			<th>Price</th>
			<th>Location in Store</th>
		</tr>
		
	<?php
	
		if ($search_result->num_rows > 0) {
		
		while($row = $search_result->fetch_assoc()) {

		echo "<tr>";
			echo "<td>" . $row["medid"]. "</td>";
			echo "<td>" . $row["medname"] . "</td>";
			echo "<td>" . $row["medqty"]. "</td>";
			echo "<td>" . $row["medcategory"]. "</td>";
			echo "<td>" . $row["medprice"] . "</td>";
			echo "<td>" . $row["medlocation"]. "</td>";
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
