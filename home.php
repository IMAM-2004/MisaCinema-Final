<?php
session_start();
require 'vendor/autoload.php';

$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$collection = $client->misacinema_db->shows; 
$senaraiMovie = $collection->find([]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>MISA CINEMA | Premium Experience</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #e50914; --dark: #000; }
        * { box-sizing: border-box; scroll-behavior: smooth; }
        body { margin: 0; background: #000; color: #fff; font-family: 'Montserrat', sans-serif; overflow-x: hidden; }

        /* --- NAVBAR & PROFILE FIX --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 6%; position: fixed; width: 100%; top: 0; z-index: 2000;
            transition: 0.5s; background: linear-gradient(to bottom, rgba(0,0,0,0.9), transparent);
        }
        .navbar.scrolled { background: rgba(0,0,0,0.95); padding: 12px 6%; backdrop-filter: blur(15px); }
        .logo-text { font-family: 'Bebas Neue', sans-serif; font-size: 2.2rem; color: #fff; text-decoration: none; }
        .logo-text span { color: var(--primary); }
        
        .nav-links { display: flex; align-items: center; gap: 20px; }
        .nav-links a { color: #fff; text-decoration: none; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; }

        /* Profile Circle (IM) */
        .profile-circle {
            width: 40px; height: 40px; background: var(--primary); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 800; text-decoration: none; font-size: 0.9rem; border: 2px solid transparent;
        }
        .profile-circle:hover { border-color: white; transform: scale(1.1); }
        .btn-logout { background: var(--primary); padding: 8px 15px; border-radius: 5px; font-weight: 800; text-decoration: none; color: white; font-size: 0.75rem; }

        /* --- HERO CAROUSEL FIX (NO MORE DARK FLASH) --- */
        .hero-carousel { position: relative; width: 100%; height: 100vh; background: #000; overflow: hidden; }
        .carousel-item {
            position: absolute; width: 100%; height: 100%;
            opacity: 0; visibility: hidden; 
            transition: opacity 1.2s ease-in-out; /* Smooth cross-fade */
            display: flex; align-items: center; padding: 0 8%;
        }
        .carousel-item.active { opacity: 1; visibility: visible; }

        .video-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; z-index: -2; filter: brightness(0.5);
            background: #000; /* Pre-fill with black so it doesn't flicker white */
        }

        .carousel-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(90deg, #000 10%, transparent 70%),
                        linear-gradient(0deg, #000 5%, transparent 40%);
            z-index: -1;
        }

        .hero-content { position: relative; z-index: 10; max-width: 800px; }
        .hero-content h1 { font-family: 'Bebas Neue', sans-serif; font-size: 5.5rem; line-height: 0.9; margin: 0; }
        .hero-content p { font-size: 1.1rem; color: #ccc; margin: 20px 0 30px; }

        /* --- MOBILE VIEW OPTIMIZATION --- */
        @media (max-width: 768px) {
            .navbar { padding: 15px 5%; }
            .logo-text { font-size: 1.8rem; }
            .nav-links { gap: 10px; }
            .nav-links a:not(.profile-circle):not(.btn-logout) { display: none; } /* Hide text links on mobile to save space */
            .hero-content h1 { font-size: 3rem; }
            .hero-content p { font-size: 0.9rem; }
            .hero-carousel { height: 70vh; }
            .movie-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .poster-wrapper { height: 260px; }
        }

        /* --- MOVIE GRID --- */
        .container { padding: 60px 8%; }
        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 30px; }
        .movie-card { background: #111; border-radius: 12px; overflow: hidden; transition: 0.4s; border: 1px solid #222; position: relative; }
        .poster-wrapper { height: 360px; position: relative; }
        .poster-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        
        .overlay { 
            position: absolute; top:0; left:0; width:100%; height:100%; 
            background: rgba(0,0,0,0.7); display:flex; align-items:center; 
            justify-content:center; opacity:0; transition:0.3s;
        }
        .movie-card:hover .overlay { opacity:1; }
        .movie-card:hover img { transform: scale(1.1); }
        .btn-booking { background: var(--primary); color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 50px; font-weight: 700; font-size: 0.8rem; }
        .card-info { padding: 15px; text-align: center; }

        /* CONTROLS */
        .carousel-btn { position: absolute; top: 50%; transform: translateY(-50%); z-index: 100; background: rgba(255,255,255,0.1); border: none; color: #fff; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; transition: 0.3s; }
        .carousel-btn:hover { background: var(--primary); }
        .prev { left: 20px; } .next { right: 20px; }
        .sound-toggle { position: absolute; bottom: 30px; right: 30px; z-index: 100; background: var(--primary); border: none; color: #fff; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-weight: bold; font-size: 0.8rem; }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <a href="home.php" class="logo-text">MISA<span>CINEMA</span></a>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="#now-showing">Movies</a>
            <?php if(isset($_SESSION['user'])): ?>
                <a href="profile.php" class="profile-circle">
                    <?php echo strtoupper(substr($_SESSION['user']['fullname'], 0, 2)); ?>
                </a>
                <a href="logout.php" class="btn-logout">LOGOUT</a>
            <?php else: ?>
                <a href="login.php" class="btn-logout">SIGN IN</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero-carousel">
        <div class="carousel-item active">
            <video class="video-bg" autoplay loop playsinline muted preload="auto">
                <source src="assets/videos/Papa Zola .mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <h1 style="color:var(--primary)">PAPA ZOLA: <br>THE MOVIE</h1>
                <p>Kebenaran akan muncul! Sertai wira paling berani di galaksi.</p>
                <a href="#now-showing" class="btn-booking" style="padding:15px 35px">Book Tickets</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" autoplay loop playsinline muted preload="auto">
                <source src="assets/videos/Avatar .mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <h1>AVATAR: <br>FIRE AND ASH</h1>
                <p>Kembali ke Pandora. Rasai peperangan elemen yang menggegarkan.</p>
                <a href="#now-showing" class="btn-booking" style="padding:15px 35px">Reserve Seats</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" autoplay loop playsinline muted preload="auto">
                <source src="assets/videos/The SpongeBob.mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <h1>SEARCH FOR <br>SQUAREPANTS</h1>
                <p>Pencarian bermula! Terokai dasar laut bersama SpongeBob & Patrick.</p>
                <a href="#now-showing" class="btn-booking" style="padding:15px 35px">Get Now</a>
            </div>
        </div>

        <button class="carousel-btn prev" onclick="changeSlide(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-btn next" onclick="changeSlide(1)"><i class="fas fa-chevron-right"></i></button>
        
        <button class="sound-toggle" onclick="toggleMute()">
            <i id="vol-icon" class="fas fa-volume-mute"></i> <span id="vol-text">UNMUTE</span>
        </button>
    </section>

    <div class="container" id="now-showing">
        <h2 style="font-family:'Bebas Neue'; font-size:3rem; margin-bottom:30px">Now Showing</h2>
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
                        <h3 style="margin:0; font-size:1.1rem"><?php echo $movie['name']; ?></h3>
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

        // Logic Penukaran Slide Tanpa "Gelap"
        function changeSlide(direction) {
            const nextSlide = (currentSlide + direction + slides.length) % slides.length;
            
            // 1. Play video seterusnya di background dulu
            videos[nextSlide].play();
            videos[nextSlide].muted = isMuted;

            // 2. Tukar class active (CSS transition 1.2s cross-fade)
            slides[currentSlide].classList.remove('active');
            slides[nextSlide].classList.add('active');

            // 3. Pause video lama selepas transition bermula sedikit
            setTimeout(() => {
                videos[currentSlide].pause();
                currentSlide = nextSlide;
            }, 500);
        }

        // Auto slide setiap 12 saat
        let slideTimer = setInterval(() => changeSlide(1), 12000);

        function toggleMute() {
            isMuted = !isMuted;
            const icon = document.getElementById('vol-icon');
            const text = document.getElementById('vol-text');
            
            videos[currentSlide].muted = isMuted;
            icon.className = isMuted ? "fas fa-volume-mute" : "fas fa-volume-up";
            text.innerText = isMuted ? "UNMUTE" : "SOUND ON";
            
            // Browser policy: Video mesti play selepas unmute
            if(!isMuted) videos[currentSlide].play();
        }

        window.onscroll = function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 80) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        };

        // Mulakan video pertama
        window.onload = () => { videos[0].play(); };
    </script>
</body>
</html>