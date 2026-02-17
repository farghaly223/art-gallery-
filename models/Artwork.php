<?php
class Artwork extends BaseModel {
    protected $table = 'artworks';
    
    // Constructor to ensure proper initialization
    public function __construct() {
        parent::__construct();
        // Make sure the table has the proper structure
        $this->ensureTableStructure();
    }
    
    // Make sure the artworks table has all required columns
    private function ensureTableStructure() {
        // Check if artworks table exists
        $tableExists = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
        if (!$tableExists || $tableExists->num_rows == 0) {
            // Create artworks table if it doesn't exist
            $createTableSQL = "
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(100) NOT NULL,
                description TEXT,
                image_path VARCHAR(255) NOT NULL,
                price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                user_id INT NOT NULL,
                status ENUM('available', 'sold') DEFAULT 'available',
                category_id INT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
            ";
            
            $this->db->query($createTableSQL);
            
            // After creating the table, check for users table to add foreign key
            $usersExist = $this->db->query("SHOW TABLES LIKE 'users'");
            if ($usersExist && $usersExist->num_rows > 0) {
                $this->db->query("ALTER TABLE {$this->table} ADD CONSTRAINT fk_artwork_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
            }
            
            return; // Table created with all columns, no need to check individual columns
        }
        
        // Check if user_id column exists
        $result = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'user_id'");
        
        if (!$result || $result->num_rows == 0) {
            // Add user_id column if it doesn't exist
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN user_id INT NOT NULL");
            
            // Add foreign key constraint if users table exists
            $usersExist = $this->db->query("SHOW TABLES LIKE 'users'");
            if ($usersExist && $usersExist->num_rows > 0) {
                $this->db->query("ALTER TABLE {$this->table} ADD CONSTRAINT fk_artwork_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
            }
        }
        
        // Check if status column exists
        $result = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'status'");
        
        if (!$result || $result->num_rows == 0) {
            // Add status column if it doesn't exist
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN status ENUM('available', 'sold') DEFAULT 'available'");
        }
        
        // Check if price column exists
        $result = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'price'");
        
        if (!$result || $result->num_rows == 0) {
            // Add price column if it doesn't exist
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT 0.00");
        }
        
        // Check if category_id column exists
        $result = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'category_id'");
        
        if (!$result || $result->num_rows == 0) {
            // Add category_id column if it doesn't exist
            $this->db->query("ALTER TABLE {$this->table} ADD COLUMN category_id INT NULL");
            
            // Add foreign key constraint if categories table exists
            $categoriesExist = $this->db->query("SHOW TABLES LIKE 'categories'");
            if ($categoriesExist && $categoriesExist->num_rows > 0) {
                $this->db->query("ALTER TABLE {$this->table} ADD CONSTRAINT fk_category FOREIGN KEY (category_id) REFERENCES categories(id)");
            }
        }
    }
    
    // Create a new artwork
    public function createArtwork($title, $description, $imagePath, $price, $userId, $categoryId = null) {
        $data = [
            'title' => $title,
            'description' => $description,
            'image_path' => $imagePath,
            'price' => $price,
            'user_id' => $userId,
            'status' => 'available',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($categoryId) {
            $data['category_id'] = $categoryId;
        }
        
        return $this->insert($data);
    }
    
    // Get all artworks by user ID
    public function getArtworksByUser($userId) {
        // Check if categories table exists before joining
        $categoryTableExists = $this->db->query("SHOW TABLES LIKE 'categories'");
        
        if ($categoryTableExists && $categoryTableExists->num_rows > 0) {
            $sql = "SELECT a.*, c.name as category_name 
                    FROM {$this->table} a 
                    LEFT JOIN categories c ON a.category_id = c.id
                    WHERE a.user_id = {$userId} 
                    ORDER BY a.created_at DESC";
        } else {
            $sql = "SELECT a.*, NULL as category_name 
                    FROM {$this->table} a 
                    WHERE a.user_id = {$userId} 
                    ORDER BY a.created_at DESC";
        }
        
        return $this->db->query($sql);
    }
    
    // Get all available artworks
    public function getAvailableArtworks() {
        try {
            // First check if artworks table exists
            $artworksTableExists = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
            if (!$artworksTableExists) {
                throw new Exception("Failed to check if artworks table exists: " . $this->db->getError());
            }
            
            if ($artworksTableExists->num_rows == 0) {
                // If artworks table doesn't exist yet
                return array();
            }
            
            $hasStatus = $this->columnExists('status');
            
            // Check if users table exists before joining
            $usersTableExists = $this->db->query("SHOW TABLES LIKE 'users'");
            if (!$usersTableExists) {
                throw new Exception("Failed to check if users table exists: " . $this->db->getError());
            }
            
            // If users table doesn't exist, get basic artwork info without joining
            if ($usersTableExists->num_rows == 0) {
                if ($hasStatus) {
                    $sql = "SELECT a.* FROM {$this->table} a WHERE a.status = 'available' ORDER BY a.created_at DESC";
                } else {
                    $sql = "SELECT a.* FROM {$this->table} a ORDER BY a.created_at DESC";
                }
                
                $result = $this->db->query($sql);
                if (!$result) {
                    throw new Exception("Error fetching artworks (without users): " . $this->db->getError());
                }
                return $result;
            }
            
            // Check if categories table exists before joining
            $categoryTableExists = $this->db->query("SHOW TABLES LIKE 'categories'");
            if (!$categoryTableExists) {
                throw new Exception("Failed to check if categories table exists: " . $this->db->getError());
            }
            
            $categoryJoin = ($categoryTableExists->num_rows > 0) 
                ? "LEFT JOIN categories c ON a.category_id = c.id" 
                : "";
            
            // Build select clause
            $select = "a.*, u.name as artist_name";
            if ($categoryTableExists->num_rows > 0) {
                $select .= ", c.name as category_name";
            } else {
                $select .= ", NULL as category_name";
            }
            
            if ($hasStatus) {
                $sql = "SELECT {$select} 
                        FROM {$this->table} a 
                        JOIN users u ON a.user_id = u.id 
                        {$categoryJoin}
                        WHERE a.status = 'available' 
                        ORDER BY a.created_at DESC";
            } else {
                // If status column doesn't exist yet, just get all artworks
                $sql = "SELECT {$select} 
                        FROM {$this->table} a 
                        JOIN users u ON a.user_id = u.id 
                        {$categoryJoin}
                        ORDER BY a.created_at DESC";
            }
            
            $result = $this->db->query($sql);
            if (!$result) {
                throw new Exception("Error fetching artworks (with joins): " . $this->db->getError());
            }
            return $result;
        } catch (Exception $e) {
            // Re-throw exception with additional context
            throw new Exception("GetAvailableArtworks failed: " . $e->getMessage());
        }
    }
    
    // Check if a column exists in the table
    private function columnExists($columnName) {
        // First check if the table exists
        $tableExists = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
        if (!$tableExists || $tableExists->num_rows == 0) {
            return false; // Table doesn't exist yet
        }
        
        // Check if column exists
        $result = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE '{$columnName}'");
        return ($result && $result->num_rows > 0);
    }
    
    // Mark artwork as sold
    public function markAsSold($artworkId) {
        // Ensure status column exists
        if (!$this->columnExists('status')) {
            $this->ensureTableStructure();
        }
        
        // Use direct SQL update to ensure it works
        $sql = "UPDATE {$this->table} SET status = 'sold' WHERE id = " . intval($artworkId);
        $result = $this->db->query($sql);
        
        // Log for debugging
        if (!$result) {
            error_log("Failed to mark artwork {$artworkId} as sold: " . $this->db->getError());
        }
        
        return $result;
    }
    
    // Count all artworks by an artist
    public function countArtworksByArtist($artistId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = {$artistId}";
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            return $data['count'];
        }
        
        return 0;
    }
    
    // Count sold artworks by an artist
    public function countSoldArtworksByArtist($artistId) {
        if (!$this->columnExists('status')) {
            $this->ensureTableStructure();
            return 0; // No artworks could be sold yet if the column was just created
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = {$artistId} AND status = 'sold'";
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            return $data['count'];
        }
        
        return 0;
    }
    
    // Get total revenue from sold artworks
    public function getTotalRevenue($artistId) {
        // Check if sales table exists
        $result = $this->db->query("SHOW TABLES LIKE 'sales'");
        if (!$result || $result->num_rows == 0) {
            // Sales table doesn't exist yet
            return 0;
        }
        
        $sql = "SELECT SUM(sale_price) as total FROM sales WHERE seller_id = {$artistId}";
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            return $data['total'] ? $data['total'] : 0;
        }
        
        return 0;
    }
    
    // Record a sale
    public function recordSale($artworkId, $sellerId, $buyerId, $price) {
        // Make sure status column exists before marking as sold
        if (!$this->columnExists('status')) {
            $this->ensureTableStructure();
        }
        
        // Mark artwork as sold
        $this->markAsSold($artworkId);
        
        // Check if sales table exists
        $result = $this->db->query("SHOW TABLES LIKE 'sales'");
        if (!$result || $result->num_rows == 0) {
            // Create sales table if it doesn't exist
            $this->db->query("
                CREATE TABLE IF NOT EXISTS sales (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    artwork_id INT NOT NULL,
                    seller_id INT NOT NULL,
                    buyer_id INT NOT NULL,
                    sale_price DECIMAL(10,2) NOT NULL,
                    sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (artwork_id) REFERENCES artworks(id) ON DELETE CASCADE,
                    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB;
            ");
        }
        
        // Insert sale record
        $sql = "INSERT INTO sales (artwork_id, seller_id, buyer_id, sale_price) 
                VALUES ({$artworkId}, {$sellerId}, {$buyerId}, {$price})";
        
        return $this->db->query($sql);
    }
    
    // Delete artwork
    public function deleteArtwork($artworkId, $userId) {
        // Ensure the user owns the artwork
        $sql = "DELETE FROM {$this->table} WHERE id = {$artworkId} AND user_id = {$userId}";
        return $this->db->query($sql);
    }
    
    // Upload artwork image
    public function uploadImage($file) {
        $targetDir = "public/uploads/";
        
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
    
    // Get artworks filtered by search term, category, price range
    public function searchArtworks($search = null, $categoryId = null, $minPrice = null, $maxPrice = null) {
        try {
            // First check if artworks table exists
            $artworksTableExists = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
            if (!$artworksTableExists) {
                throw new Exception("Failed to check if artworks table exists: " . $this->db->getError());
            }
            
            if ($artworksTableExists->num_rows == 0) {
                // If artworks table doesn't exist yet
                return array();
            }
            
            // Check if users table exists before joining
            $usersTableExists = $this->db->query("SHOW TABLES LIKE 'users'");
            if (!$usersTableExists) {
                throw new Exception("Failed to check if users table exists: " . $this->db->getError());
            }
            
            $conditions = [];
            
            // If users table doesn't exist, get basic artwork info without joining
            if ($usersTableExists->num_rows == 0) {
                // Add status condition if column exists
                if ($this->columnExists('status')) {
                    $conditions[] = "a.status = 'available'";
                }
                
                // Add search condition
                if ($search) {
                    $search = $this->db->getConnection()->real_escape_string($search);
                    $conditions[] = "(a.title LIKE '%{$search}%' OR a.description LIKE '%{$search}%')";
                }
                
                // Add price range conditions
                if ($minPrice && is_numeric($minPrice)) {
                    $conditions[] = "a.price >= {$minPrice}";
                }
                
                if ($maxPrice && is_numeric($maxPrice)) {
                    $conditions[] = "a.price <= {$maxPrice}";
                }
                
                // Build conditions string
                $conditionsStr = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";
                
                $sql = "SELECT a.* 
                        FROM {$this->table} a 
                        {$conditionsStr}
                        ORDER BY a.created_at DESC";
                
                $result = $this->db->query($sql);
                if (!$result) {
                    throw new Exception("Error searching artworks (without users): " . $this->db->getError());
                }
                return $result;
            }
            
            // User table exists, so continue with the joins
            $joins = ["JOIN users u ON a.user_id = u.id"];
            
            // Check if categories table exists before joining
            $categoryTableExists = $this->db->query("SHOW TABLES LIKE 'categories'");
            if (!$categoryTableExists) {
                throw new Exception("Failed to check if categories table exists: " . $this->db->getError());
            }
            
            if ($categoryTableExists->num_rows > 0) {
                $joins[] = "LEFT JOIN categories c ON a.category_id = c.id";
            }
            
            // Add status condition if column exists
            if ($this->columnExists('status')) {
                $conditions[] = "a.status = 'available'";
            }
            
            // Add search condition
            if ($search) {
                $search = $this->db->getConnection()->real_escape_string($search);
                if ($usersTableExists->num_rows > 0) {
                    $conditions[] = "(a.title LIKE '%{$search}%' OR a.description LIKE '%{$search}%' OR u.name LIKE '%{$search}%')";
                } else {
                    $conditions[] = "(a.title LIKE '%{$search}%' OR a.description LIKE '%{$search}%')";
                }
            }
            
            // Add category condition
            if ($categoryId && is_numeric($categoryId) && $categoryTableExists->num_rows > 0) {
                $conditions[] = "a.category_id = {$categoryId}";
            }
            
            // Add price range conditions
            if ($minPrice && is_numeric($minPrice)) {
                $conditions[] = "a.price >= {$minPrice}";
            }
            
            if ($maxPrice && is_numeric($maxPrice)) {
                $conditions[] = "a.price <= {$maxPrice}";
            }
            
            // Build conditions string
            $conditionsStr = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";
            
            // Build select clause
            $select = "a.*";
            $select .= ", u.name as artist_name";
            
            if ($categoryTableExists->num_rows > 0) {
                $select .= ", c.name as category_name";
            } else {
                $select .= ", NULL as category_name";
            }
            
            // Build final query
            $sql = "SELECT {$select}
                    FROM {$this->table} a 
                    " . implode(" ", $joins) . "
                    {$conditionsStr}
                    ORDER BY a.created_at DESC";
            
            $result = $this->db->query($sql);
            if (!$result) {
                throw new Exception("Error searching artworks (with joins): " . $this->db->getError());
            }
            return $result;
        } catch (Exception $e) {
            // Re-throw exception with additional context
            throw new Exception("SearchArtworks failed: " . $e->getMessage());
        }
    }
    
    // Get all categories from the database
    public function getAllCategories() {
        try {
            // Check if categories table exists
            $categoryTableExists = $this->db->query("SHOW TABLES LIKE 'categories'");
            if (!$categoryTableExists) {
                throw new Exception("Failed to check if categories table exists: " . $this->db->getError());
            }
            
            if ($categoryTableExists->num_rows == 0) {
                // If categories table doesn't exist, return empty array
                return array();
            }
            
            $sql = "SELECT * FROM categories ORDER BY name ASC";
            $result = $this->db->query($sql);
            if (!$result) {
                throw new Exception("Error fetching categories: " . $this->db->getError());
            }
            return $result;
        } catch (Exception $e) {
            // Re-throw with additional context
            throw new Exception("GetAllCategories failed: " . $e->getMessage());
        }
    }
    
    // Get category name by ID
    public function getCategoryNameById($categoryId) {
        // Check if categories table exists
        $tableExists = $this->db->query("SHOW TABLES LIKE 'categories'");
        if (!$tableExists || $tableExists->num_rows == 0) {
            return null;
        }
        
        $sql = "SELECT name FROM categories WHERE id = " . intval($categoryId);
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $category = $result->fetch_assoc();
            return $category['name'];
        }
        
        return null;
    }
}
?> 