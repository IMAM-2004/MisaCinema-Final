<?php
session_start();

// 1. CHECK LOGIN DULU (Paling Atas)
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require 'vendor/autoload.php';

// 2. SETUP DATABASE
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$db = $client->misacinema_db;
$collection = $db->bookings;
$movieCollection = $db->shows;

// 3. HELPER FUNCTION
function safeStr($input) {
    if (is_null($input)) return '-';
    if (is_string($input) || is_numeric($input)) return htmlspecialchars((string)$input);
    $arr = (array)$input;
    if (isset($arr['fullname'])) return htmlspecialchars($arr['fullname']);
    if (isset($arr['name'])) return htmlspecialchars($arr['name']);
    return '-';
}

// 4. GET USER DATA (Support Array & Object)
$userFullname = '';
$userEmail = '';

// Tukar BSONDocument jadi Array supaya senang baca
$userData = (array)$_SESSION['user'];

$userFullname = $userData['fullname'] ?? $userData['username'] ?? 'User';
$userEmail = $userData['email'] ?? '';

// 5. QUERY BOOKINGS
$filter = [
    '$or' => [
        ['customer_name' => $userFullname],
        ['customer_name.fullname' => $userFullname],
        ['email' => $userEmail],
        ['customer_name.email' => $userEmail]
    ]
];

$myBookings = $collection->find($filter, ['sort' => ['_id' => -1]]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Bookings - Misa Cinema</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- GLOBAL & RESET --- */
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.9)), url('assets/images/bg_cinema.png');
            background-size: cover; background-position: center; background-attachment: fixed;
            color: white; font-family: 'Roboto', sans-serif; margin: 0; padding: 20px; min-height: 100vh;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #333; padding-bottom: 15px; }
        .header h1 { color: #e50914; margin: 0; font-size: 1.8rem; text-transform: uppercase; letter-spacing: 1px; }
        
        .btn-back { color: #ccc; text-decoration: none; font-weight: bold; border: 1px solid #444; padding: 8px 15px; border-radius: 4px; transition: 0.3s; font-size: 0.9rem; }
        .btn-back:hover { background: #333; color: white; border-color: #fff; }

        .booking-card { background: #1a1a1a; border: 1px solid #333; border-radius: 8px; margin-bottom: 20px; display: flex; overflow: hidden; transition: transform 0.2s; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        @media(min-width: 769px) { .booking-card:hover { transform: translateY(-3px); border-color: #555; } }

        .poster-img { width: 140px; background-color: #222; background-size: cover; background-position: center; flex-shrink: 0; }
        .card-details { padding: 20px; flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .movie-title { font-size: 1.3rem; font-weight: bold; color: white; margin-bottom: 10px; line-height: 1.2; }
        .meta-info { color: #aaa; font-size: 0.9rem; margin-bottom: 6px; display: flex; align-items: center; }
        .meta-info i { color: #e50914; margin-right: 10px; width: 20px; text-align: center; }

        .seats-container { margin-top: 15px; }
        .seats-badge { background: #333; color: #fff; padding: 4px 8px; border: 1px solid #444; border-radius: 4px; font-size: 0.8rem; margin-right: 4px; margin-bottom: 4px; font-weight: bold; display: inline-block; }

        .action-section { background: #111; padding: 20px; display: flex; flex-direction: column; justify-content: center; align-items: center; min-width: 170px; border-left: 1px solid #333; }
        .total-price { color: #2ecc71; font-size: 1.4rem; font-weight: bold; margin-bottom: 5px; }
        .status { color: #666; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; }

        .btn-ticket { background: white; color: #000; padding: 10px 20px; text-decoration: none; border-radius: 30px; font-size: 0.9rem; font-weight: bold; transition: 0.2s; display: flex; align-items: center; gap: 8px; }
        .btn-ticket:hover { background: #e50914; color: white; }

        @media (max-width: 768px) {
            body { padding: 15px 10px; }
            .header h1 { font-size: 1.5rem; }
            .booking-card { flex-direction: column; }
            .poster-img { width: 100%; height: 180px; background-position: center 20%; }
            .card-details { padding: 15px; }
            .action-section { border-left: none; border-top: 1px solid #333; flex-direction: row; justify-content: space-between; padding: 15px 20px; background: #0a0a0a; }
            .status { display: none; }
            .total-price { margin-bottom: 0; font-size: 1.2rem; }
            .btn-ticket { padding: 8px 16px; font-size: 0.85rem; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-history"></i> My History</h1>
        <a href="home.php" class="btn-back"><i class="fas fa-arrow-left"></i> Home</a>
    </div>

    <?php 
    $count = 0; 
    foreach ($myBookings as $booking): 
        $count++;
        $rawMovieName = $booking['movie_name'] ?? 'Unknown Movie';
        $movieName = safeStr($rawMovieName);
        $hallName = safeStr($booking['hall_name'] ?? 'Hall -');
        
        // Cari Poster Movie
        $posterUrl = 'assets/img/poster_placeholder.jpg'; // Default kalau tak jumpa
        $movieData = $movieCollection->findOne(['name' => $rawMovieName]);
        if ($movieData && isset($movieData['image']) && !empty($movieData['image'])) {
            $posterUrl = 'assets/img/' . $movieData['image']; 
        }

        $dateStr = '-';
        if (isset($booking['booking_date'])) $dateStr = date('d M Y, h:i A', strtotime($booking['booking_date']));
        elseif (isset($booking['showtime'])) $dateStr = date('d M Y, h:i A', strtotime($booking['showtime']));

        $price = isset($booking['total_price']) ? number_format((float)$booking['total_price'], 2) : '0.00';
        $seats = $booking['seats'] ?? [];
        $bookingId = (string)$booking['_id']; 
    ?>
    
    <div class="booking-card">
        <div class="poster-img" style="background-image: url('<?php echo $posterUrl; ?>');"></div>
        <div class="card-details">
            <div class="movie-title"><?php echo $movieName; ?></div>
            <div class="meta-info"><i class="far fa-calendar-alt"></i> <?php echo $dateStr; ?></div>
            <div class="meta-info"><i class="fas fa-couch"></i> <?php echo $hallName; ?></div>
            <div class="seats-container">
                <?php 
                $seatArr = (array)$seats;
                if (!empty($seatArr)) {
                    foreach($seatArr as $s) echo '<span class="seats-badge">' . safeStr($s) . '</span>';
                } else {
                    echo '<span style="color:#666; font-size:0.8rem;">No Seats</span>';
                }
                ?>
            </div>
        </div>
        <div class="action-section">
            <div>
                <div class="total-price">RM <?php echo $price; ?></div>
                <div class="status"><i class="fas fa-check-circle"></i> Paid</div>
            </div>
            <a href="receipt.php?id=<?php echo $bookingId; ?>" target="_blank" class="btn-ticket">
                <i class="fas fa-ticket-alt"></i> View Ticket
            </a>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if ($count == 0): ?>
        <div style="text-align:center; padding:60px 20px; color:#555;">
            <i class="fas fa-film" style="font-size:3rem; margin-bottom:15px; opacity: 0.5;"></i>
            <h3>No bookings found</h3>
            <p style="color: #888;">You haven't booked any movies yet.</p>
            <a href="home.php" class="btn-ticket" style="display:inline-flex; background:#e50914; color:white; margin-top:15px;">Book Now</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>