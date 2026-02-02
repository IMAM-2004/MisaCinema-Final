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
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #e50914; }
        body { margin: 0; background: #000; color: #fff; font-family: 'Montserrat', sans-serif; overflow-x: hidden; }

        /* --- NAVBAR PREMIUM --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            /* Padding dikurangkan ke 4% supaya nampak lebih luas (edge-to-edge) */
            padding: 20px 4%; 
            position: fixed; width: 100%; top: 0; z-index: 2000;
            background: linear-gradient(to bottom, rgba(0,0,0,0.8), transparent);
            transition: 0.5s ease;
            box-sizing: border-box; /* Pastikan padding tak kacau width */
        }
        .navbar.scrolled { 
            background: rgba(0,0,0,0.95); 
            padding: 12px 4%; 
            backdrop-filter: blur(10px); 
            border-bottom: 1px solid #222;
        }
        .logo-text { font-family: 'Bebas Neue', sans-serif; font-size: 2.5rem; color: #fff; text-decoration: none; letter-spacing: 1px; }
        .logo-text span { color: var(--primary); }
        
        .nav-links { display: flex; align-items: center; gap: 25px; }
        .nav-links a { color: #fff; text-decoration: none; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; transition: 0.3s; }
        .nav-links a:hover { color: var(--primary); }

        /* PROFILE STYLE */
        .profile-wrapper { display: flex; align-items: center; gap: 15px; }
        .profile-box {
            width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--primary);
            overflow: hidden; display: flex; align-items: center; justify-content: center;
            background: var(--primary); text-decoration: none; color: white; font-weight: 800; transition: 0.3s;
        }
        .profile-box:hover { transform: scale(1.1); }
        .profile-box img { width: 100%; height: 100%; object-fit: cover; }
        .logout-btn { 
            color: #fff; background: var(--primary); padding: 5px 15px; border-radius: 4px; 
            font-size: 0.75rem; text-decoration: none; font-weight: bold; transition: 0.3s;
        }
        .logout-btn:hover { background: #b20710; }

        /* --- HERO VIDEO CAROUSEL (FULLSCREEN) --- */
        .hero-carousel { position: relative; width: 100%; height: 100vh; background: #000; overflow: hidden; }
        .carousel-item {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; visibility: hidden;
            /* Transition opacity 1.5s supaya video 'blend' masuk smooth gila */
            transition: opacity 1.5s ease-in-out; 
            display: flex; align-items: center; padding: 0 6%;
        }
        .carousel-item.active { opacity: 1; visibility: visible; }

        .video-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; filter: brightness(0.4); z-index: -1;
            background: #000;
        }

        .hero-content { position: relative; z-index: 10; max-width: 800px; transform: translateY(20px); transition: 1s; opacity: 0; }
        .carousel-item.active .hero-content { transform: translateY(0); opacity: 1; } /* Text naik perlahan bila active */

        .hero-content h1 { font-family: 'Bebas Neue', sans-serif; font-size: 6rem; margin: 0; line-height: 0.9; text-shadow: 2px 2px 10px rgba(0,0,0,0.5); }
        .hero-content p { font-size: 1.2rem; margin-bottom: 30px; color: #ddd; max-width: 600px; }
        .hero-btn { background: var(--primary); color: #fff; padding: 15px 40px; border-radius: 50px; text-decoration: none; font-weight: 700; font-size: 1rem; transition: 0.3s; box-shadow: 0 4px 15px rgba(229,9,20,0.4); }
        .hero-btn:hover { background: #ff0f1f; transform: scale(1.05); }

        /* --- SEARCH & TITLE SECTION --- */
        .section-header {
            display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap;
            margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 15px;
        }
        .search-box { position: relative; width: 300px; }
        .search-input { 
            width: 100%; background: #1a1a1a; border: 1px solid #333; padding: 12px 20px 12px 45px; 
            border-radius: 30px; color: white; font-family: 'Montserrat'; outline: none; transition: 0.3s;
        }
        .search-input:focus { border-color: var(--primary); background: #222; }
        .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #777; }

        /* --- MOVIE GRID --- */
        .container { padding: 60px 4%; min-height: 100vh; background: #000; }
        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 25px; }
        .movie-card { background: #111; border-radius: 12px; overflow: hidden; position: relative; transition: 0.4s; border: 1px solid #222; }
        .movie-card.hidden { display: none; }
        
        .poster-box { height: 360px; position: relative; overflow: hidden; }
        .poster-box img { width: 100%; height: 100%; object-fit: cover; transition: 0.6s; }
        
        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
            display: flex; align-items: center; justify-content: center; 
            opacity: 0; transition: 0.3s;
        }
        .movie-card:hover .overlay { opacity: 1; }
        .movie-card:hover { transform: translateY(-10px); box-shadow: 0 10px 20px rgba(0,0,0,0.5); border-color: #444; }
        .movie-card:hover img { transform: scale(1.1); }
        
        .movie-info { padding: 15px; text-align: center; }
        .movie-title { margin: 0; font-size: 1rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* --- MOBILE RESPONSIVE --- */
        @media (max-width: 768px) {
            .navbar { padding: 15px 5%; }
            .nav-links a:not(.profile-link):not(.logout-btn) { display: none; }
            .hero-content h1 { font-size: 3.5rem; }
            .hero-carousel { height: 75vh; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .search-box { width: 100%; }
            .movie-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .poster-box { height: 250px; }
            .hero-btn { padding: 12px 30px; font-size: 0.9rem; }
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
                    <a href="logout.php" class="logout-btn">LOGOUT</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="hero-btn" style="padding: 8px 25px; font-size: 0.8rem;">SIGN IN</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero-carousel">
        <div class="carousel-item active">
            <video class="video-bg" loop muted playsinline preload="auto">
                <source src="assets/videos/Papa Zola .mp4" type="video/mp4">
            </video>
            <div class="hero-content">
                <h1 style="color:var(--primary)">PAPA ZOLA <br>THE MOVIE</h1>
                <p>Justice is back! The hero we need, the hero we deserve.</p>
                <a href="#now-showing" class="hero-btn">GET TICKETS</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" loop muted playsinline preload="auto">
                <source src="assets/videos/Avatar .mp4" type="video/mp4">
            </video>
            <div class="hero-content">
                <h1>AVATAR <br>FIRE AND ASH</h1>
                <p>Return to Pandora. A new clan rises from the ashes.</p>
                <a href="#now-showing" class="hero-btn">RESERVE SEATS</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" loop muted playsinline preload="auto">
                <source src="assets/videos/The SpongeBob.mp4" type="video/mp4">
            </video>
            <div class="hero-content">
                <h1>SPONGEBOB <br>SQUAREPANTS</h1>
                <p>Are you ready kids? Aye aye captain!</p>
                <a href="#now-showing" class="hero-btn">BOOK NOW</a>
            </div>
        </div>
    </section>

    <div class="container" id="now-showing">
        
        <div class="section-header">
            <h2 style="font-family:'Bebas Neue'; font-size:3rem; margin:0; line-height:1;">Now Showing</h2>
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="movieSearch" class="search-input" placeholder="Search movies...">
            </div>
        </div>

        <div class="movie-grid" id="movieGrid">
            <?php foreach($senaraiMovie as $movie): ?>
                <div class="movie-card" data-title="<?php echo strtolower($movie['name']); ?>">
                    <div class="poster-box">
                        <img src="assets/img/<?php echo $movie['image']; ?>">
                        <div class="overlay">
                            <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="hero-btn" style="padding:10px 25px; font-size:0.8rem;">BUY TICKET</a>
                        </div>
                    </div>
                    <div class="movie-info">
                        <h3 class="movie-title"><?php echo $movie['name']; ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p id="noResult" style="display:none; text-align:center; color:#666; margin-top:40px;">No movies found.</p>
    </div>

    <script>
        // --- 1. SUPER SMOOTH VIDEO CAROUSEL ---
        let current = 0;
        const items = document.querySelectorAll('.carousel-item');
        const vids = document.querySelectorAll('.video-bg');

        // Play first video immediately
        vids[0].play();

        function changeSlide() {
            let next = (current + 1) % items.length;
            
            // Trick: Play next video BEFORE showing it
            vids[next].play();
            
            // CSS Transition handles the fade in/out
            items[current].classList.remove('active');
            items[next].classList.add('active');

            // Pause old video to save RAM (after transition done)
            setTimeout(() => {
                vids[current].pause();
                current = next;
            }, 1500); // 1.5s match CSS transition
        }

        // Change every 8 seconds
        setInterval(changeSlide, 8000);


        // --- 2. SEARCH BAR LOGIC ---
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

        // --- 3. NAVBAR GLASS EFFECT ---
        window.onscroll = function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        };
    </script>
</body>
</html>