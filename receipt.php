<?php
session_start();
require 'vendor/autoload.php'; 

use MongoDB\BSON\ObjectId;

// 1. SETUP DATABASE
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$db = $client->misacinema_db;
$collection = $db->bookings;

// 2. CHECK ID
if (!isset($_GET['id'])) { echo "No ticket ID provided."; exit(); }

try {
    $bookingId = new ObjectId($_GET['id']);
    $booking = $collection->findOne(['_id' => $bookingId]);

    if (!$booking) { echo "Booking not found."; exit(); }

    // --- LOGIC NAMA CUSTOMER ---
    $customerName = 'Guest';
    if (isset($booking['customer_name'])) {
        $dbName = $booking['customer_name'];
        if (is_object($dbName) || is_array($dbName)) {
            $customerName = isset($dbName['fullname']) ? $dbName['fullname'] : (isset($dbName['username']) ? $dbName['username'] : 'Valued Customer');
        } else {
            $customerName = $dbName;
        }
    } 
    elseif (isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
        if (is_object($user) || is_array($user)) {
            $customerName = isset($user['fullname']) ? $user['fullname'] : 'Valued Customer';
        } else {
            $customerName = $user;
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage(); exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title>Booking Confirmed - Misa Cinema</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        :root {
            --primary: #e50914;
            --dark-bg: #050505;
            --ticket-bg: #151515;
            --text-main: #fff;
            --text-sub: #aaa;
        }

        body {
            background-color: var(--dark-bg); 
            background-image: radial-gradient(circle at 50% 0%, #220505 0%, #000000 70%);
            font-family: 'Montserrat', sans-serif; 
            color: white;
            display: flex; 
            flex-direction: column; 
            align-items: center;
            min-height: 100vh; 
            margin: 0;
            padding: 40px 20px;
            box-sizing: border-box;
        }

        /* --- TICKET CONTAINER --- */
        .ticket-wrapper {
            perspective: 1000px;
            margin-bottom: 30px;
            filter: drop-shadow(0 0 30px rgba(229, 9, 20, 0.15));
        }

        .ticket-visual {
            background: var(--ticket-bg);
            width: 340px;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* The "Notch" / Koyak Effect using Pseudo-elements */
        .ticket-visual::before, .ticket-visual::after {
            content: ''; position: absolute; top: 68%; 
            width: 24px; height: 24px; 
            background-color: var(--dark-bg); /* Must match body bg */
            border-radius: 50%;
            z-index: 10;
        }
        .ticket-visual::before { left: -12px; }
        .ticket-visual::after { right: -12px; }

        /* Dashed Line */
        .dashed-line {
            position: absolute; top: 68%; left: 10%; right: 10%;
            height: 24px;
            border-bottom: 2px dashed rgba(255,255,255,0.15);
            pointer-events: none;
        }

        /* --- HEADER SECTION --- */
        .ticket-header {
            background: linear-gradient(135deg, #e50914, #b2070f);
            padding: 25px;
            text-align: center;
            position: relative;
        }
        .brand { font-weight: 900; letter-spacing: 2px; text-transform: uppercase; font-size: 1.2rem; margin-bottom: 5px; }
        .status { font-size: 0.75rem; background: rgba(0,0,0,0.3); padding: 4px 12px; border-radius: 20px; display: inline-block; font-weight: 600; }

        /* --- BODY SECTION --- */
        .ticket-body { padding: 30px 25px 50px 25px; } /* Extra padding bottom for dashed line space */

        .movie-title { 
            font-size: 1.4rem; font-weight: 800; line-height: 1.2; 
            margin-bottom: 5px; text-transform: uppercase; 
            text-shadow: 0 0 10px rgba(255,255,255,0.3);
        }
        .hall-name { color: var(--primary); font-weight: 700; font-size: 0.9rem; margin-bottom: 20px; display: block; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .info-item label { display: block; font-size: 0.65rem; color: var(--text-sub); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px; }
        .info-item span { font-size: 0.95rem; font-weight: 600; color: white; }
        
        .seats-display {
            background: rgba(255,255,255,0.05);
            padding: 10px; border-radius: 8px; text-align: center; border: 1px solid rgba(255,255,255,0.1);
        }
        .seats-display label { font-size: 0.65rem; color: var(--text-sub); text-transform: uppercase; display: block; margin-bottom: 3px; }
        .seats-display span { color: var(--primary); font-weight: 800; font-size: 1.1rem; letter-spacing: 1px; }

        /* --- FOOTER (QR) SECTION --- */
        .ticket-footer {
            background: #eee; /* Light bg for QR readability */
            padding: 25px;
            text-align: center;
            color: #111;
        }
        .qr-wrapper {
            background: white; padding: 10px; border-radius: 10px;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .ref-id { font-family: monospace; font-size: 0.7rem; color: #555; margin-top: 10px; letter-spacing: 1px; }
        
        /* --- BUTTONS --- */
        .actions { 
            display: flex; gap: 15px; width: 100%; max-width: 340px; 
            flex-direction: column;
            animation: fadeIn 1s ease;
        }
        
        .btn {
            padding: 15px; border-radius: 50px; border: none; font-weight: 700;
            cursor: pointer; font-size: 1rem; text-decoration: none; text-align: center;
            transition: transform 0.2s, box-shadow 0.2s; font-family: 'Montserrat', sans-serif;
        }
        .btn-download {
            background: white; color: black;
        }
        .btn-download:hover { transform: translateY(-3px); box-shadow: 0 5px 20px rgba(255,255,255,0.2); }
        
        .btn-home {
            background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-home:hover { background: rgba(255,255,255,0.2); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="ticket-wrapper">
    <div class="ticket-visual" id="ticketToPrint">
        <div class="ticket-header">
            <div class="brand"><i class="fas fa-film"></i> Misa Cinema</div>
            <div class="status">PAYMENT SUCCESSFUL</div>
        </div>

        <div class="ticket-body">
            <div class="movie-title"><?php echo htmlspecialchars((string)($booking['movie_name'] ?? 'Movie')); ?></div>
            <span class="hall-name"><?php echo htmlspecialchars((string)($booking['hall_name'] ?? 'Hall')); ?></span>

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
                    <span style="text-transform: capitalize; font-size: 0.85rem;"><?php echo htmlspecialchars((string)$customerName); ?></span>
                </div>
                <div class="info-item" style="text-align: right;">
                    <label>Price</label>
                    <span>RM <?php echo number_format($booking['total_price'], 2); ?></span>
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
            <div style="font-size:0.6rem; margin-top:5px; color:#888;">Scan at the entrance</div>
        </div>
    </div>
</div>

<div class="actions">
    <button onclick="downloadPDF()" class="btn btn-download">
        <i class="fas fa-arrow-down"></i> Save Ticket
    </button>
    <a href="home.php" class="btn btn-home">
        Back to Home
    </a>
</div>

<script>
    const { jsPDF } = window.jspdf;

    window.onload = function() {
        var qrContainer = document.getElementById("qrcode");
        qrContainer.innerHTML = "";

        // Generate clean QR Code
        new QRCode(qrContainer, {
            text: "<?php echo (string)$bookingId; ?>",
            width: 100,
            height: 100,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // Optional: Auto download after 1.5s
        // setTimeout(downloadPDF, 1500);
    };

    function downloadPDF() {
        const ticketElement = document.getElementById('ticketToPrint');
        
        // Temporarily remove border-radius for cleaner edge capture if needed, 
        // but html2canvas usually handles it fine.
        
        html2canvas(ticketElement, {
            scale: 3, // High resolution
            useCORS: true,
            backgroundColor: "#151515", // Ensure dark background in PDF
            logging: false
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            
            // Calculate center position
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const imgWidth = 80; 
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            const xPos = (pdfWidth - imgWidth) / 2;
            const yPos = 30;

            pdf.setFillColor(20, 20, 20); // Dark grey background for PDF paper
            pdf.rect(0, 0, pdfWidth, 297, 'F');
            
            pdf.addImage(imgData, 'PNG', xPos, yPos, imgWidth, imgHeight);
            pdf.save('MisaCinema-<?php echo substr((string)$bookingId, -6); ?>.pdf');
        });
    }
</script>

<?php include 'footer.php'; ?>
</body>
</html> 