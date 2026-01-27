<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Sale - PHARMACIA</title>
</head>
<body class="bg-slate-50">
    <?php require('../sidebar.php'); ?>

    <div class="mb-10 text-center lg:text-left">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Point of Sale</h2>
        <p class="text-slate-500 mt-1 font-medium">Create a new order and record transactions.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Step 1: Customer Selection -->
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-black text-blue-600 uppercase tracking-widest mb-6">Step 1: Customer</h3>
            <form action="<?=$_SERVER['PHP_SELF']?>" method="post" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Select ID</label>
                    <select id="cid" name="cid" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none font-bold">
                        <option value="0">*Select Customer ID</option>
                        <?php	
                        include "../../../config/config.php";
                        $qry="SELECT c_id FROM customer";
                        $result= $conn->query($qry);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<option>".$row["c_id"]."</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="custadd" class="w-full bg-blue-600 text-white font-black py-4 rounded-xl shadow-lg shadow-blue-100 hover:bg-blue-700 active:scale-95 transition-all">
                    Initialize Order
                </button>
            </form>
            
            <?php
            if (session_status() == PHP_SESSION_NONE) session_start();
            // Fetch employee ID from session (matching logic from pos1.php)
            $qry1="SELECT id from admin where a_username='$_SESSION[user]'";
            $result1=$conn->query($qry1);
            $row1=$result1 ? $result1->fetch_row() : [null];
            $eid=$row1[0];
            
            if(isset($_POST['custadd'])) {
                $cid=$_POST['cid'];
                $qry2="INSERT INTO sales(c_id,e_id) VALUES ('$cid','$eid')"; 
                if($conn->query($qry2)) {
                    echo "<div class='mt-4 text-xs font-bold text-emerald-600 bg-emerald-50 p-3 rounded-lg flex items-center'><svg class='w-4 h-4 mr-2' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'></path></svg> Order Session Active</div>";
                } else {
                    echo "<div class='mt-4 text-xs font-bold text-red-600 bg-red-50 p-3 rounded-lg'>Error starting session.</div>";
                }
            }
            ?>
        </div>

        <!-- Step 2: Medicine Search -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <h3 class="text-sm font-black text-indigo-600 uppercase tracking-widest mb-6">Step 2: Add Medical Items</h3>
                <form method="post" class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="flex-grow">
                        <select id="med" name="med" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none font-bold">
                            <option value="0">Select Medicine</option>
                            <?php	
                            $qry3="SELECT med_name FROM meds";
                            $result3 = $conn->query($qry3);
                            if ($result3->num_rows > 0) {
                                while($row4 = $result3->fetch_assoc()) {
                                    echo "<option>".$row4["med_name"]."</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="search" class="bg-indigo-600 text-white font-black py-3 px-10 rounded-xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">
                        Check Stock
                    </button>
                </form>

                <?php
                if(isset($_POST['search']) && !empty($_POST['med'])) {
                    $med=$_POST['med'];
                    $qry4="SELECT * FROM meds where med_name='$med'";
                    $result4=$conn->query($qry4); 
                    $row4 = $result4 ? $result4->fetch_row() : null;
                    if($row4){
                ?>
                <div class="mt-8 pt-8 border-t border-slate-100 animate-in fade-in duration-500">
                    <form method="post">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8 text-center sm:text-left">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Med ID</p>
                                <input type="number" name="medid" value="<?php echo $row4[0]; ?>" readonly class="w-full bg-transparent border-none p-0 text-sm font-bold text-slate-900 focus:ring-0">
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Name</p>
                                <input type="text" name="mdname" value="<?php echo $row4[1]; ?>" readonly class="w-full bg-transparent border-none p-0 text-sm font-bold text-slate-900 focus:ring-0">
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Available</p>
                                <input type="number" name="mqty" value="<?php echo $row4[2]; ?>" readonly class="w-full bg-transparent border-none p-0 text-sm font-bold text-emerald-600 focus:ring-0 text-center sm:text-left">
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Price (1 Unit)</p>
                                <input type="number" name="mprice" value="<?php echo $row4[4]; ?>" readonly class="w-full bg-transparent border-none p-0 text-sm font-black text-slate-900 focus:ring-0">
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-end space-y-4 sm:space-y-0 sm:space-x-4 bg-slate-900 p-6 rounded-2xl">
                            <div class="flex-grow w-full">
                                <label class="block text-xs font-bold text-slate-400 mb-2 uppercase">Quantity Required</label>
                                <input type="number" name="mcqty" required class="w-full bg-slate-800 border-slate-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none font-black text-lg">
                            </div>
                            <button type="submit" name="add" class="w-full sm:w-auto bg-blue-600 text-white font-black py-4 px-10 rounded-xl shadow-xl hover:bg-blue-700 transition-all active:scale-95">
                                Add to Cart
                            </button>
                        </div>
                    </form>
                </div>
                <?php } } ?>

                <?php
                if(isset($_POST['add'])) {
                    $qry5="select sale_id from sales ORDER BY sale_id DESC LIMIT 1";
                    $result5=$conn->query($qry5); 
                    $row5=$result5->fetch_row();
                    $sid=$row5[0];
                    
                    $mid=$_POST['medid'];
                    $aqty=$_POST['mqty'];
                    $qty=$_POST['mcqty'];
                    
                    if($qty > $aqty || $qty <= 0) {
                        echo "<div class='mt-6 p-4 bg-red-50 text-red-700 rounded-xl font-bold flex items-center'><svg class='w-5 h-5 mr-3' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'></path></svg> INVALID QUANTITY: Maximum available is $aqty</div>";
                    } else {
                        $price=$_POST['mprice']*$qty;
                        $qry6="INSERT INTO sales_items(`sale_id`,`med_id`,`sale_qty`,`tot_price`) VALUES($sid,$mid,$qty,$price)";
                        if(mysqli_query($conn,$qry6)) {
                            echo "<div class='mt-6 p-8 bg-blue-600 rounded-3xl text-white flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0'>";
                            echo "<div><p class='text-blue-100 text-xs font-black uppercase tracking-widest mb-1'>Item Added Successfully</p><p class='text-xl font-black'>Total for Item: Rs. ".number_format($price, 2)."</p></div>";
                            echo "<a class='bg-white text-blue-600 font-black py-3 px-8 rounded-xl shadow-lg hover:bg-blue-50 transition-all' href='pos2.php?sid=".$sid."'>Finalize Invoice &rarr;</a>";
                            echo "</div>";
                        }
                    }
                }	
                ?>
            </div>
        </div>
    </div>

    <!-- End tags from sidebar.php -->
    </main>
    </div>
    </div>
</body>
</html>