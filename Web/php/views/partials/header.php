<?php
    // session_start();
    $firstName = $_SESSION['firstName'];
    $firstInitial = strtoupper(substr($firstName, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title></title>
    <!-- <link rel="stylesheet" href="Web/css/header.css?v=1.0" /> -->
    <link rel="stylesheet" href="Web/css/notification.css?v=1.0" />
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
            <div class="logo"><a href="/home" style="text-decoration: none; color: inherit;">ParaBook</a></div>
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
                        <input type="text" placeholder="Search bookings, customers..." />
                        <button><i class="fas fa-search"></i></button>
                    </div>
                    <div class="darkmode" onclick="darkModeToggle()">
                        <button><i class="fas fa-moon" style="color:#007BFF;"></i></button>
                    </div>
                </div>
                <div class="right-section">
                    <!-- Company Notification Bell -->
                    <div class="notification-container">
                        <a href="#" class="notification-bell" id="notification-bell"
                            onclick="toggleNotifications(event)">
                            <i class="far fa-bell" id="bell-icon"></i>
                            <span class="notification-badge" id="notification-badge">0</span>
                        </a>

                        <div class="notification-dropdown" id="notification-dropdown">
                            <div class="notification-header">
                                <h3>Notifications</h3>
                                <div class="notification-header-right">
                                    <div class="notification-actions">
                                        <button class="refresh-btn" onclick="refreshNotifications()" title="Refresh">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button class="mark-all-btn" id="mark-all-btn" onclick="markAllAsRead()"
                                            style="display: none;">
                                            Mark all as read
                                        </button>
                                    </div>
                                    <button class="close-notification" onclick="closeNotifications()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="notification-list" id="notification-list">
                                <div class="loading-notifications" id="loading-notifications">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading notifications...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message/Envelope Icon -->
                    <div class="envelope-container">
                        <a href="#" class="envelope-bell" id="envelope-bell"
                            onclick="toggleEnvelopeNotifications(event)">
                            <i class="far fa-envelope" id="envelope-icon"></i>
                            <span class="envelope-badge" id="envelope-badge">0</span>
                        </a>

                        <div class="envelope-dropdown" id="envelope-dropdown">
                            <div class="envelope-header">
                                <h3>Messages</h3>
                                <div class="envelope-header-right">
                                    <div class="envelope-actions">
                                        <button class="envelope-refresh-btn" onclick="refreshEnvelopeNotifications()"
                                            title="Refresh">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button class="envelope-mark-all-btn" id="envelope-mark-all-btn"
                                            onclick="markAllEnvelopeAsRead()" style="display: none;">
                                            Mark all as read
                                        </button>
                                    </div>
                                    <button class="close-envelope" onclick="closeEnvelopeNotifications()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>


                            <div class="envelope-list" id="envelope-list">
                                <div class="loading-envelope" id="loading-envelope">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading messages...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Favorite/Heart Icon -->
                    <div class="heart-container">
                        <a href="#" class="heart-bell" id="heart-bell" onclick="toggleHeartNotifications(event)">
                            <i class="far fa-heart" id="heart-icon"></i>
                            <span class="heart-badge" id="heart-badge">0</span>
                        </a>

                        <div class="heart-dropdown" id="heart-dropdown">
                            <div class="heart-header">
                                <h3>Favorites</h3>
                                <div class="heart-header-right">
                                    <div class="heart-actions">
                                        <button class="heart-refresh-btn" onclick="refreshHeartNotifications()"
                                            title="Refresh">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button class="heart-clear-btn" id="heart-clear-btn"
                                            onclick="clearAllFavorites()" style="display: none;">
                                            Clear all
                                        </button>
                                    </div>
                                    <button class="close-heart" onclick="closeHeartNotifications()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>


                            <div class="heart-list" id="heart-list">
                                <div class="loading-heart" id="loading-heart">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading favorites...</p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Headphones Icon -->
                    <a href="" onclick="toggleIcon(this.querySelector('i'), 'headphones')">
                        <i class="fa-solid fa-headphones"></i>
                    </a>

                    <a class="complete-profile" href="/completeProfile">Complete your profile</a>
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
                                <button type="submit" class="logout-btn"><i class="fa fa-sign-out"></i> Log out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script src="Web/scripts/notification.js?v=1.0"></script>

</body>

</html>