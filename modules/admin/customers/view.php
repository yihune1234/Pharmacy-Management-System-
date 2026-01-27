<!DOCTYPE html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="../../../assets/css/nav.css">
<link rel="stylesheet" type="text/css" href="../../../assets/css/table.css">
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
	<h2>  CUSTOMER LIST</h2>
	</div>
	</center>

	
	<table align="right" id="table1" style="margin-right:100px;">
		<tr>
			<th>Customer ID</th>
			<th>First Name</th>
			<th>Last Name</th>
			<th>Age</th>
			<th>Sex</th>
			<th>Phone Number</th>
			<th>Email Address</th>
			<th>Action</th>
		</tr>
	<?php
	
	include "../../../config/config.php";
	$sql = "SELECT c_id,c_fname,c_lname,c_age,c_sex,c_phno,c_mail FROM customer";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
	
		while($row = $result->fetch_assoc()) {

		echo "<tr>";
			echo "<td>" . $row["c_id"]. "</td>";
			echo "<td>" . $row["c_fname"] . "</td>";
			echo "<td>" . $row["c_lname"]. "</td>";
			echo "<td>" . $row["c_age"]. "</td>";
			echo "<td>" . $row["c_sex"] . "</td>";
			echo "<td>" . $row["c_phno"]. "</td>";
			echo "<td>" . $row["c_mail"]. "</td>";
			echo "<td align=center>";
				echo "<a class='button1 edit-btn' href='update.php?id=".$row['c_id']."'>Edit</a>";
				echo "<a class='button1 del-btn' href='delete.php?id=".$row['c_id']."'>Delete</a>";
			echo "</td>";
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
