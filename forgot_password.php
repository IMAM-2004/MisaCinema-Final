<?php
session_start();
require 'vendor/autoload.php';

$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$usersCollection = $client->misacinema_db->users;

$step = 1; // Step 1: Verify, Step 2: Reset
$error = '';
$success = '';

// --- LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ACTION 1: VERIFY USER (Check Email & Phone)
    if (isset($_POST['verify_user'])) {
        $email = $_POST['email'];
        $phone = $_POST['phone'];

        $user = $usersCollection->findOne(['email' => $email, 'phone' => $phone]);

        if ($user) {
            // User found! Move to Step 2
            $_SESSION['reset_email'] = $email; // Store email temporarily
            $step = 2;
        } else {
            $error = "No account found with that Email and Phone number.";
        }
    }

    // ACTION 2: UPDATE PASSWORD (YANG SAYA DAH BETULKAN)
    if (isset($_POST['reset_password'])) {
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];
        $email = $_SESSION['reset_email'];

        if ($new_pass === $confirm_pass) {
            if (strlen($new_pass) >= 6) {
                
                // --- INI PERUBAHAN PENTING ---
                // Kita tukar password biasa jadi Hash (kod rahsia) sebelum simpan
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

                // Update MongoDB dengan password yang dah di-hash
                $usersCollection->updateOne(
                    ['email' => $email],
                    ['$set' => ['password' => $hashed_password]] 
                );
                
                $success = "Password updated successfully! <a href='login.php'>Login Now</a>";
                // Clear session
                unset($_SESSION['reset_email']);
                $step = 3; // Step 3: Success Message
            } else {
                $error = "Password must be at least 6 characters.";
                $step = 2; // Stay on reset form
            }
        } else {
            $error = "Passwords do not match!";
            $step = 2; // Stay on reset form
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Misa Cinema</title>
    <link rel="icon" type="image/jpeg" href="assets/img/logo_misa.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('assets/images/bg_cinema.png');
            background-size: cover; background-position: center;
            color: white; font-family: 'Roboto', sans-serif;
            display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;
        }
        .box { 
            background: rgba(20, 20, 20, 0.95); padding: 40px; border-radius: 8px; width: 350px; 
            border: 1px solid #333; text-align: center;
        }
        h2 { color: #e50914; margin-bottom: 20px; }
        input { 
            width: 100%; padding: 12px; background: #333; border: 1px solid #444; 
            color: white; margin-bottom: 15px; border-radius: 4px; box-sizing: border-box; 
        }
        .btn { 
            width: 100%; padding: 12px; background: #e50914; color: white; border: none; 
            font-weight: bold; border-radius: 4px; cursor: pointer; 
        }
        .btn:hover { background: #ff0f1f; }
        .error { color: #ff4444; font-size: 0.9em; margin-bottom: 15px; }
        .success { color: #00C851; font-size: 1.1em; margin-bottom: 15px; }
        .back-link { display: block; margin-top: 15px; color: #aaa; text-decoration: none; font-size: 0.8em;}
        .back-link:hover { color: white; }
        /* Style untuk success link */
        .success a { color: #e50914; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>

    <div class="box">
        <?php if ($step == 1): ?>
            <h2>RECOVER ACCOUNT</h2>
            <p style="color:#ccc; font-size:0.9em; margin-bottom:20px;">Enter your Email and Phone Number to verify identity.</p>
            
            <?php if($error) echo "<div class='error'>$error</div>"; ?>

            <form method="POST">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="text" name="phone" placeholder="Phone Number" required>
                <button type="submit" name="verify_user" class="btn">VERIFY ACCOUNT</button>
            </form>

        <?php elseif ($step == 2): ?>
            <h2>RESET PASSWORD</h2>
            <p style="color:#ccc; font-size:0.9em; margin-bottom:20px;">Identity Verified. Create a new password.</p>

            <?php if($error) echo "<div class='error'>$error</div>"; ?>

            <form method="POST">
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" name="reset_password" class="btn">UPDATE PASSWORD</button>
            </form>

        <?php elseif ($step == 3): ?>
            <h2>SUCCESS!</h2>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <a href="login.php" class="back-link">Back to Login</a>
    </div>

</body>
</html>