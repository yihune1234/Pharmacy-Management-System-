
<?php
// Determine the relative path to the pharmacist root
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path = ($current_dir == 'pharmacist') ? '' : '../';
?>

	<div class="sidenav">
			<h2 style="font-family:Arial; color:white; text-align:center;"> PHARMACIA </h2>
			<a href="<?php echo $path; ?>dashboard.php">Dashboard</a>
			
			<a href="<?php echo $path; ?>inventory/view.php">View Inventory</a>
			<a href="<?php echo $path; ?>sales/pos1.php">Add New Sale</a>
			<button class="dropdown-btn">Customers
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="<?php echo $path; ?>customers/add.php">Add New Customer</a>
				<a href="<?php echo $path; ?>customers/view.php">View Customers</a>
			</div>
	</div>
	
	<?php
	
	include $path . "../../config/config.php";
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	
	$sql="SELECT E_FNAME from EMPLOYEE WHERE E_ID='$_SESSION[user]'";
	$result=$conn->query($sql);
	$row=$result->fetch_row();
	
	$ename=$row[0];
		
	?>

	<div class="topnav">
		<button class="menu-toggle" id="menu-toggle">
			<span></span>
			<span></span>
			<span></span>
		</button>
		<a href="<?php echo $path; ?>../auth/logout.php">Logout (<?php echo $ename; ?>)</a>
	</div>

	<script>
		document.getElementById('menu-toggle').addEventListener('click', function() {
			document.querySelector('.sidenav').classList.toggle('mobile-visible');
			this.classList.toggle('active');
		});
	</script>
	