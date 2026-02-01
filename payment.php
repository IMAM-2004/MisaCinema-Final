<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

// Security Check
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

require 'vendor/autoload.php';
use MongoDB\BSON\ObjectId;

// =========================================================
// 1. TANGKAP DATA DARI BOOKING.PHP (POST)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_seats'])) {
    
    // Simpan data dalam variable biasa untuk paparan HTML
    $movieName = $_POST['movie_name'];
    $hallName = $_POST['hall_name'];
    $showtime = $_POST['showtime'];
    $showtimeId = $_POST['showtime_id']; 
    $seatsString = $_POST['selected_seats']; 
    $seatsArray = explode(',', $seatsString);
    $totalPrice = $_POST['total_price'];

} elseif (isset($_POST['submit_payment'])) {
    // =========================================================
    // 2. PROSES PEMBAYARAN & SAVE KE DB
    // =========================================================
    
    $client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
    $bookingCollection = $client->misacinema_db->bookings;

    $finalSeats = explode(",", $_POST['final_seats']); 
    
    $insertResult = $bookingCollection->insertOne([
        'customer_name' => $_SESSION['user']['fullname'], 
        'movie_name' => $_POST['final_movie'],
        'hall_name' => $_POST['final_hall'],
        'showtime' => $_POST['final_showtime'],
        'showtime_id' => $_POST['final_showtime_id'], 
        'seats' => $finalSeats,
        'total_price' => $_POST['final_price'],
        'payment_method' => $_POST['payment_method'],
        'bank_name' => ($_POST['payment_method'] == 'Online Banking') ? $_POST['bank_name'] : 'Visa/Mastercard',
        'booking_date' => date('Y-m-d H:i:s'),
        'status' => 'confirmed'
    ]);

    if ($insertResult->getInsertedCount() > 0) {
        $newId = $insertResult->getInsertedId();
        header("Location: receipt.php?id=" . $newId);
        exit();
    } else {
        echo "<script>alert('System Error. Please try again.'); window.location.href='home.php';</script>";
    }

} else {
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>Secure Payment</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            background-color: #000; 
            font-family: 'Roboto', sans-serif; 
            display: flex; 
            flex-direction: column; /* FIXED: Stacks content and footer vertically */
            min-height: 100vh; 
            margin: 0; 
            color: #fff; 
            box-sizing: border-box;
        }

        /* FIXED: This wrapper centers the form while pushing footer down */
        .page-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .payment-modal { 
            background-color: #1a1a1a; 
            width: 100%; 
            max-width: 600px; 
            border-radius: 12px; 
            overflow: hidden; 
            border: 1px solid #333; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.5); 
        }

        .header { background-color: #e50914; padding: 20px; text-align: center; }
        .header h2 { margin: 0; font-size: 1.2rem; text-transform: uppercase; letter-spacing: 1px; }

        .content { padding: 30px; }

        .summary-box { 
            background-color: #252525; 
            padding: 20px; 
            border-radius: 6px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
            border-left: 5px solid #e50914; 
        }
        
        .price-amount { font-size: 1.8rem; color: #00ff7f; font-weight: bold; }

        .method-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }

        .method-card { background: #252525; padding: 20px; text-align: center; cursor: pointer; border: 2px solid transparent; border-radius: 8px; transition: 0.3s; color: #aaa; }
        .method-card:hover { background: #333; color: white; }
        .method-card.active { background: #222; border-color: #e50914; color: white; }
        .method-card i { font-size: 2rem; margin-bottom: 10px; display: block; }
        
        .form-section { background: #222; padding: 20px; margin-bottom: 20px; border: 1px solid #333; border-radius: 5px; display: none; }
        .form-section.show { display: block; animation: fadeIn 0.3s ease-in-out; }
        
        label { display: block; margin-bottom: 8px; font-size: 0.9em; color: #ccc; }
        input, select { width: 100%; padding: 14px; background: #111; border: 1px solid #444; color: white; border-radius: 6px; box-sizing: border-box; outline: none; font-size: 16px; }
        input:focus, select:focus { border-color: #e50914; }
        
        .btn-pay { width: 100%; padding: 16px; background: #e50914; color: white; border: none; font-weight: bold; font-size: 1.1rem; cursor: pointer; margin-top: 10px; border-radius: 6px; text-transform: uppercase; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 500px) {
            .content { padding: 20px; }
            .summary-box { flex-direction: column; text-align: center; gap: 10px; }
            .method-grid { grid-template-columns: 1fr; }
            .method-card { display: flex; align-items: center; gap: 15px; padding: 15px; }
            .method-card i { margin-bottom: 0; font-size: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="page-container">
    <div class="payment-modal">
        <div class="header">
            <h2><i class="fas fa-lock"></i> Secure Payment</h2>
        </div>

        <div class="content">
            <div class="summary-box">
                <div style="width: 100%;">
                    <h3 style="margin: 0 0 5px 0; color:white; font-size: 1.1rem;"><?php echo htmlspecialchars($movieName); ?></h3>
                    <small style="color:#aaa; display:block; margin-bottom:5px;">
                        <?php echo htmlspecialchars($hallName); ?> <br> 
                        <?php echo date('d M, h:i A', strtotime($showtime)); ?>
                    </small>
                    <small style="color:#e50914; font-weight:bold;">Seats: <?php echo htmlspecialchars($seatsString); ?></small>
                </div>
                <div class="price-amount">RM <?php echo number_format($totalPrice, 2); ?></div>
            </div>

            <form action="" method="POST">
                <input type="hidden" name="final_movie" value="<?php echo htmlspecialchars($movieName); ?>">
                <input type="hidden" name="final_hall" value="<?php echo htmlspecialchars($hallName); ?>">
                <input type="hidden" name="final_showtime" value="<?php echo htmlspecialchars($showtime); ?>">
                <input type="hidden" name="final_showtime_id" value="<?php echo htmlspecialchars($showtimeId); ?>"> 
                <input type="hidden" name="final_seats" value="<?php echo htmlspecialchars($seatsString); ?>">
                <input type="hidden" name="final_price" value="<?php echo htmlspecialchars($totalPrice); ?>">
                <input type="hidden" name="payment_method" id="payment_method" value="Credit Card">

                <div class="method-grid">
                    <div class="method-card active" onclick="selectMethod('Credit Card', this)">
                        <i class="fab fa-cc-visa"></i> 
                        <span>Credit / Debit Card</span>
                    </div>
                    <div class="method-card" onclick="selectMethod('Online Banking', this)">
                        <i class="fas fa-university"></i> 
                        <span>FPX Banking</span>
                    </div>
                </div>

                <div id="card-inputs" class="form-section show">
                    <label>Card Number</label>
                    <input type="tel" placeholder="0000 0000 0000 0000" maxlength="19">
                    <div style="display:flex; gap:10px; margin-top:15px;">
                        <div style="flex:1">
                            <label>Expiry</label>
                            <input type="tel" placeholder="MM/YY">
                        </div>
                        <div style="flex:1">
                            <label>CVC</label>
                            <input type="tel" placeholder="123" maxlength="4">
                        </div>
                    </div>
                </div>

                <div id="bank-inputs" class="form-section">
                    <label>Select Your Bank</label>
                    <select name="bank_name">
                        <option value="Maybank2u">Maybank2u</option>
                        <option value="CIMB Clicks">CIMB Clicks</option>
                        <option value="Public Bank">Public Bank</option>
                        <option value="RHB Now">RHB Now</option>
                        <option value="Bank Islam">Bank Islam</option>
                    </select>
                </div>

                <button type="submit" name="submit_payment" class="btn-pay">CONFIRM PAYMENT</button>
            </form>
        </div>
    </div>
</div>

<script>
    function selectMethod(method, element) {
        document.querySelectorAll('.method-card').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        document.getElementById('payment_method').value = method;
        const cardSec = document.getElementById('card-inputs');
        const bankSec = document.getElementById('bank-inputs');
        if (method === 'Credit Card') {
            cardSec.classList.add('show');
            bankSec.classList.remove('show');
        } else {
            cardSec.classList.remove('show');
            bankSec.classList.add('show');
        }
    }
</script>

<?php include 'footer.php'; ?>
</body>
</html>