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
    <title>MISA CINEMA | Premium Experience</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #e50914; --gold: #FFD700; }
        * { box-sizing: border-box; scroll-behavior: smooth; }
        body { margin: 0; background: #000; color: #fff; font-family: 'Montserrat', sans-serif; overflow-x: hidden; }

        /* --- NAVIGATION --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 25px 6%; position: fixed; width: 100%; top: 0; z-index: 2000;
            transition: 0.5s;
        }
        .navbar.scrolled { background: rgba(0,0,0,0.9); padding: 15px 6%; backdrop-filter: blur(10px); }
        .logo-text { font-family: 'Bebas Neue', sans-serif; font-size: 2.5rem; color: #fff; text-decoration: none; }
        .logo-text span { color: var(--primary); }
        .nav-links { display: flex; align-items: center; gap: 30px; }
        .nav-links a { color: #fff; text-decoration: none; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; }

        /* --- HERO VIDEO CAROUSEL --- */
        .hero-carousel { position: relative; width: 100%; height: 100vh; background: #000; overflow: hidden; }
        .carousel-item {
            position: absolute; width: 100%; height: 100%;
            opacity: 0; visibility: hidden; transition: opacity 1.2s ease-in-out;
            display: flex; align-items: center; padding: 0 8%;
        }
        .carousel-item.active { opacity: 1; visibility: visible; }

        .video-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; z-index: -1; filter: brightness(0.4);
        }

        .carousel-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(90deg, #000 15%, transparent 60%),
                        linear-gradient(0deg, #000 5%, transparent 30%);
            z-index: 1;
        }

        .hero-content { position: relative; z-index: 10; max-width: 700px; transform: translateX(-50px); opacity: 0; transition: 1s 0.5s; }
        .carousel-item.active .hero-content { transform: translateX(0); opacity: 1; }
        .hero-content h1 { font-family: 'Bebas Neue', sans-serif; font-size: 6.5rem; line-height: 0.9; margin: 0; }
        .hero-content p { font-size: 1.2rem; color: #ddd; margin: 25px 0 40px; font-weight: 300; }

        /* CAROUSEL CONTROLS */
        .carousel-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); 
            color: white; width: 60px; height: 60px; cursor: pointer; z-index: 100; 
            border-radius: 50%; transition: 0.3s; display: flex; align-items: center; justify-content: center;
        }
        .carousel-btn:hover { background: var(--primary); border-color: var(--primary); transform: translateY(-50%) scale(1.1); }
        .prev { left: 30px; }
        .next { right: 30px; }

        /* THEATER SOUND TOGGLE */
        .sound-toggle {
            position: absolute; bottom: 40px; right: 40px; z-index: 100;
            background: rgba(229, 9, 20, 0.8); border: none; color: #fff;
            padding: 15px 25px; border-radius: 50px; cursor: pointer; font-weight: bold;
            display: flex; align-items: center; gap: 10px; transition: 0.3s;
        }
        .sound-toggle:hover { background: #fff; color: #000; transform: scale(1.05); }

        /* --- MOVIE GRID --- */
        .container { padding: 100px 8%; background: #000; }
        .section-title { font-family: 'Bebas Neue', sans-serif; font-size: 3.5rem; margin-bottom: 50px; }
        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 40px; }
        .movie-card { background: #0a0a0a; border-radius: 15px; overflow: hidden; transition: 0.5s; border: 1px solid #1a1a1a; }
        .movie-card:hover { transform: translateY(-15px); border-color: var(--primary); }
        
        .poster-wrapper { height: 420px; position: relative; }
        .poster-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .btn-ticket { background: var(--primary); color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 50px; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <a href="home.php" class="logo-text">MISA<span>CINEMA</span></a>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="#now-showing">Movies</a>
            <?php if(isset($_SESSION['user'])): ?>
                <a href="logout.php" style="background:var(--primary); padding:8px 20px; border-radius:5px; text-decoration:none; color:white;">Logout</a>
            <?php else: ?>
                <a href="login.php" style="background:var(--primary); padding:8px 20px; border-radius:5px; text-decoration:none; color:white;">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero-carousel">
        <div class="carousel-item active" data-index="0">
            <video class="video-bg" autoplay muted loop playsinline>
                <source src="assets/videos/Papa Zola .mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <p style="color:var(--primary); font-weight:800; letter-spacing:5px;">FAMILY ADVENTURE</p>
                <h1>PAPA ZOLA: <br>THE MOVIE</h1>
                <p>Join the justice-loving hero in his most hilarious adventure yet!</p>
                <a href="#now-showing" class="btn-ticket" style="padding:18px 45px;">Get Tickets Now</a>
            </div>
        </div>

        <div class="carousel-item" data-index="1">
            <video class="video-bg" autoplay muted loop playsinline>
                <source src="assets/videos/Avatar .mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <p style="color:var(--primary); font-weight:800; letter-spacing:5px;">VISUAL MASTERPIECE</p>
                <h1>AVATAR: <br>FIRE AND ASH</h1>
                <p>Return to Pandora. Experience the war of elements in breathtaking IMAX.</p>
                <a href="#now-showing" class="btn-ticket" style="padding:18px 45px;">Reserve Seats</a>
            </div>
        </div>

        <div class="carousel-item" data-index="2">
            <video class="video-bg" autoplay muted loop playsinline>
                <source src="assets/videos/The SpongeBob.mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <p style="color:var(--primary); font-weight:800; letter-spacing:5px;">UNDERWATER QUEST</p>
                <h1>SEARCH FOR <br>SQUAREPANTS</h1>
                <p>The hunt is on! Dive into the newest SpongeBob cinematic journey.</p>
                <a href="#now-showing" class="btn-ticket" style="padding:18px 45px;">Book Now</a>
            </div>
        </div>

        <button class="carousel-btn prev" onclick="manualChange(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-btn next" onclick="manualChange(1)"><i class="fas fa-chevron-right"></i></button>
        
        <button class="sound-toggle" onclick="toggleTheaterSound()">
            <i id="vol-icon" class="fas fa-volume-mute"></i>
            <span id="vol-text">UNMUTE THEATER</span>
        </button>
    </section>

    <div class="container" id="now-showing">
        <h2 class="section-title">Now Showing</h2>
        <div class="movie-grid">
            <?php foreach($senaraiMovie as $movie): ?>
                <div class="movie-card">
                    <div class="poster-wrapper">
                        <img src="assets/img/<?php echo $movie['image'] ?? 'default.jpg'; ?>">
                    </div>
                    <div class="card-info">
                        <h3 style="margin:0;"><?php echo $movie['name']; ?></h3>
                        <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="btn-ticket" style="display:inline-block; margin-top:15px; font-size:0.8rem;">Buy Ticket</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        let isPaused = false;
        const slides = document.querySelectorAll('.carousel-item');
        const videos = document.querySelectorAll('.video-bg');

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            slides[index].classList.add('active');
            currentSlide = index;
        }

        function manualChange(direction) {
            isPaused = true; // Stop auto-sliding when user clicks
            let nextSlide = (currentSlide + direction + slides.length) % slides.length;
            showSlide(nextSlide);
            
            // Resume auto-slide after 15 seconds of inactivity
            setTimeout(() => { isPaused = false; }, 15000);
        }

        // Auto-slide every 10 seconds
        setInterval(() => {
            if (!isPaused) {
                let nextSlide = (currentSlide + 1) % slides.length;
                showSlide(nextSlide);
            }
        }, 10000);

        function toggleTheaterSound() {
            const icon = document.getElementById('vol-icon');
            const text = document.getElementById('vol-text');
            
            videos.forEach(v => {
                v.muted = !v.muted;
            });

            if (videos[0].muted) {
                icon.className = "fas fa-volume-mute";
                text.innerText = "UNMUTE THEATER";
            } else {
                icon.className = "fas fa-volume-up";
                text.innerText = "THEATER SOUND ON";
            }
        }

        window.onscroll = function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 100) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        };
    </script>
</body>
</html>