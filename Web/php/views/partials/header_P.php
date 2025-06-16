<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="/Web/css/views.css" />
</head>

<body>
    <header class="header">
        <div class="top-bar">
            <!-- Hamburger menu for mobile -->
            <div class="hamburger" onclick="openSidebar()">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <!-- Desktop ParaBook logo -->
            <div class="logo">ParaBook</div>
            <!-- Responsive ParaBook logo in the center for mobile -->
            <div class="responsive-logo">Parabook</div>
            <div class="search-icon-mobile" onclick="toggleMobileSearch()">
                <i class="fas fa-search"></i>
            </div>
            <div class="mobile-search-box" id="mobile-search-box">
                <input type="text" placeholder="Search..."
                    style="border:none;outline:none;flex:1;font-size:14px;background:transparent;">
            </div>
            <div class="top-bar-content" style="display: flex; flex: 1; align-items: center;">
                <div class="search-container">
                    <div class="search-box">
                        <input type="text" placeholder="looking for any company today ?" />
                        <button><i class="fas fa-search"></i></button>
                    </div>
                    <div class="darkmode">
                        <a href="#"><i class="fas fa-moon" style="color:#007BFF;"></i></a>
                    </div>
                </div>
                <div class="right-section">
                    <a href="#"><i class="fas fa-bell"></i></a>
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-heart"></i></a>
                    <a href="#"><i class="fas fa-headphones"></i></a>
                    <span class="switch-text">Switch to passenger</span>
                    <div class="avatar"><img src="<?php echo $avatar ?>" alt="image not found"></div>
                </div>
            </div>
        </div>
        </div>
        <!-- Navigation Bar -->
        <div class="nav-bar">
            <a href="#">Dashboard</a>
            <a href="#">Bookings</a>
            <a href="#">Statistics</a>
        </div>
    </header>

    <button class="logout-btn" onclick="window.location.href='/logout'">Log out</button>
    <!-- Blur overlay for sidebar -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <!-- Sidebar for mobile -->
    <div class="sidebar" id="sidebar">
        <button class="close-btn" onclick="closeSidebar()">&times;</button>
        <!-- Avatar at the top -->
        <div class="avatar" style="margin-bottom: 24px; margin-top: 10px; align-self: center;"><img
                src="<?php echo $avatar ?>">
        </div>
        <div class="sidebar-section sidebar-links">
            <a href="#">Dashboard</a>
            <a href="#">Bookings</a>
            <a href="#">Flights</a>
            <a href="#">Services</a>
            <a href="#">Statistics</a>
        </div>
        <div class="sidebar-section">
            <a href="#" style="text-decoration: none;">Notfication</a><br>
            <a href="#" style="text-decoration: none;">Mail</a><br>
            <a href="#" style="text-decoration: none;">Favourites</a><br>
            <a href="#" style="text-decoration: none;">Contact</a>
            <span class="switch-text">Switch to passenger</span>
            <button class="logout-btn" onclick="window.location.href='/logout'">Log out</button>
        </div>
    </div>
    <script src="/Web/scripts/views.js"></script>
</body>

</html>