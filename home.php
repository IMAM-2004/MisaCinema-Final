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
    <title>MISA CINEMA | God Mode</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #e50914; --gold: linear-gradient(45deg, #FFD700, #FFA500); }
        * { box-sizing: border-box; }
        body { margin: 0; background: #000; color: #fff; font-family: 'Montserrat', sans-serif; overflow-x: hidden; }

        /* --- NAVIGATION --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 6%; position: fixed; width: 100%; top: 0; z-index: 2000;
            backdrop-filter: blur(10px); transition: 0.5s; background: linear-gradient(to bottom, rgba(0,0,0,0.8), transparent);
        }
        .navbar.scrolled { background: rgba(0,0,0,0.95); padding: 12px 6%; }
        .logo-text { font-family: 'Bebas Neue', sans-serif; font-size: 2.5rem; color: #fff; text-decoration: none; }
        .logo-text span { color: var(--primary); }
        .nav-links { display: flex; align-items: center; gap: 25px; }
        .nav-links a { color: #fff; text-decoration: none; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; }
        
        /* PROFILE IMAGE STYLING */
        .user-panel { display: flex; align-items: center; gap: 15px; }
        .profile-pic { 
            width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--primary); 
            object-fit: cover; background: var(--primary); display: flex; 
            align-items: center; justify-content: center; font-weight: bold; color: white;
            text-decoration: none; overflow: hidden;
        }
        .profile-pic img { width: 100%; height: 100%; object-fit: cover; }

        /* --- VIDEO CAROUSEL --- */
        .hero-carousel { position: relative; width: 100%; height: 100vh; overflow: hidden; background: #000; }
        .carousel-item {
            position: absolute; width: 100%; height: 100%; top: 0; left: 0;
            opacity: 0; visibility: hidden; transition: opacity 1.2s ease-in-out;
            display: flex; align-items: center; padding: 0 8%;
        }
        .carousel-item.active { opacity: 1; visibility: visible; }

        .video-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; z-index: -1; filter: brightness(0.5);
        }

        .carousel-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to right, #000 10%, transparent 70%),
                        linear-gradient(to top, #000 5%, transparent 30%);
            z-index: 0;
        }

        .hero-content { position: relative; z-index: 10; max-width: 800px; }
        .hero-content h1 { font-family: 'Bebas Neue', sans-serif; font-size: 6rem; margin: 0; line-height: 0.9; }
        .hero-content p { font-size: 1.1rem; color: #ccc; margin: 20px 0; }

        /* CAROUSEL CONTROLS */
        .carousel-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.1); border: none; color: white;
            padding: 20px; cursor: pointer; z-index: 100; border-radius: 50%;
            transition: 0.3s;
        }
        .carousel-btn:hover { background: var(--primary); }
        .prev { left: 20px; } .next { right: 20px; }

        /* VOLUME TOGGLE */
        .volume-control {
            position: absolute; bottom: 40px; right: 40px; z-index: 100;
            background: rgba(0,0,0,0.5); border: 1px solid #fff; color: #fff;
            padding: 15px 25px; border-radius: 30px; cursor: pointer; display: flex; align-items: center; gap: 10px;
            font-weight: bold; font-size: 0.9rem; transition: 0.3s;
        }
        .volume-control:hover { background: var(--primary); border-color: var(--primary); }

        /* --- SEARCH BAR --- */
        .search-container { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; margin-bottom: 40px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .search-box { position: relative; width: 300px; }
        .search-input { 
            width: 100%; background: #1a1a1a; border: none; padding: 12px 20px 12px 45px; 
            border-radius: 30px; color: white; font-family: 'Montserrat'; outline: none; 
        }
        .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #777; }

        /* --- MOVIE GRID --- */
        .container { padding: 80px 8%; background: #000; min-height: 100vh; }
        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; }
        .movie-card { background: #111; border-radius: 15px; overflow: hidden; transition: 0.4s; border: 1px solid #222; }
        .movie-card.hidden { display: none; }
        .movie-card:hover { transform: translateY(-10px); border-color: var(--primary); }
        .poster-wrapper { height: 380px; position: relative; overflow: hidden; }
        .poster-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.75); display: flex; flex-direction: column; justify-content: center; align-items: center; opacity: 0; transition: 0.3s; }
        .movie-card:hover .overlay { opacity: 1; }
        .movie-card:hover img { transform: scale(1.1); }
        .btn-book { background: var(--primary); color: #fff; padding: 12px 25px; text-decoration: none; border-radius: 30px; font-weight: bold; }
        .card-info { padding: 20px; text-align: center; }

        /* MOBILE FIX */
        @media (max-width: 768px) {
            .navbar { padding: 15px 5%; }
            .nav-links a:not(.profile-link):not(.btn-sign) { display: none; }
            .hero-content h1 { font-size: 3.5rem; }
            .movie-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .poster-wrapper { height: 250px; }
            .search-container { flex-direction: column; align-items: flex-start; gap: 15px; }
            .search-box { width: 100%; }
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
                <div class="user-panel">
                    <a href="profile.php" class="profile-pic profile-link">
                        <?php if(!empty($_SESSION['user']['image'])): ?>
                            <img src="assets/img/profile/<?php echo $_SESSION['user']['image']; ?>">
                        <?php else: ?>
                            <?php echo strtoupper(substr($_SESSION['user']['fullname'], 0, 2)); ?>
                        <?php endif; ?>
                    </a>
                    <a href="logout.php" class="btn-sign" style="background:var(--primary); padding:5px 15px; border-radius:5px; font-size:0.75rem;">LOGOUT</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-sign" style="background:var(--primary); padding:8px 20px; border-radius:5px;">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero-carousel">
        <div class="carousel-item active">
            <video class="video-bg" loop muted playsinline preload="auto">
                <source src="assets/videos/Papa Zola .mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <p style="color:var(--primary); font-weight:bold; letter-spacing:4px;">PREMIUM EXPERIENCE</p>
                <h1>PAPA ZOLA <br>THE MOVIE</h1>
                <p>Justice is back! The hero we need.</p>
                <a href="#now-showing" class="btn-book" style="padding:15px 40px;">Get Tickets</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" loop muted playsinline preload="auto">
                <source src="assets/videos/Avatar .mp4" type="video/mp4">
            </video>
            <div class="carousel-overlay"></div>
            <div class="hero-content">
                <p style="color:var(--primary); font-weight:bold; letter-spacing:4px;">NOW SHOWING</p>
                <h1>AVATAR: <br>FIRE & ASH</h1>
                <p>Mankind was born on Earth. It was never meant to die here.</p>
                <a href="#now-showing" class="btn-book" style="padding:15px 40px;">Book Tickets</a>
            </div>
        </div>

        <button class="carousel-btn prev" onclick="changeSlide(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-btn next" onclick="changeSlide(1)"><i class="fas fa-chevron-right"></i></button>
        
        <button class="volume-control" onclick="toggleMute()">
            <i id="vol-icon" class="fas fa-volume-mute"></i> <span id="vol-text">UNMUTE SOUND</span>
        </button>
    </section>

    <div class="container" id="now-showing">
        
        <div class="search-container">
            <h2 style="font-family:'Bebas Neue'; font-size:3rem; margin:0;">Now Showing</h2>
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="movieSearch" class="search-input" placeholder="Search movies...">
            </div>
        </div>

        <div class="movie-grid">
            <?php foreach($senaraiMovie as $movie): ?>
                <div class="movie-card" data-title="<?php echo strtolower($movie['name']); ?>">
                    <div class="poster-wrapper">
                        <img src="assets/img/<?php echo $movie['image'] ?? 'default.jpg'; ?>">
                        <div class="overlay">
                            <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="btn-book">Buy Ticket</a>
                        </div>
                    </div>
                    <div class="card-info">
                        <h3 class="movie-title"><?php echo $movie['name']; ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p id="noResult" style="display:none; text-align:center; color:#666; margin-top:20px;">No movies found.</p>
    </div>

    <script>
        // --- SEARCH BAR LOGIC ---
        const searchInput = document.getElementById('movieSearch');
        const cards = document.querySelectorAll('.movie-card');
        const noResult = document.getElementById('noResult');

        searchInput.addEventListener('keyup', function(e) {
            const term = e.target.value.toLowerCase();
            let visibleCount = 0;

            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                if(title.includes(term)) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });
            noResult.style.display = (visibleCount === 0) ? 'block' : 'none';
        });

        // --- CAROUSEL LOGIC (NO FLICKER) ---
        let current = 0;
        let isMuted = true;
        const slides = document.querySelectorAll('.carousel-item');
        const videos = document.querySelectorAll('.video-bg');

        // Start first video
        window.onload = () => { videos[0].play(); };

        function changeSlide(direction) {
            let next = (current + direction + slides.length) % slides.length;
            
            // 1. Play next video BEFORE showing (Fixes Flicker)
            videos[next].muted = isMuted;
            videos[next].play();

            // 2. Swap Classes
            slides[current].classList.remove('active');
            slides[next].classList.add('active');

            // 3. Cleanup old video after transition
            setTimeout(() => {
                videos[current].pause();
                current = next;
            }, 1200);
        }

        // Auto slide every 10 seconds
        setInterval(() => changeSlide(1), 10000);

        function toggleMute() {
            isMuted = !isMuted;
            const icon = document.getElementById('vol-icon');
            const text = document.getElementById('vol-text');
            
            // Update current video immediately
            videos[current].muted = isMuted;
            
            // Update UI
            if (isMuted) {
                icon.className = 'fas fa-volume-mute';
                text.innerText = 'UNMUTE SOUND';
            } else {
                icon.className = 'fas fa-volume-up';
                text.innerText = 'SOUND ON';
                videos[current].play(); // Ensure playing if unmuted
            }
        }

        window.onscroll = function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        };
    </script>
</body>
</html>