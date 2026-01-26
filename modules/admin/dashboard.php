<!DOCTYPE html>
<html>

<head>
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
	
	<a href="sales/pos1.php" title="Add New Sale">
	<img src="../../assets/images/admin/carticon1.png" style="padding:8px;margin-left:450px;margin-top:40px;width:200px;height:200px;border:2px solid black;" alt="Add New Sale">
	</a>
	
	<a href="inventory/view.php" title="View Inventory">
	<img src="../../assets/images/common/inventory.png" style="padding:8px;margin-left:100px;margin-top:40px;width:200px;height:200px;border:2px solid black;" alt="Inventory">
	</a>
	
	<a href="employees/view.php" title="View Employees">
	<img src="../../assets/images/admin/emp.png" style="padding:8px;margin-left:100px;margin-top:40px;width:200px;height:200px;border:2px solid black;" alt="Employees List">
	</a>
	<br>
	<a href="reports/sales_report.php" title="View Transactions">
	<img src="../../assets/images/common/moneyicon.png" style="padding:8px;margin-left:550px;margin-top:40px;width:200px;height:200px;border:2px solid black;" alt="Transactions List">
	</a>
	
	<a href="reports/stock_report.php" title="Low Stock Alert">
	<img src="../../assets/images/admin/alert.png" style="padding:8px;margin-left:100px;margin-top:40px;width:200px;height:200px;border:2px solid black;" alt="Low Stock Report">
	</a>
	
	
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