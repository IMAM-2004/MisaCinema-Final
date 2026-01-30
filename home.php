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
    <title>Misa Cinema - Watch The Best Movies</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* RESET & BASE */
        * { box-sizing: border-box; }
        
        body {
            margin: 0; padding: 0;
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('assets/images/bg_cinema.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            font-family: 'Roboto', sans-serif;
        }

        /* NAVBAR (Glass Effect) */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 50px;
            background: rgba(0,0,0,0.85);
            position: fixed; width: 100%; top: 0; z-index: 100;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #333;
        }

        /* --- NEON LOGO STYLE --- */
        .logo { 
            font-size: 1.8rem; 
            font-weight: 900; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            text-decoration: none;
            
            /* Warna asas putih supaya tengah dia terang */
            color: #fff; 
            
            /* NEON GLOW MERAH */
            text-shadow: 
                0 0 5px #fff,       /* Glow putih dekat */
                0 0 10px #e50914,   /* Glow merah sikit */
                0 0 20px #e50914,   /* Glow merah sederhana */
                0 0 40px #e50914;   /* Glow merah jauh */
            
            /* Animasi Kelip-kelip */
            animation: neon-flicker 4s infinite alternate;
        }

        @keyframes neon-flicker {
            0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
                text-shadow: 
                    0 0 5px #fff,
                    0 0 10px #e50914,
                    0 0 20px #e50914,
                    0 0 40px #e50914;
                opacity: 1;
            }
            20%, 24%, 55% {
                text-shadow: none;
                opacity: 0.5; /* Gelap sekejap */
            }
        }
        /* --- END NEON LOGO --- */
        
        .nav-links { display: flex; align-items: center; }
        .nav-links a {
            color: #ccc; text-decoration: none; margin-left: 20px; font-weight: 500; transition: 0.3s; font-size: 0.9rem;
        }
        .nav-links a:hover { color: #e50914; }
        .btn-login {
            background: #e50914; padding: 8px 20px; border-radius: 4px; color: white !important;
        }

        /* HERO SECTION */
        .hero {
            height: 75vh;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.9) 100%),
                        url('https://wallpapers.com/images/hd/avengers-endgame-desktop-4k-af4370211.jpg') no-repeat center center/cover;
            display: flex; flex-direction: column; justify-content: center;
            padding: 0 50px;
            margin-top: 0;
            border-bottom: 1px solid #333;
        }
        .hero h1 { font-size: 3.5rem; margin: 0 0 10px; max-width: 700px; line-height: 1.1; text-transform: uppercase; text-shadow: 2px 2px 4px rgba(0,0,0,0.8); }
        .hero p { font-size: 1.2rem; max-width: 600px; margin-bottom: 30px; color: #ddd; text-shadow: 1px 1px 2px black; }
        .btn-hero {
            padding: 12px 30px; background: #e50914; color: white; 
            text-decoration: none; font-weight: bold; font-size: 1rem; 
            border-radius: 5px; display: inline-block; transition: 0.3s;
        }
        .btn-hero:hover { background: #ff0f1f; transform: scale(1.05); }

        /* MOVIE GRID SECTION */
        .container { padding: 50px; }
        .section-title { border-left: 5px solid #e50914; padding-left: 15px; margin-bottom: 30px; font-size: 2rem; text-shadow: 2px 2px 4px black;}
        
        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
        }

        /* MOVIE CARD DESIGN */
        .movie-card {
            background: #1a1a1a; border-radius: 8px; overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        .movie-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(229, 9, 20, 0.4);
            z-index: 2;
        }
        .poster-wrapper {
            height: 320px; overflow: hidden; position: relative;
        }
        .poster-wrapper img {
            width: 100%; height: 100%; object-fit: cover;
        }
        
        /* Overlay Button */
        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex; justify-content: center; align-items: center;
            opacity: 0; transition: 0.3s;
        }
        .movie-card:hover .overlay { opacity: 1; }
        
        .btn-book {
            background: transparent; border: 2px solid #e50914; color: white;
            padding: 10px 25px; font-weight: bold; text-decoration: none;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-book:hover { background: #e50914; }

        .card-info { padding: 15px; }
        .movie-title { font-size: 1rem; margin: 0 0 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: bold; }
        .movie-genre { font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 1px; }

        /* FOOTER */
        footer {
            text-align: center; padding: 40px; background: rgba(0,0,0,0.9); color: #555; font-size: 0.9rem; margin-top: 50px; border-top: 1px solid #222;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="home.php" class="logo">🎬 MISA CINEMA</a>
        
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="#now-showing">Now Showing</a>
            
            <?php if(isset($_SESSION['user'])): ?>
                
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] != 'admin'): ?>
                    <a href="my_bookings.php" style="color: #e50914;"><i class="fas fa-ticket-alt"></i> My Bookings</a>
                    <span style="color:#444; margin: 0 15px;">|</span>
                <?php endif; ?>
                
                <?php 
                    // --- LOGIC NAMA & GAMBAR ---
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

                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <div style="display: flex; align-items: center; gap: 10px; color: white; margin-left: 20px; cursor: default;">
                        <img src="<?php echo $profilePic; ?>" alt="Profile" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #e50914;">
                        <span>Hi, <?php echo $displayName; ?></span>
                    </div>

                <?php else: ?>
                    <a href="profile.php" style="display: flex; align-items: center; gap: 10px; color: white; text-decoration: none; font-weight: bold;">
                        <img src="<?php echo $profilePic; ?>" alt="Profile" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #e50914;">
                        <span>Hi, <?php echo $displayName; ?></span>
                    </a>
                <?php endif; ?>

                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="admin.php">Admin Panel</a>
                <?php endif; ?>

                <a href="logout.php" class="btn-login" style="background:#333;">Logout</a>

            <?php else: ?>
                <a href="login.php" class="btn-login">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <h1>Welcome to <br> Misa Cinema</h1>
        <p>Experience the latest blockbusters with immersive sound and crystal clear visuals.</p>
        <a href="#now-showing" class="btn-hero">Browse Movies</a>
    </header>

    <div class="container" id="now-showing">
        <h2 class="section-title">Now Showing</h2>
        
        <div class="movie-grid">
            <?php foreach($senaraiMovie as $movie): ?>
                <div class="movie-card">
                    
                    <div class="poster-wrapper">
                        <img src="assets/img/<?php echo $movie['image']; ?>" alt="<?php echo $movie['name']; ?>">
                        
                        <div class="overlay">
                            <a href="booking.php?id=<?php echo $movie['_id']; ?>" class="btn-book">Buy Ticket</a>
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

    <footer>
        <p>&copy; 2024 Misa Cinema. All Rights Reserved.</p>
    </footer>

</body>
</html>