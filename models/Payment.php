<?php
class Payment extends BaseModel {
    protected $table = 'payments';
    private $artworkModel;
    private $eGiftModel;
    
    public function __construct() {
        parent::__construct();
        $this->ensureTableStructure();
        
        // Load other models
        require_once 'models/Artwork.php';
        require_once 'models/EGift.php';
        $this->artworkModel = new Artwork();
        $this->eGiftModel = new EGift();
    }
    
    private function ensureTableStructure() {
        // Check if table exists
        $tableExists = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
        
        if (!$tableExists || $tableExists->num_rows == 0) {
            // Create payments table if it doesn't exist
            $sql = "
            CREATE TABLE IF NOT EXISTS payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                payment_type ENUM('artwork', 'egift') NOT NULL,
                related_id INT NOT NULL,
                transaction_id VARCHAR(50) NULL,
                status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
                payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
            ";
            
            $this->db->query($sql);
        } else {
            // Check if transaction_id column exists
            $columnCheckResult = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE 'transaction_id'");
            if (!$columnCheckResult || $columnCheckResult->num_rows == 0) {
                // Add the transaction_id column
                $this->db->query("ALTER TABLE {$this->table} ADD COLUMN transaction_id VARCHAR(50) NULL AFTER related_id");
            }
        }
    }
    
    // Create a new artwork payment - returns the new payment ID or false
    public function createArtworkPayment($userId, $artworkId, $amount) {
        $data = [
            'user_id' => $userId,
            'amount' => $amount,
            'payment_type' => 'artwork',
            'related_id' => $artworkId,
            'status' => 'pending',
            'payment_date' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->insert($data);
        if (!$result) {
            return false;
        }
        // Return the actual inserted payment ID (insert() returns true, not the ID)
        $paymentId = $this->db->getConnection()->insert_id;
        return $paymentId ? $paymentId : false;
    }
    
    // Create a new e-gift payment
    public function createEGiftPayment($userId, $giftId, $amount) {
        $data = [
            'user_id' => $userId,
            'amount' => $amount,
            'payment_type' => 'egift',
            'related_id' => $giftId,
            'status' => 'pending',
            'payment_date' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data);
    }
    
    // Complete an e-gift payment directly (for new flow)
    public function completeEGiftPayment($userId, $giftId, $amount, $transactionId) {
        $data = [
            'user_id' => $userId,
            'amount' => $amount,
            'payment_type' => 'egift',
            'related_id' => $giftId,
            'transaction_id' => $transactionId,
            'status' => 'completed',
            'payment_date' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data);
    }
    
    // Process a payment (simulated)
    public function processPayment($paymentId) {
        // In a real system, this would integrate with a payment gateway
        // Here we'll simulate a successful payment
        
        $paymentId = (int) $paymentId;
        if ($paymentId <= 0) {
            return false;
        }
        
        // Get payment details (must be the payment we just created)
        $payment = $this->getById($paymentId);
        
        if (!$payment || !isset($payment['payment_type']) || !isset($payment['related_id'])) {
            error_log("processPayment: payment not found or invalid for id {$paymentId}");
            return false;
        }
        
        // Generate a random transaction ID
        $transactionId = 'TX' . date('YmdHis') . rand(1000, 9999);
        
        // Mark payment as completed
        $this->update($paymentId, [
            'status' => 'completed',
            'transaction_id' => $transactionId
        ]);
        
        // Handle post-payment actions based on type
        if ($payment['payment_type'] == 'artwork') {
            $artwork = $this->artworkModel->getById($payment['related_id']);
            
            if ($artwork) {
                // Double-check artwork is not already sold (race condition protection)
                if (isset($artwork['status']) && $artwork['status'] == 'sold') {
                    // Artwork already sold, refund or handle accordingly
                    error_log("Attempted to sell already sold artwork ID: " . $payment['related_id']);
                    return false;
                }
                
                // Mark artwork as sold - this must happen first
                $soldResult = $this->artworkModel->markAsSold($payment['related_id']);
                
                if (!$soldResult) {
                    error_log("Failed to mark artwork {$payment['related_id']} as sold after payment {$paymentId}");
                    // Continue anyway to record the sale
                }
                
                // Record the sale
                $saleResult = $this->artworkModel->recordSale(
                    $payment['related_id'],     // artwork_id
                    $artwork['user_id'],        // seller_id
                    $payment['user_id'],        // buyer_id
                    $payment['amount']          // sale_price
                );
                
                if (!$saleResult) {
                    error_log("Failed to record sale for artwork {$payment['related_id']} after payment {$paymentId}");
                }
                
                // Notify the seller
                $this->eGiftModel->createNotification(
                    $artwork['user_id'],
                    'Your artwork "' . $artwork['title'] . '" has been sold for $' . number_format($payment['amount'], 2) . '!',
                    'sale',
                    $payment['related_id']
                );
            } else {
                error_log("Artwork {$payment['related_id']} not found when processing payment {$paymentId}");
            }
        } elseif ($payment['payment_type'] == 'egift') {
            // Mark gift as delivered
            $this->eGiftModel->markAsDelivered($payment['related_id']);
        }
        
        return $transactionId;
    }
    
    // Get user's payment history
    public function getUserPaymentHistory($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = {$userId} ORDER BY payment_date DESC";
        return $this->db->query($sql);
    }
    
    // Get payment by transaction ID
    public function getByTransactionId($transactionId) {
        $sql = "SELECT * FROM {$this->table} WHERE transaction_id = '{$transactionId}'";
        $result = $this->db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
}
?> 