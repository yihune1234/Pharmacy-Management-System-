<?php 
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/session_check.php';

// Validate pharmacist access
require_pharmacist();
validate_role_area('pharmacist');

	if(isset($_POST['search'])) {
		
		$search=$_POST['valuetosearch'];
		$query="SELECT c_id, c_fname,c_lname,c_phno FROM `customer` 
			WHERE CONCAT(c_id, c_fname,c_lname,c_phno) LIKE '%".$search."%';";
		$search_result=filtertable($query);
	}
	
	else {
			$query="SELECT c_id, c_fname,c_lname,c_phno FROM `customer`";
			$search_result=filtertable($query);
	}
	
	function filtertable($query)
	{	global $conn;
		$filter_result=mysqli_query($conn,$query);
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
Customers
</title>
</head>

<body>

		<?php require('../sidebar.php'); ?>
	
	<center>
	
	<div class="head">
	<h2>  CUSTOMER LIST</h2>
	</div>
	
	<form method="post">
	<input type="text" name="valuetosearch" placeholder="Enter any value to Search" style="width:400px; margin-left:250px;">&nbsp;&nbsp;&nbsp;
	<input type="submit" name="search" value="Search">
	<br><br>
	</form> 
	
	</center>

	
	<table align="right" id="table1" style="margin-right:100px;">
		<tr>
			<th>Customer ID</th>
			<th>First Name</th>
			<th>Last Name</th>
			
			<th>Phone Number</th>
		</tr>
		
	<?php
	
		if ($search_result->num_rows > 0) {
		while($row = $search_result->fetch_assoc()) {

		echo "<tr>";
			echo "<td>" . $row["c_id"]. "</td>";
			echo "<td>" . $row["c_fname"] . "</td>";
			echo "<td>" . $row["c_lname"]. "</td>";
			echo "<td>" . $row["c_phno"]. "</td>";
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
