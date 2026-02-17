<?php
class User extends BaseModel {
    protected $table = 'users';
    
    // Constructor to ensure proper initialization
    public function __construct() {
        parent::__construct();
        // Make sure the table has the proper structure
        $this->ensureTableStructure();
    }
    
    // Make sure the users table has all required columns
    private function ensureTableStructure() {
        // Check if users table exists
        $tableExists = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
        if (!$tableExists || $tableExists->num_rows == 0) {
            // Create users table if it doesn't exist
            $createTableSQL = "
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'artist', 'viewer') DEFAULT 'viewer',
                profile_image VARCHAR(255) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
            ";
            
            $this->db->query($createTableSQL);
            return; // Table created with all columns, no need to check individual columns
        }
        
        // Check if profile_image column exists
        $result = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'profile_image'");
        
        if (!$result || $result->num_rows == 0) {
            // Add profile_image column if it doesn't exist
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN profile_image VARCHAR(255) NULL");
        }
        
        // Check if name column exists (from previous fixes)
        $result = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'name'");
        
        if (!$result || $result->num_rows == 0) {
            // Add name column if it doesn't exist
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN name VARCHAR(100) NOT NULL AFTER id");
        }
    }
    
    // Register a new user
    public function register($name, $email, $password, $role = 'viewer') {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare data for insertion
        $data = [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert user
        return $this->insert($data);
    }
    
    // Login a user
    public function login($email, $password) {
        // Escape email for SQL
        $emailEscaped = $this->db->getConnection()->real_escape_string(trim($email));
        $sql = "SELECT * FROM {$this->table} WHERE email = '{$emailEscaped}' LIMIT 1";
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $storedPassword = $user['password'];
            
            // Verify password: try hashed first (bcrypt), then plain-text fallback for manually set passwords
            $passwordValid = false;
            
            if (password_verify($password, $storedPassword)) {
                $passwordValid = true;
            } elseif ($this->isPasswordHashed($storedPassword) === false) {
                // Stored password is not a hash (e.g. manually set in DB) - compare plain text
                $passwordValid = ($password === $storedPassword);
                if ($passwordValid) {
                    // Upgrade: re-save as hashed for security
                    $this->update($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
                }
            }
            
            if ($passwordValid) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                return true;
            }
        }
        
        return false;
    }
    
    // Check if stored password looks like a bcrypt hash
    private function isPasswordHashed($stored) {
        return (is_string($stored) && strlen($stored) >= 60 && (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0 || strpos($stored, '$2b$') === 0));
    }
    
    // Logout a user
    public function logout() {
        // Remove session variables
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        
        // Destroy the session
        session_destroy();
        
        return true;
    }
    
    // Check if email already exists
    public function emailExists($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = '{$email}' LIMIT 1";
        $result = $this->db->query($sql);
        
        return ($result && $result->num_rows > 0);
    }
    
    // Update user role
    public function updateRole($userId, $role) {
        $data = [
            'role' => $role
        ];
        
        return $this->update($userId, $data);
    }
    
    // Get user by ID
    public function getUserById($userId) {
        return $this->getById($userId);
    }
    
    // Get all artists
    public function getAllArtists() {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'artist'";
        return $this->db->query($sql);
    }
    
    // Count subscribers for an artist
    public function countSubscribers($artistId) {
        // Check if subscriptions table exists
        $result = $this->db->query("SHOW TABLES LIKE 'subscriptions'");
        if (!$result || $result->num_rows == 0) {
            return 0; // No subscribers yet
        }
        
        $sql = "SELECT COUNT(*) as count FROM subscriptions WHERE artist_id = {$artistId}";
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            return $data['count'];
        }
        
        return 0;
    }
    
    // Check if a user is subscribed to an artist
    public function isSubscribed($userId, $artistId) {
        // Ensure subscriptions table exists
        $result = $this->db->query("SHOW TABLES LIKE 'subscriptions'");
        if (!$result || $result->num_rows == 0) {
            return false;
        }
        
        $sql = "SELECT * FROM subscriptions WHERE user_id = {$userId} AND artist_id = {$artistId}";
        $result = $this->db->query($sql);
        
        return ($result && $result->num_rows > 0);
    }
    
    // Subscribe to an artist
    public function subscribe($artistId, $userId) {
        // Ensure subscriptions table exists
        $result = $this->db->query("SHOW TABLES LIKE 'subscriptions'");
        if (!$result || $result->num_rows == 0) {
            return false;
        }
        
        // Check if already subscribed
        if ($this->isSubscribed($userId, $artistId)) {
            return true;
        }
        
        $sql = "INSERT INTO subscriptions (user_id, artist_id, created_at) VALUES ({$userId}, {$artistId}, NOW())";
        $result = $this->db->query($sql);
        
        // Create notification for artist if subscription was successful
        if ($result) {
            $this->createSubscriptionNotification($artistId, $userId);
        }
        
        return $result;
    }
    
    // Create subscription notification
    private function createSubscriptionNotification($artistId, $subscriberId) {
        $subscriber = $this->getById($subscriberId);
        $message = "{$subscriber['name']} subscribed to your artwork";
        
        $sql = "INSERT INTO notifications (user_id, message, type, related_id, is_read, created_at) 
                VALUES ({$artistId}, '{$this->db->getConnection()->real_escape_string($message)}', 'subscription', {$subscriberId}, 0, NOW())";
        
        return $this->db->query($sql);
    }
    
    // Unsubscribe from an artist
    public function unsubscribe($artistId, $userId) {
        // Ensure subscriptions table exists
        $result = $this->db->query("SHOW TABLES LIKE 'subscriptions'");
        if (!$result || $result->num_rows == 0) {
            return true; // Nothing to unsubscribe from
        }
        
        $sql = "DELETE FROM subscriptions WHERE user_id = {$userId} AND artist_id = {$artistId}";
        return $this->db->query($sql);
    }

    // Search users by name or email
    public function searchUsers($query, $currentUserId = null) {
        $query = $this->db->getConnection()->real_escape_string($query);
        $sql = "SELECT id, name, email, role, profile_image, created_at 
                FROM users 
                WHERE (name LIKE '%{$query}%' OR email LIKE '%{$query}%')";
        
        // Exclude current user if provided
        if ($currentUserId) {
            $sql .= " AND id != {$currentUserId}";
        }
        
        $sql .= " ORDER BY name ASC";
        
        return $this->db->query($sql);
    }

    // Send friend request
    public function sendFriendRequest($userId, $friendId) {
        // Check if request already exists
        $checkSql = "SELECT * FROM friends 
                    WHERE (user_id = {$userId} AND friend_id = {$friendId}) 
                    OR (user_id = {$friendId} AND friend_id = {$userId})";
        $result = $this->db->query($checkSql);
        
        if ($result && $result->num_rows > 0) {
            // Friendship already exists or pending
            return false;
        }
        
        $data = [
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $sql = "INSERT INTO friends (user_id, friend_id, status, created_at) 
                VALUES ({$userId}, {$friendId}, 'pending', '{$data['created_at']}')";
        
        $result = $this->db->query($sql);
        
        if ($result) {
            // Create notification for friend
            $this->createFriendNotification($friendId, $userId);
            return true;
        }
        
        return false;
    }

    // Create friend request notification
    private function createFriendNotification($userId, $requesterId) {
        $requester = $this->getById($requesterId);
        $message = "{$requester['name']} sent you a friend request";
        
        $sql = "INSERT INTO notifications (user_id, message, type, related_id, is_read, created_at) 
                VALUES ({$userId}, '{$this->db->getConnection()->real_escape_string($message)}', 'friend', {$requesterId}, 0, NOW())";
        
        return $this->db->query($sql);
    }

    // Get all friend requests
    public function getFriendRequests($userId) {
        // First check if the friends table exists
        $tableExists = $this->db->query("SHOW TABLES LIKE 'friends'");
        if (!$tableExists || $tableExists->num_rows == 0) {
            return array(); // Return empty array if table doesn't exist
        }
        
        $sql = "SELECT f.*, u.name, u.email, u.role, u.profile_image 
                FROM friends f 
                JOIN users u ON f.user_id = u.id 
                WHERE f.friend_id = {$userId} AND f.status = 'pending' 
                ORDER BY f.created_at DESC";
        
        return $this->db->query($sql);
    }

    // Get all friends
    public function getFriends($userId) {
        // First check if the friends table exists
        $tableExists = $this->db->query("SHOW TABLES LIKE 'friends'");
        if (!$tableExists || $tableExists->num_rows == 0) {
            return array(); // Return empty array if table doesn't exist
        }
        
        $sql = "SELECT u.id, u.name, u.email, u.role, u.profile_image, f.created_at 
                FROM users u 
                JOIN friends f ON (u.id = f.friend_id OR u.id = f.user_id) 
                WHERE ((f.user_id = {$userId} OR f.friend_id = {$userId}) 
                AND f.status = 'accepted' 
                AND u.id != {$userId}) 
                ORDER BY u.name ASC";
        
        return $this->db->query($sql);
    }

    // Accept friend request
    public function acceptFriendRequest($requestId) {
        $sql = "UPDATE friends SET status = 'accepted' WHERE id = {$requestId}";
        return $this->db->query($sql);
    }

    // Reject friend request
    public function rejectFriendRequest($requestId) {
        $sql = "UPDATE friends SET status = 'rejected' WHERE id = {$requestId}";
        return $this->db->query($sql);
    }

    // Subscribe to artist
    public function subscribeToArtist($userId, $artistId) {
        return $this->subscribe($artistId, $userId);
    }

    // Unsubscribe from artist
    public function unsubscribeFromArtist($userId, $artistId) {
        return $this->unsubscribe($artistId, $userId);
    }

    // Get user's subscriptions
    public function getSubscriptions($userId) {
        // First check if the subscriptions table exists
        $tableExists = $this->db->query("SHOW TABLES LIKE 'subscriptions'");
        if (!$tableExists || $tableExists->num_rows == 0) {
            return array(); // Return empty array if table doesn't exist
        }

        $sql = "SELECT u.id, u.name, u.email, u.profile_image, s.created_at 
                FROM users u 
                JOIN subscriptions s ON u.id = s.artist_id 
                WHERE s.user_id = {$userId} 
                ORDER BY u.name ASC";
        
        return $this->db->query($sql);
    }

    // Get artist's subscribers
    public function getSubscribers($artistId) {
        // First check if the subscriptions table exists
        $tableExists = $this->db->query("SHOW TABLES LIKE 'subscriptions'");
        if (!$tableExists || $tableExists->num_rows == 0) {
            return array(); // Return empty array if table doesn't exist
        }
        
        $sql = "SELECT u.id, u.name, u.email, u.profile_image, s.created_at 
                FROM users u 
                JOIN subscriptions s ON u.id = s.user_id 
                WHERE s.artist_id = {$artistId} 
                ORDER BY u.name ASC";
        
        return $this->db->query($sql);
    }

    // Check if user is already friend or has pending request
    public function checkFriendStatus($userId, $friendId) {
        // First check if the friends table exists
        $tableExists = $this->db->query("SHOW TABLES LIKE 'friends'");
        if (!$tableExists || $tableExists->num_rows == 0) {
            return false; // Return false if table doesn't exist
        }
        
        $sql = "SELECT * FROM friends 
                WHERE (user_id = {$userId} AND friend_id = {$friendId}) 
                OR (user_id = {$friendId} AND friend_id = {$userId})";
        
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }

    // Add profile image functionality
    public function uploadProfileImage($file) {
        $targetDir = "public/uploads/profiles/";
        
        // Create directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . basename($file['name']);
        $targetFile = $targetDir . $filename;
        
        // Check file size (max 5MB)
        if ($file['size'] > 5000000) {
            return false;
        }
        
        // Check file type (only images)
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            return false;
        }
        
        // Upload file
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $targetFile;
        }
        
        return false;
    }
    
    // Update user profile image
    public function updateProfileImage($userId, $imagePath) {
        $data = [
            'profile_image' => $imagePath
        ];
        
        return $this->update($userId, $data);
    }
}
?> 