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
    <title>Booking Confirmed - Misa Cinema</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        body {
            background-color: #111; font-family: 'Roboto', sans-serif; color: white;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            min-height: 100vh; margin: 0;
        }
        .ticket-wrapper {
            margin-bottom: 20px;
            padding: 20px; 
            background: #111;
        }
        .ticket-container {
            background: #fff; color: #000; width: 350px;
            border-radius: 10px; overflow: hidden;
            box-shadow: 0 0 50px rgba(229, 9, 20, 0.3);
            position: relative;
        }
        .ticket-header { background: #dc3545; padding: 20px; text-align: center; color: white; border-bottom: 2px dashed #fff; }
        .ticket-header h2 { margin: 0; font-size: 1.5rem; text-transform: uppercase; }
        .ticket-body { padding: 25px; }

        .info-group { margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .info-group:last-child { border: none; }
        .label { font-size: 0.75rem; color: #888; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 5px; }
        .value { font-size: 1.1rem; font-weight: bold; color: #000; display: block; }
        .seats-value { color: #dc3545; font-size: 1.2rem; }

        .qr-section { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 2px dashed #ccc; }
        
        /* Box QR Code */
        .qr-box {
            width: 120px; 
            height: 120px; 
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        /* Pastikan gambar QR fit dalam kotak */
        .qr-box img {
            max-width: 100%;
        }

        .ticket-id { font-size: 0.7rem; color: #aaa; margin-top: 10px; font-family: monospace; }
        .btn-group { width: 350px; display: flex; gap: 10px; margin-top: 10px;}
        .btn { flex: 1; padding: 15px; text-align: center; text-decoration: none; font-weight: bold; text-transform: uppercase; border-radius: 5px; cursor: pointer; border:none; font-family: inherit;}
        .btn-home { background: #222; color: white; }
        .btn-home:hover { background: #444; }
        .btn-download { background: #fff; color: #dc3545; font-weight: 800;}
        .btn-download:hover { background: #f0f0f0; }
        .status-badge { background: #28a745; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.7rem; display: inline-block; margin-top: 5px; }
    </style>
</head>
<body>

<div class="ticket-wrapper" id="ticketToPrint">
    <div class="ticket-container">
        <div class="ticket-header">
            <h2>Booking Confirmed</h2>
            <div class="status-badge"><i class="fas fa-check-circle"></i> PAID SUCCESS</div>
        </div>

        <div class="ticket-body">
            
            <div class="info-group">
                <span class="label">Customer Name</span>
                <span class="value" style="text-transform: capitalize;"><?php echo htmlspecialchars((string)$customerName); ?></span>
            </div>

            <div class="info-group">
                <span class="label">Movie</span>
                <span class="value">
                    <?php 
                        $mName = isset($booking['movie_name']) ? $booking['movie_name'] : '-';
                        echo is_string($mName) ? htmlspecialchars($mName) : 'Movie'; 
                    ?>
                </span>
            </div>

            <div class="info-group" style="display:flex; justify-content:space-between;">
                <div>
                    <span class="label">Date</span>
                    <span class="value"><?php echo date('d M Y', strtotime($booking['showtime'])); ?></span>
                </div>
                <div style="text-align:right;">
                    <span class="label">Time</span>
                    <span class="value"><?php echo date('h:i A', strtotime($booking['showtime'])); ?></span>
                </div>
            </div>

            <div class="info-group">
                <span class="label">Hall</span>
                <span class="value">
                    <?php 
                        $hName = isset($booking['hall_name']) ? $booking['hall_name'] : '-';
                        echo is_string($hName) ? htmlspecialchars($hName) : 'Cinema Hall';
                    ?>
                </span>
            </div>

            <div class="info-group">
                <span class="label">Seats</span>
                <span class="value seats-value">
                    <?php 
                        if (isset($booking['seats'])) {
                            if (is_array($booking['seats']) || is_object($booking['seats'])) {
                                echo htmlspecialchars(implode(", ", (array)$booking['seats']));
                            } else {
                                echo htmlspecialchars((string)$booking['seats']);
                            }
                        } else {
                            echo "-";
                        }
                    ?>
                </span>
            </div>

            <div class="info-group" style="text-align: center; background: #f8f9fa; padding: 10px; border-radius: 5px; border:none;">
                <span class="label">Total Amount</span>
                <span class="value" style="font-size:1.5rem; color:#222;">RM <?php echo number_format($booking['total_price'], 2); ?></span>
            </div>

            <div class="qr-section">
                <div id="qrcode" class="qr-box"></div>
                <div class="ticket-id">Ref: <?php echo $bookingId; ?></div>
                <p style="font-size:0.7rem; color:#888;">Show this QR at the cinema entrance.</p>
            </div>
        </div>
    </div>
</div>

<div class="btn-group">
    <a href="home.php" class="btn btn-home">Back to Home</a>
    <button onclick="downloadPDF()" class="btn btn-download"><i class="fas fa-file-pdf"></i> Download PDF</button>
</div>

<script>
    const { jsPDF } = window.jspdf;

    // 1. GENERATE QR CODE MASA PAGE LOAD
    window.onload = function() {
        var qrContainer = document.getElementById("qrcode");
        
        // Bersihkan kalau ada sisa
        qrContainer.innerHTML = "";

        // Guna library QRCode.js untuk lukis
        new QRCode(qrContainer, {
            text: "<?php echo (string)$bookingId; ?>",
            width: 120,
            height: 120,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // Auto download lepas 1.5 saat (bagi masa QR siap lukis)
        setTimeout(function() {
             downloadPDF(); // <--- DAH UNCOMMENT, SEKARANG DIA AKAN JALAN!
        }, 1500); 
    };

    function downloadPDF() {
        const ticketContainer = document.querySelector('.ticket-container');

        // Tak perlu risau pasal CORS sebab QR ni local punya
        html2canvas(ticketContainer, {
            scale: 2,
            backgroundColor: "#ffffff",
            logging: true
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const doc = new jsPDF('p', 'mm', 'a4'); 
            const imgWidth = 80; 
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            const xPos = (210 - imgWidth) / 2;
            const yPos = 20; 

            doc.addImage(imgData, 'PNG', xPos, yPos, imgWidth, imgHeight);
            doc.save('MisaCinema-Ticket-<?php echo $bookingId; ?>.pdf');
        }).catch(err => {
            console.error("Error generating PDF:", err);
            alert("Failed to generate PDF. Check console for details.");
        });
    }
</script>

</body>
</html>
