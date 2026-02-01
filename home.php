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
    
    <title>Misa Cinema - Premium Experience</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- RESET & BASE --- */
        * { box-sizing: border-box; }
        
        body {
            margin: 0; padding: 0;
            background: linear-gradient(to bottom, rgba(20, 20, 20, 0.5), rgba(0, 0, 0, 0.9)), url('assets/images/bg_cinema.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #e0e0e0;
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
        }

        /* --- NAVBAR (Ultra Glass) --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px 50px;
            background: rgba(0, 0, 0, 0.7);
            position: fixed; width: 100%; top: 0; z-index: 1000;
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
            transition: 0.3s;
        }

        /* --- LOGO --- */
        .logo-container {
            display: flex; align-items: center; gap: 10px; text-decoration: none;
        }
        
        .logo-icon {
            font-size: 2rem;
            background: linear-gradient(45deg, #FFD700, #FF8C00); /* Gold Color */
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.8));
            animation: star-pulse 3s infinite;
        }

        .logo-text {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.2rem;
            color: #fff;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(229, 9, 20, 0.8);
        }

        .logo-text span { color: #e50914; }

        @keyframes star-pulse {
            0% { transform: scale(1); filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.8)); }
            50% { transform: scale(1.1) rotate(5deg); filter: drop-shadow(0 0 15px rgba(255, 215, 0, 1)); }
            100% { transform: scale(1); filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.8)); }
        }

        /* --- NAV LINKS --- */
        .nav-links { display: flex; align-items: center; gap: 25px; }
        
        .nav-links a.menu-item {
            color: #fff; text-decoration: none;
            font-weight: 600; transition: 0.3s; font-size: 0.85rem;
            text-transform: uppercase; letter-spacing: 1px;
            position: relative;
        }
        
        /* Underline Effect */
        .nav-links a.menu-item::after {
            content: ''; position: absolute; width: 0; height: 2px;
            bottom: -5px; left: 0; background-color: #e50914;
            transition: width 0.3s;
        }
        .nav-links a.menu-item:hover::after { width: 100%; }
        .nav-links a.menu-item:hover { color: #e50914; text-shadow: 0 0 8px rgba(229,9,20,0.6); }

        .btn-login {
            background: linear-gradient(45deg, #e50914, #b20710);
            padding: 10px 25px; border-radius: 30px; color: white !important;
            text-decoration: none; font-weight: bold; font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(229, 9, 20, 0.4);
            display: inline-block;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(229, 9, 20, 0.6); }

        /* --- USER PROFILE SECTION --- */
        .user-panel {
            display: flex; align-items: center; gap: 15px;
            border-left: 1px solid #444; padding-left: 20px;
        }
        
        .user-nav { display: flex; align-items: center; gap: 10px; color: white; text-decoration: none; }
        .user-nav img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #e50914; }
        .user-nav span { font-weight: bold; font-size: 0.85rem; }

        /* --- HERO SECTION --- */
        .hero {
            height: 85vh;
            background: linear-gradient(to top, #000 0%, rgba(0,0,0,0.1) 60%, rgba(0,0,0,0.3) 100%),
                        url('https://wallpapers.com/images/hd/avengers-endgame-desktop-4k-af4370211.jpg') no-repeat center center/cover;
            display: flex; flex-direction: column; justify-content: center;
            padding: 0 8%;
            position: relative;
        }
        
        .hero h1 { 
            font-family: 'Bebas Neue', sans-serif;
            font-size: 5rem; 
            margin: 0; line-height: 1; text-transform: uppercase; letter-spacing: 3px;
            background: -webkit-linear-gradient(#fff, #aaa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0px 10px 20px rgba(0,0,0,0.8);
            animation: fadeInUp 1s ease-out;
        }

        .hero p { 
            font-size: 1.1rem; max-width: 600px; margin: 20px 0 40px; color: #ccc; 
            font-weight: 300; line-height: 1.6;
            animation: fadeInUp 1.2s ease-out;
        }

        .btn-hero {
            padding: 15px 40px; background: #e50914; color: white; 
            text-decoration: none; font-weight: bold; font-size: 1rem; 
            border-radius: 5px; display: inline-block; transition: 0.3s;
            text-transform: uppercase; letter-spacing: 2px;
            border: 1px solid #e50914;
            animation: fadeInUp 1.4s ease-out;
        }
        .btn-hero:hover { 
            background: transparent; border-color: #fff; 
            box-shadow: 0 0 20px rgba(255,255,255,0.2); 
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- MOVIE GRID SECTION --- */
        .container { padding: 60px 8%; background: #000; }
        
        .section-header { 
            display: flex; align-items: center; margin-bottom: 40px; 
            border-bottom: 1px solid #333; padding-bottom: 10px;
        }
        .section-title { 
            font-family: 'Bebas Neue', sans-serif;
            font-size: 3rem; margin: 0; color: #e50914;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(229, 9, 20, 0.3);
        }
        .section-subtitle { margin-left: 20px; color: #666; font-size: 0.9rem; margin-top: 10px;}
        
        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 30px;
        }

        /* --- MOVIE CARD DESIGN --- */
        .movie-card {
            background: #111; border-radius: 12px; overflow: hidden;
            transition: all 0.4s ease;
            position: relative; border: 1px solid #222;
        }
        .movie-card:hover {
            transform: translateY(-15px) scale(1.02);
            border-color: #e50914;
            box-shadow: 0 15px 40px rgba(229, 9, 20, 0.3);
            z-index: 2;
        }
        
        .poster-wrapper { height: 360px; overflow: hidden; position: relative; }
        .poster-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .movie-card:hover .poster-wrapper img { transform: scale(1.1); filter: brightness(0.4); }
        
        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: flex; justify-content: center; align-items: center;
            opacity: 0; transition: 0.4s ease;
            flex-direction: column; gap: 10px;
        }
        .movie-card:hover .overlay { opacity: 1; }
        
        .btn-book {
            background: #e50914; color: white; border: none;
            padding: 12px 30px; font-weight: 700; text-decoration: none;
            text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem;
            border-radius: 50px; transform: translateY(20px); transition: 0.4s;
        }
        .movie-card:hover .btn-book { transform: translateY(0); }
        .btn-book:hover { background: #fff; color: #e50914; }

        .card-info { padding: 20px; text-align: center; }
        .movie-title { 
            font-size: 1.1rem; margin: 0 0 5px; 
            font-weight: 700; color: #fff; 
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; 
        }
        .movie-genre { 
            font-size: 0.75rem; color: #888; text-transform: uppercase; 
            letter-spacing: 1.5px; border: 1px solid #333; 
            display: inline-block; padding: 3px 8px; border-radius: 4px; margin-top: 5px;
        }

        /* =========================================
           MOBILE RESPONSIVE MAGIC
           ========================================= */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 15px 20px;
                gap: 15px;
                background: rgba(0, 0, 0, 0.95); /* Darker on mobile for readability */
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
                width: 100%;
            }

            .user-panel {
                border-left: none;
                padding-left: 0;
                width: 100%;
                justify-content: center;
                border-top: 1px solid #333;
                padding-top: 15px;
            }

            .hero {
                padding: 0 20px;
                text-align: center;
                align-items: center;
            }
            .hero h1 { font-size: 3rem; } /* Smaller title for phone */
            .hero p { font-size: 1rem; }

            .container { padding: 40px 20px; }
            
            .section-header { 
                flex-direction: column; 
                align-items: flex-start; 
            }
            .section-subtitle { margin-left: 0; margin-top: 5px; }

            .movie-grid {
                /* Show 1 column on small phones, 2 on big phones */
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); 
                gap: 15px;
            }
            .poster-wrapper { height: 250px; } /* Smaller cards on phone */
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="home.php" class="logo-container">
            <i class="fas fa-star logo-icon"></i>
            <div class="logo-text">MISA <span>CINEMA</span></div>
        </a>
        
        <div class="nav-links">
            <a href="home.php" class="menu-item">Home</a>
            <a href="#now-showing" class="menu-item">Now Showing</a>
            
            <?php if(isset($_SESSION['user'])): ?>
                
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] != 'admin'): ?>
                    <a href="my_bookings.php" class="menu-item" style="color: #e50914;">My Bookings</a>
                <?php endif; ?>
                
                <?php 
                    // --- PROFILE LOGIC ---
                    $profilePic = "https://via.placeholder.com/40"; 
                    if (is_array($_SESSION['user']) && isset($_SESSION['user']['profile_pic']) && !empty($_SESSION['user']['profile_pic'])) {
                        $profilePic = "uploads/" . $_SESSION['user']['profile_pic'];
                    }

                    $displayName = "User";
                    if (is_array($_SESSION['user'])) {
                        if (isset($_SESSION['user']['fullname'])) {
                            $displayName = explode(" ", $_SESSION['user']['fullname'])[0]; 
                        } elseif (isset($_SESSION['user']['username'])) {
                            $displayName = $_SESSION['user']['username'];
                        }
                    } else {
                        $displayName = $_SESSION['user']; 
                    }
                ?>

                <div class="user-panel">
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <div class="user-nav" style="cursor: default;">
                            <img src="<?php echo $profilePic; ?>" alt="Profile">
                            <span>HI, <?php echo strtoupper($displayName); ?></span>
                        </div>
                    <?php else: ?>
                        <a href="profile.php" class="user-nav">
                            <img src="<?php echo $profilePic; ?>" alt="Profile">
                            <span>HI, <?php echo strtoupper($displayName); ?></span>
                        </a>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <a href="admin.php" class="menu-item" style="font-size: 0.8rem;">Admin Panel</a>
                    <?php endif; ?>

                    <a href="logout.php" class="btn-login" style="background: #333; box-shadow: none; font-size: 0.8rem; padding: 8px 15px;">Logout</a>
                </div>

            <?php else: ?>
                <a href="login.php" class="btn-login">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <div style="z-index: 2;">
            <p style="color: #e50914; font-weight: bold; letter-spacing: 2px; margin-bottom: 5px; text-transform: uppercase;">The Ultimate Experience</p>
            <h1>Welcome to <br> Misa Cinema</h1>
            <p>Experience the latest blockbusters with immersive sound <br>and crystal clear visuals like never before.</p>
            <a href="#now-showing" class="btn-hero">Browse Movies</a>
        </div>
    </header>

    <div class="container" id="now-showing">
        <div class="section-header">
            <div>
                <h2 class="section-title">Now Showing</h2>
                <div class="section-subtitle">Book your tickets for the latest releases</div>
            </div>
        </div>
        
        <div class="movie-grid">
            <?php foreach($senaraiMovie as $movie): ?>
                <div class="movie-card">
                    
                    <div class="poster-wrapper">
                        <?php 
                            $img = isset($movie['image']) ? $movie['image'] : 'default_poster.jpg';
                        ?>
                        <img src="assets/img/<?php echo $img; ?>" alt="<?php echo $movie['name']; ?>">
                        
                        <div class="overlay">
                            
                            <i class="fas fa-play-circle" style="font-size: 3rem; color: white; margin-bottom: 10px;"></i>
                            <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="btn-book"><i class="fas fa-ticket-alt"></i> Buy Ticket</a>
                        </div>
                    </div>

                    <div class="card-info">
                        <h3 class="movie-title"><?php echo $movie['name']; ?></h3>
                        <div class="movie-genre"><?php echo isset($movie['genre']) ? $movie['genre'] : 'Movie'; ?></div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>

    </body>
</html>