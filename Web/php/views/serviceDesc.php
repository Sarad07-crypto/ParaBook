<?php
    
    // Start session first before any output
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    require 'avatar.php';
    require 'Web/php/connection.php';
    
    // Get service_id from URL parameter
    $serviceId = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
    
    // Debug: Check if service_id is being received
    error_log("Service ID from URL: " . $serviceId);
    
    if (!$serviceId) {
        header('Location: /error?message=Invalid service ID');
        exit();
    }
    
    // Store in session
    $_SESSION['service_id'] = $serviceId;

    try {
    // 1. Fetch service details
    $stmt = $connect->prepare("SELECT * FROM company_services WHERE id = ?");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();

    if (!$service) {
    header('Location: /error?message=Service not found');
    exit;
    }

    // 2. Fetch flight types and get minimum price
    $stmt = $connect->prepare("SELECT flight_type_name AS name, price FROM service_flight_types WHERE service_id = ? ORDER
    BY price ASC");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $flightTypes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $minPrice = !empty($flightTypes) ? $flightTypes[0]['price'] : 0;

    // 3. Fetch office photos
    $stmt = $connect->prepare("SELECT photo_path FROM service_office_photos WHERE service_id = ? ORDER BY photo_order ASC
    LIMIT 4");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();

    $officePhotos = [];
    while ($row = $result->fetch_assoc()) {
    $officePhotos[] = $row['photo_path'];
    }

    // If no photos, use default placeholder
    if (empty($officePhotos)) {
    $officePhotos = ['default-service.jpg'];
    }

    } catch (Exception $e) {
    error_log("Error fetching service data: " . $e->getMessage());
    header('Location: /error?message=Error loading service');
    exit;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($service['service_title'] ?? 'Service Details'); ?></title>
    <link rel="stylesheet" href="Web/css/serviceDesc.css?v=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php
        require('partials/header.php');
        $accType = $_SESSION['acc_type'] ?? 'passenger';
        if ($accType === 'company') {
            require('partials/nav_C.php');
        } else {
            require('partials/nav_P.php');
        }
    ?>
    <div class="main-wrap">
        <!-- Left Section -->
        <div class="left-section">
            <div class="company-title">
                <h1>
                    <?php echo htmlspecialchars($service['service_title'] ?? ''); ?></h1>
            </div>
            <div class="profile-row">
                <img src="" alt="Company Logo" class="profile-pic">
                <div class="profile-info">
                    <span class="profile-name"><?php echo htmlspecialchars($service['company_name'] ?? ''); ?></span>
                    <span class="profile-address"><?php echo htmlspecialchars($service['address'] ?? ''); ?></span>
                    <span class="profile-address"><?php echo htmlspecialchars($service['contact'] ?? ''); ?></span>
                </div>
            </div>
            <div class="rating-row">
                <span class="stars">★★★★☆</span>
                <span class="reviews">4.0 (120 reviews)</span>
            </div>
            <div class="highlight">
                <b>Popular choice!</b> This service has excellent customer satisfaction.
            </div>

            <!-- Enhanced Dynamic Image Slider -->
            <div class="slider-area">
                <div class="main-slider">
                    <?php if (count($officePhotos) > 1): ?>
                    <button class="slider-arrow left" onclick="prevScreenshot()" aria-label="Previous">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <?php endif; ?>
                    <img id="mainImage" src="<?php echo htmlspecialchars($officePhotos[0]); ?>" alt="Service Image">
                    <?php if (count($officePhotos) > 1): ?>
                    <button class="slider-arrow right" onclick="nextScreenshot()" aria-label="Next">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <?php endif; ?>
                </div>

                <?php if (count($officePhotos) > 1): ?>
                <div class="thumbs-bar screenshots-list" id="screenshotsList">
                    <?php foreach ($officePhotos as $index => $photo): ?>
                    <img src="<?php echo htmlspecialchars($photo); ?>" alt="Screenshot <?php echo $index + 1; ?>"
                        onclick="selectScreenshot(<?php echo $index; ?>)"
                        class="<?php echo $index === 0 ? 'selected' : ''; ?>">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="desc-section">
                <h2>About This Service</h2>
                <div style="white-space: pre-line;">
                    <?php echo htmlspecialchars($service['service_description'] ?? 'No description available.'); ?>
                </div>

                <?php if (!empty($flightTypes)): ?>
                <h3>Available Options</h3>
                <ul>
                    <?php foreach ($flightTypes as $flightType): ?>
                    <li><?php echo htmlspecialchars($flightType['name']); ?> -
                        Rs.<?php echo htmlspecialchars($flightType['price']); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Section -->
        <div class="right-section">
            <div class="book-box">
                <div class="book-price">Rs.<?php echo htmlspecialchars($minPrice); ?></div>
                <div class="book-desc">
                    <?php echo htmlspecialchars($service['service_title'] ?? 'Service Booking'); ?><br>
                    <span style="font-size:0.98rem;color:rgba(255,255,255,0.8);">Available now • Flexible booking</span>
                </div>
                <!-- Replace the existing book-btn line with this corrected version -->
                <button class="book-btn"
                    onclick="window.location.href='/bookingpassenger?service_id=<?php echo $_SESSION['service_id']; ?>'">Book
                    Now</button>
                <!-- New Chat Button -->
                <button class="chat-btn" onclick="openChatModal()"
                    <?php echo isset($_SESSION['user_id']) ? '' : 'disabled title="Login to chat"'; ?>>
                    <i class="fas fa-comments"></i> Send Message
                </button>
            </div>
        </div>
    </div>

    <!-- Chat Modal -->
    <div id="chatModal" class="chat-modal">
        <div class="chat-container">
            <div class="chat-header">
                <h3>Chat with <?php echo htmlspecialchars($service['company_name'] ?? 'Company'); ?></h3>
                <div class="connection-status-container">
                    <span id="connectionStatus" class="connection-status">Not Connected</span>
                </div>
                <button class="close-chat" onclick="closeChatModal()">&times;</button>
            </div>
            <div class="chat-messages" id="chatMessages">
                <!-- Messages will be loaded here -->
            </div>
            <div id="typingIndicator" class="typing-indicator" style="display: none;">
                Someone is typing...
            </div>
            <div class="chat-input-area">
                <input type="text" id="messageInput" placeholder="Type your message..."
                    onkeypress="handleKeyPress(event)">
                <button onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
    <script>
    // =============================================================================
    // SLIDESHOW FUNCTIONALITY
    // =============================================================================
    const screenshots = <?php echo json_encode($officePhotos); ?>;
    let selected = 0;
    const mainImage = document.getElementById('mainImage');
    const thumbsList = document.getElementById('screenshotsList');
    let sliderInterval;

    function updateScreenshot(idx) {
        if (screenshots.length <= 1) return;

        selected = idx;
        mainImage.style.opacity = 0.6;
        setTimeout(() => {
            mainImage.src = screenshots[idx];
            mainImage.style.opacity = 1;
        }, 120);

        if (thumbsList) {
            const thumbs = thumbsList.children;
            for (let i = 0; i < thumbs.length; i++) {
                thumbs[i].classList.toggle('selected', i === idx);
            }
        }
    }

    function selectScreenshot(idx) {
        updateScreenshot(idx);
        resetSliderInterval();
    }

    function prevScreenshot() {
        if (screenshots.length <= 1) return;
        selected = (selected - 1 + screenshots.length) % screenshots.length;
        updateScreenshot(selected);
        resetSliderInterval();
    }

    function nextScreenshot() {
        if (screenshots.length <= 1) return;
        selected = (selected + 1) % screenshots.length;
        updateScreenshot(selected);
        resetSliderInterval();
    }

    function autoSlide() {
        nextScreenshot();
    }

    function resetSliderInterval() {
        if (screenshots.length <= 1) return;
        clearInterval(sliderInterval);
        sliderInterval = setInterval(autoSlide, 5000);
    }

    // Initialize slideshow
    if (screenshots.length > 1) {
        updateScreenshot(0);
        resetSliderInterval();

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') prevScreenshot();
            if (e.key === 'ArrowRight') nextScreenshot();
        });

        // Pause auto-slide on hover
        const slider = document.querySelector('.main-slider');
        if (slider) {
            slider.addEventListener('mouseenter', () => clearInterval(sliderInterval));
            slider.addEventListener('mouseleave', () => resetSliderInterval());
        }
    }

    // =============================================================================
    // RESPONSIVE LAYOUT
    // =============================================================================
    function handleResize() {
        const bookBox = document.querySelector('.book-box');
        if (!bookBox) return;

        if (window.innerWidth <= 900) {
            bookBox.style.position = 'fixed';
            bookBox.style.bottom = '0';
            bookBox.style.top = 'auto';
            bookBox.style.left = '0';
            bookBox.style.right = '0';
            bookBox.style.width = '100%';
        } else {
            bookBox.style.position = 'fixed';
            bookBox.style.top = '120px';
            bookBox.style.right = '32px';
            bookBox.style.bottom = 'auto';
            bookBox.style.left = 'auto';
            bookBox.style.width = '320px';
        }
    }

    window.addEventListener('resize', handleResize);
    window.addEventListener('load', handleResize);

    // =============================================================================
    // CHAT FUNCTIONALITY
    // =============================================================================
    const serviceId = <?php echo json_encode($_SESSION['service_id'] ?? 0); ?>;
    const userId = <?php echo json_encode($_SESSION['user_id'] ?? 0); ?>;

    class ChatClient {
        constructor() {
            this.socket = null;
            this.isConnected = false;
            this.isAuthenticated = false;
            this.currentConversationId = null;
            this.userId = null;
            this.serviceId = null;
            this.reconnectAttempts = 0;
            this.maxReconnectAttempts = 5;
            this.typingTimer = null;
            this.typingUsers = new Set();
        }

        connect(userId, serviceId) {
            this.userId = userId;
            this.serviceId = serviceId;

            if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                console.log('Already connected to chat server');
                return;
            } else {
                console.log('Connecting to chat server...');
            }

            try {
                this.socket = new WebSocket('ws://localhost:8081');
                this.updateConnectionStatus('Connecting...');

                this.socket.onopen = () => {
                    console.log('Connected to chat server');
                    this.isConnected = true;
                    this.reconnectAttempts = 0;
                    this.authenticate();
                    this.updateConnectionStatus('Connected');
                };

                this.socket.onmessage = (event) => {
                    console.log('Raw event data:', event.data);
                    try {
                        const data = JSON.parse(event.data);
                        console.log('Parsed data:', data);
                        this.handleMessage(data);
                    } catch (error) {
                        console.error('Error parsing message:', error);
                    }
                };

                this.socket.onclose = () => {
                    console.log('Disconnected from chat server');
                    this.isConnected = false;
                    this.isAuthenticated = false;
                    this.updateConnectionStatus('Disconnected');
                    this.handleReconnect();
                };

                this.socket.onerror = (error) => {
                    console.error('WebSocket error:', error);
                    this.updateConnectionStatus('Connection Error');
                };

            } catch (error) {
                console.error('Failed to connect:', error);
                this.updateConnectionStatus('Failed to Connect');
            }
        }

        authenticate() {
            if (!this.isConnected || !this.userId) return;

            console.log('User ID:', this.userId, 'Type:', typeof this.userId);
            console.log('Service ID:', this.serviceId, 'Type:', typeof this.serviceId);

            // Ensure user ID is sent as integer
            const userIdInt = parseInt(this.userId);
            if (isNaN(userIdInt)) {
                console.error('Invalid user ID:', this.userId);
                return;
            }

            const authData = {
                type: 'auth',
                token: `user_${userIdInt}`,
                user_id: userIdInt
            };

            console.log('Sending auth data:', authData);
            this.send(authData);
        }

        send(data) {
            if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                this.socket.send(JSON.stringify(data));
                console.log('WebSocket send:', data);
            } else {
                console.log('WebSocket not connected');
            }
        }

        handleMessage(data) {
            console.log('Received message:', data);

            switch (data.type) {
                case 'system':
                    console.log('System message:', data.message);
                    break;

                case 'auth_success':
                    this.isAuthenticated = true;
                    console.log('Authentication successful');
                    this.updateConnectionStatus('Online');
                    this.createOrJoinConversation();
                    break;

                case 'conversation_joined':
                    console.log('Joined conversation:', data.conversation_id);
                    this.currentConversationId = data.conversation_id;
                    this.displayMessages(data.messages);
                    break;

                case 'new_message':
                    // Enhanced validation for new_message
                    console.log('Processing new_message:', data);

                    if (!data.message) {
                        console.error('Received new_message with missing message property:', data);
                        return;
                    }

                    if (typeof data.message !== 'object') {
                        console.error('Received new_message with invalid message type:', typeof data.message, data);
                        return;
                    }

                    // Validate required message properties
                    const requiredProps = ['id', 'conversation_id', 'sender_user_id', 'message', 'created_at'];
                    const missingProps = requiredProps.filter(prop =>
                        data.message[prop] === null || data.message[prop] === undefined
                    );

                    if (missingProps.length > 0) {
                        console.error('Message missing required properties:', missingProps, data.message);
                        return;
                    }

                    // Additional validation for critical fields
                    if (!Number.isInteger(data.message.sender_user_id) || data.message.sender_user_id <= 0) {
                        console.error('Invalid sender_user_id:', data.message.sender_user_id, data.message);
                        return;
                    }

                    if (typeof data.message.message !== 'string') {
                        console.error('Invalid message content type:', typeof data.message.message, data.message);
                        return;
                    }

                    this.displayNewMessage(data.message);
                    break;

                case 'typing_status':
                    this.handleTypingStatus(data);
                    break;

                case 'error':
                    console.error('Chat error:', data.message);
                    console.error('Full error data:', data);
                    this.updateConnectionStatus('Error');
                    break;

                default:
                    console.warn('Unknown message type:', data.type, data);
                    break;
            }
        }

        createOrJoinConversation() {
            // Validate required data
            if (!this.serviceId || this.serviceId <= 0) {
                console.error('Invalid service ID:', this.serviceId);
                this.updateConnectionStatus('Error: Invalid Service');
                return;
            }

            // Call the updated API - no longer need company_user_id
            fetch('Web/php/chat/api/create_conversation.php', {
                    method: 'POST',
                    credentials: 'include', // Important for session-based auth
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        service_id: this.serviceId
                        // Removed company_user_id - API will find it automatically
                    })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);

                    // Get the response text first to see what we're actually receiving
                    return response.text().then(text => {
                        console.log('Raw response text:', text);

                        if (!response.ok) {
                            throw new Error(
                                `HTTP ${response.status}: ${response.statusText}\nResponse: ${text}`
                            );
                        }

                        // Try to parse as JSON
                        try {
                            return JSON.parse(text);
                        } catch (jsonError) {
                            console.error('JSON Parse Error:', jsonError);
                            console.error('Response was not valid JSON:', text);
                            throw new Error(`Invalid JSON response: ${text.substring(0, 200)}...`);
                        }
                    });
                })
                .then(data => {
                    console.log('API Response:', data);
                    if (data.success) {
                        this.currentConversationId = data.conversation_id;
                        console.log('Conversation ID set to:', this.currentConversationId);

                        // Now connect to WebSocket and join the conversation
                        this.send({
                            type: 'join_conversation',
                            conversation_id: data.conversation_id
                        });

                        // Update UI to show conversation status
                        if (data.existing) {
                            console.log('Joined existing conversation');
                        } else {
                            console.log('Created new conversation');
                        }
                    } else {
                        console.error('API Error:', data.error);
                        this.updateConnectionStatus('Error: ' + data.error);

                        // Show user-friendly error message
                        this.displaySystemMessage('Error: ' + (data.error || 'Unknown error'));

                        // If there's debug info, log it
                        if (data.debug) {
                            console.error('Debug info:', data.debug);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error creating conversation:', error);
                    this.updateConnectionStatus('Connection Error');
                    this.displaySystemMessage('Failed to connect to chat service. Please try again.');
                });
        }

        sendMessage(message) {
            if (!this.isAuthenticated || !this.currentConversationId) {
                console.log('Cannot send message - not ready');
                console.log('Authenticated:', this.isAuthenticated, 'Conversation ID:', this.currentConversationId);
                return;
            }

            this.send({
                type: 'send_message',
                conversation_id: this.currentConversationId,
                message: message
            });
        }

        sendTypingStatus(isTyping) {
            if (!this.isAuthenticated || !this.currentConversationId) return;

            this.send({
                type: 'typing',
                conversation_id: this.currentConversationId,
                is_typing: isTyping
            });
        }

        displayMessages(messages) {
            const container = document.getElementById('chatMessages');
            if (!container) {
                console.error('Chat messages container not found');
                return;
            }

            container.innerHTML = '';

            if (messages && Array.isArray(messages) && messages.length > 0) {
                messages.forEach((message, index) => {
                    if (message && typeof message === 'object') {
                        // Validate message before displaying
                        if (this.validateMessageObject(message)) {
                            this.displayNewMessage(message);
                        } else {
                            console.warn(`Invalid message at index ${index}:`, message);
                        }
                    } else {
                        console.warn(`Invalid message at index ${index}:`, message);
                    }
                });
            } else {
                // Show welcome message for new conversations
                this.displaySystemMessage('Chat started. Say hello!');
            }
        }

        validateMessageObject(message) {
            if (!message || typeof message !== 'object') {
                return false;
            }

            // Check required properties
            const requiredProps = ['id', 'sender_user_id', 'message', 'created_at'];
            for (const prop of requiredProps) {
                if (message[prop] === null || message[prop] === undefined) {
                    console.error(`Message missing required property: ${prop}`, message);
                    return false;
                }
            }

            // Validate data types
            if (!Number.isInteger(message.sender_user_id) || message.sender_user_id <= 0) {
                console.error('Invalid sender_user_id:', message.sender_user_id);
                return false;
            }

            if (typeof message.message !== 'string') {
                console.error('Invalid message content type:', typeof message.message);
                return false;
            }

            return true;
        }

        displayNewMessage(message) {
            // Use the validation function
            if (!this.validateMessageObject(message)) {
                console.error('displayNewMessage called with invalid message:', message);
                return;
            }

            const container = document.getElementById('chatMessages');
            if (!container) {
                console.error('Chat messages container not found');
                return;
            }

            const messageDiv = document.createElement('div');

            // Safe comparison with proper type conversion
            const senderUserId = parseInt(message.sender_user_id);
            const currentUserId = parseInt(this.userId);

            if (isNaN(senderUserId) || isNaN(currentUserId)) {
                console.warn('Invalid user IDs for message comparison:', {
                    sender_user_id: message.sender_user_id,
                    current_user_id: this.userId,
                    message: message
                });
            }

            const isOwnMessage = senderUserId === currentUserId;
            messageDiv.className = `message ${isOwnMessage ? 'sent' : 'received'}`;

            // Safe timestamp handling
            let timestamp;
            try {
                timestamp = message.created_at ? new Date(message.created_at) : new Date();
            } catch (e) {
                console.warn('Invalid timestamp:', message.created_at);
                timestamp = new Date();
            }

            const timeString = timestamp.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });

            // Safe message content handling
            const messageContent = message.message || '[Empty message]';

            messageDiv.innerHTML = `
            <div class="message-bubble">${this.escapeHtml(messageContent)}</div>
            <div class="message-time">${timeString}</div>
        `;

            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
        }

        displaySystemMessage(message) {
            const container = document.getElementById('chatMessages');
            if (!container) return;

            const messageDiv = document.createElement('div');
            messageDiv.className = 'message system';
            messageDiv.innerHTML = `
            <div class="message-bubble system">${this.escapeHtml(message)}</div>
        `;

            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
        }

        handleTypingStatus(data) {
            if (data.user_id == this.userId) return;

            const indicator = document.getElementById('typingIndicator');
            if (!indicator) return;

            if (data.is_typing) {
                indicator.style.display = 'block';
            } else {
                indicator.style.display = 'none';
            }
        }

        handleReconnect() {
            if (this.reconnectAttempts < this.maxReconnectAttempts) {
                this.reconnectAttempts++;
                console.log(`Reconnecting... Attempt ${this.reconnectAttempts}`);

                setTimeout(() => {
                    this.connect(this.userId, this.serviceId);
                }, 2000 * this.reconnectAttempts);
            } else {
                console.error('Max reconnection attempts reached');
                this.updateConnectionStatus('Connection Failed');
            }
        }

        updateConnectionStatus(status) {
            const statusElement = document.getElementById('connectionStatus');
            if (statusElement) {
                statusElement.textContent = status;
                statusElement.className = `connection-status ${status.toLowerCase().replace(/\s+/g, '-')}`;
            }
            console.log('Connection status:', status);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        disconnect() {
            if (this.socket) {
                this.socket.close();
            }
        }
    }

    // Initialize chat client
    const chatClient = new ChatClient();

    // Chat Modal Functions
    function openChatModal() {
        console.log('Opening chat modal for service ID:', serviceId, 'and user ID:', userId);

        // Validate required data
        if (!userId || userId <= 0) {
            alert('Please log in to start a chat');
            return;
        }

        if (!serviceId || serviceId <= 0) {
            alert('Service information not available');
            return;
        }

        const modal = document.getElementById('chatModal');
        if (!modal) {
            console.error('Chat modal not found');
            return;
        }

        modal.style.display = 'block';

        if (!chatClient.isConnected) {
            chatClient.connect(userId, serviceId);
        }
    }

    function closeChatModal() {
        const modal = document.getElementById('chatModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission if inside a form
            sendMessage();
        } else {
            // Send typing indicator
            if (chatClient.isAuthenticated) {
                chatClient.sendTypingStatus(true);

                clearTimeout(chatClient.typingTimer);
                chatClient.typingTimer = setTimeout(() => {
                    chatClient.sendTypingStatus(false);
                }, 1000);
            }
        }
    }

    function sendMessage() {
        const messageInput = document.getElementById('messageInput');
        if (!messageInput) {
            console.error('Message input not found');
            return;
        }

        const message = messageInput.value.trim();

        if (!message) return;

        chatClient.sendMessage(message);
        messageInput.value = '';

        // Stop typing indicator
        clearTimeout(chatClient.typingTimer);
        chatClient.sendTypingStatus(false);
    }

    function bookService(serviceId) {
        window.location.href = `/booking?service_id=${serviceId}`;
    }

    // Event Listeners
    window.onclick = function(event) {
        const modal = document.getElementById('chatModal');
        if (event.target === modal) {
            closeChatModal();
        }
    }

    window.addEventListener('beforeunload', () => {
        chatClient.disconnect();
    });
    </script>
    <?php require('partials/footer.php'); ?>
</body>

</html>