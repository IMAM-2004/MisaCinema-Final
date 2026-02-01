<?php
session_start();
require 'vendor/autoload.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 2. DATABASE CONNECTION
$client = new MongoDB\Client("mongodb+srv://adminmisa:123@cluster0.sv61lap.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0");
$db = $client->misacinema_db;
$usersCollection = $db->users;

// 3. SMART USER ID DETECTION
$userId = null;

if (is_array($_SESSION['user']) && isset($_SESSION['user']['_id'])) {
    $userId = new MongoDB\BSON\ObjectId($_SESSION['user']['_id']);
} 
else if (is_string($_SESSION['user'])) {
    $nameStr = $_SESSION['user'];
    $tempUser = $usersCollection->findOne(['fullname' => $nameStr]);
    if ($tempUser) {
        $userId = $tempUser['_id'];
        $_SESSION['user'] = (array)$tempUser;
    } else {
        header("Location: logout.php");
        exit();
    }
} 
else {
    $arr = (array)$_SESSION['user'];
    if (isset($arr['_id'])) {
        $userId = new MongoDB\BSON\ObjectId($arr['_id']);
    }
}

// 4. LOGIC UPDATE PROFILE
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateData = [];

    if (isset($_POST['fullname'])) $updateData['fullname'] = htmlspecialchars($_POST['fullname']);
    if (isset($_POST['phone'])) $updateData['phone'] = htmlspecialchars($_POST['phone']);
    if (isset($_POST['email'])) $updateData['email'] = htmlspecialchars($_POST['email']);
    if (isset($_POST['birthday'])) $updateData['birthday'] = $_POST['birthday'];

    // Handle Image Upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_pic']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            $newFilename = "profile_" . (string)$userId . "." . $filetype;
            $destination = "uploads/" . $newFilename;

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
                $updateData['profile_pic'] = $newFilename;
            } else {
                $message = "Error uploading image.";
            }
        }
    }

    if (!empty($updateData)) {
        $usersCollection->updateOne(
            ['_id' => $userId],
            ['$set' => $updateData]
        );
        $currentUser = $usersCollection->findOne(['_id' => $userId]);
        $_SESSION['user'] = (array)$currentUser;
        $message = "Profile updated successfully!";
    }
}

// 5. FETCH DATA UNTUK DISPLAY
$user = $usersCollection->findOne(['_id' => $userId]);

$fullname = isset($user['fullname']) ? $user['fullname'] : 'User';
$email = isset($user['email']) ? $user['email'] : '';
$phone = isset($user['phone']) ? $user['phone'] : '';
$birthday = isset($user['birthday']) ? $user['birthday'] : '';
$pic = isset($user['profile_pic']) ? "uploads/".$user['profile_pic'] : "https://via.placeholder.com/150";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Misa Cinema</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* UNIVERSAL BACKGROUND */
        body { 
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('assets/images/bg_cinema.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white; 
            font-family: 'Roboto', sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }

        .profile-card { 
            background: rgba(21, 21, 21, 0.95); /* Sedikit transparent */
            width: 100%; 
            max-width: 450px; 
            padding: 40px; 
            border-radius: 10px; 
            border: 1px solid #333; 
            text-align: center; 
            box-shadow: 0 0 20px rgba(0,0,0,0.7);
        }

        .profile-img-container { position: relative; width: 120px; height: 120px; margin: 0 auto 20px; }
        .profile-img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 3px solid #e50914; }
        .upload-btn-wrapper { position: absolute; bottom: 0; right: 0; background: #e50914; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; }
        .upload-btn-wrapper input[type=file] { position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer; height: 100%; width: 100%; }
        
        h2 { margin: 10px 0 5px; text-transform: uppercase; color: #fff; letter-spacing: 1px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        label { display: block; color: #aaa; font-size: 0.8em; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; background: #222; border: 1px solid #444; color: white; border-radius: 4px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #e50914; }
        
        .btn-save { width: 100%; background: #e50914; color: white; padding: 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        .btn-save:hover { background: #ff0f1f; }
        
        .links { margin-top: 20px; display: flex; justify-content: space-between; font-size: 0.9em; }
        .links a { color: #aaa; text-decoration: none; }
        .links a:hover { color: white; }
        .msg { background: #2ecc71; color: #fff; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="profile-card">
        <?php if($message): ?>
            <div class="msg"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="profile-img-container">
                <img src="<?php echo $pic; ?>" alt="Profile" class="profile-img">
                <div class="upload-btn-wrapper">📷<input type="file" name="profile_pic" onchange="this.form.submit()"></div>
            </div>

            <h2><?php echo $fullname; ?></h2>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" value="<?php echo $fullname; ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $email; ?>">
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" value="<?php echo $phone; ?>">
            </div>

            <div class="form-group">
                <label>Birthday</label>
                <input type="date" name="birthday" value="<?php echo $birthday; ?>">
            </div>

            <button type="submit" class="btn-save">Save Changes</button>
        </form>

        <div class="links">
            <a href="home.php">← Back to Home</a>
            <a href="logout.php" style="color: #e50914;">Logout</a>
        </div>
    </div>

</body>

</html>

