<?php
session_start();
require 'vendor/autoload.php';

// MongoDB Setup
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$collection = $client->misacinema_db->shows; 
$senaraiMovie = $collection->find([]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>MISA CINEMA | Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #e50914; --dark-bg: #000; }
        * { box-sizing: border-box; scroll-behavior: smooth; }
        body { margin: 0; background: var(--dark-bg); color: #fff; font-family: 'Montserrat', sans-serif; overflow-x: hidden; }

        /* --- NAVBAR (Restored & Responsive) --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 6%; position: fixed; width: 100%; top: 0; z-index: 2000;
            transition: 0.5s; background: linear-gradient(to bottom, rgba(0,0,0,0.8), transparent);
        }
        .navbar.scrolled { background: rgba(0,0,0,0.95); padding: 12px 6%; backdrop-filter: blur(10px); }
        .logo-text { font-family: 'Bebas Neue', sans-serif; font-size: 2.2rem; color: #fff; text-decoration: none; letter-spacing: 2px; }
        .logo-text span { color: var(--primary); }
        
        .nav-links { display: flex; align-items: center; gap: 25px; }
        .nav-links a { color: #fff; text-decoration: none; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
        .nav-links a:hover { color: var(--primary); }

        /* Profile Button Style from Screenshot */
        .profile-btn {
            width: 40px; height: 40px; background: var(--primary); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 800; text-decoration: none; border: 2px solid transparent; transition: 0.3s;
        }
        .profile-btn:hover { border-color: white; transform: scale(1.1); }
        .logout-btn { background: var(--primary); padding: 8px 18px; border-radius: 4px; font-weight: 800; }

        /* --- HERO CAROUSEL --- */
        .hero-carousel { position: relative; width: 100%; height: 100vh; overflow: hidden; background: #000; }
        .carousel-item {
            position: absolute; width: 100%; height: 100%;
            opacity: 0; visibility: hidden; transition: opacity 1s ease-in-out;
            display: flex; align-items: center; padding: 0 8%;
        }
        .carousel-item.active { opacity: 1; visibility: visible; }
        .video-bg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1; filter: brightness(0.4); }
        .carousel-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(90deg, #000 10%, transparent 70%); z-index: 1; }

        .hero-content { position: relative; z-index: 10; max-width: 800px; }
        .hero-content h1 { font-family: 'Bebas Neue', sans-serif; font-size: 6rem; line-height: 0.9; margin: 0; }
        
        /* --- MOBILE RESPONSIVENESS --- */
        @media (max-width: 768px) {
            .navbar { padding: 15px 4%; }
            .nav-links { gap: 15px; }
            .nav-links a { font-size: 0.7rem; }
            .hero-content h1 { font-size: 3.5rem; }
            .hero-content p { font-size: 1rem; }
            .movie-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .poster-wrapper { height: 250px; }
            .hero-carousel { height: 80vh; }
        }

        /* --- MOVIE GRID & OVERLAY --- */
        .container { padding: 60px 8%; }
        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; }
        .movie-card { background: #111; border-radius: 12px; overflow: hidden; transition: 0.4s; border: 1px solid #222; position: relative; }
        .poster-wrapper { height: 380px; position: relative; overflow: hidden; }
        .poster-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        
        .overlay { 
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.7); display: flex; align-items: center; 
            justify-content: center; opacity: 0; transition: 0.3s; z-index: 10;
        }
        .movie-card:hover .overlay { opacity: 1; }
        .movie-card:hover img { transform: scale(1.1); }
        .btn-booking { background: var(--primary); color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 0.8rem; }
        .card-info { padding: 15px; text-align: center; }

        /* CONTROL BUTTONS */
        .carousel-btn { position: absolute; top: 50%; transform: translateY(-50%); z-index: 100; background: rgba(255,255,255,0.1); border: none; color: #fff; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; transition: 0.3s; }
        .carousel-btn:hover { background: var(--primary); }
        .prev { left: 20px; } .next { right: 20px; }
        .sound-toggle { position: absolute; bottom: 30px; right: 30px; z-index: 100; background: var(--primary); border: none; color: #fff; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <a href="home.php" class="logo-text">MISA<span>CINEMA</span></a>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="#now-showing">Now Showing</a>
            <a href="my_bookings.php">My Tickets</a>
            
            <?php if(isset($_SESSION['user'])): ?>
                <a href="profile.php" class="profile-btn">
                    <?php 
                        $initials = strtoupper(substr($_SESSION['user']['fullname'], 0, 2)); 
                        echo $initials;
                    ?>
                </a>
                <a href="logout.php" class="logout-btn">LOGOUT</a>
            <?php else: ?>
                <a href="login.php" class="logout-btn">SIGN IN</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero-carousel">
        <div class="carousel-item active">
            <video class="video-bg" loop playsinline muted preload="auto">
                <source src="assets/videos/Papa Zola .mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <h1>PAPA ZOLA: <br>THE MOVIE</h1>
                <p>Justice and fun under the sun!</p>
                <a href="#now-showing" class="btn-booking">Get Tickets</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" loop playsinline muted preload="auto">
                <source src="assets/videos/Avatar .mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <h1>AVATAR: <br>FIRE AND ASH</h1>
                <p>Experience the war of elements on the big screen.</p>
                <a href="#now-showing" class="btn-booking">Reserve Seats</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" loop playsinline muted preload="auto">
                <source src="assets/videos/The SpongeBob.mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <h1>SEARCH FOR <br>SQUAREPANTS</h1>
                <p>Join the crew on an underwater hunt!</p>
                <a href="#now-showing" class="btn-booking">Book Now</a>
            </div>
        </div>

        <button class="carousel-btn prev" onclick="changeSlide(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-btn next" onclick="changeSlide(1)"><i class="fas fa-chevron-right"></i></button>
        
        <button class="sound-toggle" onclick="toggleMute()">
            <i id="vol-icon" class="fas fa-volume-mute"></i> <span id="vol-text">UNMUTE</span>
        </button>
    </section>

    <div class="container" id="now-showing">
        <h2 style="font-family: 'Bebas Neue'; font-size: 3rem; margin-bottom: 30px;">Now Showing</h2>
        <div class="movie-grid">
            <?php foreach($senaraiMovie as $movie): ?>
                <div class="movie-card">
                    <div class="poster-wrapper">
                        <img src="assets/img/<?php echo $movie['image'] ?? 'default.jpg'; ?>">
                        <div class="overlay">
                            <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="btn-booking">BUY TICKET</a>
                        </div>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $movie['name']; ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        let isMuted = true;
        const slides = document.querySelectorAll('.carousel-item');
        const videos = document.querySelectorAll('.video-bg');

        function changeSlide(direction) {
            // Hide current
            videos[currentSlide].pause(); 
            // We removed "currentTime = 0" to stop the flickering gap
            slides[currentSlide].classList.remove('active');

            // Show next
            currentSlide = (currentSlide + direction + slides.length) % slides.length;
            slides[currentSlide].classList.add('active');
            
            videos[currentSlide].muted = isMuted;
            videos[currentSlide].play().catch(() => console.log("User interaction needed for audio"));
        }

        // Auto slide 12 seconds
        let autoPlay = setInterval(() => changeSlide(1), 12000);

        function toggleMute() {
            isMuted = !isMuted;
            const icon = document.getElementById('vol-icon');
            const text = document.getElementById('vol-text');
            
            videos[currentSlide].muted = isMuted;
            icon.className = isMuted ? "fas fa-volume-mute" : "fas fa-volume-up";
            text.innerText = isMuted ? "UNMUTE" : "SOUND ON";
            
            if(!isMuted) videos[currentSlide].play();
        }

        window.onscroll = function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 80) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        };

        // Start first video on load
        window.onload = () => { videos[0].play(); };
    </script>
</body>
</html>