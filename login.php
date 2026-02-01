<?php
session_start();
require 'vendor/autoload.php';

// Setup MongoDB
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$usersCollection = $client->misacinema_db->users;

// Variable untuk simpan error local (kotak merah dalam form)
$localError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $role = $_POST['role']; // customer atau admin

    // --- 1. LOGIN SEBAGAI ADMIN (Hardcoded) ---
    if ($role == 'admin') {
        if ($email == 'admin@gmail.com' && $pass == '123') {
            $_SESSION['user'] = 'Administrator';
            $_SESSION['role'] = 'admin';
            header("Location: admin.php");
            exit();
        } else {
            $localError = "Salah Email atau Password Admin!";
        }
    } 
    // --- 2. LOGIN SEBAGAI CUSTOMER (MongoDB) ---
    else {
        // Cari user berdasarkan EMAIL sahaja dulu
        $user = $usersCollection->findOne(['email' => $email]);

        // Kalau user wujud DAN password dia match dengan hash
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user'] = (array)$user; 
            $_SESSION['role'] = 'customer';
            header("Location: home.php");
            exit();
        } else {
            $localError = "Email atau Password customer salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Misa Cinema</title>
    <link rel="icon" type="image/jpeg" href="assets/img/logo_misa.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- UNIVERSAL BACKGROUND --- */
        body { 
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/bg_cinema.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            color: white; 
            font-family: 'Roboto', sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }

        .login-box { 
            background: rgba(20, 20, 20, 0.9);
            padding: 60px; 
            border-radius: 8px; 
            width: 350px; 
            text-align: center; 
            border: 1px solid #333; 
            box-shadow: 0px 0px 20px rgba(0,0,0,0.8);
        }

        h1 { color: #e50914; margin-bottom: 20px; letter-spacing: 2px; }
        
        /* --- STYLE BARU UNTUK BUTTON PILIHAN (TOGGLE) --- */
        .role-switch {
            display: flex;
            justify-content: center;
            margin-bottom: 25px;
            background: #333;
            border-radius: 30px;
            padding: 5px;
            position: relative;
        }

        .role-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            color: #aaa;
            cursor: pointer;
            font-weight: bold;
            border-radius: 25px;
            transition: 0.3s;
            z-index: 2;
        }

        .role-btn.active {
            background: #e50914; /* Warna Merah Misa */
            color: white;
            box-shadow: 0 4px 10px rgba(229, 9, 20, 0.4);
        }

        .role-btn:focus { outline: none; }

        /* --- INPUT FIELDS --- */
        input { 
            width: 100%; 
            padding: 15px; 
            background: #333; 
            border: 1px solid #444; 
            color: white; 
            margin-bottom: 20px; 
            border-radius: 4px; 
            box-sizing: border-box; 
        }

        .btn { 
            width: 100%; 
            padding: 15px; 
            background: #e50914; 
            color: white; 
            border: none; 
            font-weight: bold; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px; 
            margin-top: 10px; 
            transition: background 0.3s;
        }

        .btn:hover { background: #ff0f1f; }
        
        .link { margin-top: 20px; font-size: 0.9em; color: #aaa; }
        .link a { color: white; text-decoration: none; font-weight: bold; }
        .link a:hover { text-decoration: underline; }
        
        /* ALERT BOXES */
        .alert-success {
            background-color: #d4edda; color: #155724; padding: 15px; 
            border-radius: 4px; margin-bottom: 20px; font-size: 0.9em; border: 1px solid #c3e6cb;
        }
        .alert-error { 
            background: #f8d7da; color: #721c24; padding: 15px; 
            border-radius: 4px; margin-bottom: 20px; font-size: 0.9em; border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <h1>MISA CINEMA</h1>
        <p style="color:#ccc; margin-bottom:20px;">Welcome back! Please login.</p>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> 
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']); // Padam lepas refresh
                ?>
            </div>
        <?php endif; ?>

        <?php if($localError): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $localError; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> 
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']); 
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            
            <div class="role-switch">
                <button type="button" class="role-btn active" onclick="setRole('customer')">Customer</button>
                <button type="button" class="role-btn" onclick="setRole('admin')">Admin</button>
            </div>

            <input type="hidden" name="role" id="roleInput" value="customer">

            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <div style="text-align: right; margin-bottom: 15px; font-size: 0.8em;">
                <a href="forgot_password.php" style="color: #aaa; text-decoration: none;">Forgot Password?</a>
            </div>

            <button type="submit" class="btn">LOGIN TO ACCOUNT</button>
        </form>

        <div class="link">
            New to MisaCinema? <a href="register.php">Register here</a>
        </div>
    </div>

    <script>
        function setRole(role) {
            // 1. Update hidden input value
            document.getElementById('roleInput').value = role;

            // 2. Update visual button (Warna Merah)
            const buttons = document.querySelectorAll('.role-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Cari button yang kena klik dan tambah class active
            if(role === 'customer') {
                buttons[0].classList.add('active');
            } else {
                buttons[1].classList.add('active');
            }
        }
    </script>

</body>
</html>
