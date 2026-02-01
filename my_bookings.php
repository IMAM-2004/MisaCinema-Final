<?php
session_start();
require 'vendor/autoload.php';

// 1. CHECK LOGIN 
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 2. SETUP DATABASE
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$db = $client->misacinema_db;
$collection = $db->bookings;
$movieCollection = $db->shows; // PENTING: Untuk ambil gambar poster

// 3. HELPER FUNCTION (Anti-Crash)
function safeStr($input) {
    if (is_null($input)) return '-';
    if (is_string($input) || is_numeric($input)) return htmlspecialchars((string)$input);
    $arr = (array)$input;
    if (isset($arr['fullname'])) return htmlspecialchars($arr['fullname']);
    if (isset($arr['name'])) return htmlspecialchars($arr['name']);
    return '-';
}

// 4. DATA USER DARI SESSION
$userFullname = '';
$userEmail = '';

if (is_array($_SESSION['user'])) {
    $userFullname = $_SESSION['user']['fullname'] ?? $_SESSION['user']['username'] ?? '';
    $userEmail = $_SESSION['user']['email'] ?? '';
} else if (is_object($_SESSION['user'])) {
    $userFullname = $_SESSION['user']->fullname ?? $_SESSION['user']->username ?? '';
    $userEmail = $_SESSION['user']->email ?? '';
} else {
    $userFullname = $_SESSION['user'];
}

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
    <title>My Bookings - Misa Cinema</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.9)), url('assets/images/bg_cinema.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 40px;
            min-height: 100vh;
        }

        .container { max-width: 900px; margin: 0 auto; }

        .header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 20px;
        }
        .header h1 { color: #e50914; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .btn-back { 
            color: #ccc; text-decoration: none; font-weight: bold; 
            border: 1px solid #444; padding: 8px 15px; border-radius: 4px; transition: 0.3s;
        }
        .btn-back:hover { background: #333; color: white; border-color: #fff; }

        /* CARD STYLE */
        .booking-card {
            background: #1a1a1a; border: 1px solid #333; border-radius: 8px;
            margin-bottom: 20px; display: flex; overflow: hidden;
            transition: transform 0.2s; position: relative;
        }
        .booking-card:hover { transform: translateY(-3px); border-color: #555; }

        /* POSTER IMAGE */
        .poster-img {
            width: 130px; 
            background-color: #222; 
            background-size: cover; 
            background-position: center;
            min-height: 180px;
        }

        .card-details { padding: 20px; flex: 1; display: flex; flex-direction: column; justify-content: center; }
        
        .movie-title { font-size: 1.4rem; font-weight: bold; color: white; margin-bottom: 8px; }
        
        .meta-info { color: #aaa; font-size: 0.95rem; margin-bottom: 5px; display: flex; align-items: center; }
        .meta-info i { color: #e50914; margin-right: 10px; width: 20px; text-align: center; }

        .seats-container { margin-top: 15px; }
        .seats-badge { 
            background: #e50914; color: white; padding: 4px 8px; 
            border-radius: 3px; font-size: 0.85rem; margin-right: 5px; font-weight: bold; display: inline-block;
        }

        .action-section {
            background: #111; padding: 20px; display: flex; flex-direction: column; 
            justify-content: center; align-items: center; min-width: 160px; border-left: 1px solid #333;
        }
        .total-price { color: #2ecc71; font-size: 1.3rem; font-weight: bold; margin-bottom: 5px; }
        .status { color: #888; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; }

        .btn-ticket {
            background: white; color: #000; padding: 8px 15px; 
            text-decoration: none; border-radius: 4px; font-size: 0.85rem; font-weight: bold;
            transition: 0.2s; display: flex; align-items: center; gap: 8px;
        }
        .btn-ticket:hover { background: #e50914; color: white; }

        @media (max-width: 768px) {
            .booking-card { flex-direction: column; }
            .poster-img { width: 100%; height: 200px; }
            .action-section { border-left: none; border-top: 1px solid #333; flex-direction: row; justify-content: space-between; padding: 15px 20px; }
            .btn-ticket { padding: 6px 12px; }
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
        
        // 1. DATA BASIC
        $rawMovieName = $booking['movie_name'] ?? 'Unknown Movie';
        $movieName = safeStr($rawMovieName);
        $hallName = safeStr($booking['hall_name'] ?? 'Hall -');
        
        // 2. DATA GAMBAR (Cari dari collection 'shows')
        $posterUrl = 'assets/img/default_poster.jpg'; // Gambar default kalau tak jumpa
        
        // Cari movie dalam database shows berdasarkan nama
        $movieData = $movieCollection->findOne(['name' => $rawMovieName]);
        
        if ($movieData && isset($movieData['image']) && !empty($movieData['image'])) {
            // Pastikan path ini betul ikut folder awak. Biasanya 'assets/img/' atau 'uploads/'
            $posterUrl = 'assets/img/' . $movieData['image']; 
        }

        // 3. TARIKH & HARGA
        $dateStr = '-';
        if (isset($booking['booking_date'])) $dateStr = date('d M Y, h:i A', strtotime($booking['booking_date']));
        elseif (isset($booking['showtime'])) $dateStr = date('d M Y, h:i A', strtotime($booking['showtime']));

        $price = isset($booking['total_price']) ? number_format((float)$booking['total_price'], 2) : '0.00';
        $seats = $booking['seats'] ?? [];
        $bookingId = (string)$booking['_id']; // ID untuk link tiket
    ?>
    
    <div class="booking-card">
        <div class="poster-img" style="background-image: url('<?php echo $posterUrl; ?>');"></div>
        
        <div class="card-details">
            <div class="movie-title"><?php echo $movieName; ?></div>
            
            <div class="meta-info"><i class="far fa-calendar-alt"></i> <?php echo $dateStr; ?></div>
            <div class="meta-info"><i class="fas fa-couch"></i> <?php echo $hallName; ?></div>

            <div class="seats-container">
                <small style="color:#666; display:block; margin-bottom:5px; font-size:0.8em;">SEATS:</small>
                <?php 
                $seatArr = (array)$seats;
                if (!empty($seatArr)) {
                    foreach($seatArr as $s) {
                        echo '<span class="seats-badge">' . safeStr($s) . '</span>';
                    }
                } else {
                    echo '<span style="color:#666;">-</span>';
                }
                ?>
            </div>
        </div>

        <div class="action-section">
            <div class="total-price">RM <?php echo $price; ?></div>
            <div class="status"><i class="fas fa-check-circle"></i> Paid</div>
            
            <a href="receipt.php?id=<?php echo $bookingId; ?>" target="_blank" class="btn-ticket">
                <i class="fas fa-ticket-alt"></i> View Ticket
            </a>
        </div>
    </div>

    <?php endforeach; ?>

    <?php if ($count == 0): ?>
        <div style="text-align:center; padding:60px; color:#555;">
            <i class="fas fa-film" style="font-size:3rem; margin-bottom:15px;"></i>
            <h3>No bookings found</h3>
            <p>Your booking history is empty.</p>
        </div>
    <?php endif; ?>

</div>
<?php include 'footer.php'; ?>
</body>

</html>
