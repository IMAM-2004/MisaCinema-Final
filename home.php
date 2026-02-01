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
    <title>MISA CINEMA</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #e50914; }
        body { margin: 0; background: #000; color: #fff; font-family: 'Montserrat', sans-serif; overflow-x: hidden; }

        /* NAVBAR FIX */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px 6%; position: fixed; width: 100%; top: 0; z-index: 2000;
            background: linear-gradient(to bottom, rgba(0,0,0,0.9), transparent);
        }
        .logo-text { font-family: 'Bebas Neue', sans-serif; font-size: 2rem; color: #fff; text-decoration: none; }
        .logo-text span { color: var(--primary); }
        
        .nav-links { display: flex; align-items: center; gap: 15px; }
        
        /* PROFILE BUTTON (IM) */
        .profile-btn {
            width: 38px; height: 38px; background: var(--primary); color: #fff;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 800; text-decoration: none; font-size: 0.8rem;
        }

        /* CAROUSEL FIX: Menghilangkan Gelap */
        .hero-carousel { position: relative; width: 100%; height: 100vh; background: #000; }
        .carousel-item {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; transition: opacity 1.5s ease-in-out; /* Cross-fade lebih lama */
            display: flex; align-items: center; padding: 0 8%;
            z-index: 1;
        }
        .carousel-item.active { opacity: 1; z-index: 2; }

        .video-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; filter: brightness(0.4);
        }

        .hero-content { position: relative; z-index: 10; transform: translateY(20px); transition: 1s; opacity: 0; }
        .active .hero-content { transform: translateY(0); opacity: 1; }
        .hero-content h1 { font-family: 'Bebas Neue', sans-serif; font-size: 5rem; margin: 0; line-height: 1; }

        /* MOBILE VIEW */
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 3rem; }
            .nav-links a:not(.profile-btn):not(.logout-link) { display: none; }
            .movie-grid { grid-template-columns: repeat(2, 1fr) !important; padding: 0 15px; }
            .hero-carousel { height: 60vh; }
        }

        /* MOVIE GRID */
        .movie-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; padding: 40px 8%; }
        .movie-card { background: #111; border-radius: 8px; overflow: hidden; position: relative; transition: 0.3s; }
        .movie-card:hover { transform: scale(1.05); }
        .movie-card img { width: 100%; height: 300px; object-fit: cover; }
        
        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); display: flex; align-items: center;
            justify-content: center; opacity: 0; transition: 0.3s;
        }
        .movie-card:hover .overlay { opacity: 1; }
        .btn-book { background: var(--primary); color: #fff; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 0.8rem; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="home.php" class="logo-text">MISA<span>CINEMA</span></a>
        <div class="nav-links">
            <?php if(isset($_SESSION['user'])): ?>
                <a href="profile.php" class="profile-btn"><?php echo strtoupper(substr($_SESSION['user']['fullname'], 0, 2)); ?></a>
                <a href="logout.php" class="logout-link" style="color:var(--primary); font-weight:bold; text-decoration:none; font-size:0.8rem;">LOGOUT</a>
            <?php else: ?>
                <a href="login.php" class="btn-book">SIGN IN</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="hero-carousel">
        <div class="carousel-item active">
            <video class="video-bg" loop muted playsinline preload="auto">
                <source src="assets/videos/Papa Zola .mp4" type="video/mp4">
            </video>
            <div class="hero-content">
                <h1>PAPA ZOLA <br>THE MOVIE</h1>
                <p>Justice is back!</p>
                <a href="#now-showing" class="btn-book">GET TICKETS</a>
            </div>
        </div>

        <div class="carousel-item">
            <video class="video-bg" loop muted playsinline preload="auto">
                <source src="assets/videos/Avatar .mp4" type="video/mp4">
            </video>
            <div class="hero-content">
                <h1>AVATAR <br>FIRE AND ASH</h1>
                <p>The elements are at war.</p>
                <a href="#now-showing" class="btn-book">BOOK NOW</a>
            </div>
        </div>
    </section>

    <div id="now-showing">
        <h2 style="padding: 40px 8% 0; font-family: 'Bebas Neue'; font-size: 2.5rem;">Now Showing</h2>
        <div class="movie-grid">
            <?php foreach($senaraiMovie as $movie): ?>
                <div class="movie-card">
                    <img src="assets/img/<?php echo $movie['image']; ?>">
                    <div class="overlay">
                        <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="btn-book">BUY TICKET</a>
                    </div>
                    <div style="padding:10px; text-align:center; font-size:0.9rem;"><?php echo $movie['name']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        let current = 0;
        const items = document.querySelectorAll('.carousel-item');
        const vids = document.querySelectorAll('.video-bg');

        // Pastikan video pertama main
        vids[0].play();

        function nextSlide() {
            let next = (current + 1) % items.length;

            // 1. Play video seterusnya secara senyap di background
            vids[next].play();

            // 2. Selepas video seterusnya "ready", baru tukar opacity
            setTimeout(() => {
                items[current].classList.remove('active');
                items[next].classList.add('active');
                
                // 3. Pause video lama SELEPAS transition tamat (1.5s)
                setTimeout(() => {
                    vids[current].pause();
                    current = next;
                }, 1500);
            }, 100); 
        }

        setInterval(nextSlide, 10000); // 10 saat setiap slide
    </script>
</body>
</html>