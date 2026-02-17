<?php
class Report extends BaseModel {
    protected $table = 'reports';
    
    // Constructor to ensure proper initialization
    public function __construct() {
        parent::__construct();
        // Make sure the table has the proper structure
        $this->ensureTableStructure();
    }
    
    // Make sure the reports table has all required columns
    private function ensureTableStructure() {
        // Check if reports table exists
        $tableExists = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
        if (!$tableExists || $tableExists->num_rows == 0) {
            // Start with a basic table structure
            $createTableSQL = "
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reporter_id INT NOT NULL,
                reported_user_id INT NULL,
                artwork_id INT NULL,
                report_type ENUM('user', 'artwork', 'other') NOT NULL,
                reason TEXT NOT NULL,
                status ENUM('pending', 'resolved', 'rejected') DEFAULT 'pending',
                admin_notes TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB;
            ";
            
            $this->db->query($createTableSQL);
            
            // Check if users and artworks tables exist before trying to add foreign keys
            $usersExists = $this->db->query("SHOW TABLES LIKE 'users'");
            $artworksExists = $this->db->query("SHOW TABLES LIKE 'artworks'");
            
            if ($usersExists && $usersExists->num_rows > 0) {
                // Add foreign keys to users table after creation
                $this->db->query("ALTER TABLE {$this->table} 
                    ADD CONSTRAINT fk_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
                    ADD CONSTRAINT fk_reported_user FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE SET NULL");
            }
            
            if ($artworksExists && $artworksExists->num_rows > 0) {
                // Add foreign key to artworks table after creation
                $this->db->query("ALTER TABLE {$this->table} 
                    ADD CONSTRAINT fk_artwork_report FOREIGN KEY (artwork_id) REFERENCES artworks(id) ON DELETE SET NULL");
            }
        }
    }
    
    // Create a report
    public function createReport($reporterId, $reportType, $reason, $reportedUserId = null, $artworkId = null) {
        // Prepare data for insertion
        $data = [
            'reporter_id' => $reporterId,
            'report_type' => $reportType,
            'reason' => $reason,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($reportedUserId) {
            $data['reported_user_id'] = $reportedUserId;
        }
        
        if ($artworkId) {
            $data['artwork_id'] = $artworkId;
        }
        
        return $this->insert($data);
    }
    
    // Get reports for a specific reporter
    public function getReportsByReporter($reporterId) {
        $sql = "SELECT r.*, 
                u.name as reported_user_name,
                a.title as artwork_title
                FROM {$this->table} r
                LEFT JOIN users u ON r.reported_user_id = u.id
                LEFT JOIN artworks a ON r.artwork_id = a.id
                WHERE r.reporter_id = {$reporterId}
                ORDER BY r.created_at DESC";
                
        return $this->db->query($sql);
    }
    
    // Get report details
    public function getReportById($reportId, $reporterId = null) {
        $sql = "SELECT r.*, 
                u1.name as reporter_name,
                u2.name as reported_user_name,
                a.title as artwork_title,
                a.image_path as artwork_image
                FROM {$this->table} r
                JOIN users u1 ON r.reporter_id = u1.id
                LEFT JOIN users u2 ON r.reported_user_id = u2.id
                LEFT JOIN artworks a ON r.artwork_id = a.id
                WHERE r.id = {$reportId}";
        
        if ($reporterId) {
            $sql .= " AND r.reporter_id = {$reporterId}";
        }
        
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
}
?> 