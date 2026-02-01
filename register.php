<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP; // Tambah ini untuk debug
use PHPMailer\PHPMailer\Exception;

// Database Connection
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$usersCollection = $client->misacinema_db->users;

$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // --- SKIP VALIDATION BIASA UNTUK DEBUGGING ---
    // Terus check database
    $checkUser = $usersCollection->findOne(['email' => $email]);
    
    if ($checkUser) {
        $errorMsg = "Email dah ada. Sila guna email lain untuk test.";
    } else {
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
            
            // --- MODE PENYIASAT (DEBUG ON) ---
            $mail = new PHPMailer(true);
            try {
                // Setting Server
                $mail->SMTPDebug = SMTP::DEBUG_SERVER; // 2 = Tunjuk semua conversation server
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'nrimam04@gmail.com'; 
                $mail->Password   = 'bqxmxppkllelidrd';   
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Kita cuba Port 587 balik, selalunya lebih stabil untuk debug
                $mail->Port       = 587;

                // PENTING: Google tak suka kalau 'From' lain dari 'Username'
                $mail->setFrom('nrimam04@gmail.com', 'MISA Admin Debug'); 
                $mail->addAddress($email, $fullname);

                $mail->isHTML(true);
                $mail->Subject = 'Test Debugging MISA';
                $mail->Body    = 'Kalau dapat email ni, maknanya setting OK.';

                $mail->send();
                echo "<h1>BERJAYA! Email dah dihantar.</h1>";
                echo "<a href='login.php'>Pergi Login</a>";
                exit();

            } catch (Exception $e) {
                // INI YANG KITA NAK TENGOK
                echo "<h1>GAGAL HANTAR EMAIL!</h1>";
                echo "<h3>Error: {$mail->ErrorInfo}</h3>";
                echo "<pre>";
                // Tunjuk log error tak perlu di sini sebab SMTPDebug dah keluarkan di atas
                echo "</pre>";
                die(); // Mati di sini supaya awak boleh baca error
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Debug Register</title>
    <style>body{background:#000; color:white; font-family:sans-serif; padding:50px;}</style>
</head>
<body>
    <h2>DEBUG MODE: REGISTER</h2>
    <?php if($errorMsg) echo "<h3 style='color:red'>$errorMsg</h3>"; ?>
    <form method="POST">
        <input type="text" name="fullname" value="Test User" placeholder="Nama"><br><br>
        <input type="text" name="phone" value="0123456789" placeholder="Phone"><br><br>
        <input type="email" name="email" required placeholder="Masukan Email Test"><br><br>
        <input type="password" name="password" value="testing123" placeholder="Pass"><br><br>
        <button type="submit" style="padding:10px; background:red; color:white;">TEST HANTAR EMAIL</button>
    </form>
</body>
</html>