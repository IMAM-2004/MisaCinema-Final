<?php
session_start();
require 'vendor/autoload.php';

$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$usersCollection = $client->misacinema_db->users;

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // 1. Check kalau dia ADMIN (Hardcoded)
    if ($email == 'admin@gmail.com' && $pass == '123') {
        $_SESSION['user'] = 'Administrator';
        $_SESSION['role'] = 'admin';
        header("Location: admin.php");
        exit();
    } 
    
    // 2. Check kalau dia USER BIASA (Cari dalam Database)
    $user = $usersCollection->findOne(['email' => $email, 'password' => $pass]);

    if ($user) {
        // --- PERUBAHAN PENTING DI SINI ---
        // Kita simpan FULL DATA user (Array) ke dalam session
        // Supaya home.php dan profile.php boleh baca ID, Gambar, dll.
        $_SESSION['user'] = (array)$user; 
        $_SESSION['role'] = 'customer';
        
        header("Location: home.php");
        exit();
    } else {
        $error = "Email atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Misa Cinema</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* --- UNIVERSAL BACKGROUND --- */
        body { 
            /* Gambar background dengan overlay gelap */
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
            background: rgba(20, 20, 20, 0.9); /* Sedikit transparent supaya nampak background sikit */
            padding: 60px; 
            border-radius: 8px; 
            width: 350px; 
            text-align: center; 
            border: 1px solid #333; 
            box-shadow: 0px 0px 20px rgba(0,0,0,0.8);
        }

        h1 { color: #e50914; margin-bottom: 30px; letter-spacing: 2px; }
        
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
        
        .error { 
            background: #e50914; 
            color: white; 
            padding: 10px; 
            border-radius: 4px;
            margin-bottom: 20px; 
            font-size: 0.9em;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <h1>MISA CINEMA</h1>
        <p style="color:#ccc; margin-bottom:30px;">Welcome back! Please login.</p>

        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">LOGIN TO ACCOUNT</button>
        </form>

        <div class="link">
            New to MisaCinema? <a href="register.php">Register here</a>
        </div>
    </div>

</body>
</html>