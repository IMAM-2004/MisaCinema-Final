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
    <title>MISA CINEMA</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #e50914; }
        body { margin: 0; background: #000; color: #fff; font-family: 'Montserrat', sans-serif; overflow-x: hidden; }

        /* --- NAVBAR FIX --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px 6%; position: fixed; width: 100%; top: 0; z-index: 2000;
            background: linear-gradient(to bottom, rgba(0,0,0,0.9), transparent);
            transition: 0.4s;
        }
        .navbar.scrolled { background: rgba(0,0,0,0.95); padding: 10px 6%; backdrop-filter: blur(10px); }
        .logo-text { font-family: 'Bebas Neue', sans-serif; font-size: 2.2rem; color: #fff; text-decoration: none; }
        .logo-text span { color: var(--primary); }
        
        .nav-links { display: flex; align-items: center; gap: 20px; }
        .nav-links a { color: #fff; text-decoration: none; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; }

        /* PROFILE SECTION (IMAGE SUPPORT) */
        .profile-wrapper { display: flex; align-items: center; gap: 10px; }
        .profile-box {
            width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--primary);
            overflow: hidden; display: flex; align-items: center; justify-content: center;
            background: var(--primary); text-decoration: none; color: white; font-weight: 800;
        }
        .profile-box img { width: 100%; height: 100%; object-fit: cover; }

        /* --- HERO CAROUSEL (ANTI-FLICKER) --- */
        .hero-carousel { position: relative; width: 100%; height: 100vh; background: #000; overflow: hidden; }
        .carousel-item {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; visibility: hidden;
            transition: opacity 1.2s ease-in-out; 
            display: flex; align-items: center; padding: 0 8%;
        }
        .carousel-item.active { opacity: 1; visibility: visible; }

        .video-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; filter: brightness(0.4); z-index: -1;
            background: #000;
        }

        .hero-content { position: relative; z-index: 10; max-width: 700px; }
        .hero-content h1 { font-family: 'Bebas Neue', sans-serif; font-size: 5.5rem; margin: 0; line-height: 0.9; }

        /* --- MOBILE OPTIMIZATION --- */
        @media (max-width: 768px) {
            .navbar { padding: 10px 5%; }
            .nav-links a:not(.profile-link):not(.logout-txt) { display: none; }
            .hero-content h1 { font-size: 3.2rem; }
            .hero-carousel { height: 70vh; }
            .movie-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 15px !important; }
            .poster-box { height: 260px !important; }
        }

        /* --- MOVIE GRID --- */
        .container { padding: 50px 8%; }
        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 25px; }
        .movie-card { background: #111; border-radius: 10px; overflow: hidden; position: relative; transition: 0.3s; }
        .poster-box { height: 340px; position: relative; overflow: hidden; }
        .poster-box img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        
        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); display: flex; align-items: center;
            justify-content: center; opacity: 0; transition: 0.3s;
        }
        .movie-card:hover .overlay { opacity: 1; }
        .movie-card:hover img { transform: scale(1.1); }
        .btn-buy { background: var(--primary); color: #fff; padding: 10px 20px; border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 0.8rem; }

        /* AUDIO CONTROL BUTTON */
        .sound-btn {
            position: absolute; bottom: 30px; right: 30px; z-index: 1000;
            background: rgba(229, 9, 20, 0.8); border: none; color: white;
            padding: 12px 20px; border-radius: 30px; cursor: pointer;
            font-weight: bold; display: flex; align-items: center; gap: 10px;
        }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <a href="home.php" class="logo-text">MISA<span>CINEMA</span></a>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="#now-showing">Movies</a>
            <?php if(isset($_SESSION['user'])): ?>
                <div class="profile-wrapper">
                    <a href="profile.php" class="profile-box profile-link">
                        <?php if(!empty($_SESSION['user']['image'])): ?>
                            <img src="assets/img/profile/<?php echo $_SESSION['user']['image']; ?>">
                        <?php else: ?>
                            <?php echo strtoupper(substr($_SESSION['user']['fullname'], 0, 2)); ?>
                        <?php endif; ?>
                    </a>
                    <a href="logout.php" class="logout-txt" style="color:var(--primary); font-weight:bold; font-size:0.8rem;">LOGOUT</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-buy">SIGN IN</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero-carousel">
        <button class="sound-btn" onclick="toggleMute()">
            <i id="vol-icon" class="fas fa-volume-mute"></i> <span id="vol-text">UNMUTE SOUND</span>
        </button>

        <div class="carousel-item active">
            <video class="video-bg" loop playsinline muted preload="auto">
                <source src="assets/videos/Papa Zola .mp4" type="video/mp4">
            </video>
            <div class="hero-content">
                <h1 style="color:var(--primary)">PAPA ZOLA <br>THE MOVIE</h1>
                <p>Justice is back! Experience the legend.</p>
                <a href="#now-showing" class="btn-buy">GET TICKETS</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" loop playsinline muted preload="auto">
                <source src="assets/videos/Avatar .mp4" type="video/mp4">
            </video>
            <div class="hero-content">
                <h1>AVATAR <br>FIRE AND ASH</h1>
                <p>The journey to Pandora continues.</p>
                <a href="#now-showing" class="btn-buy">RESERVE SEATS</a>
            </div>
        </div>
    </section>

    <div class="container" id="now-showing">
        <h2 style="font-family:'Bebas Neue'; font-size:2.8rem; margin-bottom:20px;">Now Showing</h2>
        <div class="movie-grid">
            <?php foreach($senaraiMovie as $movie): ?>
                <div class="movie-card">
                    <div class="poster-box">
                        <img src="assets/img/<?php echo $movie['image']; ?>">
                        <div class="overlay">
                            <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="btn-buy">BUY TICKET</a>
                        </div>
                    </div>
                    <div style="padding:15px; text-align:center;">
                        <h3 style="margin:0; font-size:1rem;"><?php echo $movie['name']; ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        let current = 0;
        let isMuted = true;
        const items = document.querySelectorAll('.carousel-item');
        const vids = document.querySelectorAll('.video-bg');

        // Mainkan video pertama (mesti muted untuk elak gelap)
        window.onload = () => { vids[0].play(); };

        function changeSlide() {
            let next = (current + 1) % items.length;
            
            vids[next].muted = isMuted; // Ikut setting sound semasa
            vids[next].play();
            
            items[current].classList.remove('active');
            items[next].classList.add('active');

            setTimeout(() => {
                vids[current].pause();
                current = next;
            }, 1200);
        }

        setInterval(changeSlide, 10000);

        function toggleMute() {
            isMuted = !isMuted;
            const icon = document.getElementById('vol-icon');
            const text = document.getElementById('vol-text');

            vids[current].muted = isMuted;
            icon.className = isMuted ? "fas fa-volume-mute" : "fas fa-volume-up";
            text.innerText = isMuted ? "UNMUTE SOUND" : "SOUND ON";
            
            // Re-play video bila unmute (Chrome requirement)
            if(!isMuted) vids[current].play();
        }

        window.onscroll = function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        };
    </script>
</body>
</html>