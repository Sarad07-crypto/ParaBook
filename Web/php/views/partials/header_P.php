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
    <style>
    /* Notification Styles */
    .notification-container {
        position: relative;
        display: inline-block;
    }

    .notification-bell {
        position: relative;
        color: #666;
        font-size: 18px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .notification-bell:hover {
        color: #007BFF;
    }

    .notification-bell.active {
        color: #007BFF;
    }

    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #ff4757;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 11px;
        display: none;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .notification-badge.show {
        display: flex;
    }

    .notification-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        width: 380px;
        max-width: 90vw;
        background: white;
        border: 1px solid #e1e8ed;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        max-height: 500px;
        overflow: hidden;
    }

    .notification-dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .notification-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e1e8ed;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
        border-radius: 12px 12px 0 0;
    }

    .notification-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }

    .notification-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .mark-all-btn,
    .refresh-btn {
        background: none;
        border: none;
        color: #007BFF;
        font-size: 12px;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background 0.2s;
    }

    .mark-all-btn:hover,
    .refresh-btn:hover {
        background: rgba(0, 123, 255, 0.1);
    }

    .notification-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f3f4;
        cursor: pointer;
        transition: background 0.2s;
        position: relative;
    }

    .notification-item:hover {
        background: #f8f9fa;
    }

    .notification-item.unread {
        background: #f0f8ff;
        border-left: 4px solid #007BFF;
    }

    .notification-item.unread::before {
        content: '';
        position: absolute;
        top: 20px;
        right: 20px;
        width: 8px;
        height: 8px;
        background: #007BFF;
        border-radius: 50%;
    }

    .notification-content {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }

    .notification-icon.booking {
        background: rgba(0, 123, 255, 0.1);
        color: #007BFF;
    }

    .notification-icon.status {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .notification-icon.system {
        background: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }

    .notification-text {
        flex: 1;
    }

    .notification-message {
        font-size: 14px;
        color: #333;
        margin: 0 0 4px 0;
        line-height: 1.4;
    }

    .notification-time {
        font-size: 12px;
        color: #6c757d;
    }

    .no-notifications {
        padding: 40px 20px;
        text-align: center;
        color: #6c757d;
    }

    .no-notifications i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .loading-notifications {
        padding: 20px;
        text-align: center;
        color: #6c757d;
    }

    .close-notification {
        position: absolute;
        top: 16px;
        right: 16px;
        background: none;
        border: none;
        font-size: 20px;
        color: #6c757d;
        cursor: pointer;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }

    .close-notification:hover {
        background: rgba(0, 0, 0, 0.1);
    }
    </style>
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
                    <!-- Notification Bell -->
                    <div class="notification-container">
                        <a href="#" class="notification-bell" id="notification-bell"
                            onclick="toggleNotifications(event)">
                            <i class="far fa-bell" id="bell-icon"></i>
                            <span class="notification-badge" id="notification-badge">0</span>
                        </a>

                        <div class="notification-dropdown" id="notification-dropdown">
                            <div class="notification-header">
                                <h3>Notifications</h3>
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

                            <div class="notification-list" id="notification-list">
                                <div class="loading-notifications" id="loading-notifications">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading notifications...</p>
                                </div>
                            </div>
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
            <a href="#" style="text-decoration: none;">Notification</a><br>
            <a href="#" style="text-decoration: none;">Mail</a><br>
            <a href="#" style="text-decoration: none;">Favourites</a><br>
            <a href="#" style="text-decoration: none;">Contact</a>
            <span class="switch-text">Switch to passenger</span>
            <button class="logout-btn" onclick="window.location.href='/logout'">Log out</button>
        </div>
    </div>

    <script src="/Web/scripts/views.js?v=1.0"></script>
    <script>
    // Notification System JavaScript
    let notificationsVisible = false;
    let notifications = [];

    // Toggle notifications dropdown
    function toggleNotifications(event) {
        event.preventDefault();

        const dropdown = document.getElementById('notification-dropdown');
        const bellIcon = document.getElementById('bell-icon');
        const bell = document.getElementById('notification-bell');

        if (notificationsVisible) {
            closeNotifications();
        } else {
            openNotifications();
        }
    }

    function openNotifications() {
        const dropdown = document.getElementById('notification-dropdown');
        const bellIcon = document.getElementById('bell-icon');
        const bell = document.getElementById('notification-bell');

        dropdown.classList.add('show');
        bellIcon.className = 'fas fa-bell'; // Switch to filled bell
        bell.classList.add('active');
        notificationsVisible = true;

        // Load notifications when opening
        loadNotifications();
    }

    function closeNotifications() {
        const dropdown = document.getElementById('notification-dropdown');
        const bellIcon = document.getElementById('bell-icon');
        const bell = document.getElementById('notification-bell');

        dropdown.classList.remove('show');
        bellIcon.className = 'far fa-bell'; // Switch back to outline bell
        bell.classList.remove('active');
        notificationsVisible = false;
    }

    // Load notifications from API
    async function loadNotifications() {
        const loadingElement = document.getElementById('loading-notifications');
        const listElement = document.getElementById('notification-list');

        try {
            loadingElement.style.display = 'block';

            const response = await fetch('Web/php/AJAX/bookingNotificationAPI.php?action=get&limit=20');
            const data = await response.json();

            loadingElement.style.display = 'none';

            if (data.success) {
                notifications = data.notifications || [];
                renderNotifications();
                updateNotificationBadge();
            } else {
                showError('Failed to load notifications');
            }
        } catch (error) {
            loadingElement.style.display = 'none';
            console.error('Error loading notifications:', error);
            showError('Error loading notifications');
        }
    }

    // Render notifications in the dropdown
    function renderNotifications() {
        const listElement = document.getElementById('notification-list');
        const markAllBtn = document.getElementById('mark-all-btn');

        if (notifications.length === 0) {
            listElement.innerHTML = `
                    <div class="no-notifications">
                        <i class="far fa-bell"></i>
                        <p>No notifications yet</p>
                    </div>
                `;
            markAllBtn.style.display = 'none';
            return;
        }

        const hasUnread = notifications.some(n => n.is_read == 0);
        markAllBtn.style.display = hasUnread ? 'inline-block' : 'none';

        const notificationHTML = notifications.map(notification => {
            const isUnread = notification.is_read == 0;
            const iconClass = getNotificationIcon(notification.type);
            const timeAgo = formatRelativeTime(notification.created_at);

            return `
                    <div class="notification-item ${isUnread ? 'unread' : ''}" 
                         data-notification-id="${notification.id}"
                         onclick="markAsRead(${notification.id})">
                        <div class="notification-content">
                            <div class="notification-icon ${notification.type}">
                                <i class="${iconClass}"></i>
                            </div>
                            <div class="notification-text">
                                <p class="notification-message">${notification.message}</p>
                                <span class="notification-time">${timeAgo}</span>
                            </div>
                        </div>
                    </div>
                `;
        }).join('');

        listElement.innerHTML = notificationHTML;
    }

    // Get icon based on notification type
    function getNotificationIcon(type) {
        switch (type) {
            case 'booking_created':
            case 'booking':
                return 'fas fa-plane';
            case 'booking_approved':
            case 'booking_confirmed':
                return 'fas fa-check-circle';
            case 'booking_rejected':
            case 'booking_cancelled':
                return 'fas fa-times-circle';
            case 'payment':
                return 'fas fa-credit-card';
            case 'system':
                return 'fas fa-info-circle';
            default:
                return 'fas fa-bell';
        }
    }

    // Mark notification as read
    async function markAsRead(notificationId) {
        try {
            const response = await fetch('Web/php/AJAX/bookingNotificationAPI.php?action=mark_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `notification_id=${notificationId}`
            });

            const data = await response.json();

            if (data.success) {
                // Update notification in local array
                const notification = notifications.find(n => n.id == notificationId);
                if (notification) {
                    notification.is_read = 1;
                }

                // Update UI
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread');
                }

                updateNotificationBadge();

                // Hide mark all button if no unread notifications
                const hasUnread = notifications.some(n => n.is_read == 0);
                const markAllBtn = document.getElementById('mark-all-btn');
                markAllBtn.style.display = hasUnread ? 'inline-block' : 'none';
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Mark all notifications as read
    async function markAllAsRead() {
        try {
            const response = await fetch('Web/php/AJAX/bookingNotificationAPI.php?action=mark_all_read', {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                // Update all notifications in local array
                notifications.forEach(n => n.is_read = 1);

                // Update UI
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });

                updateNotificationBadge();
                document.getElementById('mark-all-btn').style.display = 'none';

                showToast('All notifications marked as read', 'success');
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            showToast('Error updating notifications', 'error');
        }
    }

    // Refresh notifications
    async function refreshNotifications() {
        await loadNotifications();
        showToast('Notifications refreshed', 'success');
    }

    // Update notification badge
    function updateNotificationBadge() {
        const badge = document.getElementById('notification-badge');
        const unreadCount = notifications.filter(n => n.is_read == 0).length;

        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            badge.classList.add('show');
        } else {
            badge.classList.remove('show');
        }
    }

    // Format relative time
    function formatRelativeTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        const diffInMinutes = Math.floor(diffInSeconds / 60);
        const diffInHours = Math.floor(diffInMinutes / 60);
        const diffInDays = Math.floor(diffInHours / 24);

        if (diffInSeconds < 60) {
            return 'Just now';
        } else if (diffInMinutes < 60) {
            return `${diffInMinutes}m ago`;
        } else if (diffInHours < 24) {
            return `${diffInHours}h ago`;
        } else if (diffInDays < 7) {
            return `${diffInDays}d ago`;
        } else {
            return date.toLocaleDateString();
        }
    }

    // Show toast notification
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 6px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                transition: all 0.3s ease;
                max-width: 300px;
            `;

        switch (type) {
            case 'success':
                toast.style.backgroundColor = '#28a745';
                break;
            case 'error':
                toast.style.backgroundColor = '#dc3545';
                break;
            default:
                toast.style.backgroundColor = '#007BFF';
        }

        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    // Show error message
    function showError(message) {
        const listElement = document.getElementById('notification-list');
        listElement.innerHTML = `
                <div class="no-notifications">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${message}</p>
                    <button onclick="loadNotifications()" style="margin-top: 10px; padding: 8px 16px; background: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Try Again
                    </button>
                </div>
            `;
    }

    // Load initial notification count
    async function loadNotificationCount() {
        try {
            const response = await fetch('Web/php/AJAX/bookingNotificationAPI.php?action=unread_count');
            const data = await response.json();

            if (data.success) {
                const badge = document.getElementById('notification-badge');
                const unreadCount = data.unread_count || 0;

                if (unreadCount > 0) {
                    badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    badge.classList.add('show');
                }
            }
        } catch (error) {
            console.error('Error loading notification count:', error);
        }
    }

    // Close notifications when clicking outside
    document.addEventListener('click', function(event) {
        const notificationContainer = document.querySelector('.notification-container');

        if (notificationsVisible && !notificationContainer.contains(event.target)) {
            closeNotifications();
        }
    });

    // Auto-refresh notification count every 30 seconds
    setInterval(loadNotificationCount, 30000);

    // Load notification count on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadNotificationCount();
    });
    </script>
</body>

</html>