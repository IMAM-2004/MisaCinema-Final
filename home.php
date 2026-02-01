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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MISA CINEMA | Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #e50914; }
        body { margin: 0; background: #000; color: #fff; font-family: 'Montserrat', sans-serif; overflow-x: hidden; }

        /* --- NAVBAR --- */
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

        /* PROFILE SECTION */
        .profile-container { display: flex; align-items: center; gap: 15px; }
        .profile-img {
            width: 40px; height: 40px; border-radius: 50%; object-fit: cover;
            border: 2px solid var(--primary); background: var(--primary);
            display: flex; align-items: center; justify-content: center; font-weight: 800;
        }

        /* --- ANTI-FLICKER HERO CAROUSEL --- */
        .hero-carousel { position: relative; width: 100%; height: 100vh; background: #000; }
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
        }

        .hero-content { position: relative; z-index: 10; max-width: 700px; opacity: 0; transform: translateY(30px); transition: 1s 0.5s; }
        .active .hero-content { opacity: 1; transform: translateY(0); }
        .hero-content h1 { font-family: 'Bebas Neue', sans-serif; font-size: 6rem; margin: 0; line-height: 0.9; }

        /* --- MOBILE VIEW --- */
        @media (max-width: 768px) {
            .navbar { padding: 10px 4%; }
            .logo-text { font-size: 1.8rem; }
            .nav-links a:not(.profile-link) { display: none; } /* Hide menu text on small mobile */
            .hero-content h1 { font-size: 3.5rem; }
            .hero-carousel { height: 75vh; }
            .movie-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 15px !important; }
            .poster-wrapper { height: 250px !important; }
        }

        /* --- MOVIE GRID --- */
        .container { padding: 60px 8%; }
        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 30px; }
        .movie-card { background: #111; border-radius: 12px; overflow: hidden; position: relative; transition: 0.4s; }
        .movie-card:hover { transform: translateY(-10px); border: 1px solid var(--primary); }
        .poster-wrapper { height: 350px; position: relative; overflow: hidden; }
        .poster-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        
        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); display: flex; align-items: center;
            justify-content: center; opacity: 0; transition: 0.3s;
        }
        .movie-card:hover .overlay { opacity: 1; }
        .movie-card:hover img { transform: scale(1.1); }
        .btn-book { background: var(--primary); color: #fff; padding: 10px 20px; border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 0.8rem; }

        /* CONTROLS */
        .carousel-btn { position: absolute; top: 50%; transform: translateY(-50%); z-index: 100; background: rgba(255,255,255,0.1); border: none; color: #fff; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; transition: 0.3s; }
        .carousel-btn:hover { background: var(--primary); }
        .prev { left: 20px; } .next { right: 20px; }
        
        /* UNMUTE WARNING (Browsers block auto-audio) */
        .audio-notice { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); background: var(--primary); padding: 5px 15px; border-radius: 20px; font-size: 0.7rem; font-weight: bold; z-index: 1000; animation: bounce 2s infinite; cursor: pointer; }
        @keyframes bounce { 0%, 100% {transform: translateX(-50%) translateY(0);} 50% {transform: translateX(-50%) translateY(-5px);} }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <a href="home.php" class="logo-text">MISA<span>CINEMA</span></a>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="#now-showing">Movies</a>
            <?php if(isset($_SESSION['user'])): ?>
                <div class="profile-container">
                    <a href="profile.php" class="profile-link">
                        <?php if(!empty($_SESSION['user']['image'])): ?>
                            <img src="assets/img/profile/<?php echo $_SESSION['user']['image']; ?>" class="profile-img">
                        <?php else: ?>
                            <div class="profile-img"><?php echo strtoupper(substr($_SESSION['user']['fullname'], 0, 2)); ?></div>
                        <?php endif; ?>
                    </a>
                    <a href="logout.php" style="color:var(--primary); font-weight:bold;">LOGOUT</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-book">SIGN IN</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero-carousel" onclick="enableAudio()">
        <div class="audio-notice" id="audio-hint">Click anywhere to enable sound 🔊</div>
        
        <div class="carousel-item active">
            <video class="video-bg" loop playsinline preload="auto">
                <source src="assets/videos/Papa Zola .mp4" type="video/mp4">
            </video>
            <div class="hero-content">
                <h1>PAPA ZOLA <br>THE MOVIE</h1>
                <p>Kebenaran akan muncul! Join Papa Zola sekarang.</p>
                <a href="#now-showing" class="btn-book">BUY TICKETS</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" loop playsinline preload="auto">
                <source src="assets/videos/Avatar .mp4" type="video/mp4">
            </video>
            <div class="hero-content">
                <h1>AVATAR <br>FIRE AND ASH</h1>
                <p>The journey to Pandora continues.</p>
                <a href="#now-showing" class="btn-book">RESERVE SEATS</a>
            </div>
        </div>

        <button class="carousel-btn prev" onclick="changeSlide(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-btn next" onclick="changeSlide(1)"><i class="fas fa-chevron-right"></i></button>
    </section>

    <div class="container" id="now-showing">
        <h2 style="font-family: 'Bebas Neue'; font-size: 3rem; margin-bottom: 30px;">Now Showing</h2>
        <div class="movie-grid">
            <?php foreach($senaraiMovie as $movie): ?>
                <div class="movie-card">
                    <div class="poster-wrapper">
                        <img src="assets/img/<?php echo $movie['image']; ?>">
                        <div class="overlay">
                            <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="btn-book">BUY TICKET</a>
                        </div>
                    </div>
                    <div style="padding:15px; text-align:center;">
                        <h3 style="margin:0; font-size:1.1rem;"><?php echo $movie['name']; ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        let current = 0;
        const items = document.querySelectorAll('.carousel-item');
        const vids = document.querySelectorAll('.video-bg');

        // Trick: Mainkan semua video tapi volume 0 dulu untuk prevent "Gelap"
        window.onload = () => {
            vids.forEach((v, i) => {
                v.volume = 0.5;
                if(i !== 0) v.pause(); // Cuma video 1 jalan dulu
            });
            vids[0].play();
        };

        function changeSlide(dir) {
            let next = (current + dir + items.length) % items.length;
            
            // Play video baru dulu siap-siap
            vids[next].play();
            
            // Tukar opacity (Transition CSS handle cross-fade)
            items[current].classList.remove('active');
            items[next].classList.add('active');

            // Pause video lama lepas transition habis
            setTimeout(() => {
                vids[current].pause();
                current = next;
            }, 1200);
        }

        // Browser block audio kalau tak klik. So kita minta user klik sekali.
        function enableAudio() {
            vids.forEach(v => v.muted = false);
            document.getElementById('audio-hint').style.display = 'none';
        }

        setInterval(() => changeSlide(1), 12000);

        window.onscroll = function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        };
    </script>
</body>
</html>