<?php
/**
 * WebSocket Chat Server for Paragliding Booking System
 * Pure PHP Implementation with ReactPHP
 */

// Enable error logging to file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/websocket_errors.log');
require_once '../../vendor/autoload.php';
require_once 'config/database.php';
require_once 'classes/ChatManager.php';
require_once 'classes/ConnectionManager.php';

use React\EventLoop\Loop;
use React\Socket\SocketServer;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Stream\WritableResourceStream;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer as RatchetHttpServer;
use Ratchet\WebSocket\WsServer;

class ChatServer implements \Ratchet\MessageComponentInterface {
    protected $clients;
    protected $connections;
    protected $chatManager;
    protected $connectionManager;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->connections = [];
        $this->chatManager = new ChatManager();
        $this->connectionManager = new ConnectionManager();
        
        // Add debug call
        $this->debugDatabaseConnection();
        
        echo "Chat Server initialized\n";
    }

    public function onOpen(\Ratchet\ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->connections[$conn->resourceId] = [
            'connection' => $conn,
            'user_id' => null,
            'authenticated' => false,
            'last_ping' => time()
        ];
        
        echo "New connection! ({$conn->resourceId})\n";
        
        // Send welcome message
        $conn->send(json_encode([
            'type' => 'system',
            'message' => 'Connected to chat server',
            'connection_id' => $conn->resourceId
        ]));
    }

    public function onMessage(\Ratchet\ConnectionInterface $from, $msg) {
        try {
            echo "Received message: $msg\n";
            $data = json_decode($msg, true);
            
            if (!$data || !isset($data['type'])) {
                $this->sendError($from, 'Invalid message format');
                return;
            }

            switch ($data['type']) {
                case 'auth':
                    $this->handleAuthentication($from, $data);
                    break;
                    
                case 'join_conversation':
                    $this->handleJoinConversation($from, $data);
                    break;
                    
                case 'send_message':
                    $this->handleSendMessage($from, $data);
                    break;
                    
                case 'typing':
                    $this->handleTyping($from, $data);
                    break;
                    
                case 'mark_read':
                    $this->handleMarkRead($from, $data);
                    break;
                    
                case 'ping':
                    $this->handlePing($from);
                    break;
                    
                default:
                    $this->sendError($from, 'Unknown message type');
            }
            
        } catch (Exception $e) {
            echo "Error handling message: " . $e->getMessage() . "\n";
            error_log("WebSocket message handling error: " . $e->getMessage() . " | Stack trace: " . $e->getTraceAsString());
            $this->sendError($from, 'Server error occurred');
        }
    }

    public function onClose(\Ratchet\ConnectionInterface $conn) {
        $this->clients->detach($conn);
        
        if (isset($this->connections[$conn->resourceId])) {
            $user_id = $this->connections[$conn->resourceId]['user_id'];
            
            if ($user_id) {
                $this->connectionManager->setUserOffline($user_id);
                $this->broadcastUserStatus($user_id, 'offline');
            }
            
            unset($this->connections[$conn->resourceId]);
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(\Ratchet\ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        error_log("WebSocket connection error: " . $e->getMessage());
        $conn->close();
    }

    private function handleAuthentication($conn, $data) {
        if (!isset($data['token']) || !isset($data['user_id'])) {
            $this->sendError($conn, 'Missing authentication data');
            return;
        }

        // Validate token (implement your JWT or session validation)
        if ($this->validateToken($data['token'], $data['user_id'])) {
            $this->connections[$conn->resourceId]['user_id'] = $data['user_id'];
            $this->connections[$conn->resourceId]['authenticated'] = true;
            
            $this->connectionManager->setUserOnline($data['user_id'], $conn->resourceId);
            
            $conn->send(json_encode([
                'type' => 'auth_success',
                'user_id' => $data['user_id'],
                'message' => 'Authentication successful'
            ]));
            
            $this->broadcastUserStatus($data['user_id'], 'online');
            
            echo "User {$data['user_id']} authenticated\n";
        } else {
            $this->sendError($conn, 'Authentication failed');
        }
    }

    private function handleJoinConversation($conn, $data) {
        if (!$this->isAuthenticated($conn)) return;
        
        if (!isset($data['conversation_id'])) {
            $this->sendError($conn, 'Missing conversation_id');
            return;
        }

        $user_id = $this->connections[$conn->resourceId]['user_id'];
        $conversation_id = $data['conversation_id'];

        // Verify user has access to conversation
        if (!$this->chatManager->hasConversationAccess($user_id, $conversation_id)) {
            $this->sendError($conn, 'Access denied to conversation');
            return;
        }

        // Add user to conversation room
        if (!isset($this->connections[$conn->resourceId]['conversations'])) {
            $this->connections[$conn->resourceId]['conversations'] = [];
        }
        $this->connections[$conn->resourceId]['conversations'][] = $conversation_id;

        // Get recent messages
        $messages = $this->chatManager->getRecentMessages($conversation_id, 50);
        
        $conn->send(json_encode([
            'type' => 'conversation_joined',
            'conversation_id' => $conversation_id,
            'messages' => $messages
        ]));

        echo "User {$user_id} joined conversation {$conversation_id}\n";
    }

    private function handleSendMessage($conn, $data) {
        if (!$this->isAuthenticated($conn)) return;
        
        $required = ['conversation_id', 'message'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $this->sendError($conn, "Missing {$field}");
                return;
            }
        }

        $user_id = $this->connections[$conn->resourceId]['user_id'];
        $conversation_id = $data['conversation_id'];
        $message = $data['message'];
        $message_type = $data['message_type'] ?? 'text';

        echo "=== WEBSOCKET SEND MESSAGE DEBUG START ===\n";
        echo "User ID: $user_id\n";
        echo "Conversation ID: $conversation_id\n";
        echo "Message: $message\n";
        echo "Message Type: $message_type\n";

        // Verify access
        if (!$this->chatManager->hasConversationAccess($user_id, $conversation_id)) {
            echo "Access denied for user $user_id to conversation $conversation_id\n";
            $this->sendError($conn, 'Access denied');
            return;
        }

        echo "Access verified\n";

        // Save message to database
        try {
            $message_id = $this->chatManager->saveMessage([
                'conversation_id' => $conversation_id,
                'sender_user_id' => $user_id,
                'message' => $message,
                'message_type' => $message_type
            ]);

            echo "Save message result: " . ($message_id ? "SUCCESS (ID: $message_id)" : "FAILED") . "\n";

            if ($message_id) {
                // Add a small delay to ensure the database transaction is committed
                usleep(100000); // 100ms delay
                
                // Get complete message data
                echo "Attempting to retrieve message with ID: $message_id\n";
                $messageData = $this->chatManager->getMessage($message_id);
                
                echo "Retrieved message data: " . json_encode($messageData) . "\n";
                
                if ($messageData && is_array($messageData)) {
                    // Validate message data before broadcasting
                    $requiredFields = ['id', 'conversation_id', 'sender_user_id', 'message', 'created_at'];
                    $missingFields = [];
                    
                    foreach ($requiredFields as $field) {
                        if (!isset($messageData[$field]) || $messageData[$field] === null) {
                            $missingFields[] = $field;
                        }
                    }
                    
                    if (empty($missingFields)) {
                        // Broadcast to all clients in conversation
                        $broadcastData = [
                            'type' => 'new_message',
                            'conversation_id' => $conversation_id,
                            'message' => $messageData
                        ];
                        
                        echo "Broadcasting data: " . json_encode($broadcastData) . "\n";
                        
                        $this->broadcastToConversation($conversation_id, $broadcastData);

                        // Update conversation last message time
                        $this->chatManager->updateConversationActivity($conversation_id);
                        
                        echo "Message sent successfully in conversation {$conversation_id} by user {$user_id}\n";
                    } else {
                        echo "ERROR: Message data missing required fields: " . implode(', ', $missingFields) . "\n";
                        echo "Message data: " . json_encode($messageData) . "\n";
                        $this->sendError($conn, 'Invalid message data structure');
                    }
                } else {
                    echo "ERROR: Failed to retrieve message data after saving or data is not an array\n";
                    echo "Returned data type: " . gettype($messageData) . "\n";
                    echo "Returned data: " . json_encode($messageData) . "\n";
                    $this->sendError($conn, 'Failed to retrieve message data');
                }
                
            } else {
                echo "ERROR: Failed to save message\n";
                $this->sendError($conn, 'Failed to save message');
            }
        } catch (Exception $e) {
            echo "EXCEPTION in handleSendMessage: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
            error_log("Error in handleSendMessage: " . $e->getMessage() . " | Stack trace: " . $e->getTraceAsString());
            $this->sendError($conn, 'Database error occurred');
        }
        
        echo "=== WEBSOCKET SEND MESSAGE DEBUG END ===\n";
    }

    private function handleTyping($conn, $data) {
        if (!$this->isAuthenticated($conn)) return;
        
        if (!isset($data['conversation_id'])) {
            $this->sendError($conn, 'Missing conversation_id');
            return;
        }

        $user_id = $this->connections[$conn->resourceId]['user_id'];
        $conversation_id = $data['conversation_id'];
        $is_typing = $data['is_typing'] ?? true;

        // Broadcast typing status to other users in conversation
        $this->broadcastToConversation($conversation_id, [
            'type' => 'typing_status',
            'conversation_id' => $conversation_id,
            'user_id' => $user_id,
            'is_typing' => $is_typing
        ], $user_id); // Exclude sender
    }

    private function handleMarkRead($conn, $data) {
        if (!$this->isAuthenticated($conn)) return;
        
        if (!isset($data['conversation_id'])) {
            $this->sendError($conn, 'Missing conversation_id');
            return;
        }

        $user_id = $this->connections[$conn->resourceId]['user_id'];
        $conversation_id = $data['conversation_id'];

        // Mark messages as read
        $this->chatManager->markMessagesAsRead($conversation_id, $user_id);

        $conn->send(json_encode([
            'type' => 'messages_marked_read',
            'conversation_id' => $conversation_id
        ]));
    }

    private function handlePing($conn) {
        $this->connections[$conn->resourceId]['last_ping'] = time();
        $conn->send(json_encode(['type' => 'pong']));
    }

    private function broadcastToConversation($conversation_id, $data, $exclude_user_id = null) {
        $broadcast_count = 0;
        foreach ($this->connections as $connection_data) {
            if (!$connection_data['authenticated']) continue;
            if ($exclude_user_id && $connection_data['user_id'] == $exclude_user_id) continue;
            
            if (isset($connection_data['conversations']) && 
                in_array($conversation_id, $connection_data['conversations'])) {
                try {
                    $connection_data['connection']->send(json_encode($data));
                    $broadcast_count++;
                } catch (Exception $e) {
                    echo "Error broadcasting to connection: " . $e->getMessage() . "\n";
                }
            }
        }
        echo "Broadcasted to $broadcast_count connections\n";
    }

    private function broadcastUserStatus($user_id, $status) {
        $data = [
            'type' => 'user_status',
            'user_id' => $user_id,
            'status' => $status,
            'timestamp' => time()
        ];

        foreach ($this->connections as $connection_data) {
            if ($connection_data['authenticated'] && $connection_data['user_id'] != $user_id) {
                try {
                    $connection_data['connection']->send(json_encode($data));
                } catch (Exception $e) {
                    echo "Error broadcasting user status: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    private function isAuthenticated($conn) {
        if (!isset($this->connections[$conn->resourceId]['authenticated']) || 
            !$this->connections[$conn->resourceId]['authenticated']) {
            $this->sendError($conn, 'Not authenticated');
            return false;
        }
        return true;
    }

    private function sendError($conn, $message) {
        try {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => $message,
                'timestamp' => time()
            ]));
        } catch (Exception $e) {
            echo "Error sending error message: " . $e->getMessage() . "\n";
        }
    }

    private function validateToken($token, $user_id) {
        echo "Validating token: $token for user: $user_id\n";
        
        // Simple token format validation
        if (strpos($token, 'user_') !== 0) {
            echo "Token format invalid\n";
            return false;
        }
        
        $extracted_id = str_replace('user_', '', $token);
        
        if ((string)$extracted_id !== (string)$user_id) {
            echo "Token ID mismatch\n";
            return false;
        }
        
        try {
            $db = getDBConnection();
            
            // Query with JOIN to get user info
            $stmt = $db->prepare("
                SELECT u.id, u.email, ui.acc_type, ui.firstName, ui.lastName 
                FROM users u 
                JOIN users_info ui ON u.id = ui.user_id 
                WHERE u.id = ?
            ");
            
            if (!$stmt->execute([$user_id])) {
                echo "Failed to execute query\n";
                return false;
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                echo "User not found in database for ID: $user_id\n";
                return false;
            }
            
            echo "User found: " . json_encode($user) . "\n";
            return true;
            
        } catch (Exception $e) {
            echo "Database error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function debugDatabaseConnection() {
        try {
            $db = getDBConnection();
            if (!$db) {
                echo "Database connection is null\n";
                return false;
            }
            
            // Test basic query
            $result = $db->query("SELECT 1 as test")->fetch();
            echo "Database test query result: " . json_encode($result) . "\n";
            
            // Check users table structure
            $columns = $db->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
            echo "Users table structure: " . json_encode($columns) . "\n";
            
            // Check users_info table structure
            $columns_info = $db->query("DESCRIBE users_info")->fetchAll(PDO::FETCH_ASSOC);
            echo "Users_info table structure: " . json_encode($columns_info) . "\n";
            
            // Check messages table structure
            $messages_columns = $db->query("DESCRIBE chat_messages")->fetchAll(PDO::FETCH_ASSOC);
            echo "chat_messages table structure: " . json_encode($messages_columns) . "\n";
            
            // Show some sample user data
            $users = $db->query("SELECT id, email FROM users LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
            echo "Sample users: " . json_encode($users) . "\n";
            
            return true;
        } catch (Exception $e) {
            echo "Database debug error: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Start the server
$server = IoServer::factory(
    new RatchetHttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8081 // Port
);

echo "Chat server started on port 8081\n";
echo "WebSocket endpoint: ws://localhost:8081\n";

$server->run();