<?php
// Determine the relative path to the admin root
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path = ($current_dir == 'admin') ? '' : '../';
?>

	<div class="sidenav">
			<h2 style="font-family:Arial; color:white; text-align:center;"> PHARMACIA </h2>
			<a href="<?php echo $path; ?>dashboard.php">Dashboard</a>
			<button class="dropdown-btn">Inventory
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="<?php echo $path; ?>inventory/add.php">Add New Medicine</a>
				<a href="<?php echo $path; ?>inventory/view.php">Manage Inventory</a>
			</div>
			<button class="dropdown-btn">Suppliers
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="<?php echo $path; ?>suppliers/add.php">Add New Supplier</a>
				<a href="<?php echo $path; ?>suppliers/view.php">Manage Suppliers</a>
			</div>
			<button class="dropdown-btn">Stock Purchase
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="<?php echo $path; ?>purchases/add.php">Add New Purchase</a>
				<a href="<?php echo $path; ?>purchases/view.php">Manage Purchases</a>
			</div>
			<button class="dropdown-btn">Employees
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="<?php echo $path; ?>employees/add.php">Add New Employee</a>
				<a href="<?php echo $path; ?>employees/view.php">Manage Employees</a>
			</div>
			<button class="dropdown-btn">Customers
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="<?php echo $path; ?>customers/add.php">Add New Customer</a>
				<a href="<?php echo $path; ?>customers/view.php">Manage Customers</a>
			</div>
			<a href="<?php echo $path; ?>sales/view.php">View Sales Invoice Details</a>
			<a href="<?php echo $path; ?>sales/items_view.php">View Sold Products Details</a>
			<a href="<?php echo $path; ?>sales/pos1.php">Add New Sale</a>
			<button class="dropdown-btn">Reports
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="<?php echo $path; ?>reports/stock_report.php">Medicines - Low Stock</a>
				<a href="<?php echo $path; ?>reports/expiry_report.php">Medicines - Soon to Expire</a>
				<a href="<?php echo $path; ?>reports/sales_report.php">Transactions Reports</a>
			</div>
	</div>

	<div class="topnav">
		<button class="menu-toggle" id="menu-toggle">
			<span></span>
			<span></span>
			<span></span>
		</button>
		<a href="<?php echo $path; ?>../auth/logout.php">Logout (Admin)</a>
	</div>

	<script>
		document.getElementById('menu-toggle').addEventListener('click', function() {
			document.querySelector('.sidenav').classList.toggle('mobile-visible');
			this.classList.toggle('active');
		});
	</script>
