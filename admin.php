<?php
session_start();

// 1. SECURITY CHECK
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') { 
    header("Location: login.php"); 
    exit(); 
}

require 'vendor/autoload.php';
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$db = $client->misacinema_db;

// ==========================================
// A. LOGIK STATISTIK (BISNES)
// ==========================================
$totalMovies = $db->shows->countDocuments();
$totalUsers = $db->users->countDocuments(['role' => 'customer']);

$bookings = $db->bookings->find();
$totalRevenue = 0;
$totalTickets = 0;

foreach ($bookings as $b) {
    $totalRevenue += isset($b['total_price']) ? (float)$b['total_price'] : 0;
    if (isset($b['seats']) && is_array($b['seats'])) {
        $totalTickets += count($b['seats']);
    } else {
        $totalTickets++;
    }
}

// Senarai 5 Booking Terakhir
$recentBookings = $db->bookings->find([], ['limit' => 5, 'sort' => ['_id' => -1]]);

function safeStr($input) {
    if (is_string($input) || is_numeric($input)) return $input;
    if (is_array($input)) return $input['fullname'] ?? $input['username'] ?? '-';
    if (is_object($input)) return $input->fullname ?? $input->username ?? '-';
    return '-';
}

// ==========================================
// B. LOGIK LIVE HALL MONITOR (OPERASI)
// ==========================================
$halls = [];
// Setup Hall 1-6
for ($i = 1; $i <= 6; $i++) {
    $type = ($i % 3 == 1) ? "VIP" : (($i % 3 == 2) ? "IMAX" : "Standard");
    $badgeColor = ($type == "VIP") ? "#FFD700" : (($type == "IMAX") ? "#007bff" : "#888");
    
    $halls["Hall $i"] = [
        'type' => $type,
        'badge_color' => $badgeColor,
        'status' => 'Available', 
        'movie' => '-', 
        'end_time' => '-',
        'progress' => 0
    ];
}

// Check Masa Sekarang vs Jadual Movie
$currentTime = time(); 
$allMovies = $db->shows->find();

