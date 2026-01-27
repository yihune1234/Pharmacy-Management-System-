<!DOCTYPE html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="../../assets/css/table.css">
<link rel="stylesheet" type="text/css" href="../../assets/css/nav.css">
<title>
Pharmacist Dashboard
</title>
</head>
<style>
body {font-family:Arial;}
</style>

<body>
<?php
require_once('./sidebar.php')
?>
	<center>
	<div class="head">
	<h2> PHARMACIST DASHBOARD </h2>
	</div>
	</center>
	
	<div class="main-content">
		<div class="dashboard-cards">
			<a href="sales/pos1.php" class="dashboard-card" title="Add New Sale">
				<img src="../../assets/images/admin/carticon1.png" alt="Add New Sale">
				<span>Point of Sale</span>
			</a>
			
			<a href="inventory/view.php" class="dashboard-card" title="View Inventory">
				<img src="../../assets/images/common/inventory.png" alt="Inventory">
				<span>View Inventory</span>
			</a>
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
		  } 
		  else {
		  dropdownContent.style.display = "block";
		  }
		});
	}
	
</script>

</html>