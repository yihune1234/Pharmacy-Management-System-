<!DOCTYPE html>
<html>

<head>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="stylesheet" type="text/css" href="../../assets/css/nav.css">
<title>
Admin Dashboard
</title>
</head>

<body>
<?php 
require('./sidebar.php');
?>
	<center>
	<div class="head">
	<h2> ADMIN DASHBOARD </h2>
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
			
			<a href="employees/view.php" class="dashboard-card" title="View Employees">
				<img src="../../assets/images/admin/emp.png" alt="Employees List">
				<span>Manage Employees</span>
			</a>

			<a href="reports/sales_report.php" class="dashboard-card" title="View Transactions">
				<img src="../../assets/images/common/moneyicon.png" alt="Transactions List">
				<span>Sales Reports</span>
			</a>
			
			<a href="reports/stock_report.php" class="dashboard-card" title="Low Stock Alert">
				<img src="../../assets/images/admin/alert.png" alt="Low Stock Report">
				<span>Low Stock Alert</span>
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
	  } else {
	  dropdownContent.style.display = "block";
	  }
	  });
	}
</script>

</html>