foreach ($allMovies as $m) {
    if (isset($m['showtimes'])) {
        foreach ($m['showtimes'] as $show) {
            $showStart = strtotime($show['datetime']);
            $duration = isset($m['duration']) ? intval($m['duration']) : 120; // Default 120 min
            $showEnd = $showStart + ($duration * 60);
            $cleaningEnd = $showEnd + (30 * 60); // +30 minit cuci

            // Kalau Hall ini wujud dalam list kita
            $hallKey = explode(" (", $show['hall'])[0]; // Bersihkan nama hall jika ada extra text
            if (!isset($halls[$hallKey])) $hallKey = $show['hall']; // Cuba nama asal

            if (isset($halls[$hallKey])) {
                // Check Status
                if ($currentTime >= $showStart && $currentTime < $showEnd) {
                    // SEDANG TAYANG
                    $halls[$hallKey]['status'] = 'ONGOING';
                    $halls[$hallKey]['movie'] = $m['name'];
                    $halls[$hallKey]['end_time'] = date('h:i A', $showEnd);
                    
                    $totalDuration = $showEnd - $showStart;
                    $elapsed = $currentTime - $showStart;
                    $halls[$hallKey]['progress'] = ($elapsed / $totalDuration) * 100;

                } elseif ($currentTime >= $showEnd && $currentTime < $cleaningEnd) {
                    // TENGAH CUCI
                    $halls[$hallKey]['status'] = 'CLEANING';
                    $halls[$hallKey]['movie'] = 'Housekeeping...';
                    $halls[$hallKey]['end_time'] = date('h:i A', $cleaningEnd);
                    $halls[$hallKey]['progress'] = 100;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Misa Cinema</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Roboto', sans-serif; }
        
        body { 
            background-color: #0b0b0b; 
            color: white; 
            display: flex; 
            min-height: 100vh;
        }

        .main-content {
            margin-left: 250px; 
            padding: 40px;
            width: calc(100% - 250px);
        }

        .header-title { margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 20px; }
        .header-title h1 { color: #e50914; text-transform: uppercase; letter-spacing: 1px; }

        /* --- STATS CSS --- */
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;
        }
        .stat-card {
            background: #1a1a1a; padding: 25px; border-radius: 8px; border-left: 4px solid #e50914;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3); transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-5px); background: #222; }
        .stat-card h3 { font-size: 0.9rem; color: #aaa; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .value { font-size: 2rem; font-weight: bold; color: white; }
        .stat-card i { float: right; font-size: 2.5rem; color: #333; }

        /* --- TABLE CSS --- */
        .section-title { font-size: 1.2rem; margin: 40px 0 20px 0; color: #fff; border-left: 4px solid #e50914; padding-left: 10px; display: flex; justify-content: space-between; align-items: center;}
        .table-container { background: #1a1a1a; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.2); margin-bottom: 40px;}
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #333; }
        th { background: #222; color: #e50914; text-transform: uppercase; font-size: 0.85rem; }
        tr:hover { background: #222; }
        td { color: #ccc; font-size: 0.95rem; }
        .status-paid { background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;}

        /* --- LIVE HALL MONITOR CSS --- */
        .halls-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .hall-card {
            background: #161616; border: 1px solid #333; border-radius: 8px; padding: 20px; position: relative; overflow: hidden;
        }
        .hall-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .hall-name { font-size: 1.2rem; font-weight: bold; }
        .hall-type { font-size: 0.7rem; padding: 2px 6px; border: 1px solid; border-radius: 4px; margin-left: 10px; }
        
        .status-badge { font-size: 0.7rem; font-weight: bold; padding: 4px 8px; border-radius: 12px; text-transform: uppercase; }
        .status-available { background: rgba(100, 100, 100, 0.2); color: #aaa; border: 1px solid #555; }
        .status-ongoing { background: rgba(229, 9, 20, 0.2); color: #e50914; border: 1px solid #e50914; animation: pulse 2s infinite; }
        .status-cleaning { background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; }

        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }

        .movie-display { height: 60px; display: flex; flex-direction: column; justify-content: center; }
        .current-movie { font-size: 1rem; color: white; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .time-info { font-size: 0.8rem; color: #777; margin-top: 5px; }

        .progress-container { width: 100%; background: #333; height: 4px; border-radius: 2px; margin-top: 15px; }
        .progress-bar { height: 100%; background: #e50914; transition: width 0.5s; }
        .cleaning-bar { background: #ffc107; }

    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="header-title">
            <h1>Business Overview</h1>
            <p style="color: #777;">Welcome back, Admin.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #2ecc71;">
                <i class="fas fa-wallet"></i>
                <h3>Total Revenue</h3>
                <div class="value">RM <?php echo number_format($totalRevenue, 2); ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #3498db;">
                <i class="fas fa-ticket-alt"></i>
                <h3>Tickets Sold</h3>
                <div class="value"><?php echo $totalTickets; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #e50914;">
                <i class="fas fa-film"></i>
                <h3>Total Movies</h3>
                <div class="value"><?php echo $totalMovies; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: #f1c40f;">
                <i class="fas fa-users"></i>
                <h3>Customers</h3>
                <div class="value"><?php echo $totalUsers; ?></div>
            </div>
        </div>

        <h3 class="section-title">Recent Transactions</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>Customer</th><th>Movie</th><th>Date</th><th>Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $hasData = false;
                    foreach($recentBookings as $rb): 
                        $hasData = true;
                        $cName = safeStr($rb['customer_name'] ?? 'Guest');
                        $mName = safeStr($rb['movie_name'] ?? '-');
                        $bDate = isset($rb['booking_date']) ? date('d/m/Y', strtotime($rb['booking_date'])) : '-';
                        $price = number_format((float)($rb['total_price'] ?? 0), 2);
                    ?>
                    <tr>
                        <td style="font-weight:bold; color:white;"><?php echo $cName; ?></td>
                        <td><?php echo $mName; ?></td>
                        <td><?php echo $bDate; ?></td>
                        <td>RM <?php echo $price; ?></td>
                        <td><span class="status-paid">PAID</span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(!$hasData): ?><tr><td colspan="5" style="text-align:center;">No transactions found.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <h3 class="section-title">
            Live Hall Status 
            <span style="font-size:0.8rem; background:#333; padding:5px 10px; border-radius:4px; font-weight:normal;">
                <i class="fas fa-satellite-dish" style="color:#e50914;"></i> Real-time
            </span>
        </h3>
        
        <div class="halls-grid">
            <?php foreach($halls as $name => $info): ?>
            <div class="hall-card">
                <div class="hall-header">
                    <div>
                        <span class="hall-name"><?php echo $name; ?></span>
                        <span class="hall-type" style="color:<?php echo $info['badge_color']; ?>; border-color:<?php echo $info['badge_color']; ?>">
                            <?php echo $info['type']; ?>
                        </span>
                    </div>
                    <div class="status-badge status-<?php echo strtolower($info['status']); ?>">
                        <?php echo $info['status']; ?>
                    </div>
                </div>

                <div class="movie-display">
                    <?php if($info['status'] == 'Available'): ?>
                        <div style="color: #444; font-style: italic;">Standby Mode</div>
                    <?php else: ?>
                        <div class="current-movie"><?php echo $info['movie']; ?></div>
                        <div class="time-info">
                            <?php echo ($info['status'] == 'CLEANING') ? 'Ready at' : 'Ends at'; ?>: 
                            <span style="color:white;"><?php echo $info['end_time']; ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="progress-container">
                    <div class="progress-bar <?php echo ($info['status']=='CLEANING')?'cleaning-bar':''; ?>" 
                         style="width: <?php echo $info['progress']; ?>%">
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <br><br> </div>
<?php include 'footer.php'; ?>
</body>

</html>
