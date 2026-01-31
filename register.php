<?php
require 'vendor/autoload.php';
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$usersCollection = $client->misacinema_db->users;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // --- 1. VALIDATION CHECK ---
    
    // Check Email (Format Valid & Ada @)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid Email! Please include @ and a valid domain.'); window.history.back();</script>";
        exit();
    }

    // Check Password (Min 6 Character)
    if (strlen($password) < 6) {
        echo "<script>alert('Password too short! Must be at least 6 characters.'); window.history.back();</script>";
        exit();
    }

    // Check Password (Mesti ada Huruf DAN Nombor)
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        echo "<script>alert('Password must contain both LETTERS and NUMBERS.'); window.history.back();</script>";
        exit();
    }

    // --- 2. JIKA LULUS, BARU MASUK DATABASE ---

    $newUser = [
        'fullname' => $fullname,
        'phone' => $phone,
        'email' => $email,
        'password' => $password, // Note: Hash password nanti kalau nak lagi power
        'role' => 'customer',
        'joined_at' => date("Y-m-d H:i:s")
    ];

    $usersCollection->insertOne($newUser);
    echo "<script>alert('Account Created! Please Login.'); window.location.href='login.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Misa Cinema</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
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

        .register-box { 
            background: rgba(20, 20, 20, 0.95);
            padding: 40px; 
            border-radius: 8px; 
            width: 400px; 
            border: 1px solid #333; 
            box-shadow: 0px 0px 25px rgba(0,0,0,0.8);
        }

        .header { 
            background: #e50914; 
            margin: -40px -40px 30px -40px; 
            padding: 20px; 
            text-align: center; 
            border-radius: 8px 8px 0 0; 
        }

        h2 { margin: 0; font-size: 1.5em; letter-spacing: 1px; }
        
        input { 
            width: 100%; 
            padding: 12px; 
            background: #222; 
            border: 1px solid #444; 
            color: white; 
            margin-bottom: 15px; 
            border-radius: 4px; 
            box-sizing: border-box; 
        }
        
        input:focus {
            outline: none;
            border-color: #e50914;
        }

        label { 
            font-size: 0.8em; 
            color: #aaa; 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
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
            margin-top: 10px; 
            transition: background 0.3s;
        }
        
        .btn:hover { background: #ff0f1f; }

        .link { text-align: center; margin-top: 20px; font-size: 0.9em; color: #aaa; }
        .link a { color: #e50914; text-decoration: none; font-weight: bold; }
        .link a:hover { text-decoration: underline; }
        
        /* Tambahan CSS sikit untuk hint password */
        .hint { font-size: 0.7em; color: #666; margin-top: -10px; margin-bottom: 10px; display: block;}
    </style>
</head>
<body>

    <div class="register-box">
        <div class="header">
            <h2>JOIN THE FAMILY</h2>
            <span style="font-size:0.9em; opacity:0.9;">Create your account to start booking</span>
        </div>

        <form method="POST">
            <label>FULL NAME</label>
            <input type="text" name="fullname" required>

            <label>PHONE NUMBER</label>
            <input type="text" name="phone" required>

            <label>EMAIL ADDRESS</label>
            <input type="email" name="email" required placeholder="example@mail.com">

            <label>CREATE PASSWORD</label>
            <input type="password" name="password" required placeholder="Min 6 chars (Letters & Numbers)">
            <span class="hint">* Must contain letters & numbers</span>

            <button type="submit" class="btn">REGISTER ACCOUNT</button>
        </form>

        <div class="link">
            Already have an account? <a href="login.php">Login Here</a>
        </div>
    </div>
<?php include 'footer.php'; ?>
</body>
</html>

