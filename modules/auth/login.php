<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Pharmacy Management System - Login</title>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-white tracking-tight mb-2">Pharmacy</h1>
            <p class="text-slate-400 text-lg">Management System</p>
        </div>

        <div id="div_login" class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl">
            <h2 class="text-2xl font-bold text-white text-center mb-8">Admin Login</h2>
            
            <form method="post" action="" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Username</label>
                    <input type="text" name="uname" placeholder="Enter your username" required 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                    <input type="password" name="pwd" placeholder="Enter your password" required 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
                </div>

                <div class="pt-4">
                    <input type="submit" value="Login" name="submit" id="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/30 transform transition-all hover:-translate-y-0.5 active:scale-[0.98] cursor-pointer" />
                </div>
            </form>
        </div>

        <div class="mt-8 text-center text-slate-500 text-sm">
            Powered by VE Technologies.
        </div>
    </div>

<?php
include "../../config/config.php";
session_start();
// ... (rest of the PHP logic remains same)

if(isset($_POST['submit'])){

    $uname = mysqli_real_escape_string($conn, $_POST['uname']);
    $password = mysqli_real_escape_string($conn, $_POST['pwd']);

    if($uname != "" && $password != ""){

        // Fetch user info along with role name
        $sql = "
            SELECT e.E_ID, e.username, e.password, r.role_name
            FROM employee e
            LEFT JOIN roles r ON e.role_id = r.role_id
            WHERE e.username = '$uname' AND e.password = '$password'
        ";
        $result = $conn->query($sql);

        if($result && $result->num_rows == 1){
            $row = $result->fetch_assoc();

            $_SESSION['user'] = $row['E_ID'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role_name'];

            // Redirect based on role
            switch(strtolower($row['role_name'])){
                case "admin":
                    header("Location: ../admin/dashboard.php");
                    exit();
                case "pharmacist":
                    header("Location: ../pharmacist/dashboard.php");
                    exit();
                case "cashier":
                    header("Location: ../cashier/dashboard.php");
                    exit();
                default:
                    echo "<p style='color:red;'>Unknown role!</p>";
            }

        } else {
            echo "<p style='color:red;'>Invalid username or password!</p>";
        }

    } else {
        echo "<p style='color:red;'>Please enter both username and password!</p>";
    }
}
?>

    <div class="footer">
        <br>
        Powered by VE Technologies. 
        <br><br>
    </div>
</body>
</html>
