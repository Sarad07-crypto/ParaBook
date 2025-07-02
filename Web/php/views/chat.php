<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: #f5f5f5;
        height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        background: white;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .chat-header h1 {
        color: #333;
        font-size: 1.5rem;
        margin: 0;
    }

    .chat-info {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .back-btn {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.2s;
    }

    .back-btn:hover {
        background: #0056b3;
        text-decoration: none;
        color: white;
    }

    .chat-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
        background: white;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    .messages-container {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .message {
        display: flex;
        flex-direction: column;
        max-width: 70%;
    }

    .message-own {
        align-self: flex-end;
        align-items: flex-end;
    }

    .message-other {
        align-self: flex-start;
        align-items: flex-start;
    }

    .message-content {
        background: white;
        padding: 12px 16px;
        border-radius: 18px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .message-own .message-content {
        background: #007bff;
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message-other .message-content {
        border-bottom-left-radius: 4px;
    }

    .message-text {
        word-wrap: break-word;
        line-height: 1.4;
    }

    .message-time {
        font-size: 0.75rem;
        opacity: 0.7;
        margin-top: 4px;
    }

    .message-sender {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 4px;
        padding: 0 8px;
    }

    .message-own .message-sender {
        text-align: right;
    }

    .input-container {
        padding: 20px;
        border-top: 1px solid #e9ecef;
        background: white;
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }

    #messageInput {
        flex: 1;
        border: 1px solid #ced4da;
        border-radius: 20px;
        padding: 12px 16px;
        resize: none;
        font-family: inherit;
        font-size: 14px;
        line-height: 1.4;
        max-height: 120px;
        transition: border-color 0.2s;
    }

    #messageInput:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }

    #sendButton {
        background: #007bff;
        color: white;
        border: none;
        border-radius: 50px;
        padding: 12px 24px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: background-color 0.2s;
        min-width: 100px;
    }

    #sendButton:hover:not(:disabled) {
        background: #0056b3;
    }

    #sendButton:disabled {
        background: #6c757d;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .loading {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }

    .loading::before {
        content: '';
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #e9ecef;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 10px;
    }

    .no-messages {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }

    .no-messages i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .error-message {
        text-align: center;
        padding: 20px;
        color: #dc3545;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 8px;
        margin: 20px;
    }

    .debug-info {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 10px;
        margin: 10px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
        display: none;
        /* Hide by default */
    }

    .debug-info.show {
        display: block;
    }

    .connection-status {
        padding: 10px;
        margin: 10px;
        border-radius: 4px;
        font-size: 14px;
        text-align: center;
    }

    .connection-status.connected {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .connection-status.connecting {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .connection-status.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 768px) {
        .chat-header {
            padding: 10px 15px;
        }

        .chat-header h1 {
            font-size: 1.2rem;
        }

        .message {
            max-width: 85%;
        }

        .messages-container {
            padding: 15px;
        }

        .input-container {
            padding: 15px;
        }
    }
    </style>
</head>

<body>
    <!-- Connection Status -->
    <div class="connection-status connecting" id="connectionStatus">
        Initializing chat...
    </div>

    <!-- Debug information (hidden by default) -->
    <div class="debug-info" id="debugInfo">
        Debug mode disabled
    </div>

    <div class="chat-header">
        <div>
            <h1 id="chatTitle">Loading...</h1>
            <div class="chat-info" id="chatInfo">Preparing chat...</div>
        </div>
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="chat-container">
        <div class="messages-container" id="messagesContainer">
            <div class="loading">Loading messages...</div>
        </div>

        <div class="input-container">
            <textarea id="messageInput" placeholder="Type your message..." rows="1" disabled></textarea>
            <button id="sendButton" disabled>
                <i class="fas fa-paper-plane"></i> Send
            </button>
        </div>
    </div>

    <script>
    (function() {
        'use strict';

        // Configuration
        const DEBUG_MODE = true; // Set to false in production

        // Global variables
        const urlParams = new URLSearchParams(window.location.search);
        let conversationId = null;
        let currentUserId = null;
        let isLoading = false;
        let autoRefreshInterval = null;
        let lastMessageCount = 0;
        let isInitialized = false;

        // DOM Elements
        let messageInput, sendButton, messagesContainer, connectionStatus, debugInfo;

        // Debug function
        function debug(message, data = null) {
            if (DEBUG_MODE) {
                console.log(`[CHAT DEBUG] ${message}`, data || '');
                updateDebugInfo(message + (data ? `: ${JSON.stringify(data)}` : ''));
            }
        }

        function updateDebugInfo(info) {
            if (debugInfo && DEBUG_MODE) {
                const timestamp = new Date().toLocaleTimeString();
                debugInfo.innerHTML = `
                    <strong>Debug Info (${timestamp}):</strong><br>
                    ${info}<br>
                    Conversation ID: ${conversationId}<br>
                    User ID: ${currentUserId}<br>
                    Initialized: ${isInitialized}<br>
                    URL: ${window.location.href}
                `;
                debugInfo.classList.add('show');
            }
        }

        function updateConnectionStatus(status, message) {
            if (connectionStatus) {
                connectionStatus.className = `connection-status ${status}`;
                connectionStatus.textContent = message;
            }
        }

        // Extract conversation ID from URL
        function getConversationId() {
            // Try multiple parameter names
            const possibleParams = ['conversation_id', 'id', 'conv_id', 'chat_id'];

            for (const param of possibleParams) {
                const value = urlParams.get(param);
                if (value) {
                    const numValue = parseInt(value);
                    if (!isNaN(numValue) && numValue > 0) {
                        debug(`Found conversation ID from ${param}`, numValue);
                        return numValue;
                    }
                }
            }

            // Try from hash
            if (window.location.hash) {
                const hashMatch = window.location.hash.match(/(?:conversation_id|id|conv_id|chat_id)[=:](\d+)/i);
                if (hashMatch) {
                    const numValue = parseInt(hashMatch[1]);
                    if (!isNaN(numValue) && numValue > 0) {
                        debug('Found conversation ID from hash', numValue);
                        return numValue;
                    }
                }
            }

            debug('No valid conversation ID found in URL');
            return null;
        }

        // Get current user ID from session
        async function getCurrentUserId() {
            try {
                // You might need to create this endpoint or modify an existing one
                const response = await fetch('Web/php/chat/api/get_user_session.php', {
                    method: 'GET',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.user_id) {
                        debug('Got user ID from session', data.user_id);
                        return parseInt(data.user_id);
                    }
                }

                // Fallback: try to extract from a test API call
                const testResponse = await fetch('Web/php/chat/api/get_messages.php?conversation_id=1', {
                    method: 'GET',
                    credentials: 'include',
                });

                if (testResponse.status === 401) {
                    throw new Error('User not authenticated');
                }

                // For now, return a default value - you should replace this with proper session handling
                debug('Using fallback user ID', 1);
                return 1;

            } catch (error) {
                debug('Error getting user ID', error.message);
                throw new Error('Could not determine current user ID');
            }
        }

        // Initialize chat
        document.addEventListener('DOMContentLoaded', async function() {
            debug('DOM loaded, initializing chat interface');

            // Get DOM elements
            messageInput = document.getElementById('messageInput');
            sendButton = document.getElementById('sendButton');
            messagesContainer = document.getElementById('messagesContainer');
            connectionStatus = document.getElementById('connectionStatus');
            debugInfo = document.getElementById('debugInfo');

            if (!messageInput || !sendButton || !messagesContainer) {
                updateConnectionStatus('error', 'Critical DOM elements missing');
                debug('Critical DOM elements not found');
                return;
            }

            updateConnectionStatus('connecting', 'Getting conversation details...');

            try {
                // Step 1: Get conversation ID
                conversationId = getConversationId();
                if (!conversationId) {
                    throw new Error('Invalid or missing conversation ID in URL parameters');
                }

                // Step 2: Get current user ID
                updateConnectionStatus('connecting', 'Authenticating user...');
                currentUserId = await getCurrentUserId();

                // Step 3: Load conversation data
                updateConnectionStatus('connecting', 'Loading conversation...');
                await loadConversationData();

                // Step 4: Setup event listeners
                setupEventListeners();

                // Step 5: Start auto-refresh
                startAutoRefresh();

                // Mark as initialized
                isInitialized = true;
                updateConnectionStatus('connected', 'Chat ready');
                debug('Chat initialization completed successfully');

                // Enable input
                messageInput.disabled = false;
                sendButton.disabled = false;
                messageInput.placeholder = "Type your message...";
                messageInput.focus();

            } catch (error) {
                debug('Initialization failed', error.message);
                updateConnectionStatus('error', `Failed to initialize: ${error.message}`);
                showError(error.message);
            }
        });

        function setupEventListeners() {
            debug('Setting up event listeners');

            // Send message on button click
            sendButton.addEventListener('click', sendMessage);

            // Send message on Enter (but allow Shift+Enter for new lines)
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Auto-resize textarea
            messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            // Focus on input when clicking anywhere in the input container
            const inputContainer = document.querySelector('.input-container');
            if (inputContainer) {
                inputContainer.addEventListener('click', function(e) {
                    if (e.target === this) {
                        messageInput.focus();
                    }
                });
            }
        }

        async function loadConversationData() {
            debug('Loading conversation data');

            try {
                const apiUrl = `Web/php/chat/api/get_messages.php?conversation_id=${conversationId}`;
                debug('Making API request to', apiUrl);

                const response = await fetch(apiUrl, {
                    method: 'GET',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'Cache-Control': 'no-cache',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                debug('API response received', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok
                });

                if (!response.ok) {
                    let errorMessage = `HTTP ${response.status}: ${response.statusText}`;

                    try {
                        const errorText = await response.text();
                        debug('Error response body', errorText);

                        // Try to parse as JSON
                        try {
                            const errorJson = JSON.parse(errorText);
                            if (errorJson.error) {
                                errorMessage += ` - ${errorJson.error}`;
                            }
                        } catch (parseError) {
                            if (errorText.length < 200) {
                                errorMessage += ` - ${errorText}`;
                            }
                        }
                    } catch (textError) {
                        debug('Could not read error response', textError.message);
                    }

                    throw new Error(errorMessage);
                }

                const data = await response.json();
                debug('Parsed response data', data);

                if (!data.success) {
                    throw new Error(data.error || 'API returned success=false');
                }

                if (!data.messages || !Array.isArray(data.messages)) {
                    throw new Error('Invalid response: messages array not found');
                }

                debug('Successfully loaded conversation data', {
                    messagesCount: data.messages.length,
                    conversationId: data.conversation?.id
                });

                // Display messages
                displayMessages(data.messages, true);

                // Update UI elements
                if (data.conversation) {
                    updateConversationInfo(data.conversation);
                }

                lastMessageCount = data.messages.length;
                return data;

            } catch (error) {
                debug('Error in loadConversationData', error.message);
                throw error;
            }
        }

        async function sendMessage() {
            if (!isInitialized || !messageInput.value.trim() || isLoading) {
                debug('Send message blocked', {
                    initialized: isInitialized,
                    hasValue: !!messageInput.value.trim(),
                    loading: isLoading
                });
                return;
            }

            const message = messageInput.value.trim();
            debug('Sending message', {
                message,
                conversationId
            });

            // Disable input while sending
            isLoading = true;
            messageInput.disabled = true;
            sendButton.disabled = true;
            sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            try {
                const response = await fetch('Web/php/chat/api/send_message.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId,
                        message: message,
                        message_type: 'text'
                    })
                });

                debug('Send message response', {
                    status: response.status,
                    ok: response.ok
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`HTTP ${response.status}: ${response.statusText} - ${errorText}`);
                }

                const data = await response.json();
                debug('Send message result', data);

                if (data.success) {
                    // Clear input
                    messageInput.value = '';
                    messageInput.style.height = 'auto';

                    // Reload messages to show the new one
                    await loadMessages(true);
                    debug('Message sent successfully');
                } else {
                    throw new Error(data.error || 'Failed to send message');
                }
            } catch (error) {
                debug('Error sending message', error.message);
                showError('Failed to send message. Please try again.');

                // Show user-friendly error
                updateConnectionStatus('error', 'Failed to send message');
                setTimeout(() => {
                    if (isInitialized) {
                        updateConnectionStatus('connected', 'Chat ready');
                    }
                }, 3000);
            } finally {
                // Re-enable input
                isLoading = false;
                messageInput.disabled = false;
                sendButton.disabled = false;
                sendButton.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
                messageInput.focus();
            }
        }

        async function loadMessages(shouldScroll = false) {
            if (!isInitialized || isLoading) return;

            try {
                isLoading = true;
                const apiUrl = `Web/php/chat/api/get_messages.php?conversation_id=${conversationId}`;

                const response = await fetch(apiUrl, {
                    method: 'GET',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'Cache-Control': 'no-cache',
                    }
                });

                if (!response.ok) {
                    debug('Error refreshing messages', response.status);
                    return;
                }

                const data = await response.json();

                if (data.success && data.messages) {
                    if (data.messages.length !== lastMessageCount) {
                        displayMessages(data.messages, shouldScroll);
                        lastMessageCount = data.messages.length;
                        debug('Messages refreshed', data.messages.length);
                    }
                }
            } catch (error) {
                debug('Error refreshing messages', error.message);
            } finally {
                isLoading = false;
            }
        }

        function startAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }

            autoRefreshInterval = setInterval(() => {
                if (isInitialized) {
                    loadMessages(false);
                }
            }, 10000);

            debug('Auto-refresh started');
        }

        function displayMessages(messages, shouldScroll = true) {
            if (!messagesContainer) return;

            if (messages.length === 0) {
                messagesContainer.innerHTML = `
                    <div class="no-messages">
                        <i class="far fa-comments"></i>
                        <p>No messages yet. Start the conversation!</p>
                    </div>
                `;
                return;
            }

            const messagesHTML = messages.map(message => {
                const isOwn = message.sender_id == currentUserId;
                const messageClass = isOwn ? 'message-own' : 'message-other';
                const timeFormatted = formatMessageTime(message.created_at);

                return `
                    <div class="message ${messageClass}" data-message-id="${message.id}">
                        <div class="message-content">
                            <div class="message-text">${escapeHtml(message.message)}</div>
                            <div class="message-time">${timeFormatted}</div>
                        </div>
                        <div class="message-sender">${escapeHtml(message.sender_name || 'Unknown User')}</div>
                    </div>
                `;
            }).join('');

            messagesContainer.innerHTML = messagesHTML;

            if (shouldScroll) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }

        function updateConversationInfo(conversation) {
            const titleElement = document.getElementById('chatTitle');
            const infoElement = document.getElementById('chatInfo');

            if (titleElement && conversation.service_name) {
                titleElement.textContent = conversation.service_name;
                document.title = `Chat - ${conversation.service_name}`;
            }

            if (infoElement && conversation.service_location) {
                infoElement.textContent = conversation.service_location;
            }
        }

        function formatMessageTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInHours = (now - date) / (1000 * 60 * 60);

            if (diffInHours < 24) {
                return date.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } else if (diffInHours < 168) {
                return date.toLocaleDateString([], {
                    weekday: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } else {
                return date.toLocaleDateString([], {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        function showError(message) {
            if (messagesContainer) {
                messagesContainer.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>${escapeHtml(message)}</p>
                        <button onclick="window.location.reload()" style="margin-top: 10px; padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-redo"></i> Reload Page
                        </button>
                    </div>
                `;
            }
        }

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        });

        // Expose debug functions
        window.chatDebug = {
            loadMessages,
            sendMessage,
            getConversationId: () => conversationId,
            getCurrentUserId: () => currentUserId,
            isInitialized: () => isInitialized,
            toggleDebug: () => {
                DEBUG_MODE = !DEBUG_MODE;
                if (DEBUG_MODE) {
                    debugInfo.classList.add('show');
                } else {
                    debugInfo.classList.remove('show');
                }
            }
        };

    })();
    </script>
</body>

</html>