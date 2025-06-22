<?php
    session_start();
    $firstName = $_SESSION['firstName'];
    $firstInitial = strtoupper(substr($firstName, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title></title>
    <link rel="stylesheet" href="Web/css/views.css?v=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
                    <div class="darkmode" onclick="darkModeToggle()">
                        <button><i class="fas fa-moon" style="color:#007BFF;"></i></button>
                    </div>
                </div>
                <div class="right-section">
                    <a href="#" class="notif-dropdown-trigger" onclick="toggleNotifDropdown(event)">
                        <i class="fas fa-bell"></i>
                    </a>
                    <div id="notifDropdown" class="notif-dropdown-menu">
                        <div class="notif-dropdown-header">
                            <strong>Notifications</strong>
                            <span class="notif-dropdown-close" onclick="closeNotifDropdown()">&times;</span>
                        </div>
                        <div class="notif-dropdown-list">
                            <div class="notif-dropdown-item notif-unread">
                                <i class="fas fa-user notif-icon"></i>
                                <div>
                                    <span><b>John Doe</b> booked a flight with you.</span>
                                    <div class="notif-time">2m ago</div>
                                </div>
                            </div>
                            <div class="notif-dropdown-item">
                                <i class="fas fa-plane notif-icon"></i>
                                <div>
                                    <span>Your flight is scheduled for tomorrow.</span>
                                    <div class="notif-time">1h ago</div>
                                </div>
                            </div>
                            <!-- Add more notifications as needed -->
                        </div>
                    </div>
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-heart"></i></a>
                    <a href="#"><i class="fas fa-headphones"></i></a>
                    <span class="switch-text">Complete your profile</span>
                    <div class="avatar-dropdown">
                        <div class="avatar" onclick="toggleDropdown()">
                            <img src="<?php echo $avatar ?>" alt="image not found"
                                onerror="showInitial(this, '<?php echo $firstInitial ?>')">
                        </div>
                        <div id="dropdownMenu" class="dropdown-menu">
                            <div class="dropdown-header">
                                <strong>
                                    <?php echo htmlspecialchars($_SESSION['firstName']) . " " . htmlspecialchars($_SESSION['lastName']); ?>
                                </strong>
                            </div>
                            <a href="/profile"><i class="fa fa-user"></i> Profile</a>
                            <a href="/settings"><i class="fa fa-cog"></i> Settings</a>
                            <a href="/help"><i class="fa fa-question-circle"></i> Help & Support</a>
                            <form action="/logout" method="post">
                                <button type="submit" class="logout-btn"><i class="fa fa-sign-out"></i> Log Out</button>
                            </form>
                        </div>
                    </div>
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
    <script src="/Web/scripts/views.js?v=1.0"></script>
</body>

</html>