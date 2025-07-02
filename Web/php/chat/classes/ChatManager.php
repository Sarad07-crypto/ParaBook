<?php
/**
 * Chat Manager - Handles all chat-related database operations
 */

// Include database configuration
require_once __DIR__ . '/../config/database.php';

class ChatManager {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
        
        // Check if database connection was successful
        if (!$this->db) {
            throw new Exception('Failed to establish database connection');
        }
    }
    
    /**
     * Check if user has access to conversation
     */
    public function hasConversationAccess($user_id, $conversation_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 1 FROM chat_conversations 
                WHERE id = ? AND (passenger_user_id = ? OR company_user_id = ?)
            ");
            $stmt->execute([$conversation_id, $user_id, $user_id]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            error_log("Error checking conversation access: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent messages for a conversation
     */
    public function getRecentMessages($conversation_id, $limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    cm.id,
                    cm.sender_user_id,
                    cm.message,
                    cm.message_type,
                    cm.file_path,
                    cm.is_read,
                    cm.created_at,
                    u.email as sender_email,
                    ui.firstName,
                    ui.lastName,
                    CONCAT(ui.firstName, ' ', ui.lastName) as sender_name
                FROM chat_messages cm
                JOIN users u ON cm.sender_user_id = u.id
                JOIN users_info ui ON u.id = ui.user_id
                WHERE cm.conversation_id = ?
                ORDER BY cm.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$conversation_id, $limit]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Reverse to get chronological order
            return array_reverse($messages);
        } catch (Exception $e) {
            error_log("Error getting recent messages: " . $e->getMessage());
            return [];
        }
    }
    
    /**
 * Save a new message - IMPROVED VERSION
 */
    public function saveMessage($data) {
        try {
            error_log("=== SAVE MESSAGE DEBUG START ===");
            error_log("Input data: " . json_encode($data));
            
            $this->db->beginTransaction();
            
            // Validate required data
            if (empty($data['conversation_id']) || empty($data['sender_user_id']) || empty($data['message'])) {
                $missing = [];
                if (empty($data['conversation_id'])) $missing[] = 'conversation_id';
                if (empty($data['sender_user_id'])) $missing[] = 'sender_user_id';
                if (empty($data['message'])) $missing[] = 'message';
                throw new Exception('Missing required message data: ' . implode(', ', $missing));
            }
            
            // Insert message
            $stmt = $this->db->prepare("
                INSERT INTO chat_messages 
                (conversation_id, sender_user_id, message, message_type, file_path, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $params = [
                $data['conversation_id'],
                $data['sender_user_id'],
                $data['message'],
                $data['message_type'] ?? 'text',
                $data['file_path'] ?? null
            ];
            
            error_log("SQL params: " . json_encode($params));
            
            $result = $stmt->execute($params);
            error_log("SQL execute result: " . ($result ? 'SUCCESS' : 'FAILED'));
            
            if (!$result) {
                error_log("SQL Error Info: " . json_encode($stmt->errorInfo()));
                throw new Exception('Failed to execute insert statement');
            }
            
            $message_id = $this->db->lastInsertId();
            error_log("Generated message_id: " . $message_id);
            
            if (!$message_id) {
                throw new Exception('Failed to get last insert ID');
            }
            
            // Verify the message was actually saved
            $verify_stmt = $this->db->prepare("SELECT id, message, sender_user_id FROM chat_messages WHERE id = ?");
            $verify_stmt->execute([$message_id]);
            $saved_message = $verify_stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Verification query result: " . json_encode($saved_message));
            
            if (!$saved_message) {
                throw new Exception('Message was not found after insert - ID: ' . $message_id);
            }
            
            // Update conversation last message time and unread counts
            $this->updateConversationUnreadCounts($data['conversation_id'], $data['sender_user_id']);
            
            $this->db->commit();
            error_log("Transaction committed successfully");
            error_log("=== SAVE MESSAGE DEBUG END ===");
            
            return $message_id;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("=== SAVE MESSAGE ERROR ===");
            error_log("Error saving message: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            error_log("=== SAVE MESSAGE ERROR END ===");
            return false;
        }
    }
    
    /**
 * Get a specific message with sender details - FIXED VERSION
 */
    public function getMessage($message_id) {
        try {
            error_log("ChatManager::getMessage called with ID: $message_id");
            
            // Fixed query - using proper aliases consistently
            $stmt = $this->db->prepare("
                SELECT 
                    m.id,
                    m.conversation_id,
                    m.sender_user_id,
                    m.message,
                    m.message_type,
                    m.file_path,
                    m.created_at,
                    m.is_read,
                    CONCAT(ui.firstName, ' ', ui.lastName) as sender_name,
                    ui.acc_type as sender_type
                FROM chat_messages m
                LEFT JOIN users u ON m.sender_user_id = u.id
                LEFT JOIN users_info ui ON u.id = ui.user_id
                WHERE m.id = ?
            ");
            
            if (!$stmt->execute([$message_id])) {
                error_log("Query execution failed. Error: " . json_encode($stmt->errorInfo()));
                return null;
            }
            
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($message) {
                error_log("Message retrieved successfully: " . json_encode($message));
                
                // Ensure all required fields are present and properly typed
                $message['id'] = (int)$message['id'];
                $message['conversation_id'] = (int)$message['conversation_id'];
                $message['sender_user_id'] = (int)$message['sender_user_id'];
                $message['is_read'] = (bool)$message['is_read'];
                $message['sender_name'] = $message['sender_name'] ?? 'Unknown User';
                $message['file_path'] = $message['file_path'] ?? null;
                
                return $message;
            } else {
                error_log("No message found with ID: $message_id");
                return null;
            }
            
        } catch (Exception $e) {
            error_log("Exception in getMessage: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }
    
    /**
     * Mark messages as read for a user in a conversation
     */
    public function markMessagesAsRead($conversation_id, $user_id) {
        try {
            $this->db->beginTransaction();
            
            // Mark messages as read
            $stmt = $this->db->prepare("
                UPDATE chat_messages 
                SET is_read = TRUE 
                WHERE conversation_id = ? AND sender_user_id != ? AND is_read = FALSE
            ");
            $stmt->execute([$conversation_id, $user_id]);
            
            // Reset unread count for this user
            $this->resetUnreadCount($conversation_id, $user_id);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error marking messages as read: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update conversation activity timestamp
     */
    public function updateConversationActivity($conversation_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE chat_conversations 
                SET last_message_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$conversation_id]);
            return true;
        } catch (Exception $e) {
            error_log("Error updating conversation activity: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new conversation between users for a service
     */
    public function createConversation($service_id, $passenger_user_id, $company_user_id) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO chat_conversations 
                (service_id, passenger_user_id, company_user_id, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$service_id, $passenger_user_id, $company_user_id]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Error creating conversation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find existing conversation between users for a service
     */
    public function findExistingConversation($service_id, $passenger_user_id, $company_user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM chat_conversations
                WHERE service_id = ?
                AND passenger_user_id = ?
                AND company_user_id = ?
                LIMIT 1
            ");
            $stmt->execute([$service_id, $passenger_user_id, $company_user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error finding existing conversation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get or create conversation between users for a service
     */
    public function getOrCreateConversation($service_id, $passenger_user_id, $company_user_id) {
        try {
            // Try to find existing conversation
            $conversation = $this->findExistingConversation($service_id, $passenger_user_id, $company_user_id);
            
            if ($conversation) {
                return $conversation['id'];
            }
            
            // Create new conversation if none exists
            return $this->createConversation($service_id, $passenger_user_id, $company_user_id);
            
        } catch (Exception $e) {
            error_log("Error getting/creating conversation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's conversations with unread counts - CORRECTED VERSION
     */
    public function getUserConversations($user_id, $limit = 20) {
        try {
            error_log("Getting conversations for user_id: $user_id");
            
            $stmt = $this->db->prepare("
                SELECT 
                    cc.id,
                    cc.service_id,
                    cc.passenger_user_id,
                    cc.company_user_id,
                    cc.last_message_at,
                    cc.passenger_unread_count,
                    cc.company_unread_count,
                    cc.status,
                    cc.created_at,
                    -- Service information (CORRECTED COLUMN NAMES)
                    COALESCE(cs.service_title, CONCAT('Service #', cc.service_id)) as service_name,
                    COALESCE(cs.address, 'Unknown Location') as service_location,
                    -- Passenger information
                    COALESCE(CONCAT(pui.firstName, ' ', pui.lastName), 'Unknown Passenger') as passenger_name,
                    -- Company information  
                    COALESCE(CONCAT(cui.firstName, ' ', cui.lastName), cs.company_name, 'Unknown Company') as company_name,
                    -- Last message information
                    COALESCE(lm.message, 'No messages yet') as last_message,
                    COALESCE(lm.message_type, 'text') as last_message_type,
                    COALESCE(lm.created_at, cc.created_at) as last_message_time,
                    lm.sender_user_id as last_message_sender_id
                FROM chat_conversations cc
                -- Join with service information
                LEFT JOIN company_services cs ON cc.service_id = cs.id
                -- Join with passenger user information
                LEFT JOIN users_info pui ON cc.passenger_user_id = pui.user_id
                -- Join with company user information  
                LEFT JOIN users_info cui ON cc.company_user_id = cui.user_id
                -- Join with last message
                LEFT JOIN (
                    SELECT 
                        cm1.conversation_id,
                        cm1.message,
                        cm1.message_type,
                        cm1.created_at,
                        cm1.sender_user_id
                    FROM chat_messages cm1
                    INNER JOIN (
                        SELECT conversation_id, MAX(created_at) as max_time
                        FROM chat_messages
                        GROUP BY conversation_id
                    ) cm2 ON cm1.conversation_id = cm2.conversation_id AND cm1.created_at = cm2.max_time
                ) lm ON cc.id = lm.conversation_id
                WHERE cc.passenger_user_id = ? OR cc.company_user_id = ?
                ORDER BY COALESCE(lm.created_at, cc.last_message_at, cc.created_at) DESC
                LIMIT ?
            ");
            
            $result = $stmt->execute([$user_id, $user_id, $limit]);
            
            if (!$result) {
                error_log("Query failed: " . json_encode($stmt->errorInfo()));
                return [];
            }
            
            $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($conversations) . " conversations for user $user_id");
            
            return $conversations;
            
        } catch (Exception $e) {
            error_log("Error getting user conversations: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Format simple conversations when full query fails
     */
    private function formatSimpleConversations($conversations) {
        $formatted = [];
        foreach ($conversations as $conv) {
            $formatted[] = [
                'id' => (int)$conv['id'],
                'service_id' => (int)$conv['service_id'],
                'passenger_user_id' => (int)$conv['passenger_user_id'],
                'company_user_id' => (int)$conv['company_user_id'],
                'passenger_unread_count' => (int)$conv['passenger_unread_count'],
                'company_unread_count' => (int)$conv['company_unread_count'],
                'status' => $conv['status'],
                'service_name' => 'Service #' . $conv['service_id'],
                'service_location' => 'Unknown Location',
                'passenger_name' => 'Unknown Passenger',
                'company_name' => 'Unknown Company',
                'last_message' => 'No messages yet',
                'last_message_type' => 'text',
                'last_message_time' => $conv['created_at']
            ];
        }
        return $formatted;
    }

    /**
     * Update unread counts when new message is sent
     */
    private function updateConversationUnreadCounts($conversation_id, $sender_user_id) {
        try {
            // Get conversation details
            $stmt = $this->db->prepare("
                SELECT passenger_user_id, company_user_id 
                FROM chat_conversations 
                WHERE id = ?
            ");
            $stmt->execute([$conversation_id]);
            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$conversation) return false;
            
            // Increment unread count for the recipient
            if ($sender_user_id == $conversation['passenger_user_id']) {
                // Sender is passenger, increment company unread count
                $stmt = $this->db->prepare("
                    UPDATE chat_conversations 
                    SET company_unread_count = company_unread_count + 1,
                        last_message_at = NOW(),
                        updated_at = NOW()
                    WHERE id = ?
                ");
            } else {
                // Sender is company, increment passenger unread count
                $stmt = $this->db->prepare("
                    UPDATE chat_conversations 
                    SET passenger_unread_count = passenger_unread_count + 1,
                        last_message_at = NOW(),
                        updated_at = NOW()
                    WHERE id = ?
                ");
            }
            
            $stmt->execute([$conversation_id]);
            return true;
            
        } catch (Exception $e) {
            error_log("Error updating unread counts: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reset unread count for a user in a conversation
     */
    private function resetUnreadCount($conversation_id, $user_id) {
        try {
            // Get conversation details to know which count to reset
            $stmt = $this->db->prepare("
                SELECT passenger_user_id, company_user_id 
                FROM chat_conversations 
                WHERE id = ?
            ");
            $stmt->execute([$conversation_id]);
            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$conversation) return false;
            
            if ($user_id == $conversation['passenger_user_id']) {
                $field = 'passenger_unread_count';
            } else {
                $field = 'company_unread_count';
            }
            
            $stmt = $this->db->prepare("
                UPDATE chat_conversations 
                SET {$field} = 0, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$conversation_id]);
            return true;
            
        } catch (Exception $e) {
            error_log("Error resetting unread count: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Search messages in conversations accessible to user - FIXED VERSION
     */
    public function searchMessages($user_id, $query, $limit = 50) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    cm.id,
                    cm.conversation_id,
                    cm.sender_user_id,
                    cm.message,
                    cm.message_type,
                    cm.created_at,
                    CONCAT(ui.firstName, ' ', ui.lastName) as sender_name,
                    cs.name as service_name
                FROM chat_messages cm
                JOIN chat_conversations cc ON cm.conversation_id = cc.id
                JOIN users u ON cm.sender_user_id = u.id
                JOIN users_info ui ON u.id = ui.user_id
                JOIN company_services cs ON cc.service_id = cs.id
                WHERE (cc.passenger_user_id = ? OR cc.company_user_id = ?)
                AND cm.message LIKE ?
                ORDER BY cm.created_at DESC
                LIMIT ?
            ");
            
            $searchTerm = '%' . $query . '%';
            $stmt->execute([$user_id, $user_id, $searchTerm, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error searching messages: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get conversation statistics
     */
    public function getConversationStats($conversation_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_messages,
                    COUNT(CASE WHEN is_read = FALSE THEN 1 END) as unread_messages,
                    MIN(created_at) as first_message_at,
                    MAX(created_at) as last_message_at
                FROM chat_messages 
                WHERE conversation_id = ?
            ");
            $stmt->execute([$conversation_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting conversation stats: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get conversation details - FIXED VERSION
     */
    public function getConversation($conversation_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    cc.*,
                    cs.name as service_name,
                    cs.location as service_location,
                    CONCAT(pui.firstName, ' ', pui.lastName) as passenger_name,
                    CONCAT(cui.firstName, ' ', cui.lastName) as company_name
                FROM chat_conversations cc
                JOIN company_services cs ON cc.service_id = cs.id
                JOIN users pu ON cc.passenger_user_id = pu.id
                JOIN users_info pui ON pu.id = pui.user_id
                JOIN users cu ON cc.company_user_id = cu.id
                JOIN users_info cui ON cu.id = cui.user_id
                WHERE cc.id = ?
            ");
            $stmt->execute([$conversation_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting conversation: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Close a conversation
     */
    public function closeConversation($conversation_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE chat_conversations 
                SET status = 'closed', updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$conversation_id]);
            return true;
        } catch (Exception $e) {
            error_log("Error closing conversation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reopen a conversation
     */
    public function reopenConversation($conversation_id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE chat_conversations 
                SET status = 'active', updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$conversation_id]);
            return true;
        } catch (Exception $e) {
            error_log("Error reopening conversation: " . $e->getMessage());
            return false;
        }
    }
}