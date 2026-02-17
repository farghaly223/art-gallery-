<?php
class Admin extends BaseModel {
    protected $table = 'users';
    
    // Constructor to ensure proper initialization
    public function __construct() {
        parent::__construct();
        // Make sure the tables have the proper structure
        $this->ensureTablesExist();
    }
    
    // Make sure the necessary tables exist
    private function ensureTablesExist() {
        // Check if user_bans table exists
        $banTableExists = $this->db->query("SHOW TABLES LIKE 'user_bans'");
        if (!$banTableExists || $banTableExists->num_rows == 0) {
            // Create user_bans table if it doesn't exist
            $createBanTableSQL = "
            CREATE TABLE IF NOT EXISTS user_bans (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                reason TEXT NOT NULL,
                banned_by INT NOT NULL,
                ban_date DATETIME NOT NULL,
                unban_date DATETIME NULL,
                is_permanent TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (banned_by) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
            ";
            
            $this->db->query($createBanTableSQL);
        }
        
        // Check if reports table exists
        $reportTableExists = $this->db->query("SHOW TABLES LIKE 'reports'");
        if (!$reportTableExists || $reportTableExists->num_rows == 0) {
            // This will automatically be handled by the Report class
            // But we'll include code to check if it exists for completeness
        }
    }
    

    
    // Get all users
    public function getAllUsers() {
        $sql = "SELECT u.*, 
                (SELECT COUNT(*) FROM artworks WHERE user_id = u.id) as artwork_count,
                (SELECT COUNT(*) FROM user_bans WHERE user_id = u.id AND (unban_date IS NULL OR unban_date > NOW())) as is_banned
                FROM {$this->table} u 
                ORDER BY u.name ASC";
        
        return $this->db->query($sql);
    }
    
    // Get all artworks
    public function getAllArtworks() {
        $sql = "SELECT a.*, u.name as artist_name 
                FROM artworks a 
                JOIN users u ON a.user_id = u.id 
                ORDER BY a.created_at DESC";
        
        return $this->db->query($sql);
    }
    
    // Get all reports
    public function getAllReports($status = null) {
        $statusFilter = $status ? "WHERE r.status = '{$status}'" : "";
        
        $sql = "SELECT r.*, 
                u1.name as reporter_name, 
                u2.name as reported_user_name,
                a.title as artwork_title
                FROM reports r
                JOIN users u1 ON r.reporter_id = u1.id
                LEFT JOIN users u2 ON r.reported_user_id = u2.id
                LEFT JOIN artworks a ON r.artwork_id = a.id
                {$statusFilter}
                ORDER BY r.created_at DESC";
        
        return $this->db->query($sql);
    }
    
    // Get report by ID
    public function getReportById($reportId) {
        $sql = "SELECT r.*, 
                u1.name as reporter_name, u1.email as reporter_email,
                u2.name as reported_user_name, u2.email as reported_user_email,
                a.title as artwork_title, a.image_path as artwork_image
                FROM reports r
                JOIN users u1 ON r.reporter_id = u1.id
                LEFT JOIN users u2 ON r.reported_user_id = u2.id
                LEFT JOIN artworks a ON r.artwork_id = a.id
                WHERE r.id = {$reportId}";
        
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Update report status
    public function updateReportStatus($reportId, $status, $adminNotes = null) {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($adminNotes) {
            $data['admin_notes'] = $adminNotes;
        }
        
        $updateFields = [];
        foreach ($data as $key => $value) {
            $updateFields[] = "{$key} = '{$this->db->getConnection()->real_escape_string($value)}'";
        }
        
        $sql = "UPDATE reports SET " . implode(", ", $updateFields) . " WHERE id = {$reportId}";
        
        return $this->db->query($sql);
    }
    
    // Ban a user
    public function banUser($userId, $reason, $adminId, $isPermanent = true, $unbanDate = null) {
        // First check if user is already banned
        $checkSql = "SELECT * FROM user_bans WHERE user_id = {$userId} AND (unban_date IS NULL OR unban_date > NOW())";
        $result = $this->db->query($checkSql);
        
        if ($result && $result->num_rows > 0) {
            // User is already banned, update ban
            $banId = $result->fetch_assoc()['id'];
            $updateData = [
                'reason' => $reason,
                'banned_by' => $adminId,
                'ban_date' => date('Y-m-d H:i:s'),
                'unban_date' => $unbanDate,
                'is_permanent' => $isPermanent ? 1 : 0
            ];
            
            $updateFields = [];
            foreach ($updateData as $key => $value) {
                if ($value === null) {
                    $updateFields[] = "{$key} = NULL";
                } else {
                    $updateFields[] = "{$key} = '{$this->db->getConnection()->real_escape_string($value)}'";
                }
            }
            
            $sql = "UPDATE user_bans SET " . implode(", ", $updateFields) . " WHERE id = {$banId}";
        } else {
            // Create new ban
            $sql = "INSERT INTO user_bans (user_id, reason, banned_by, ban_date, unban_date, is_permanent) 
                    VALUES ({$userId}, '{$this->db->getConnection()->real_escape_string($reason)}', {$adminId}, NOW(), ";
            
            $sql .= $unbanDate ? "'{$unbanDate}'" : "NULL";
            $sql .= ", " . ($isPermanent ? "1" : "0") . ")";
        }
        
        return $this->db->query($sql);
    }
    
    // Unban a user
    public function unbanUser($userId) {
        $sql = "UPDATE user_bans SET unban_date = NOW(), is_permanent = 0 WHERE user_id = {$userId} AND (unban_date IS NULL OR unban_date > NOW())";
        
        return $this->db->query($sql);
    }
    
    // Check if user is banned
    public function isUserBanned($userId) {
        $sql = "SELECT * FROM user_bans WHERE user_id = {$userId} AND (unban_date IS NULL OR unban_date > NOW())";
        $result = $this->db->query($sql);
        
        return ($result && $result->num_rows > 0);
    }
    
    // Delete a user
    public function deleteUser($userId) {
        $sql = "DELETE FROM {$this->table} WHERE id = {$userId}";
        return $this->db->query($sql);
    }
    
    // Delete an artwork
    public function deleteArtwork($artworkId) {
        $sql = "DELETE FROM artworks WHERE id = {$artworkId}";
        return $this->db->query($sql);
    }
    
    // Create a report
    public function createReport($reporterId, $reportType, $reason, $reportedUserId = null, $artworkId = null) {
        $sql = "INSERT INTO reports (reporter_id, reported_user_id, artwork_id, report_type, reason, created_at) 
                VALUES ({$reporterId}, ";
        
        $sql .= $reportedUserId ? "{$reportedUserId}" : "NULL";
        $sql .= ", ";
        $sql .= $artworkId ? "{$artworkId}" : "NULL";
        $sql .= ", '{$reportType}', '{$this->db->getConnection()->real_escape_string($reason)}', NOW())";
        
        return $this->db->query($sql);
    }
    
    // Get stats for admin dashboard
    public function getDashboardStats() {
        $stats = [];
        
        // Total users
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = $this->db->query($sql);
        $stats['total_users'] = $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
        
        // Total artists
        $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'artist'";
        $result = $this->db->query($sql);
        $stats['total_artists'] = $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
        
        // Total artworks
        $sql = "SELECT COUNT(*) as count FROM artworks";
        $result = $this->db->query($sql);
        $stats['total_artworks'] = $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
        
        // Total sold artworks
        $sql = "SELECT COUNT(*) as count FROM artworks WHERE status = 'sold'";
        $result = $this->db->query($sql);
        $stats['sold_artworks'] = $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
        
        // Pending reports
        $sql = "SELECT COUNT(*) as count FROM reports WHERE status = 'pending'";
        $result = $this->db->query($sql);
        $stats['pending_reports'] = $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
        
        // Banned users
        $sql = "SELECT COUNT(DISTINCT user_id) as count FROM user_bans WHERE (unban_date IS NULL OR unban_date > NOW())";
        $result = $this->db->query($sql);
        $stats['banned_users'] = $result && $result->num_rows > 0 ? $result->fetch_assoc()['count'] : 0;
        
        return $stats;
    }
    
    // Make user an admin
    public function makeAdmin($userId) {
        $sql = "UPDATE {$this->table} SET role = 'admin' WHERE id = {$userId}";
        return $this->db->query($sql);
    }
    
    // Get ban info for a user
    public function getUserBanInfo($userId) {
        $sql = "SELECT b.*, u.name as banned_by_name 
                FROM user_bans b 
                JOIN users u ON b.banned_by = u.id 
                WHERE b.user_id = {$userId} 
                AND (b.unban_date IS NULL OR b.unban_date > NOW())
                ORDER BY b.ban_date DESC LIMIT 1";
        
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
}
?> 