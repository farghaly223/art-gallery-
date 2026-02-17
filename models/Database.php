<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $connection;
    private $error;
    private static $instance = null;
    
    // Constructor - attempts to connect to database or creates it if it doesn't exist
    public function __construct() {
        // Try to connect to the database, if it fails, try to create it first
        if (!$this->connectDB()) {
            self::createDatabase();
            $this->connectDB();
        }
        
        // Ensure tables exist
        $this->ensureTablesExist();
    }
    
    // Singleton pattern to get instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Database connection
    private function connectDB() {
        try {
            $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
            
            if ($this->connection->connect_error) {
                $this->error = "Connection failed: " . $this->connection->connect_error;
                return false;
            }
            return true;
        } catch (Exception $e) {
            $this->error = "Connection error: " . $e->getMessage();
            return false;
        }
    }
    
    // Ensure all required tables exist
    private function ensureTablesExist() {
        if (!$this->connection) {
            return false;
        }
        
        // Check and create all required tables
        $this->createUsersTable();
        $this->createFriendsTable();
        $this->createArtworksTable();
        $this->createSubscriptionsTable();
        $this->createSubscribersTable();
        $this->createSalesTable();
        $this->createCategoriesTable();
        $this->createEGiftsTable();
        $this->createNotificationsTable();
        
        // Ensure users table has role column
        $this->ensureRoleColumnExists();
    }
    
    // Create users table
    private function createUsersTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('viewer', 'artist', 'admin') DEFAULT 'viewer',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        ";
        
        return $this->connection->query($sql);
    }
    
    // Ensure role column exists in users table
    private function ensureRoleColumnExists() {
        $result = $this->connection->query("SHOW COLUMNS FROM users LIKE 'role'");
        if ($result->num_rows == 0) {
            // Add role column if it doesn't exist
            $this->connection->query("ALTER TABLE users ADD COLUMN role ENUM('viewer', 'artist', 'admin') DEFAULT 'viewer'");
        }
    }
    
    // Create artworks table
    private function createArtworksTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS artworks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            image_path VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            user_id INT NOT NULL,
            status ENUM('available', 'sold') DEFAULT 'available',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
        ";
        
        return $this->connection->query($sql);
    }
    
    // Create subscribers table
    private function createSubscribersTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            artist_id INT NOT NULL,
            subscriber_id INT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (artist_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (subscriber_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY artist_subscriber (artist_id, subscriber_id)
        ) ENGINE=InnoDB;
        ";
        
        return $this->connection->query($sql);
    }
    
    // Create sales table
    private function createSalesTable() {
        $sql = "
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
        ";
        
        return $this->connection->query($sql);
    }
    
    // Create categories table
    private function createCategoriesTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        ";
        
        // Create the table
        $result = $this->connection->query($sql);
        
        // Check if categories table was just created and add default categories
        if ($result) {
            $count = $this->connection->query("SELECT COUNT(*) as count FROM categories");
            $data = $count->fetch_assoc();
            
            if ($data['count'] == 0) {
                // Add default categories
                $defaultCategories = [
                    ['name' => 'Painting', 'description' => 'Paintings including oil, acrylic, watercolor, etc.'],
                    ['name' => 'Sculpture', 'description' => 'Three-dimensional art made by shaping or combining materials'],
                    ['name' => 'Photography', 'description' => 'Photographs and photographic art'],
                    ['name' => 'Digital Art', 'description' => 'Art created or presented using digital technology'],
                    ['name' => 'Drawing', 'description' => 'Artwork created using pencil, ink, charcoal, etc.'],
                    ['name' => 'Mixed Media', 'description' => 'Artwork using a combination of different media or materials']
                ];
                
                foreach ($defaultCategories as $category) {
                    $name = "'" . $this->connection->real_escape_string($category['name']) . "'";
                    $description = "'" . $this->connection->real_escape_string($category['description']) . "'";
                    
                    $this->connection->query("INSERT INTO categories (name, description) VALUES ({$name}, {$description})");
                }
            }
        }
        
        return $result;
    }
    
    // Create e-gifts table
    private function createEGiftsTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS egifts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            recipient_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            message TEXT,
            status ENUM('pending', 'delivered', 'redeemed') DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
        ";
        
        return $this->connection->query($sql);
    }
    
    // Create notifications table
    private function createNotificationsTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            message TEXT NOT NULL,
            type ENUM('gift', 'sale', 'system') NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            related_id INT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
        ";
        
        return $this->connection->query($sql);
    }
    
    // Create friends table
    private function createFriendsTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS friends (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            friend_id INT NOT NULL,
            status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_friendship (user_id, friend_id)
        ) ENGINE=InnoDB;
        ";
        
        return $this->connection->query($sql);
    }
    
    // Create subscriptions table 
    private function createSubscriptionsTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            artist_id INT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (artist_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_subscription (user_id, artist_id)
        ) ENGINE=InnoDB;
        ";
        
        return $this->connection->query($sql);
    }
    
    // Get database connection
    public function getConnection() {
        return $this->connection;
    }
    
    // Run query 
    public function query($sql) {
        $result = $this->connection->query($sql);
        
        if (!$result) {
            $this->error = "Query failed: " . $this->connection->error;
            return false;
        }
        return $result;
    }
    
    // Get error message
    public function getError() {
        return $this->error;
    }
    
    // Check if database exists, if not create it
    public static function createDatabase() {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
        if ($conn->query($sql) === TRUE) {
            // Database created successfully
            $conn->close();
            return true;
        } else {
            echo "Error creating database: " . $conn->error;
            $conn->close();
            return false;
        }
    }
}
?> 