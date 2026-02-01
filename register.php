<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database Connection
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$usersCollection = $client->misacinema_db->users;

$errorMsg = "";
$successMsg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // --- 1. VALIDATION ---
    if (!preg_match("/@gmail\.com$/", $email)) {
        $errorMsg = "Sorry, the system only accepts @gmail.com accounts!";
    }
    elseif (strlen($password) < 6) {
        $errorMsg = "Password is too short! Minimum 6 characters.";
    }
    elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errorMsg = "Password must contain a combination of LETTERS and NUMBERS.";
    }
    else {
        // --- 2. CHECK DATABASE ---
        $checkUser = $usersCollection->findOne(['email' => $email]);
        
        if ($checkUser) {
            $errorMsg = "This email is already registered!";
        } else {
            // --- 3. INSERT USER ---
            $newUser = [
                'fullname' => $fullname,
                'phone' => $phone,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'customer',
                'joined_at' => date("Y-m-d H:i:s")
            ];

            $insertResult = $usersCollection->insertOne($newUser);

            if ($insertResult->getInsertedCount() > 0) {
                // --- 4. EMAIL SECTION (COMMENTED OUT) ---
                /* try {
                   // Mail logic here...
                } catch (Exception $e) { } */

                // --- 5. REDIRECT TO LOGIN ---
                $_SESSION['success'] = "Registration Successful! Please login.";
                header("Location: login.php"); 
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title>Register - Misa Cinema</title>
    <link rel="icon" type="image/jpeg" href="assets/img/logo_misa.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
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
            padding: 20px; /* Added padding for mobile edges */
            box-sizing: border-box;
        }
        .register-box { 
            background: rgba(20, 20, 20, 0.95);
            padding: 40px; 
            border-radius: 8px; 
            width: 100%;       /* Fluid width */
            max-width: 400px;  /* Max width for desktop */
            border: 1px solid #333; 
            box-shadow: 0px 0px 25px rgba(0,0,0,0.8);
            box-sizing: border-box; /* Ensures padding doesn't break width */
        }
        .header { 
            background: #e50914; 
            margin: -40px -40px 30px -40px; 
            padding: 25px; 
            text-align: center; 
            border-radius: 8px 8px 0 0; 
        }
        h2 { margin: 0; font-size: 1.5em; letter-spacing: 1px; }
        
        input { 
            width: 100%; 
            padding: 14px; /* Larger touch area */
            background: #222; 
            border: 1px solid #444; 
            color: white; 
            margin-bottom: 15px; 
            border-radius: 4px; 
            box-sizing: border-box; 
            font-size: 16px; /* Prevents iOS auto-zoom */
        }
        input:focus { outline: none; border-color: #e50914; background: #2a2a2a; }
        
        label { font-size: 0.85em; color: #aaa; display: block; margin-bottom: 8px; font-weight: bold; }
        
        /* --- PASSWORD EYE --- */
        .password-container {
            position: relative;
            width: 100%;
            margin-bottom: 5px; 
        }
        .password-container input {
            margin-bottom: 0;
            padding-right: 45px; /* Space for the icon */
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
            padding: 5px; /* Easier to tap */
        }
        .toggle-password:hover { color: white; }

        .btn { 
            width: 100%; padding: 16px; background: #e50914; color: white; border: none; 
            font-weight: bold; font-size: 1rem; border-radius: 4px; cursor: pointer; margin-top: 15px; transition: background 0.3s;
        }
        .btn:hover { background: #ff0f1f; }
        
        .link { text-align: center; margin-top: 25px; font-size: 0.95em; color: #aaa; }
        .link a { color: #e50914; text-decoration: none; font-weight: bold; }
        
        .hint { font-size: 0.75em; color: #888; margin-top: 8px; margin-bottom: 15px; display: block;}
        
        .alert-error {
            background-color: rgba(220, 53, 69, 0.2); 
            border: 1px solid #dc3545;
            color: #ff6b6b; 
            padding: 12px; 
            margin-bottom: 25px; 
            border-radius: 4px; 
            text-align: center; 
            font-size: 0.9em;
        }

        /* Mobile specific adjustments */
        @media (max-width: 480px) {
            .register-box { padding: 30px 20px; }
            .header { margin: -30px -20px 25px -20px; }
        }
    </style>
</head>
<body>
    <div class="register-box">
        <div class="header">
            <h2>JOIN THE FAMILY</h2>
            <span style="font-size:0.9em; opacity:0.9;">Create your account to start booking</span>
        </div>

        <?php if($errorMsg): ?>
            <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $errorMsg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>FULL NAME</label>
            <input type="text" name="fullname" required value="<?php echo isset($_POST['fullname']) ? $_POST['fullname'] : ''; ?>">

            <label>PHONE NUMBER</label>
            <input type="text" name="phone" required value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>">

            <label>EMAIL ADDRESS</label>
            <input type="email" name="email" required placeholder="example@gmail.com" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">

            <label>CREATE PASSWORD</label>
            
            <div class="password-container">
                <input type="password" name="password" id="passwordInput" required placeholder="Min 6 chars (Letters & Numbers)">
                <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
            </div>

            <span class="hint">* Must contain letters & numbers</span>

            <button type="submit" class="btn">REGISTER ACCOUNT</button>
        </form>

        <div class="link">
            Already have an account? <a href="login.php">Login Here</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const icon = document.querySelector('.toggle-password');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text'; // Show Password
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash'); // Cross icon
            } else {
                passwordInput.type = 'password'; // Hide Password
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye'); // Normal icon
            }
        }
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>