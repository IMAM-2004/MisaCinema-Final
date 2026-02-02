<?php
// --- 1. SAFETY FIRST: Show Errors ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'vendor/autoload.php'; 

use MongoDB\BSON\ObjectId;

// --- 2. DATABASE CONNECTION ---
try {
    $client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
    $db = $client->misacinema_db;
    $collection = $db->bookings;
} catch (Exception $e) {
    die("Database Connection Error. Please check internet.");
}

// --- 3. GET DATA ---
// Kalau user masuk page ni tanpa ?id=..., kita redirect atau tunjuk error cantik
if (!isset($_GET['id'])) {
    // Redirect ke home kalau tak valid (optional), atau tunjuk error
    echo "<script>alert('No Ticket ID found!'); window.location.href='index.php';</script>";
    exit();
}

try {
    $bookingId = new ObjectId($_GET['id']);
    $booking = $collection->findOne(['_id' => $bookingId]);

    if (!$booking) {
        echo "<script>alert('Ticket not found in database!'); window.location.href='index.php';</script>";
        exit();
    }

    // Nama Customer Logic
    $customerName = 'Guest';
    if (isset($booking['customer_name'])) {
        $dbName = $booking['customer_name'];
        if (is_array($dbName) || is_object($dbName)) {
            $customerName = $dbName['fullname'] ?? $dbName['username'] ?? 'Guest';
        } else {
            $customerName = $dbName;
        }
    }

} catch (Exception $e) {
    die("Error: Invalid Ticket ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Misa Cinema</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        body {
            background-color: #050505;
            background-image: radial-gradient(circle at 50% 0%, #330000 0%, #000000 80%);
            color: white; font-family: 'Montserrat', sans-serif;
            min-height: 100vh; margin: 0; padding: 20px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }

        /* --- TICKET DESIGN --- */
        .ticket-container {
            background: #1a1a1a;
            width: 100%; max-width: 350px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 40px rgba(229, 9, 20, 0.3);
            margin-bottom: 25px;
            position: relative;
            border: 1px solid #333;
        }

        .ticket-header {
            background: #e50914; padding: 20px; text-align: center;
        }
        .logo-text { font-weight: 800; font-size: 1.2rem; letter-spacing: 2px; margin: 0; }
        .success-badge { 
            background: rgba(0,0,0,0.3); color: white; padding: 5px 15px; 
            border-radius: 20px; font-size: 0.7rem; font-weight: bold; 
            display: inline-block; margin-top: 5px; text-transform: uppercase;
        }

        .ticket-body { padding: 25px; position: relative; }
        
        .movie-title { font-size: 1.3rem; font-weight: 800; text-transform: uppercase; line-height: 1.2; margin-bottom: 5px; }
        .hall { color: #e50914; font-weight: 700; font-size: 0.9rem; margin-bottom: 20px; display: block; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 0.9rem; margin-bottom: 20px; }
        .label { font-size: 0.65rem; color: #888; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .value { font-weight: 600; }

        .seats-box {
            background: #222; padding: 10px; border-radius: 8px; text-align: center; border: 1px solid #333;
        }
        .seats-val { color: #e50914; font-weight: 800; font-size: 1.1rem; }

        .dashed-line { border-bottom: 2px dashed #444; margin: 0 10px; }

        .ticket-footer { padding: 20px; text-align: center; background: #fff; color: black; }
        #qrcode { display: inline-block; padding: 5px; background: white; }
        .ref-id { font-family: monospace; font-size: 0.8rem; margin-top: 10px; color: #555; }

        /* --- BUTTONS --- */
        .btn-group { display: flex; flex-direction: column; gap: 15px; width: 100%; max-width: 350px; text-align: center; }
        
        .btn {
            display: block; padding: 15px; border-radius: 50px; text-decoration: none; 
            font-weight: 700; transition: 0.3s; cursor: pointer; border: none; font-size: 1rem;
        }
        .btn-download { background: white; color: black; }
        .btn-download:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,255,255,0.2); }
        
        .btn-home { background: transparent; border: 2px solid #333; color: #888; }
        .btn-home:hover { border-color: white; color: white; background: rgba(255,255,255,0.1); }

        .auto-msg { font-size: 0.75rem; color: #666; margin-top: 5px; }
    </style>
</head>
<body>

    <div class="ticket-container" id="ticketVisual">
        <div class="ticket-header">
            <h1 class="logo-text"><i class="fas fa-film"></i> MISA CINEMA</h1>
            <div class="success-badge">Booking Confirmed</div>
        </div>

        <div class="ticket-body">
            <div class="movie-title"><?php echo htmlspecialchars($booking['movie_name']); ?></div>
            <span class="hall"><?php echo htmlspecialchars($booking['hall_name'] ?? 'Cinema Hall'); ?></span>

            <div class="info-grid">
                <div>
                    <span class="label">Date</span>
                    <span class="value"><?php echo date('d M Y', strtotime($booking['showtime'])); ?></span>
                </div>
                <div style="text-align: right;">
                    <span class="label">Time</span>
                    <span class="value"><?php echo date('h:i A', strtotime($booking['showtime'])); ?></span>
                </div>
                <div>
                    <span class="label">Guest</span>
                    <span class="value"><?php echo htmlspecialchars(substr($customerName, 0, 15)); ?></span>
                </div>
                <div style="text-align: right;">
                    <span class="label">Price</span>
                    <span class="value">RM <?php echo number_format($booking['total_price'], 2); ?></span>
                </div>
            </div>

            <div class="seats-box">
                <span class="label">SEATS</span>
                <div class="seats-val">
                    <?php 
                        $seats = $booking['seats'];
                        echo is_array($seats) ? implode(', ', $seats) : $seats; 
                    ?>
                </div>
            </div>
        </div>

        <div class="dashed-line"></div>

        <div class="ticket-footer">
            <div id="qrcode"></div>
            <div class="ref-id">ID: <?php echo substr((string)$bookingId, -8); ?></div>
        </div>
    </div>

    <div class="btn-group">
        <div>
            <button onclick="downloadPDF()" class="btn btn-download" style="width:100%">
                <i class="fas fa-download"></i> Download Ticket
            </button>
            <div class="auto-msg" id="autoMsg"><i class="fas fa-spinner fa-spin"></i> Auto-downloading in 2s...</div>
        </div>

        <a href="index.php" class="btn btn-home">
            <i class="fas fa-home"></i> Back to Home
        </a>
    </div>

    <script>
        window.onload = function() {
            // 1. Generate QR Code
            new QRCode(document.getElementById("qrcode"), {
                text: "<?php echo (string)$bookingId; ?>",
                width: 100,
                height: 100
            });

            // 2. Fire Confetti (Celebration)
            var duration = 2000;
            var end = Date.now() + duration;
            (function frame() {
                confetti({ particleCount: 5, angle: 60, spread: 55, origin: { x: 0 } });
                confetti({ particleCount: 5, angle: 120, spread: 55, origin: { x: 1 } });
                if (Date.now() < end) { requestAnimationFrame(frame); }
            }());

            // 3. AUTO DOWNLOAD LOGIC (Tunggu 2 saat, lepas tu download)
            setTimeout(function() {
                downloadPDF();
                document.getElementById('autoMsg').innerHTML = "Ticket downloaded!";
            }, 2000);
        };

        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            const element = document.getElementById('ticketVisual');

            html2canvas(element, {
                scale: 2, // Kualiti tinggi
                backgroundColor: "#1a1a1a", // Background color match ticket
                useCORS: true
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                
                // Center image in A4
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const imgWidth = 80; // Lebar gambar dalam PDF (mm)
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                const x = (pdfWidth - imgWidth) / 2;
                
                // Dark background PDF
                pdf.setFillColor(20, 20, 20);
                pdf.rect(0, 0, pdfWidth, 297, 'F');
                
                pdf.addImage(imgData, 'PNG', x, 30, imgWidth, imgHeight);
                pdf.save("MisaCinema-<?php echo substr((string)$bookingId, -4); ?>.pdf");
            });
        }
    </script>
</body>
</html>