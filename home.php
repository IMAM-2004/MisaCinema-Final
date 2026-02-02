<?php
session_start();
require 'vendor/autoload.php';

// Connection DB
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
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #e50914; --dark: #0a0a0a; --gray: #808080; }
        body { margin: 0; background: var(--dark); color: #fff; font-family: 'Montserrat', sans-serif; overflow-x: hidden; }

        /* --- NAVBAR --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 5%;
            position: fixed; width: 100%; top: 0; z-index: 2000;
            background: linear-gradient(to bottom, rgba(0,0,0,0.9) 0%, transparent 100%);
            transition: 0.4s ease;
            box-sizing: border-box;
        }
        .navbar.scrolled { 
            background: rgba(10, 10, 10, 0.95); 
            padding: 12px 5%; 
            backdrop-filter: blur(10px); 
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .logo-text { font-family: 'Bebas Neue', sans-serif; font-size: 2.2rem; color: #fff; text-decoration: none; letter-spacing: 2px; text-shadow: 0 0 10px rgba(229, 9, 20, 0.5); }
        .logo-text span { color: var(--primary); }
        
        .nav-links { display: flex; align-items: center; gap: 30px; }
        .nav-links a { 
            color: #ddd; text-decoration: none; font-weight: 500; font-size: 0.85rem; 
            text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
        }
        .nav-links a:hover { color: #fff; text-shadow: 0 0 10px rgba(255,255,255,0.5); }
        
        /* Highlight active link trick */
        .nav-links a.active { color: var(--primary); font-weight: 700; }

        /* --- PROFILE POP-UP FIX --- */
        .profile-wrapper { display: flex; align-items: center; gap: 20px; }
        
        .profile-box {
            width: 42px; height: 42px; 
            border-radius: 50%; 
            border: 2px solid var(--primary);
            overflow: hidden; 
            display: flex; align-items: center; justify-content: center;
            background: #222; 
            text-decoration: none; color: white; font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 0 0 rgba(229, 9, 20, 0);
            position: relative;
        }
        /* Pastikan gambar sentiasa fit bulat */
        .profile-box img { 
            width: 100%; height: 100%; 
            object-fit: cover; /* Wajib ada untuk elak gambar gepeng */
            display: block;
        }
        .profile-box:hover { 
            transform: scale(1.1); 
            box-shadow: 0 0 15px var(--primary); /* Neon Glow effect */
            border-color: #fff;
        }

        .logout-btn { 
            color: #fff !important; background: transparent; border: 1px solid var(--primary);
            padding: 6px 20px; border-radius: 30px; 
            font-size: 0.75rem !important; font-weight: 600; transition: 0.3s;
        }
        .logout-btn:hover { background: var(--primary); box-shadow: 0 0 15px rgba(229,9,20,0.6); }

        /* --- HERO --- */
        .hero-carousel { position: relative; width: 100%; height: 100vh; background: #000; overflow: hidden; }
        .carousel-item {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; visibility: hidden; transition: opacity 1s ease-in-out; z-index: 1;
            display: flex; align-items: center; padding: 0 6%;
        }
        .carousel-item.active { opacity: 1; visibility: visible; z-index: 2; }
        .video-bg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.4); z-index: -1; background: #000; }
        .hero-content { position: relative; z-index: 10; max-width: 800px; opacity: 0; transform: translateY(30px); transition: 1s ease-out; }
        .carousel-item.active .hero-content { opacity: 1; transform: translateY(0); transition-delay: 0.5s; }
        .hero-content h1 { font-family: 'Bebas Neue', sans-serif; font-size: 6rem; margin: 0; line-height: 0.9; text-shadow: 0 10px 30px rgba(0,0,0,0.8); }
        .hero-btn { 
            background: var(--primary); color: #fff; padding: 12px 35px; border-radius: 4px; 
            text-decoration: none; font-weight: 700; font-size: 0.9rem; transition: 0.3s; text-transform: uppercase;
        }
        .hero-btn:hover { background: #fff; color: var(--primary); }

        /* --- SUPERB MOVIE GRID --- */
        .container { padding: 100px 5% 50px 5%; min-height: 100vh; background: #0a0a0a; }
        
        .section-header {
            display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;
            border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;
        }
        .section-title { font-family: 'Bebas Neue'; font-size: 3.5rem; color: #fff; margin: 0; letter-spacing: 2px; }
        .section-title span { color: var(--primary); }

        .search-box { position: relative; width: 300px; }
        .search-input { 
            width: 100%; background: #151515; border: 1px solid #333; padding: 12px 20px 12px 45px; 
            border-radius: 50px; color: #fff; font-family: 'Montserrat'; outline: none; transition: 0.3s;
        }
        .search-input:focus { border-color: var(--primary); background: #222; box-shadow: 0 0 15px rgba(229,9,20,0.2); }
        .search-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #666; }

        /* GRID SYSTEM */
        .movie-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
            gap: 40px 25px; /* Gap AtasBawah 40px, KiriKanan 25px */
        }
        .movie-card.hidden { display: none; }

        /* --- CARD DESIGN (NETFLIX STYLE) --- */
        .movie-card {
            background: transparent;
            position: relative;
            cursor: pointer;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .poster-box {
            height: 320px;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
            transition: 0.4s ease;
            border: 2px solid transparent; /* Prepare border for hover */
        }

        .poster-box img {
            width: 100%; height: 100%; object-fit: cover;
            transition: 0.6s ease;
        }

        /* Hover Effects */
        .movie-card:hover .poster-box {
            transform: translateY(-10px) scale(1.02);
            border-color: var(--primary);
            box-shadow: 0 0 25px rgba(229, 9, 20, 0.4); /* Red Glow */
        }
        .movie-card:hover img {
            transform: scale(1.1);
            filter: brightness(0.4); /* Gelapkan gambar sikit supaya button jelas */
        }

        /* Content Overlay */
        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            opacity: 0; transition: 0.3s ease;
            padding: 20px; box-sizing: border-box; text-align: center;
        }
        
        .movie-card:hover .overlay { opacity: 1; }

        .overlay h3 {
            font-family: 'Bebas Neue'; font-size: 1.8rem; letter-spacing: 1px; margin: 0 0 15px 0;
            transform: translateY(20px); transition: 0.4s; color: #fff;
        }
        .movie-card:hover .overlay h3 { transform: translateY(0); }

        .ticket-btn {
            background: var(--primary); color: white; padding: 10px 25px;
            border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 0.8rem;
            transform: translateY(20px); transition: 0.4s 0.1s; /* Delay sikit */
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }
        .ticket-btn:hover { background: white; color: var(--primary); }
        .movie-card:hover .ticket-btn { transform: translateY(0); }

        /* INFO BELOW CARD */
        .movie-info { padding-top: 12px; }
        .movie-title-text {
            font-size: 0.95rem; font-weight: 600; color: #ddd; margin: 0;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            transition: 0.3s;
        }
        .movie-card:hover .movie-title-text { color: var(--primary); }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .navbar { padding: 15px 5%; }
            .nav-links a:not(.profile-link):not(.logout-btn) { display: none; }
            .hero-content h1 { font-size: 3.5rem; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 15px; }
            .search-box { width: 100%; }
            .movie-grid { grid-template-columns: repeat(2, 1fr); gap: 20px 15px; }
            .poster-box { height: 250px; }
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
                <a href="my_booking.php">My Bookings</a>

                <div class="profile-wrapper">
                    <a href="profile.php" class="profile-box profile-link" title="My Profile">
                        <?php if(!empty($_SESSION['user']['image'])): ?>
                            <img src="assets/img/profile/<?php echo $_SESSION['user']['image']; ?>" 
                                 onerror="this.style.display='none'; this.parentElement.innerText='<?php echo strtoupper(substr($_SESSION['user']['fullname'], 0, 2)); ?>'">
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
            <video class="video-bg" loop muted playsinline preload="auto" poster="assets/img/poster_placeholder.jpg">
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
            <h2 class="section-title">Now <span>Showing</span></h2>
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="movieSearch" class="search-input" placeholder="Find your movie...">
            </div>
        </div>

        <div class="movie-grid" id="movieGrid">
            <?php foreach($senaraiMovie as $movie): ?>
                <div class="movie-card" data-title="<?php echo strtolower($movie['name']); ?>">
                    <div class="poster-box">
                        <img src="assets/img/<?php echo $movie['image']; ?>" alt="<?php echo $movie['name']; ?>">
                        
                        <div class="overlay">
                            <h3><?php echo $movie['name']; ?></h3>
                            <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="ticket-btn">
                                GET TICKET
                            </a>
                        </div>
                    </div>
                    
                    <div class="movie-info">
                        <p class="movie-title-text"><?php echo $movie['name']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p id="noResult" style="display:none; text-align:center; color:#666; margin-top:50px; font-size:1.2rem;">Movie not found.</p>
    </div>

    <script>
        // --- 1. VIDEO FIX ---
        let current = 0;
        const items = document.querySelectorAll('.carousel-item');
        const vids = document.querySelectorAll('.video-bg');

        vids[0].play();

        function changeSlide() {
            let next = (current + 1) % items.length;
            let playPromise = vids[next].play();

            if (playPromise !== undefined) {
                playPromise.then(_ => {
                    items[current].classList.remove('active');
                    items[next].classList.add('active');

                    setTimeout(() => {
                        vids[current].pause();
                        vids[current].currentTime = 0;
                        current = next;
                    }, 1000); 
                }).catch(error => {
                    console.log("Video error, skipping animation");
                });
            }
        }
        setInterval(changeSlide, 8000);

        // --- 2. SEARCH ---
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

        // --- 3. NAVBAR GLASS ---
        window.onscroll = function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        };
    </script>
</body>
</html>