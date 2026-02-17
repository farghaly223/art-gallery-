<?php
class EGift extends BaseModel {
    protected $table = 'egifts';
    
    public function __construct() {
        parent::__construct();
        // Ensure tables exist
        $this->ensureTablesExist();
    }
    
    // Make sure the necessary tables exist
    private function ensureTablesExist() {
        // Check if egifts table exists
        $egiftsTableExists = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
        if (!$egiftsTableExists || $egiftsTableExists->num_rows == 0) {
            // Create egifts table if it doesn't exist
            $createEgiftsSQL = "
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sender_id INT NOT NULL,
                recipient_id INT NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                message TEXT NULL,
                status ENUM('pending', 'scheduled', 'delivered', 'redeemed') DEFAULT 'pending',
                delivery_date DATE DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
            ";
            
            $this->db->query($createEgiftsSQL);
        } else {
            // Check if delivery_date column exists, add it if not
            $columnCheckResult = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'delivery_date'");
            if (!$columnCheckResult || $columnCheckResult->num_rows == 0) {
                // Add the delivery_date column
                $this->db->query("ALTER TABLE {$this->table} ADD COLUMN delivery_date DATE DEFAULT NULL AFTER status");
            }
            
            // Check if 'scheduled' status is in the enum, update if not
            $columnCheckResult = $this->db->query("SHOW COLUMNS FROM {$this->table} WHERE Field = 'status'");
            if ($columnCheckResult && $columnCheckResult->num_rows > 0) {
                $statusColumn = $columnCheckResult->fetch_assoc();
                // Check if 'scheduled' is not in the enum values
                if (strpos($statusColumn['Type'], 'scheduled') === false) {
                    $this->db->query("ALTER TABLE {$this->table} MODIFY COLUMN status ENUM('pending', 'scheduled', 'delivered', 'redeemed') DEFAULT 'pending'");
                }
            }
        }
        
        // Check if notifications table exists
        $notificationsTableExists = $this->db->query("SHOW TABLES LIKE 'notifications'");
        if (!$notificationsTableExists || $notificationsTableExists->num_rows == 0) {
            // Create notifications table if it doesn't exist
            $createNotificationsSQL = "
            CREATE TABLE IF NOT EXISTS notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                message TEXT NOT NULL,
                type VARCHAR(50) NOT NULL,
                related_id INT NULL,
                is_read TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
            ";
            
            $this->db->query($createNotificationsSQL);
        }
    }
    
    // Get all available users (excluding the current user)
    public function getAvailableUsers($currentUserId) {
        $sql = "SELECT id, name, email, role FROM users WHERE id != {$currentUserId} ORDER BY name ASC";
        return $this->db->query($sql);
    }
    
    // Create a new e-gift
    public function createEGift($senderId, $recipientId, $amount, $message) {
        $data = [
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'amount' => $amount,
            'message' => $message,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->insert($data);
        
        if ($result) {
            // Create notification for recipient
            $this->createNotification($recipientId, 'You have received an e-gift card!', 'gift', $this->db->getConnection()->insert_id);
            return $this->db->getConnection()->insert_id;
        }
        
        return false;
    }
    
    // Create a new scheduled e-gift with delivery date
    public function createScheduledEGift($senderId, $recipientId, $amount, $message, $deliveryDate) {
        // Determine if delivery is immediate or scheduled
        $currentDate = date('Y-m-d');
        $isImmediate = ($deliveryDate === $currentDate);
        
        $data = [
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'amount' => $amount,
            'message' => $message,
            'status' => $isImmediate ? 'delivered' : 'scheduled',
            'delivery_date' => $deliveryDate,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->insert($data);
        
        if ($result) {
            $giftId = $this->db->getConnection()->insert_id;
            
            // If delivery is immediate, create notification now
            if ($isImmediate) {
                $this->createNotification(
                    $recipientId, 
                    'You have received an e-gift card of $' . number_format($amount, 2) . '!', 
                    'gift', 
                    $giftId
                );
            }
            
            // Create a notification for the sender as confirmation
            $this->createNotification(
                $senderId,
                'Your e-gift card of $' . number_format($amount, 2) . ' has been ' . 
                ($isImmediate ? 'sent' : 'scheduled for delivery on ' . date('F j, Y', strtotime($deliveryDate))),
                'gift_confirmation',
                $giftId
            );
            
            return $giftId;
        }
        
        return false;
    }
    
    // Process scheduled e-gifts that are due for delivery
    public function processScheduledEGifts() {
        $currentDate = date('Y-m-d');
        
        // Find scheduled e-gifts due for delivery
        $sql = "SELECT * FROM {$this->table} WHERE status = 'scheduled' AND delivery_date <= '{$currentDate}'";
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($gift = $result->fetch_assoc()) {
                // Mark gift as delivered
                $this->db->query("UPDATE {$this->table} SET status = 'delivered' WHERE id = {$gift['id']}");
                
                // Create notification for recipient
                $this->createNotification(
                    $gift['recipient_id'],
                    'You have received an e-gift card of $' . number_format($gift['amount'], 2) . '!',
                    'gift',
                    $gift['id']
                );
            }
            
            return $result->num_rows; // Return count of processed gifts
        }
        
        return 0;
    }
    
    // Create a notification
    public function createNotification($userId, $message, $type, $relatedId = null) {
        $data = [
            'user_id' => $userId,
            'message' => $message,
            'type' => $type,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($relatedId) {
            $data['related_id'] = $relatedId;
        }
        
        $sql = "INSERT INTO notifications (user_id, message, type, is_read, related_id, created_at) 
                VALUES ({$userId}, '{$this->db->getConnection()->real_escape_string($message)}', '{$type}', 0, " . 
                ($relatedId ? $relatedId : "NULL") . ", '{$data['created_at']}')";
        
        return $this->db->query($sql);
    }
    
    // Get all gifts sent by user
    public function getSentGifts($userId) {
        $sql = "SELECT e.*, r.name as recipient_name 
                FROM {$this->table} e 
                JOIN users r ON e.recipient_id = r.id 
                WHERE e.sender_id = {$userId} 
                ORDER BY e.created_at DESC";
        return $this->db->query($sql);
    }
    
    // Get all gifts received by user
    public function getReceivedGifts($userId) {
        $sql = "SELECT e.*, s.name as sender_name 
                FROM {$this->table} e 
                JOIN users s ON e.sender_id = s.id 
                WHERE e.recipient_id = {$userId} 
                ORDER BY e.created_at DESC";
        return $this->db->query($sql);
    }
    
    // Mark gift as delivered
    public function markAsDelivered($giftId) {
        $data = [
            'status' => 'delivered'
        ];
        
        return $this->update($giftId, $data);
    }
    
    // Mark gift as redeemed
    public function markAsRedeemed($giftId) {
        $data = [
            'status' => 'redeemed'
        ];
        
        return $this->update($giftId, $data);
    }
    
    // Get user's notifications
    public function getUserNotifications($userId) {
        $sql = "SELECT * FROM notifications WHERE user_id = {$userId} ORDER BY created_at DESC";
        return $this->db->query($sql);
    }
    
    // Mark notification as read
    public function markNotificationAsRead($notificationId) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = {$notificationId}";
        return $this->db->query($sql);
    }
    
    // Get unread notification count
    public function getUnreadNotificationCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = {$userId} AND is_read = 0";
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            return $data['count'];
        }
        
        return 0;
    }
}
?> 