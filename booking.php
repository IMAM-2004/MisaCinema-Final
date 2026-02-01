<?php
session_start();
// --- FIX TIMEZONE ---
date_default_timezone_set('Asia/Kuala_Lumpur'); 

// 1. SECURITY CHECK
if (!isset($_SESSION['user'])) { 
    header("Location: login.php"); 
    exit(); 
}

require 'vendor/autoload.php';
use MongoDB\BSON\ObjectId;

// 2. DATABASE CONNECTION
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$db = $client->misacinema_db;
$collection = $db->shows; 

$movie = null;
$selectedShowtime = null;

// --- LOGIC TO GET MOVIE & SHOWTIME ---
try {
    if (isset($_GET['showtime_id'])) {
        // If user already chose a time (Screen 2 - Select Seat)
        $sId = new ObjectId($_GET['showtime_id']);
        $movie = $collection->findOne(['showtimes._id' => $sId]);
        
        if ($movie) {
            foreach ($movie['showtimes'] as $s) {
                if ($s['_id'] == $sId) {
                    $selectedShowtime = $s;
                    break;
                }
            }
        }
    } elseif (isset($_GET['id'])) {
        // If user just clicked from Home (Screen 1 - Select Time)
        $movie = $collection->findOne(['_id' => new ObjectId($_GET['id'])]);
    } else {
        header("Location: home.php"); exit();
    }
} catch (Exception $e) {
    echo "Error: Invalid ID format."; exit();
}

if (!$movie) { echo "Movie not found."; exit(); }

$movieTitle = isset($movie['name']) ? $movie['name'] : (isset($movie['title']) ? $movie['title'] : 'Unknown Movie');

