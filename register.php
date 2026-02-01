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
        $errorMsg = "Harap maaf, sistem hanya menerima akaun @gmail.com sahaja!";
    }
    elseif (strlen($password) < 6) {
        $errorMsg = "Password terlalu pendek! Minimum 6 huruf.";
    }
    elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errorMsg = "Password mesti ada gabungan HURUF dan NOMBOR.";
    }
    else {
        // --- 2. CHECK DUPLICATE ---
        $checkUser = $usersCollection->findOne(['email' => $email]);
        
        if ($checkUser) {
            $errorMsg = "Email ini sudah berdaftar!";
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
                
                // --- 4. HANTAR EMAIL (TRY & CATCH) ---
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'nrimam04@gmail.com'; 
                    $mail->Password   = 'bqxmxppkllelidrd';   
                    
                    // --- SETTING BARU (OPTION B) ---
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Guna SMTPS
                    $mail->Port       = 465; // Guna Port 465

                    $mail->setFrom('no-reply@misacinema.com', 'MISA Cinema Admin');
                    $mail->addAddress($email, $fullname);

                    $mail->isHTML(true);
                    $mail->Subject = 'Welcome to MISA Cinema!';
                    $mail->Body    = "
                        <h3>Hi, $fullname!</h3>
                        <p>Terima kasih kerana mendaftar dengan <b>MISA Cinema</b>.</p>
                        <p>Akaun anda telah berjaya dicipta.</p>
                        <br>
                        <a href='http://localhost/misa/login.php'>Login Sekarang</a>
                    ";

                    // KITA AKTIFKAN BALIK EMAIL
                    $mail->send();
                    
                } catch (Exception $e) {
                    // Kalau email gagal/slow, biar je dia continue ke Login
                }

                // --- 5. REDIRECT KE LOGIN ---
                $_SESSION['success'] = "Pendaftaran Berjaya! Sila login.";
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
    <title>Register - Misa Cinema</title>
    <link rel="icon" type="image/jpeg" href="assets/img/logo_misa.jpg">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    
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
        input:focus { outline: none; border-color: #e50914; }
        label { font-size: 0.8em; color: #aaa; display: block; margin-bottom: 5px; font-weight: bold; }
        .btn { 
            width: 100%; padding: 15px; background: #e50914; color: white; border: none; 
            font-weight: bold; border-radius: 4px; cursor: pointer; margin-top: 10px; transition: background 0.3s;
        }
        .btn:hover { background: #ff0f1f; }
        .link { text-align: center; margin-top: 20px; font-size: 0.9em; color: #aaa; }
        .link a { color: #e50914; text-decoration: none; font-weight: bold; }
        .hint { font-size: 0.7em; color: #666; margin-top: -10px; margin-bottom: 10px; display: block;}
        .alert-error {
            background-color: #f8d7da; color: #721c24; padding: 10px; 
            margin-bottom: 20px; border-radius: 4px; text-align: center; font-size: 0.9em;
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
            <div class="alert-error"><?php echo $errorMsg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>FULL NAME</label>
            <input type="text" name="fullname" required value="<?php echo isset($_POST['fullname']) ? $_POST['fullname'] : ''; ?>">

            <label>PHONE NUMBER</label>
            <input type="text" name="phone" required value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>">

            <label>EMAIL ADDRESS</label>
            <input type="email" name="email" required placeholder="example@gmail.com" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">

            <label>CREATE PASSWORD</label>
            <input type="password" name="password" required placeholder="Min 6 chars (Letters & Numbers)">
            <span class="hint">* Must contain letters & numbers</span>

            <button type="submit" class="btn">REGISTER ACCOUNT</button>
        </form>

        <div class="link">
            Already have an account? <a href="login.php">Login Here</a>
        </div>
    </div>
</body>
</html>