<?php
require 'vendor/autoload.php';
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$collection = $client->misacinema_db->shows;
$id = $_GET['id'];
$movie = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking: <?php echo $movie['name']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { background-color: #0b0b0b; color: white; font-family: 'Roboto', sans-serif; margin: 0; min-height: 100vh; display: flex; flex-direction: column; }
        
        header { background-color: #000; padding: 20px 40px; border-bottom: 2px solid #e50914; }
        .logo { color: #e50914; font-size: 24px; font-weight: bold; text-transform: uppercase; text-decoration: none; }
        
        .main-content { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px; }
        
        .booking-card { display: flex; background: #181818; border-radius: 12px; overflow: hidden; max-width: 900px; width: 100%; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        
        .poster-side { width: 350px; background: #000; position: relative; }
        .poster-img { width: 100%; height: 100%; object-fit: cover; opacity: 0.8; }
        
        .info-side { padding: 40px; flex: 1; display: flex; flex-direction: column; justify-content: center; }
        
        h2 { color: #e50914; margin-top: 0; text-transform: uppercase; letter-spacing: 1px; font-size: 1.8em; }
        .meta { color: #888; margin-bottom: 20px; font-size: 0.9em; display: flex; gap: 15px; }
        .meta span { background: #222; padding: 5px 10px; border-radius: 4px; }
        
        .desc { line-height: 1.6; color: #ccc; margin-bottom: 30px; font-size: 1em; }
        
        .price-tag { font-size: 2.5em; font-weight: bold; color: white; margin-bottom: 20px; }
        .price-tag small { font-size: 0.4em; color: #888; font-weight: normal; }
        
        /* FORM */
        .form-group { margin-bottom: 20px; }
        label { display: block; color: #e50914; font-size: 0.8em; font-weight: bold; margin-bottom: 8px; text-transform: uppercase; }
        input { width: 100%; padding: 12px; background: #222; border: 1px solid #333; color: white; border-radius: 4px; font-size: 1em; outline: none; transition: 0.3s; }
        input:focus { border-color: #e50914; background: #2a2a2a; }
        
        .btn-confirm { background: #e50914; color: white; width: 100%; padding: 15px; border: none; font-size: 1.1em; font-weight: bold; text-transform: uppercase; cursor: pointer; border-radius: 4px; transition: 0.3s; margin-top: 10px; }
        .btn-confirm:hover { background: #ff0f1f; box-shadow: 0 0 15px rgba(229, 9, 20, 0.4); }
        
        .btn-cancel { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; font-size: 0.9em; }
        .btn-cancel:hover { color: white; }

    </style>
</head>
<body>

    <header>
        <a href="index.php" class="logo">🎬 MISA CINEMA</a>
    </header>

    <div class="main-content">
        <div class="booking-card">
            <div class="poster-side">
                <img src="assets/img/<?php echo $movie['image']; ?>" class="poster-img">
            </div>
            
            <div class="info-side">
                <h2><?php echo $movie['name']; ?></h2>
                <div class="meta">
                    <span>⏱ <?php echo $movie['duration']; ?></span>
                    <span>🎭 <?php echo isset($movie['genre']) ? $movie['genre'] : 'Movie'; ?></span>
                </div>
                
                <p class="desc"><?php echo substr($movie['description'], 0, 150); ?>...</p>
                
                <div class="price-tag"><small>RM</small> <?php echo $movie['price']; ?></div>

                <form action="book.php" method="POST">
                    <input type="hidden" name="movie_name" value="<?php echo $movie['name']; ?>">
                    <input type="hidden" name="price" value="<?php echo $movie['price']; ?>">
                    
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" name="customer_name" required placeholder="Enter full name">
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="customer_phone" required placeholder="012-3456789">
                    </div>

                    <button type="submit" class="btn-confirm">CONFIRM & PAY</button>
                    <a href="index.php" class="btn-cancel">Cancel Booking</a>
                </form>
            </div>
        </div>
    </div>

</body>
</html>