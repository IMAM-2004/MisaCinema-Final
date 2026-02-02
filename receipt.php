<?php
session_start();
require 'vendor/autoload.php'; 

use MongoDB\BSON\ObjectId;

// =========================================================
// 1. SETUP DATABASE
// =========================================================
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$db = $client->misacinema_db;
$collection = $db->bookings;

// =========================================================
// 2. DATA RETRIEVAL & VALIDATION
// =========================================================
if (!isset($_GET['id'])) { 
    // Fallback UI jika ID tiada
    echo "<div style='color:white; text-align:center; padding:50px; background:#111; font-family:sans-serif;'>No ticket ID provided. <br><br> <a href='home.php' style='color:red;'>Return Home</a></div>"; 
    exit(); 
}

try {
    $bookingId = new ObjectId($_GET['id']);
    $booking = $collection->findOne(['_id' => $bookingId]);

    if (!$booking) { 
        echo "<div style='color:white; text-align:center; padding:50px; background:#111; font-family:sans-serif;'>Booking not found in database. <br><br> <a href='home.php' style='color:red;'>Return Home</a></div>"; 
        exit(); 
    }

    // --- Logic Nama Customer (Handle object/array/string) ---
    $customerName = 'Valued Guest';
    if (isset($booking['customer_name'])) {
        $dbName = $booking['customer_name'];
        if (is_array($dbName) || is_object($dbName)) {
            $customerName = isset($dbName['fullname']) ? $dbName['fullname'] : (isset($dbName['username']) ? $dbName['username'] : 'Guest');
        } else {
            $customerName = $dbName;
        }
    } elseif (isset($_SESSION['user'])) {
        $customerName = $_SESSION['user']['fullname'] ?? $_SESSION['user']['username'] ?? 'Guest';
    }

} catch (Exception $e) {
    echo "System Error: " . $e->getMessage(); exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title>Ticket Confirmed - Misa Cinema</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        :root {
            --primary: #e50914;
            --dark-bg: #050505;
            --ticket-bg: #151515;
            --text-main: #fff;
            --text-sub: #aaa;
            --glass: rgba(255, 255, 255, 0.05);
        }

        body {
            background-color: var(--dark-bg); 
            background-image: radial-gradient(circle at 50% 0%, #330505 0%, #000000 70%);
            font-family: 'Montserrat', sans-serif; 
            color: white;
            display: flex; 
            flex-direction: column; 
            align-items: center;
            min-height: 100vh; 
            margin: 0;
            padding: 40px 20px;
            box-sizing: border-box;
            overflow-x: hidden;
        }

        /* --- ANIMATIONS --- */
        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.9) translateY(20px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        
        @keyframes shine {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* --- TICKET WRAPPER --- */
        .ticket-wrapper {
            perspective: 1000px;
            margin-bottom: 30px;
            animation: popIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            filter: drop-shadow(0 0 40px rgba(229, 9, 20, 0.2));
            z-index: 2;
        }

        .ticket-visual {
            background: var(--ticket-bg);
            width: 100%;
            max-width: 360px;
            min-width: 320px;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* Holographic Shine Effect */
        .ticket-visual::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent);
            transform: skewX(-25deg);
            animation: shine 6s infinite;
            pointer-events: none;
        }

        /* The "Notch" / Koyak Effect */
        .notch-left, .notch-right {
            position: absolute; top: 70%;
            width: 24px; height: 24px;
            background-color: var(--dark-bg); /* Matches body background */
            border-radius: 50%;
            z-index: 10;
        }
        .notch-left { left: -12px; }
        .notch-right { right: -12px; }

        /* Dashed Line */
        .dashed-line {
            position: absolute; top: 70%; left: 10%; right: 10%;
            height: 24px;
            border-bottom: 2px dashed rgba(255,255,255,0.2);
            pointer-events: none;
        }

        /* --- HEADER --- */
        .ticket-header {
            background: linear-gradient(135deg, #e50914, #990000);
            padding: 25px;
            text-align: center;
            position: relative;
        }
        .brand { font-weight: 900; letter-spacing: 2px; text-transform: uppercase; font-size: 1.2rem; margin-bottom: 5px; }
        .status { 
            font-size: 0.7rem; background: rgba(0,0,0,0.3); 
            padding: 5px 15px; border-radius: 20px; 
            display: inline-flex; align-items: center; gap: 5px; 
            font-weight: 600; text-transform: uppercase; letter-spacing: 1px;
        }

        /* --- BODY --- */
        .ticket-body { padding: 30px 25px 50px 25px; }

        .movie-title { 
            font-size: 1.5rem; font-weight: 800; line-height: 1.1; 
            margin-bottom: 8px; text-transform: uppercase; 
            text-shadow: 0 0 15px rgba(229, 9, 20, 0.4);
        }
        .hall-name { color: var(--primary); font-weight: 700; font-size: 0.9rem; margin-bottom: 25px; display: block; letter-spacing: 1px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .info-item label { display: block; font-size: 0.65rem; color: var(--text-sub); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .info-item span { font-size: 1rem; font-weight: 600; color: white; }
        
        .seats-display {
            background: var(--glass);
            padding: 15px; border-radius: 12px; text-align: center; border: 1px solid rgba(255,255,255,0.1);
        }
        .seats-display label { font-size: 0.65rem; color: var(--text-sub); text-transform: uppercase; display: block; margin-bottom: 5px; }
        .seats-display span { color: var(--primary); font-weight: 800; font-size: 1.2rem; letter-spacing: 1px; }

        /* --- FOOTER (QR) --- */
        .ticket-footer {
            background: #f0f0f0; 
            padding: 25px;
            text-align: center;
            color: #111;
        }
        .qr-wrapper {
            background: white; padding: 10px; border-radius: 12px;
            display: inline-block;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .ref-id { font-family: 'Courier New', monospace; font-size: 0.75rem; color: #555; margin-top: 12px; letter-spacing: 1px; font-weight: bold; }
        
        /* --- ACTION BUTTONS --- */
        .actions { 
            display: flex; gap: 15px; width: 100%; max-width: 360px; 
            flex-direction: column;
            animation: fadeIn 1s ease 0.5s backwards;
            z-index: 2;
        }
        
        .btn {
            padding: 18px; border-radius: 50px; border: none; font-weight: 800;
            cursor: pointer; font-size: 1rem; text-decoration: none; text-align: center;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase; letter-spacing: 1px;
            display: flex; justify-content: center; align-items: center; gap: 10px;
        }
        
        .btn-download {
            background: white; color: black;
            box-shadow: 0 5px 20px rgba(255,255,255,0.1);
        }
        .btn-download:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(255,255,255,0.3); }
        .btn-download:active { transform: scale(0.98); }
        
        .btn-home {
            background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-home:hover { background: rgba(255,255,255,0.2); border-color: white; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* PDF Generation Loading State */
        .btn-download.loading {
            opacity: 0.7; cursor: wait; pointer-events: none;
        }
    </style>
</head>
<body>

<div class="ticket-wrapper">
    <div class="ticket-visual" id="ticketToPrint">
        <div class="notch-left"></div>
        <div class="notch-right"></div>

        <div class="ticket-header">
            <div class="brand"><i class="fas fa-film"></i> Misa Cinema</div>
            <div class="status"><i class="fas fa-check-circle"></i> Paid & Confirmed</div>
        </div>

        <div class="ticket-body">
            <div class="movie-title"><?php echo htmlspecialchars((string)($booking['movie_name'] ?? 'Movie Title')); ?></div>
            <span class="hall-name"><?php echo htmlspecialchars((string)($booking['hall_name'] ?? 'Cinema Hall')); ?></span>

            <div class="info-grid">
                <div class="info-item">
                    <label>Date</label>
                    <span><?php echo date('d M Y', strtotime($booking['showtime'])); ?></span>
                </div>
                <div class="info-item" style="text-align: right;">
                    <label>Time</label>
                    <span><?php echo date('h:i A', strtotime($booking['showtime'])); ?></span>
                </div>
                <div class="info-item">
                    <label>Customer</label>
                    <span style="text-transform: capitalize; font-size: 0.85rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:block;">
                        <?php echo htmlspecialchars((string)$customerName); ?>
                    </span>
                </div>
                <div class="info-item" style="text-align: right;">
                    <label>Total Paid</label>
                    <span style="color:var(--primary);">RM <?php echo number_format($booking['total_price'], 2); ?></span>
                </div>
            </div>

            <div class="seats-display">
                <label>Assigned Seats</label>
                <span>
                    <?php 
                        if (isset($booking['seats'])) {
                            echo is_array($booking['seats']) ? implode(", ", (array)$booking['seats']) : htmlspecialchars((string)$booking['seats']);
                        } else { echo "-"; }
                    ?>
                </span>
            </div>
        </div>

        <div class="dashed-line"></div>

        <div class="ticket-footer">
            <div class="qr-wrapper">
                <div id="qrcode"></div>
            </div>
            <div class="ref-id">ID: <?php echo substr((string)$bookingId, -8); ?></div>
            <div style="font-size:0.6rem; margin-top:5px; color:#888;">Present this QR code at the entrance</div>
        </div>
    </div>
</div>

<div class="actions">
    <button id="btnDownload" onclick="downloadPDF()" class="btn btn-download">
        <i class="fas fa-arrow-down"></i> Save Ticket
    </button>
    
    <a href="home.php" class="btn btn-home">
        <i class="fas fa-home"></i> Back to Home
    </a>
</div>

<script>
    const { jsPDF } = window.jspdf;

    window.onload = function() {
        // 1. Generate QR Code
        var qrContainer = document.getElementById("qrcode");
        qrContainer.innerHTML = "";
        
        new QRCode(qrContainer, {
            text: "<?php echo (string)$bookingId; ?>",
            width: 110,
            height: 110,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // 2. Trigger Confetti Celebration (Kesan visual yang gempak)
        fireConfetti();
        
        // 3. AUTO DOWNLOAD (Jika anda mahu, uncomment baris di bawah)
        // setTimeout(downloadPDF, 1500); 
    };

    function fireConfetti() {
        var duration = 3 * 1000;
        var animationEnd = Date.now() + duration;
        var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

        function randomInRange(min, max) {
            return Math.random() * (max - min) + min;
        }

        var interval = setInterval(function() {
            var timeLeft = animationEnd - Date.now();
            if (timeLeft <= 0) { return clearInterval(interval); }
            var particleCount = 50 * (timeLeft / duration);
            
            // Tembak dari kiri dan kanan
            confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
            confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
        }, 250);
    }

    function downloadPDF() {
        const btn = document.getElementById('btnDownload');
        const originalText = btn.innerHTML;
        const ticketElement = document.getElementById('ticketToPrint');
        
        // UI Feedback: Beritahu user sedang generate
        btn.classList.add('loading');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';

        html2canvas(ticketElement, {
            scale: 3, 
            useCORS: true,
            backgroundColor: "#151515", 
            logging: false
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const imgWidth = 85; 
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            const xPos = (pdfWidth - imgWidth) / 2;
            const yPos = 30;

            // Set background gelap untuk PDF
            pdf.setFillColor(20, 20, 20); 
            pdf.rect(0, 0, pdfWidth, 297, 'F');
            
            // Tambah tajuk dalam PDF
            pdf.setFontSize(14);
            pdf.setTextColor(255, 255, 255);
            pdf.text("Your Misa Cinema E-Ticket", pdfWidth/2, 20, { align: 'center' });

            pdf.addImage(imgData, 'PNG', xPos, yPos, imgWidth, imgHeight);
            pdf.save('MisaCinema-Ticket-<?php echo substr((string)$bookingId, -6); ?>.pdf');

            // Reset butang
            btn.classList.remove('loading');
            btn.innerHTML = originalText;
        }).catch(err => {
            console.error(err);
            alert("Error generating ticket. Please take a screenshot instead.");
            btn.classList.remove('loading');
            btn.innerHTML = originalText;
        });
    }
</script>

<?php include 'footer.php'; ?>
</body>
</html>