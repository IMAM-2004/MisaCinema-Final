<?php
session_start();
require 'vendor/autoload.php';

// 1. SETUP DATABASE
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$collection = $client->misacinema_db->shows; 
$senaraiMovie = $collection->find([]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title>MISA CINEMA | Experience Luxury</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- GOD LEVEL STYLING --- */
        :root {
            --primary: #e50914;
            --gold: linear-gradient(45deg, #FFD700, #FFA500);
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * { box-sizing: border-box; scroll-behavior: smooth; }
        
        body {
            margin: 0; padding: 0;
            background: #000;
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
        }

        /* Moving Background Ambient Glow */
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(229, 9, 20, 0.1) 0%, transparent 50%);
            z-index: -1;
            pointer-events: none;
        }

        /* --- NAVBAR --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 6%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.9) 0%, transparent 100%);
            position: fixed; width: 100%; top: 0; z-index: 1000;
            backdrop-filter: blur(10px);
            transition: 0.5s ease;
        }

        .navbar.scrolled { padding: 10px 6%; background: rgba(0,0,0,0.95); }

        .logo-container { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        
        .logo-icon {
            font-size: 2.2rem;
            background: var(--gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.5));
        }

        .logo-text {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.5rem; color: #fff; letter-spacing: 2px;
        }
        .logo-text span { color: var(--primary); }

        .nav-links { display: flex; align-items: center; gap: 30px; }
        
        .nav-links a.menu-item {
            color: #ddd; text-decoration: none; font-weight: 600; font-size: 0.8rem;
            text-transform: uppercase; letter-spacing: 1.5px; transition: 0.3s;
        }
        .nav-links a.menu-item:hover { color: var(--primary); transform: scale(1.1); }

        .btn-login {
            background: var(--primary);
            padding: 12px 28px; border-radius: 5px; color: white !important;
            text-decoration: none; font-weight: 800; font-size: 0.8rem;
            text-transform: uppercase; transition: 0.3s;
            box-shadow: 0 5px 15px rgba(229, 9, 20, 0.3);
        }
        .btn-login:hover { transform: scale(1.05); background: #ff0000; box-shadow: 0 0 25px rgba(229, 9, 20, 0.5); }

        .user-panel { display: flex; align-items: center; gap: 20px; padding-left: 20px; border-left: 1px solid var(--glass-border); }
        .user-nav img { width: 38px; height: 38px; border-radius: 50%; border: 2px solid var(--primary); }
        .user-nav span { font-weight: 800; font-size: 0.75rem; color: #fff; text-transform: uppercase; }

        /* --- HERO --- */
        .hero {
            height: 100vh;
            background: linear-gradient(to right, #000 20%, transparent 80%),
                        linear-gradient(to top, #000 5%, transparent 40%),
                        url('https://images3.alphacoders.com/101/1011883.jpg') no-repeat center center/cover;
            display: flex; align-items: center; padding: 0 8%;
        }
        
        .hero-content { max-width: 800px; animation: slideIn 1s ease-out; }
        .hero h1 { 
            font-family: 'Bebas Neue', sans-serif; font-size: 7rem; 
            margin: 0; line-height: 0.9; text-transform: uppercase;
            text-shadow: 5px 5px 20px rgba(0,0,0,0.5);
        }
        .hero h1 span { color: var(--primary); }
        .hero p { font-size: 1.2rem; color: #ccc; margin: 25px 0 40px; font-weight: 300; line-height: 1.8; letter-spacing: 1px; }

        .btn-hero {
            padding: 18px 45px; background: var(--primary); color: white; 
            text-decoration: none; font-weight: 800; font-size: 1rem; 
            border-radius: 5px; text-transform: uppercase; letter-spacing: 2px;
            transition: 0.4s; display: inline-block;
        }
        .btn-hero:hover { background: white; color: black; transform: translateY(-5px); }

        /* --- MOVIE GRID --- */
        .container { padding: 100px 8%; background: #000; }
        
        .section-header { margin-bottom: 60px; text-align: center; }
        .section-title { 
            font-family: 'Bebas Neue', sans-serif; font-size: 4rem; color: #fff;
            letter-spacing: 4px; position: relative; display: inline-block;
        }
        .section-title::after {
            content: ""; position: absolute; width: 50%; height: 4px; background: var(--primary);
            bottom: -10px; left: 25%;
        }
        
        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 40px;
        }

        .movie-card {
            background: #0a0a0a; border-radius: 15px; overflow: hidden;
            transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid var(--glass-border); position: relative;
        }
        
        .poster-wrapper { height: 420px; position: relative; overflow: hidden; }
        .poster-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.6s; }
        
        .movie-card:hover { transform: translateY(-20px); border-color: var(--primary); box-shadow: 0 20px 50px rgba(229, 9, 20, 0.3); }
        .movie-card:hover .poster-wrapper img { transform: scale(1.1); filter: brightness(0.3) blur(2px); }

        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            opacity: 0; transition: 0.4s;
        }
        .movie-card:hover .overlay { opacity: 1; }

        .btn-book {
            background: var(--primary); color: white; padding: 15px 30px;
            font-weight: 800; text-decoration: none; border-radius: 50px;
            text-transform: uppercase; font-size: 0.85rem; transition: 0.3s;
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
        }
        .btn-book:hover { background: #fff; color: var(--primary); transform: scale(1.1); }

        .card-info { padding: 25px; background: linear-gradient(to top, #000, #0a0a0a); }
        .movie-title { font-size: 1.3rem; font-weight: 800; margin: 0; color: #fff; }
        .movie-genre { 
            font-size: 0.7rem; color: var(--primary); text-transform: uppercase; 
            letter-spacing: 2px; margin-top: 8px; font-weight: bold;
        }

        @keyframes slideIn { from { opacity: 0; transform: translateX(-50px); } to { opacity: 1; transform: translateX(0); } }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .navbar { padding: 15px 5%; flex-direction: column; gap: 15px; }
            .hero h1 { font-size: 3.5rem; }
            .hero { text-align: center; justify-content: center; }
            .movie-grid { grid-template-columns: 1fr 1fr; gap: 15px; }
            .poster-wrapper { height: 280px; }
            .user-panel { border-left: none; padding: 0; }
        }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <a href="home.php" class="logo-container">
            <i class="fas fa-film logo-icon"></i>
            <div class="logo-text">MISA<span>CINEMA</span></div>
        </a>
        
        <div class="nav-links">
            <a href="home.php" class="menu-item">Home</a>
            <a href="#now-showing" class="menu-item">Now Showing</a>
            
            <?php if(isset($_SESSION['user'])): ?>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] != 'admin'): ?>
                    <a href="my_bookings.php" class="menu-item" style="color: var(--primary);">My Tickets</a>
                <?php endif; ?>
                
                <div class="user-panel">
                    <?php 
                        $profilePic = "https://ui-avatars.com/api/?name=".urlencode($_SESSION['user']['fullname'] ?? 'User')."&background=e50914&color=fff"; 
                        if (isset($_SESSION['user']['profile_pic']) && !empty($_SESSION['user']['profile_pic'])) {
                            $profilePic = "uploads/" . $_SESSION['user']['profile_pic'];
                        }
                        $displayName = "USER";
                        if (isset($_SESSION['user']['fullname'])) {
                            $displayName = explode(" ", $_SESSION['user']['fullname'])[0]; 
                        }
                    ?>

                    <a href="<?php echo ($_SESSION['role'] ?? '') == 'admin' ? '#' : 'profile.php'; ?>" class="user-nav" style="text-decoration: none;">
                        <img src="<?php echo $profilePic; ?>" alt="Profile">
                        <span>HI, <?php echo $displayName; ?></span>
                    </a>

                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <a href="admin.php" class="menu-item" style="font-size: 0.7rem; color: #FFD700;">Admin</a>
                    <?php endif; ?>

                    <a href="logout.php" class="btn-login" style="background: #222; padding: 8px 15px; font-size: 0.7rem;">Exit</a>
                </div>

            <?php else: ?>
                <a href="login.php" class="btn-login">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-content">
            <p style="color: var(--primary); font-weight: 800; letter-spacing: 5px; text-transform: uppercase;">Cinematic Excellence</p>
            <h1>Your Ultimate <br> <span>Movie Hub</span></h1>
            <p>Escape into worlds beyond imagination. From high-octane thrillers to heart-warming dramas, experience cinema as it was meant to be seen.</p>
            <a href="#now-showing" class="btn-hero">Reserve Seats Now</a>
        </div>
    </header>

    <div class="container" id="now-showing">
        <div class="section-header">
            <h2 class="section-title">Now Showing</h2>
        </div>
        
        <div class="movie-grid">
            <?php foreach($senaraiMovie as $movie): ?>
                <div class="movie-card">
                    <div class="poster-wrapper">
                        <?php $img = !empty($movie['image']) ? $movie['image'] : 'default_poster.jpg'; ?>
                        <img src="assets/img/<?php echo $img; ?>" alt="<?php echo $movie['name']; ?>">
                        
                        <div class="overlay">
                            <i class="fas fa-play" style="font-size: 2.5rem; color: white; margin-bottom: 20px;"></i>
                            <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="btn-book">Buy Ticket</a>
                        </div>
                    </div>

                    <div class="card-info">
                        <h3 class="movie-title"><?php echo $movie['name']; ?></h3>
                        <div class="movie-genre"><?php echo $movie['genre'] ?? 'Blockbuster'; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Smooth scroll & Navbar effect
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>