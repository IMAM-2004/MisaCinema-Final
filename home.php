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

        /* --- RESTORED NAVBAR --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 25px 6%; position: fixed; width: 100%; top: 0; z-index: 2000;
            transition: 0.5s;
        }
        .navbar.scrolled { background: rgba(0,0,0,0.95); padding: 15px 6%; backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo-text { font-family: 'Bebas Neue', sans-serif; font-size: 2.5rem; color: #fff; text-decoration: none; letter-spacing: 2px; }
        .logo-text span { color: var(--primary); }
        
        .nav-links { display: flex; align-items: center; gap: 30px; }
        .nav-links a { color: #fff; text-decoration: none; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
        .nav-links a:hover { color: var(--primary); }

        .user-section { display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 35px; height: 35px; border-radius: 50%; border: 2px solid var(--primary); }
        .btn-auth { background: var(--primary); padding: 10px 22px; border-radius: 5px; font-weight: 800; }

        /* --- HERO VIDEO CAROUSEL --- */
        .hero-carousel { position: relative; width: 100%; height: 100vh; background: #000; overflow: hidden; }
        .carousel-item {
            position: absolute; width: 100%; height: 100%;
            opacity: 0; visibility: hidden; transition: opacity 1.5s ease-in-out;
            display: flex; align-items: center; padding: 0 8%;
        }
        .carousel-item.active { opacity: 1; visibility: visible; }

        .video-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; z-index: -1; filter: brightness(0.5);
        }

        .carousel-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(90deg, #000 15%, transparent 60%),
                        linear-gradient(0deg, #000 5%, transparent 30%);
            z-index: 1;
        }

        .hero-content { position: relative; z-index: 10; max-width: 700px; transform: translateX(-50px); opacity: 0; transition: 1s 0.5s; }
        .carousel-item.active .hero-content { transform: translateX(0); opacity: 1; }
        .hero-content h1 { font-family: 'Bebas Neue', sans-serif; font-size: 6.5rem; line-height: 0.9; margin: 0; text-shadow: 2px 2px 20px rgba(0,0,0,0.5); }
        .hero-content p { font-size: 1.2rem; color: #ddd; margin: 25px 0 40px; }

        /* CAROUSEL CONTROLS */
        .carousel-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); 
            color: white; width: 60px; height: 60px; cursor: pointer; z-index: 100; 
            border-radius: 50%; transition: 0.3s; display: flex; align-items: center; justify-content: center;
        }
        .carousel-btn:hover { background: var(--primary); border-color: var(--primary); }
        .prev { left: 30px; }
        .next { right: 30px; }

        .sound-toggle {
            position: absolute; bottom: 40px; right: 40px; z-index: 100;
            background: rgba(229, 9, 20, 0.8); border: none; color: #fff;
            padding: 15px 25px; border-radius: 50px; cursor: pointer; font-weight: bold;
            display: flex; align-items: center; gap: 10px; transition: 0.3s;
        }

        /* --- RESTORED MOVIE GRID & BOOKING --- */
        .container { padding: 100px 8%; background: #000; }
        .section-title { font-family: 'Bebas Neue', sans-serif; font-size: 3.5rem; margin-bottom: 50px; position: relative; }
        .section-title::after { content: ''; position: absolute; bottom: -10px; left: 0; width: 60px; height: 4px; background: var(--primary); }
        
        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 40px; }
        .movie-card { background: #0a0a0a; border-radius: 15px; overflow: hidden; transition: 0.5s; border: 1px solid #1a1a1a; position: relative; }
        .movie-card:hover { transform: translateY(-15px); border-color: var(--primary); box-shadow: 0 10px 30px rgba(229,9,20,0.3); }
        
        .poster-wrapper { height: 420px; position: relative; overflow: hidden; }
        .poster-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        
        /* RESTORED HOVER OVERLAY */
        .overlay { 
            position: absolute; top:0; left:0; width:100%; height:100%; 
            background: rgba(0,0,0,0.6); display:flex; align-items:center; 
            justify-content:center; opacity:0; transition:0.3s; z-index: 5;
        }
        .movie-card:hover .overlay { opacity:1; }
        .movie-card:hover .poster-wrapper img { transform: scale(1.1); filter: brightness(0.5); }

        .btn-booking { background: var(--primary); color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 50px; font-weight: bold; text-transform: uppercase; }
        
        .card-info { padding: 20px; text-align: center; }
        .card-info h3 { margin: 0; font-size: 1.2rem; letter-spacing: 1px; }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <a href="home.php" class="logo-text">MISA<span>CINEMA</span></a>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="#now-showing">Now Showing</a>
            <a href="my_bookings.php">My Tickets</a> <?php if(isset($_SESSION['user'])): ?>
                <div class="user-section">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user']['fullname']); ?>&background=e50914&color=fff" class="user-avatar">
                    <a href="logout.php" class="btn-auth">Logout</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-auth">Sign In</a>
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
                <p style="color:var(--primary); font-weight:800; letter-spacing:5px;">FAMILY ADVENTURE</p>
                <h1>PAPA ZOLA: <br>THE MOVIE</h1>
                <p>Justice has a new name... and it's Papa Zola!</p>
                <a href="#now-showing" class="btn-booking">Browse Movies</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" loop playsinline muted preload="auto">
                <source src="assets/videos/Avatar .mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <p style="color:var(--primary); font-weight:800; letter-spacing:5px;">VISUAL MASTERPIECE</p>
                <h1>AVATAR: <br>FIRE AND ASH</h1>
                <p>Experience the war of elements in breathtaking IMAX.</p>
                <a href="#now-showing" class="btn-booking">Reserve Seats</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" loop playsinline muted preload="auto">
                <source src="assets/videos/The SpongeBob.mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <p style="color:var(--primary); font-weight:800; letter-spacing:5px;">UNDERWATER QUEST</p>
                <h1>SEARCH FOR <br>SQUAREPANTS</h1>
                <p>The ultimate hunt under the sea begins now!</p>
                <a href="#now-showing" class="btn-booking">Book Now</a>
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
                        
                        <div class="overlay">
                            <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="btn-booking">Buy Ticket</a>
                        </div>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $movie['name']; ?></h3>
                        <p style="color:#666; font-size:0.8rem; margin-top:5px;"><?php echo $movie['genre'] ?? 'Action / Adventure'; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        let isPaused = false;
        let isMuted = true;
        const slides = document.querySelectorAll('.carousel-item');
        const videos = document.querySelectorAll('.video-bg');

        function showSlide(index) {
            videos[currentSlide].pause();
            slides[currentSlide].classList.remove('active');
            currentSlide = index;
            slides[currentSlide].classList.add('active');
            videos[currentSlide].muted = isMuted;
            videos[currentSlide].play();
        }

        function manualChange(direction) {
            isPaused = true;
            let nextSlide = (currentSlide + direction + slides.length) % slides.length;
            showSlide(nextSlide);
            setTimeout(() => { isPaused = false; }, 15000);
        }

        setInterval(() => {
            if (!isPaused) showSlide((currentSlide + 1) % slides.length);
        }, 12000);

        function toggleTheaterSound() {
            const icon = document.getElementById('vol-icon');
            const text = document.getElementById('vol-text');
            isMuted = !isMuted;
            videos[currentSlide].muted = isMuted;
            icon.className = isMuted ? "fas fa-volume-mute" : "fas fa-volume-up";
            text.innerText = isMuted ? "UNMUTE THEATER" : "THEATER SOUND ON";
        }

        window.onscroll = function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 100) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        };

        // Start first video
        window.addEventListener('load', () => { videos[0].play(); });
    </script>
</body>
</html>