<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="sidebar">
    <div class="sidebar-top">
        <div class="logo-container">
            <h2>MISA ADMIN</h2>
        </div>

        <ul class="nav-links">
            <li>
                <a href="admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>

            <li>
                <a href="admin_movies.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_movies.php' ? 'active' : ''; ?>">
                    <i class="fas fa-film"></i> Movies
                </a>
            </li>

            <li>
                <a href="admin_halls.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_halls.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chair"></i> Halls & Seats
                </a>
            </li>

            <li>
                <a href="admin_bookings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_bookings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i> Bookings
                </a>
            </li>

            <li>
                <a href="admin_staff.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_staff.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Staffs
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-bottom">
        <a href="home.php" target="_blank" class="nav-item">
            <i class="fas fa-home"></i> View Site
        </a>
        
        <a href="logout.php" class="nav-item logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<style>
    /* Reset & Font */
    body { margin: 0; font-family: 'Roboto', sans-serif; }

    /* SIDEBAR CONTAINER - Fixed & Paling Atas (z-index) */
    .sidebar {
        width: 250px;
        height: 100vh;
        background-color: #151515; /* Hitam ikut Pic 1 */
        position: fixed;
        top: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between; /* Tolak menu naik, logout turun */
        border-right: 1px solid #333;
        z-index: 99999; /* Confirm boleh tekan */
    }

    /* LOGO */
    .logo-container {
        padding: 30px 20px;
        text-align: center;
        border-bottom: 1px solid #222;
        margin-bottom: 10px;
    }
    .logo-container h2 {
        color: #e50914; /* Merah MISA */
        margin: 0;
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    /* MENU LIST */
    .nav-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-links li a {
        display: flex;
        align-items: center;
        padding: 15px 25px;
        color: #aaa; /* Kelabu asal */
        text-decoration: none;
        font-size: 16px;
        transition: 0.3s;
        border-left: 4px solid transparent;
    }

    .nav-links li a i {
        width: 30px;
        text-align: center;
        margin-right: 10px;
    }

    /* EFFECT BILA HOVER ATAU ACTIVE */
    .nav-links li a:hover, 
    .nav-links li a.active {
        background-color: #222;
        color: white;
        border-left: 4px solid #e50914; /* Line merah tepi */
    }

    /* BAHAGIAN BAWAH (VIEW SITE & LOGOUT) */
    .sidebar-bottom {
        padding: 20px;
        border-top: 1px solid #333;
        background-color: #111;
    }

    .sidebar-bottom a {
        display: flex;
        align-items: center;
        color: #aaa;
        text-decoration: none;
        padding: 12px 10px;
        font-size: 15px;
        transition: 0.3s;
        border-radius: 4px;
        margin-bottom: 5px;
    }

    .sidebar-bottom a i {
        width: 30px;
        text-align: center;
        margin-right: 10px;
    }

    .sidebar-bottom a:hover {
        background-color: #222;
        color: white;
    }

    /* KHAS UNTUK LOGOUT MERAH */
    .sidebar-bottom a.logout {
        color: #e50914; /* Merah */
        font-weight: bold;
    }
    .sidebar-bottom a.logout:hover {
        background-color: rgba(229, 9, 20, 0.1);
        color: #ff4f5e;
    }

    /* PENTING: Untuk content sebelah kanan supaya tak tertutup */
    .main-content {
        margin-left: 250px;
        padding: 20px;
    }
</style>