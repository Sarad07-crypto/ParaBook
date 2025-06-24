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
    <title>Company Dashboard</title>
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
        width: 400px;
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

    .notification-icon.booking_created {
        background: rgba(0, 123, 255, 0.1);
        color: #007BFF;
    }

    .notification-icon.booking_approved {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .notification-icon.booking_cancelled {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .notification-icon.payment_received {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
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

    .notification-details {
        font-size: 12px;
        color: #666;
        margin: 4px 0;
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

    .notification-priority {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        margin-left: 8px;
    }

    .priority-high {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .priority-medium {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .priority-low {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
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
                                <h3>Company Notifications</h3>
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
            <a href="#">Flight Management</a>
            <a href="/bookingcompany">Bookings</a>
            <a href="/statistics">Analytics</a>
            <a href="#">Revenue</a>
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
            <a href="#">Flight Management</a>
            <a href="#">Bookings</a>
            <a href="#">Analytics</a>
            <a href="#">Revenue</a>
        </div>
        <div class="sidebar-section">
            <a href="#" style="text-decoration: none;">Notifications</a><br>
            <a href="#" style="text-decoration: none;">Messages</a><br>
            <a href="#" style="text-decoration: none;">Reports</a><br>
            <a href="#" style="text-decoration: none;">Support</a>
            <span class="switch-text">Company Dashboard</span>
            <button class="logout-btn" onclick="window.location.href='/logout'">Log out</button>
        </div>
    </div>

    <script src="/Web/scripts/views.js?v=1.0"></script>
    <script>
    // Company Notification System JavaScript - Fixed Version
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

    // Load company notifications from API
    async function loadNotifications() {
        const loadingElement = document.getElementById('loading-notifications');
        const listElement = document.getElementById('notification-list');

        try {
            loadingElement.style.display = 'block';

            // Use consistent API endpoint
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

    // Render company notifications in the dropdown
    function renderNotifications() {
        const listElement = document.getElementById('notification-list');
        const markAllBtn = document.getElementById('mark-all-btn');

        if (notifications.length === 0) {
            listElement.innerHTML = `
            <div class="no-notifications">
                <i class="fas fa-plane"></i>
                <p>No new bookings or notifications</p>
                <small>You'll see customer bookings and updates here</small>
            </div>
        `;
            markAllBtn.style.display = 'none';
            return;
        }

        const hasUnread = notifications.some(n => n.is_read == 0);
        markAllBtn.style.display = hasUnread ? 'inline-block' : 'none';

        const notificationHTML = notifications.map(notification => {
            const isUnread = notification.is_read == 0;
            const iconClass = getCompanyNotificationIcon(notification.type);
            const timeAgo = formatRelativeTime(notification.created_at);
            const priority = getPriorityClass(notification.priority);

            return `
            <div class="notification-item ${isUnread ? 'unread' : ''}" 
                 data-notification-id="${notification.id}"
                 onclick="markAsRead(${notification.id})">
                <div class="notification-content">
                    <div class="notification-icon ${notification.type}">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="notification-text">
                        <p class="notification-message">
                            ${notification.message}
                            ${priority ? `<span class="notification-priority ${priority}">${notification.priority}</span>` : ''}
                        </p>
                        ${notification.booking_reference ? `<div class="notification-details">Booking: ${notification.booking_reference}</div>` : ''}
                        ${notification.customer_name ? `<div class="notification-details">Customer: ${notification.customer_name}</div>` : ''}
                        ${notification.flight_details ? `<div class="notification-details">${notification.flight_details}</div>` : ''}
                        <span class="notification-time">${timeAgo}</span>
                    </div>
                </div>
            </div>
        `;
        }).join('');

        listElement.innerHTML = notificationHTML;
    }

    // Get icon based on company notification type
    function getCompanyNotificationIcon(type) {
        switch (type) {
            case 'booking_created':
                return 'fas fa-plus-circle';
            case 'booking_approved':
                return 'fas fa-check-circle';
            case 'booking_cancelled':
                return 'fas fa-times-circle';
            case 'payment_received':
                return 'fas fa-credit-card';
            case 'payment_pending':
                return 'fas fa-clock';
            case 'customer_inquiry':
                return 'fas fa-question-circle';
            case 'flight_update':
                return 'fas fa-plane';
            case 'system':
                return 'fas fa-info-circle';
            case 'review_received':
                return 'fas fa-star';
            default:
                return 'fas fa-bell';
        }
    }

    // Get priority class for styling
    function getPriorityClass(priority) {
        if (!priority) return '';

        switch (priority.toLowerCase()) {
            case 'high':
                return 'priority-high';
            case 'medium':
                return 'priority-medium';
            case 'low':
                return 'priority-low';
            default:
                return '';
        }
    }

    // Mark notification as read - FIXED to use consistent API endpoint
    async function markAsRead(notificationId) {
        try {
            // Use the same API endpoint as other functions for consistency
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

                // Update UI immediately
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread');
                }

                // Update badge count immediately
                updateNotificationBadge();

                // Hide mark all button if no unread notifications
                const hasUnread = notifications.some(n => n.is_read == 0);
                const markAllBtn = document.getElementById('mark-all-btn');
                if (markAllBtn) {
                    markAllBtn.style.display = hasUnread ? 'inline-block' : 'none';
                }

                console.log('Notification marked as read successfully');
            } else {
                console.error('Failed to mark notification as read:', data.message || 'Unknown error');
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Mark all notifications as read - FIXED to use consistent API endpoint
    async function markAllAsRead() {
        try {
            // Use the same API endpoint for consistency
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

                // Update badge count
                updateNotificationBadge();

                // Hide mark all button
                const markAllBtn = document.getElementById('mark-all-btn');
                if (markAllBtn) {
                    markAllBtn.style.display = 'none';
                }

                showToast('All notifications marked as read', 'success');
            } else {
                showToast('Error updating notifications', 'error');
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
        if (!badge) return;

        const unreadCount = notifications.filter(n => n.is_read == 0).length;

        console.log('Updating badge - Unread count:', unreadCount); // Debug log

        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            badge.classList.add('show');
        } else {
            badge.classList.remove('show');
            badge.textContent = '0';
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

    // FIXED: Load initial notification count on page load
    async function loadNotificationCount() {
        try {
            console.log('Loading notification count...'); // Debug log

            // Use consistent API endpoint
            const response = await fetch('Web/php/AJAX/bookingNotificationAPI.php?action=unread_count');
            const data = await response.json();

            console.log('Notification count response:', data); // Debug log

            if (data.success) {
                const badge = document.getElementById('notification-badge');
                const unreadCount = data.unread_count || 0;

                console.log('Unread count from API:', unreadCount); // Debug log

                if (unreadCount > 0) {
                    badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    badge.classList.add('show');
                } else {
                    badge.classList.remove('show');
                    badge.textContent = '0';
                }
            } else {
                console.error('API returned error:', data.message || 'Unknown error');
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

    // FIXED: Load notification count immediately when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing notifications...'); // Debug log

        // Load notification count immediately
        setTimeout(() => {
            loadNotificationCount();
        }, 100); // Small delay to ensure all elements are rendered
    });

    // Also load on window load as backup
    window.addEventListener('load', function() {
        console.log('Window loaded, loading notification count...'); // Debug log
        loadNotificationCount();
    });

    // Real-time notification listener (if you implement WebSocket or similar)
    function startNotificationListener() {
        // This would connect to your real-time notification system
        // For example, using WebSocket or Server-Sent Events
        // When a new booking is made, this would trigger loadNotificationCount()
    }

    // Initialize real-time notifications
    // startNotificationListener();
    </script>
</body>

</html>