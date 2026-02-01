<?php
session_start();
require 'vendor/autoload.php';

// Setup MongoDB
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$usersCollection = $client->misacinema_db->users;

// Variable to store local error (red box in form)
$localError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $role = $_POST['role']; // customer or admin

    // --- 1. LOGIN AS ADMIN (Hardcoded) ---
    if ($role == 'admin') {
        if ($email == 'admin@gmail.com' && $pass == '123') {
            $_SESSION['user'] = 'Administrator';
            $_SESSION['role'] = 'admin';
            header("Location: admin.php");
            exit();
        } else {
            $localError = "Invalid Admin Email or Password!";
        }
    } 
    // --- 2. LOGIN AS CUSTOMER (MongoDB) ---
    else {
        // Find user by EMAIL only first
        $user = $usersCollection->findOne(['email' => $email]);

        // If user exists AND password matches hash
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user'] = (array)$user; 
            $_SESSION['role'] = 'customer';
            header("Location: home.php");
            exit();
        } else {
            $localError = "Invalid Customer Email or Password!";
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
        
        /* --- ROLE SWITCH BUTTONS --- */
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
            background: #e50914; 
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

        input:focus { outline: none; border-color: #e50914; }

        /* --- NEW PASSWORD WRAPPER FOR EYE ICON --- */
        .password-container {
            position: relative;
            width: 100%;
            margin-bottom: 20px;
        }

        .password-container input {
            margin-bottom: 0; /* Remove margin from input, keep it on container */
            padding-right: 45px; /* Make space for the eye icon so text doesn't overlap */
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            cursor: pointer;
            font-size: 1.1em;
            z-index: 10;
        }
        
        .toggle-password:hover { color: white; }

        /* --- BUTTONS & LINKS --- */
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
                    unset($_SESSION['success']); 
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

            <div class="password-container">
                <input type="password" name="password" id="passwordInput" placeholder="Password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
            </div>
            
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
        // Function to toggle User/Admin role
        function setRole(role) {
            document.getElementById('roleInput').value = role;
            const buttons = document.querySelectorAll('.role-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            if(role === 'customer') {
                buttons[0].classList.add('active');
            } else {
                buttons[1].classList.add('active');
            }
        }

        // Function to toggle Password Visibility (Eye Icon)
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const icon = document.querySelector('.toggle-password');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text'; // Show Password
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash'); // Change Icon to Crossed Eye
            } else {
                passwordInput.type = 'password'; // Hide Password
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye'); // Change Icon back to Normal Eye
            }
        }
    </script>

</body>
</html>