<?php

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $firstName = $_SESSION['firstName'];
    $firstInitial = strtoupper(substr($firstName, 0, 1));

    $accType = $_SESSION['acc_type'] ?? 'passenger';
    if ($accType === 'company') {
    } else {
    }
    
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

                    <!-- Favorite/Heart Icon - Only show for passengers -->
                    <?php if ($accType === 'passenger'): ?>
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
                    <?php endif; ?>

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
    <?php if ($accType === 'passenger'): ?>
    <script src="Web/scripts/favorite.js?v=1.0"></script>
    <?php endif; ?>
    <script>
    // Optimized Message Notification System for Company Dashboard
    class MessageNotificationSystem {
        constructor() {
            this.socket = null;
            this.isConnected = false;
            this.isAuthenticated = false;
            this.userId = null;
            this.userType = null;
            this.conversations = [];
            this.totalUnread = 0;
            this.refreshInterval = null;
            this.reconnectAttempts = 0;
            this.maxReconnectAttempts = 5;

            this.init();
        }

        init() {
            this.userId = window.userId || <?php echo json_encode($_SESSION['user_id'] ?? 0); ?>;

            if (!this.userId || this.userId <= 0) {
                console.error('User ID not available for message system');
                return;
            }

            console.log('Initializing message system for user:', this.userId);

            this.connectWebSocket();
            this.loadConversations();

            // Set up periodic refresh (every 30 seconds)
            this.refreshInterval = setInterval(() => this.loadConversations(), 30000);

            this.setupEventListeners();
        }

        connectWebSocket() {
            if (this.socket?.readyState === WebSocket.OPEN) return;

            try {
                this.socket = new WebSocket('ws://localhost:8081');

                this.socket.onopen = () => {
                    console.log('Connected to message notification WebSocket');
                    this.isConnected = true;
                    this.reconnectAttempts = 0;
                    this.authenticate();
                };

                this.socket.onmessage = (event) => {
                    try {
                        const data = JSON.parse(event.data);
                        this.handleWebSocketMessage(data);
                    } catch (error) {
                        console.error('Error parsing WebSocket message:', error);
                    }
                };

                this.socket.onclose = () => {
                    console.log('Disconnected from message notification WebSocket');
                    this.isConnected = false;
                    this.isAuthenticated = false;
                    this.handleReconnect();
                };

                this.socket.onerror = (error) => console.error('WebSocket error:', error);

            } catch (error) {
                console.error('Failed to connect to WebSocket:', error);
            }
        }

        authenticate() {
            if (!this.isConnected || !this.userId) return;

            this.socket.send(JSON.stringify({
                type: 'auth',
                token: `user_${this.userId}`,
                user_id: parseInt(this.userId)
            }));
        }

        handleWebSocketMessage(data) {
            switch (data.type) {
                case 'auth_success':
                    this.isAuthenticated = true;
                    console.log('Message system authenticated');
                    break;

                case 'new_message':
                    console.log('New message received, refreshing conversations');
                    this.loadConversations();
                    this.showBrowserNotification(data.message);
                    break;

                case 'error':
                    console.error('WebSocket error:', data.message);
                    break;
            }
        }

        handleReconnect() {
            if (this.reconnectAttempts < this.maxReconnectAttempts) {
                this.reconnectAttempts++;
                console.log(`Reconnecting to message system... Attempt ${this.reconnectAttempts}`);
                setTimeout(() => this.connectWebSocket(), 2000 * this.reconnectAttempts);
            }
        }

        async loadConversations() {
            try {
                const response = await fetch('Web/php/chat/api/get_conversations.php', {
                    method: 'GET',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                if (data.success) {
                    this.conversations = data.conversations || [];
                    this.totalUnread = data.total_unread || 0;
                    this.userType = data.user_type;

                    console.log('Loaded conversations:', this.conversations.length, 'Total unread:', this
                        .totalUnread);

                    this.updateEnvelopeBadge();
                    this.renderConversations();
                } else {
                    console.error('Failed to load conversations:', data.error);
                }
            } catch (error) {
                console.error('Error loading conversations:', error);
                this.showErrorInDropdown('Failed to load messages');
            }
        }

        updateEnvelopeBadge() {
            const badge = document.getElementById('envelope-badge');
            const icon = document.getElementById('envelope-icon');
            const markAllBtn = document.getElementById('envelope-mark-all-btn');

            if (badge) {
                badge.textContent = this.totalUnread;
                badge.style.display = this.totalUnread > 0 ? 'block' : 'none';
            }

            if (icon) {
                icon.className = this.totalUnread > 0 ? 'fas fa-envelope' : 'far fa-envelope';
                icon.style.color = this.totalUnread > 0 ? '#007bff' : '';
            }

            if (markAllBtn) {
                markAllBtn.style.display = this.totalUnread > 0 ? 'block' : 'none';
            }
        }

        renderConversations() {
            const container = document.getElementById('envelope-list');
            const loading = document.getElementById('loading-envelope');

            if (!container) return;

            if (loading) loading.style.display = 'none';

            if (this.conversations.length === 0) {
                container.innerHTML = `
                <div class="envelope-empty">
                    <i class="far fa-envelope-open"></i>
                    <p>No messages yet</p>
                </div>
            `;
                return;
            }

            container.innerHTML = this.conversations.map(conversation => {
                const timeAgo = this.formatTimeAgo(conversation.last_message_time);
                const unreadClass = conversation.has_unread ? 'unread' : '';
                const unreadBadge = conversation.unread_count > 0 ?
                    `<span class="conversation-unread-badge">${conversation.unread_count}</span>` : '';

                return `
                <div class="envelope-item ${unreadClass}" data-conversation-id="${conversation.id}">
                    <div class="envelope-item-content" onclick="openConversationChat(${conversation.id}, ${conversation.service_id})">
                        <div class="envelope-item-header">
                            <div class="envelope-sender">
                                <strong>${this.escapeHtml(conversation.other_participant_name)}</strong>
                                ${unreadBadge}
                            </div>
                            <div class="envelope-time">${timeAgo}</div>
                        </div>
                        <div class="envelope-service">
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt"></i>
                                ${this.escapeHtml(conversation.service_name)} - ${this.escapeHtml(conversation.service_location)}
                            </small>
                        </div>
                        <div class="envelope-preview">
                            <small class="text-muted">
                                <i class="fas fa-user"></i>
                                ${conversation.has_unread ? 'New message' : 'Last message'} from: ${this.escapeHtml(conversation.other_participant_name)}
                            </small>
                        </div>
                    </div>
                    <div class="envelope-actions">
                        <button class="envelope-action-btn" onclick="markConversationAsRead(${conversation.id})" 
                                title="Mark as read" ${!conversation.has_unread ? 'disabled' : ''}>
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
            `;
            }).join('');
        }

        showErrorInDropdown(message) {
            const container = document.getElementById('envelope-list');
            const loading = document.getElementById('loading-envelope');

            if (loading) loading.style.display = 'none';

            if (container) {
                container.innerHTML = `
                <div class="envelope-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>${this.escapeHtml(message)}</p>
                    <button onclick="messageSystem.loadConversations()" class="retry-btn">
                        <i class="fas fa-retry"></i> Retry
                    </button>
                </div>
            `;
            }
        }

        async markConversationAsRead(conversationId) {
            try {
                const response = await fetch('Web/php/chat/api/mark_read.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                if (data.success) {
                    console.log('Marked conversation as read:', conversationId);
                    this.updateConversationUI(conversationId);
                    this.loadConversations();
                    return true;
                } else {
                    throw new Error(data.error || 'Failed to mark as read');
                }
            } catch (error) {
                console.error('Error marking conversation as read:', error);
                throw error;
            }
        }

        updateConversationUI(conversationId) {
            const conversationElement = document.querySelector(`[data-conversation-id="${conversationId}"]`);
            if (conversationElement) {
                conversationElement.classList.remove('unread');

                const unreadBadge = conversationElement.querySelector('.conversation-unread-badge');
                if (unreadBadge) unreadBadge.remove();

                const markReadBtn = conversationElement.querySelector('.envelope-action-btn');
                if (markReadBtn) markReadBtn.disabled = true;
            }
        }

        async markAllAsRead() {
            try {
                const response = await fetch('Web/php/chat/api/mark_all_read.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                if (data.success) {
                    console.log('Marked all conversations as read');
                    showNotification(`Marked ${data.marked_count} conversations as read`, 'success');
                    this.loadConversations();
                } else {
                    throw new Error(data.error || 'Failed to mark all as read');
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
                showNotification('Failed to mark all messages as read', 'error');
            }
        }

        showBrowserNotification(message) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('New Message', {
                    body: message.message,
                    icon: '/path/to/your/icon.png',
                    tag: 'chat-message'
                });
            }
        }

        requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        }

        formatTimeAgo(dateString) {
            if (!dateString) return '';

            const now = new Date();
            const messageTime = new Date(dateString);
            const diff = now - messageTime;

            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;

            return messageTime.toLocaleDateString();
        }

        escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        setupEventListeners() {
            document.addEventListener('click', () => this.requestNotificationPermission(), {
                once: true
            });
        }

        destroy() {
            if (this.refreshInterval) clearInterval(this.refreshInterval);
            if (this.socket) this.socket.close();
        }
    }

    // Global functions for envelope dropdown
    function toggleEnvelopeNotifications(event) {
        event.preventDefault();
        const dropdown = document.getElementById('envelope-dropdown');

        if (dropdown.style.display === 'block') {
            closeEnvelopeNotifications();
        } else {
            dropdown.style.display = 'block';
            if (window.messageSystem) {
                window.messageSystem.loadConversations();
            }
        }
    }

    function closeEnvelopeNotifications() {
        const dropdown = document.getElementById('envelope-dropdown');
        if (dropdown) dropdown.style.display = 'none';
    }

    function refreshEnvelopeNotifications() {
        if (window.messageSystem) {
            const loading = document.getElementById('loading-envelope');
            if (loading) loading.style.display = 'block';
            window.messageSystem.loadConversations();
        }
    }

    function markAllEnvelopeAsRead() {
        if (window.messageSystem) {
            window.messageSystem.markAllAsRead();
        }
    }

    function markConversationAsRead(conversationId) {
        if (window.messageSystem) {
            window.messageSystem.markConversationAsRead(conversationId);
        }
    }

    // Function to open conversation chat and mark as read
    async function openConversationChat(conversationId, serviceId) {
        try {
            await markConversationAsRead(conversationId);
            window.location.href = `/chat?conversation_id=${conversationId}&service_id=${serviceId}`;
        } catch (error) {
            console.error('Error opening conversation:', error);
            showNotification('Failed to open conversation. Please try again.', 'error');
        }
    }

    // Function to show notifications to users
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const envelopeContainer = document.querySelector('.envelope-container');
        if (envelopeContainer && !envelopeContainer.contains(event.target)) {
            closeEnvelopeNotifications();
        }
    });

    // Initialize the message system when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const userId = <?php echo json_encode($_SESSION['user_id'] ?? 0); ?>;
        if (userId && userId > 0) {
            window.messageSystem = new MessageNotificationSystem();
            console.log('Message notification system initialized');
        }
    });

    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        if (window.messageSystem) {
            window.messageSystem.destroy();
        }
    });
    </script>

</body>

</html>