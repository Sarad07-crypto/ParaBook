<?php
/**
 * Connection Manager - Handles user online status and connections
 */

class ConnectionManager {
    private $redis;
    private $useRedis;
    private $onlineUsers = []; // Fallback storage
    
    public function __construct() {
        // Try to use Redis for better scalability
        $this->useRedis = $this->initializeRedis();
        if (!$this->useRedis) {
            echo "Redis not available, using memory storage for user status\n";
        }
    }
    
    private function initializeRedis() {
        try {
            if (class_exists('Redis')) {
                $this->redis = new Redis();
                $this->redis->connect('127.0.0.1:3307', 6379);
                return true;
            }
        } catch (Exception $e) {
            error_log("Redis connection failed: " . $e->getMessage());
        }
        return false;
    }
    
    /**
     * Set user as online
     */
    public function setUserOnline($user_id, $connection_id = null) {
        try {
            if ($this->useRedis) {
                // Store in Redis with TTL
                $this->redis->setex("user_online:{$user_id}", 300, json_encode([
                    'status' => 'online',
                    'connection_id' => $connection_id,
                    'last_seen' => time()
                ]));
                
                // Add to online users set
                $this->redis->sadd('online_users', $user_id);
                
                // Store connection mapping
                if ($connection_id) {
                    $this->redis->setex("connection:{$connection_id}", 300, $user_id);
                }
            } else {
                // Fallback to memory
                $this->onlineUsers[$user_id] = [
                    'status' => 'online',
                    'connection_id' => $connection_id,
                    'last_seen' => time()
                ];
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error setting user online: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set user as offline
     */
    public function setUserOffline($user_id) {
        try {
            if ($this->useRedis) {
                // Update status but keep record for a while
                $userData = $this->redis->get("user_online:{$user_id}");
                if ($userData) {
                    $data = json_decode($userData, true);
                    $data['status'] = 'offline';
                    $data['last_seen'] = time();
                    
                    $this->redis->setex("user_online:{$user_id}", 3600, json_encode($data)); // Keep for 1 hour
                }
                
                // Remove from online users set
                $this->redis->srem('online_users', $user_id);
                
                // Remove connection mapping
                if (isset($data['connection_id'])) {
                    $this->redis->del("connection:{$data['connection_id']}");
                }
            } else {
                // Fallback to memory
                if (isset($this->onlineUsers[$user_id])) {
                    $this->onlineUsers[$user_id]['status'] = 'offline';
                    $this->onlineUsers[$user_id]['last_seen'] = time();
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error setting user offline: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user is online
     */
    public function isUserOnline($user_id) {
        try {
            if ($this->useRedis) {
                return $this->redis->sismember('online_users', $user_id);
            } else {
                return isset($this->onlineUsers[$user_id]) && 
                       $this->onlineUsers[$user_id]['status'] === 'online';
            }
        } catch (Exception $e) {
            error_log("Error checking user online status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's last seen timestamp
     */
    public function getUserLastSeen($user_id) {
        try {
            if ($this->useRedis) {
                $userData = $this->redis->get("user_online:{$user_id}");
                if ($userData) {
                    $data = json_decode($userData, true);
                    return $data['last_seen'] ?? null;
                }
            } else {
                return $this->onlineUsers[$user_id]['last_seen'] ?? null;
            }
            return null;
        } catch (Exception $e) {
            error_log("Error getting user last seen: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all online users
     */
    public function getOnlineUsers() {
        try {
            if ($this->useRedis) {
                return $this->redis->smembers('online_users');
            } else {
                $online = [];
                foreach ($this->onlineUsers as $user_id => $data) {
                    if ($data['status'] === 'online') {
                        $online[] = $user_id;
                    }
                }
                return $online;
            }
        } catch (Exception $e) {
            error_log("Error getting online users: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user by connection ID
     */
    public function getUserByConnection($connection_id) {
        try {
            if ($this->useRedis) {
                return $this->redis->get("connection:{$connection_id}");
            } else {
                foreach ($this->onlineUsers as $user_id => $data) {
                    if ($data['connection_id'] === $connection_id) {
                        return $user_id;
                    }
                }
                return null;
            }
        } catch (Exception $e) {
            error_log("Error getting user by connection: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update user's last activity
     */
    public function updateUserActivity($user_id) {
        try {
            if ($this->useRedis) {
                $userData = $this->redis->get("user_online:{$user_id}");
                if ($userData) {
                    $data = json_decode($userData, true);
                    $data['last_seen'] = time();
                    $this->redis->setex("user_online:{$user_id}", 300, json_encode($data));
                }
            } else {
                if (isset($this->onlineUsers[$user_id])) {
                    $this->onlineUsers[$user_id]['last_seen'] = time();
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Error updating user activity: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean up inactive connections (called periodically)
     */
    public function cleanupInactiveUsers($timeout = 300) {
        try {
            $currentTime = time();
            
            if ($this->useRedis) {
                $onlineUsers = $this->redis->smembers('online_users');
                foreach ($onlineUsers as $user_id) {
                    $userData = $this->redis->get("user_online:{$user_id}");
                    if ($userData) {
                        $data = json_decode($userData, true);
                        if (($currentTime - $data['last_seen']) > $timeout) {
                            $this->setUserOffline($user_id);
                        }
                    }
                }
            } else {
                foreach ($this->onlineUsers as $user_id => $data) {
                    if ($data['status'] === 'online' && 
                        ($currentTime - $data['last_seen']) > $timeout) {
                        $this->setUserOffline($user_id);
                    }
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error cleaning up inactive users: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user status with details
     */
    public function getUserStatus($user_id) {
        try {
            if ($this->useRedis) {
                $userData = $this->redis->get("user_online:{$user_id}");
                if ($userData) {
                    return json_decode($userData, true);
                }
            } else {
                return $this->onlineUsers[$user_id] ?? null;
            }
            return null;
        } catch (Exception $e) {
            error_log("Error getting user status: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Set typing status for user in conversation
     */
    public function setTypingStatus($user_id, $conversation_id, $is_typing = true) {
        try {
            $key = "typing:{$conversation_id}:{$user_id}";
            
            if ($this->useRedis) {
                if ($is_typing) {
                    $this->redis->setex($key, 10, time()); // 10 second TTL
                } else {
                    $this->redis->del($key);
                }
            } else {
                // For memory storage, you'd need a separate array
                // This is simplified for the example
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error setting typing status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get typing users in conversation
     */
    public function getTypingUsers($conversation_id) {
        try {
            if ($this->useRedis) {
                $pattern = "typing:{$conversation_id}:*";
                $keys = $this->redis->keys($pattern);
                
                $typingUsers = [];
                foreach ($keys as $key) {
                    $parts = explode(':', $key);
                    if (count($parts) === 3) {
                        $typingUsers[] = $parts[2]; // user_id
                    }
                }
                return $typingUsers;
            }
            
            return []; // Simplified for memory storage
        } catch (Exception $e) {
            error_log("Error getting typing users: " . $e->getMessage());
            return [];
        }
    }
}