// =========================================================
// SCREEN 1: SELECT TIME (User hasn't chosen time yet)
// =========================================================
if (!isset($_GET['showtime_id']) || !$selectedShowtime) {
    $allShowtimes = isset($movie['showtimes']) ? (array)$movie['showtimes'] : [];
    
    // Filter past times
    $now = new DateTime(); 
    $futureShows = [];
    foreach ($allShowtimes as $s) {
        if(isset($s['datetime'])){
            $sTime = new DateTime($s['datetime']);
            if ($sTime > $now) {
                $futureShows[] = $s;
            }
        }
    }
    
    // Sort times
    usort($futureShows, function($a, $b) { return strcmp($a['datetime'], $b['datetime']); });

    // Group by Date
    $groupedShows = [];
    foreach ($futureShows as $s) {
        $dateKey = (new DateTime($s['datetime']))->format('Y-m-d');
        $groupedShows[$dateKey][] = $s;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Select Time - <?php echo $movieTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #0b0b0b; color: white; font-family: 'Roboto', sans-serif; padding: 20px; margin: 0; }
        .container { max-width: 800px; margin: 0 auto; width: 100%; }
        h1 { color: #e50914; text-align: center; text-transform: uppercase; font-size: 1.8em; }
        .date-section { margin-bottom: 30px; }
        .date-header { border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 15px; color: #ddd; font-size: 1.1em; }
        .time-grid { display: flex; gap: 15px; flex-wrap: wrap; justify-content: center; }
        .time-btn { 
            background: #1a1a1a; border: 1px solid #333; color: white; padding: 15px; border-radius: 6px; 
            text-decoration: none; min-width: 130px; transition: 0.2s; position: relative; display: block; text-align: center;
        }
        .time-btn:hover { background: #e50914; transform: translateY(-3px); border-color: #e50914; }
        .t-time { font-size: 1.2em; font-weight: bold; display: block; }
        .t-hall { font-size: 0.8em; color: #aaa; margin-top: 5px; display: block; }
        .hall-badge { position: absolute; top: 0; right: 0; font-size: 0.6em; padding: 2px 5px; background: #333; }
        .vip { background: gold; color: black; }
        .imax { background: #007bff; color: white; }

        /* Mobile Adjustments */
        @media (max-width: 600px) {
            .time-btn { width: 100%; /* Full width buttons on phone */ }
            h1 { font-size: 1.5em; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $movieTitle; ?></h1>
        <p style="text-align:center; color:#777;">Select Show Time</p>
        
        <?php if(empty($groupedShows)): ?>
            <div style="text-align:center; margin-top:50px; color:#555;">
                <h3>No upcoming showtimes available.</h3>
                <a href="home.php" style="color:#e50914;">Back to Home</a>
            </div>
        <?php endif; ?>

        <?php foreach ($groupedShows as $date => $shows): 
            $dateObj = new DateTime($date);
            $label = $dateObj->format('l, d M Y');
        ?>
        <div class="date-section">
            <div class="date-header"><strong><?php echo $label; ?></strong></div>
            <div class="time-grid">
                <?php foreach ($shows as $s): 
                    $timeObj = new DateTime($s['datetime']);
                    
                    // --- PRICE LOGIC FOR BUTTON ---
                    $hallType = 'std';
                    if (stripos($s['hall'], 'IMAX') !== false) $hallType = 'imax';
                    if (stripos($s['hall'], 'VIP') !== false) $hallType = 'vip';

                    $displayPrice = $s['price']; 
                    if ($hallType == 'vip') {
                        $displayPrice += 30;
                    } elseif ($hallType == 'imax') {
                        $displayPrice += 15;
                    }
                ?>
                <a href="booking.php?showtime_id=<?php echo $s['_id']; ?>" class="time-btn">
                    <?php if($hallType != 'std') echo "<div class='hall-badge $hallType'>".strtoupper($hallType)."</div>"; ?>
                    
                    <span class="t-time"><?php echo $timeObj->format('h:i A'); ?></span>
                    <span class="t-hall"><?php echo $s['hall']; ?></span>
                    
                    <div style="font-size:0.8em; color:#00ff7f; margin-top:3px;">
                        RM <?php echo number_format($displayPrice, 2); ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
<?php exit(); } 

// =========================================================
// SCREEN 2: SELECT SEAT (User has chosen a time)
// =========================================================

$timeObj = new DateTime($selectedShowtime['datetime']);
$displayDateTime = $timeObj->format('d M Y, h:i A');
$hallName = $selectedShowtime['hall'];

// --- TICKET PRICE LOGIC (SCREEN 2) ---
$basePrice = $selectedShowtime['price']; 
$extraCharge = 0;
$chargeLabel = "";

if (stripos($hallName, 'VIP') !== false) { 
    $extraCharge = 30; 
    $chargeLabel = "(VIP Surcharge +RM30)";
} elseif (stripos($hallName, 'IMAX') !== false) { 
    $extraCharge = 15; 
    $chargeLabel = "(IMAX Surcharge +RM15)";
}

$finalPrice = $basePrice + $extraCharge;

// CONFIG SEAT LAYOUT
if (stripos($hallName, 'VIP') !== false) { 
    $rows = 5; $cols = 8; $gapIndex = 4; // VIP
    $seatSize = '40px';
} elseif (stripos($hallName, 'IMAX') !== false) { 
    $rows = 12; $cols = 14; $gapIndex = 7; // IMAX
    $seatSize = '28px'; 
} else { 
    $rows = 8; $cols = 10; $gapIndex = 5; // Standard
    $seatSize = '32px';
}

// FETCH BOOKED SEATS
$bookedSeats = [];
$currentShowtimeId = (string)$selectedShowtime['_id'];

$existingBookings = $db->bookings->find([
    'showtime_id' => $currentShowtimeId 
]);

foreach ($existingBookings as $b) {
    if (isset($b['seats'])) {
        $bookedSeats = array_merge($bookedSeats, (array)$b['seats']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Select Seats - <?php echo $movieTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #0b0b0b; 
            color: white; 
            font-family: 'Roboto', sans-serif; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0;
            overflow-x: hidden; /* Prevent body scroll, only hall scrolls */
        }
        
        .screen { 
            background: linear-gradient(to bottom, #fff, rgba(255,255,255,0)); 
            height: 60px; 
            width: 80%; /* Responsive width */
            max-width: 400px;
            transform: perspective(300px) rotateX(-10deg); 
            box-shadow: 0 20px 50px rgba(255,255,255,0.1); 
            margin: 20px auto 40px; 
            border-radius: 8px; 
            text-align:center; color:#000; line-height:50px; font-weight:bold; letter-spacing: 5px; opacity: 0.7;
        }
        
        /* --- MOBILE RESPONSIVE HALL (Horizontal Scroll) --- */
        .cinema-hall { 
            display: flex; 
            flex-direction: column; 
            gap: 8px; 
            align-items: center; 
            padding-bottom: 140px; 
            
            /* Magic for Mobile */
            width: 100%;
            overflow-x: auto; /* Allows scrolling left/right */
            padding-left: 20px; 
            padding-right: 20px;
            box-sizing: border-box;
        }

        .row { 
            display: flex; 
            gap: 6px; 
            width: max-content; /* Force row to stay wide (no wrapping) */
            margin: 0 auto; /* Center if it fits */
        }
        
        .seat { 
            width: <?php echo $seatSize; ?>; height: <?php echo $seatSize; ?>; 
            background: #444; border-radius: 6px 6px 2px 2px; 
            cursor: pointer; display: flex; align-items: center; justify-content: center; 
            font-size: 0.7em; user-select: none;
            transition: 0.2s;
            flex-shrink: 0; /* Prevent seat from shrinking on phone */
        }
        .seat:hover { background: #777; }
        .seat.selected { background: #e50914; color: white; box-shadow: 0 0 10px #e50914; }
        .seat.occupied { background: #222; color: #444; cursor: not-allowed; pointer-events: none; border: 1px solid #333; }
        
        .aisle-gap { width: 30px; flex-shrink: 0; }
        
        .booking-bar { 
            position: fixed; bottom: 0; left: 0; width: 100%; 
            background: #151515; padding: 15px 20px; 
            border-top: 2px solid #e50914; display: flex; justify-content: space-between; align-items: center; 
            box-sizing: border-box; z-index: 100;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.5);
        }
        .info-text { font-size: 0.9em; color: #ccc; }
        .info-text span { color: white; font-weight: bold; }
        
        .btn-pay { 
            background: #e50914; color: white; border: none; padding: 12px 20px; 
            font-weight: bold; border-radius: 4px; cursor: pointer; font-size: 0.9em; text-transform: uppercase;
            transition: 0.3s;
        }
        .btn-pay:disabled { background: #333; color: #555; cursor: not-allowed; }
        .btn-pay:hover:not(:disabled) { background: #ff0f1f; }

        /* Scrollbar styling */
        .cinema-hall::-webkit-scrollbar { height: 6px; }
        .cinema-hall::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }
    </style>
</head>
<body>
    <h2 style="margin-top:20px; text-transform:uppercase; font-size: 1.5em; text-align:center; padding: 0 10px;"><?php echo $movieTitle; ?></h2>
    
    <div style="text-align:center;">
        <p style="color:#e50914; margin: 0; font-weight:bold; font-size:1.1em;"><?php echo $hallName; ?></p>
        <p style="color:#777; font-size:0.9em; margin-top:5px;"><?php echo $displayDateTime; ?></p>
        
        <p style="margin-top:5px; color:#00ff7f;">
            Ticket Price: RM <?php echo number_format($finalPrice, 2); ?> 
            <?php if($extraCharge > 0): ?>
                <span style="color:#aaa; font-size:0.8em;"><?php echo $chargeLabel; ?></span>
            <?php endif; ?>
        </p>
    </div>
    
    <div class="screen">SCREEN</div>
    
    <div style="display:flex; gap: 15px; margin-bottom: 20px; font-size: 0.8em; color: #aaa; flex-wrap: wrap; justify-content: center;">
        <div style="display:flex; align-items:center; gap:5px;"><div style="width:15px; height:15px; background:#444; border-radius:2px;"></div> Available</div>
        <div style="display:flex; align-items:center; gap:5px;"><div style="width:15px; height:15px; background:#e50914; border-radius:2px;"></div> Selected</div>
        <div style="display:flex; align-items:center; gap:5px;"><div style="width:15px; height:15px; background:#222; border:1px solid #333; border-radius:2px;"></div> Booked</div>
    </div>

    <div style="font-size: 0.7em; color: #555; margin-bottom: 10px; display: none;" id="scrollHint">
        <i class="fas fa-arrows-alt-h"></i> Swipe to see more seats
    </div>
    <script>if(window.innerWidth < 600) document.getElementById('scrollHint').style.display = 'block';</script>

    <div class="cinema-hall">
        <?php 
        for ($r = 0; $r < $rows; $r++) {
            $rowLetter = chr(65 + $r); // A, B, C...
            echo "<div class='row'>"; 
            echo "<div style='width:20px; display:flex; align-items:center; justify-content:center; color:#555; font-size:0.7em; margin-right:5px;'>$rowLetter</div>"; // Row Label
            for ($c = 1; $c <= $cols; $c++) {
                if ($c == $gapIndex + 1) echo "<div class='aisle-gap'></div>";
                
                $seatLabel = $rowLetter . $c;
                $isOccupied = in_array($seatLabel, $bookedSeats) ? 'occupied' : '';
                
                echo "<div class='seat $isOccupied' data-seat='$seatLabel'>$seatLabel</div>";
            }
            echo "</div>";
        }
        ?>
    </div>

    <form action="payment.php" method="POST">
        <input type="hidden" name="showtime_id" value="<?php echo $currentShowtimeId; ?>">
        <input type="hidden" name="movie_name" value="<?php echo $movieTitle; ?>">
        <input type="hidden" name="hall_name" value="<?php echo $hallName; ?>">
        <input type="hidden" name="showtime" value="<?php echo $selectedShowtime['datetime']; ?>">
        
        <input type="hidden" name="selected_seats" id="inputSeats">
        <input type="hidden" name="total_price" id="inputPrice">

        <div class="booking-bar">
            <div class="info-text">
                Seats: <span id="seatList" style="color:#e50914">-</span> <br>
                Total: <span style="color:#00ff7f; font-size: 1.1em;">RM <span id="totalDisplay">0.00</span></span>
            </div>
            
            <button type="submit" class="btn-pay" id="payBtn" disabled>Select</button>
        </div>
    </form>

<script>
    const hall = document.querySelector('.cinema-hall');
    const seatListSpan = document.getElementById('seatList');
    const totalDisplay = document.getElementById('totalDisplay');
    const inputSeats = document.getElementById('inputSeats');
    const inputPrice = document.getElementById('inputPrice');
    const payBtn = document.getElementById('payBtn');
    
    // Use the final price calculated by PHP
    const pricePerTicket = <?php echo $finalPrice; ?>;

    hall.addEventListener('click', (e) => {
        if (e.target.classList.contains('seat') && !e.target.classList.contains('occupied')) {
            e.target.classList.toggle('selected');
            updateSelection();
        }
    });

    function updateSelection() {
        const selected = document.querySelectorAll('.seat.selected');
        const seatsArr = [...selected].map(s => s.getAttribute('data-seat'));
        
        // Show only first 3 seats + count to save space on mobile
        if(seatsArr.length > 3) {
            seatListSpan.innerText = seatsArr.slice(0,3).join(", ") + " +" + (seatsArr.length - 3) + " more";
        } else {
            seatListSpan.innerText = seatsArr.length > 0 ? seatsArr.join(", ") : "-";
        }
        
        const total = seatsArr.length * pricePerTicket;
        totalDisplay.innerText = total.toFixed(2);
        
        inputSeats.value = seatsArr.join(",");
        inputPrice.value = total;
        
        if (seatsArr.length > 0) {
            payBtn.disabled = false;
            payBtn.innerText = "Pay";
        } else {
            payBtn.disabled = true;
            payBtn.innerText = "Select";
        }
    }
</script>
    <?php include 'footer.php'; ?>
</body>

</html>