<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Medicine - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Add Medicine</h2>
            <p class="text-slate-500 mt-1">Register a new pharmaceutical product in the database.</p>
        </div>
        <a href="view.php" class="text-slate-500 hover:text-slate-900 font-bold transition-all text-sm flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Inventory
        </a>
    </div>

    <!-- Form Card -->
    <div class="max-w-4xl bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden p-8 md:p-12 mx-auto lg:ml-0">
        <form action="<?=$_SERVER['PHP_SELF']?>" method="post" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">Medicine ID</label>
                        <input type="number" name="medid" placeholder="e.g. 1001" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium" />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">Medicine Name</label>
                        <input type="text" name="medname" placeholder="Enter formal name" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium" />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">Initial Quantity</label>
                        <input type="number" name="qty" placeholder="0" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium" />
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">Stock Category</label>
                        <select id="cat" name="cat" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium appearance-none">
                            <option value="">Select a category</option>
                            <option>Tablet</option>
                            <option>Capsule</option>
                            <option>Syrup</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">Unit Price (Rs)</label>
                        <input type="number" step="0.01" name="sp" placeholder="0.00" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-bold" />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">Store Location (Rack/Shelf)</label>
                        <input type="text" name="loc" placeholder="Shelf A-1" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all font-medium" />
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between space-y-4 sm:space-y-0">
                <p class="text-xs text-slate-400 font-medium">* Ensure all information is verified before submission.</p>
                <button type="submit" name="add" class="bg-blue-600 hover:bg-blue-700 text-white font-black py-4 px-10 rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95">
                    Save Product Details
                </button>
            </div>
        </form>

        <?php
        include "../../../config/config.php";
        if(isset($_POST['add'])) {
            $id = mysqli_real_escape_string($conn, $_REQUEST['medid']);
            $name = mysqli_real_escape_string($conn, $_REQUEST['medname']);
            $qty = mysqli_real_escape_string($conn, $_REQUEST['qty']);
            $category = mysqli_real_escape_string($conn, $_REQUEST['cat']);
            $sprice = mysqli_real_escape_string($conn, $_REQUEST['sp']);
            $location = mysqli_real_escape_string($conn, $_REQUEST['loc']);

            $sql = "INSERT INTO meds VALUES ($id, '$name', $qty,'$category',$sprice, '$location')";
            if(mysqli_query($conn, $sql)){
                echo "<div class='mt-8 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center font-bold animate-bounce'>";
                echo "<svg class='w-5 h-5 mr-3' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'></path></svg>";
                echo "Success! Medicine has been added to inventory.";
                echo "</div>";
            } else{
                echo "<div class='mt-8 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl flex items-center font-bold'>";
                echo "<svg class='w-5 h-5 mr-3' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z' clip-rule='evenodd'></path></svg>";
                echo "Error! Please check for duplicate IDs or invalid data.";
                echo "</div>";
            }
        }
        $conn->close();
        ?>
    </div>

    <!-- Sidebar footer tags handled by include -->
    </main>
    </div>
    </div>
</body>
</html>
