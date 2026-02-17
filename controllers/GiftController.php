<?php
require_once 'models/EGift.php';
require_once 'models/User.php';
require_once 'models/Payment.php';

class GiftController extends BaseController {
    private $eGiftModel;
    private $userModel;
    private $paymentModel;
    
    public function __construct() {
        // Require login for all gift actions
        $this->requireLogin();
        
        $this->eGiftModel = new EGift();
        $this->userModel = new User();
        $this->paymentModel = new Payment();
    }
    
    // Show the E-Gift hub page
    public function hub() {
        // Only viewers can access the E-Gift hub
        if ($_SESSION['user_role'] != 'viewer') {
            $_SESSION['errors'] = ['Only viewers can access E-Gift features'];
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            
            // Get sent and received gifts count
            $sentGifts = $this->eGiftModel->getSentGifts($userId);
            $receivedGifts = $this->eGiftModel->getReceivedGifts($userId);
            
            $sentCount = 0;
            $receivedCount = 0;
            $recentActivity = [];
            
            if ($sentGifts && $sentGifts->num_rows > 0) {
                $sentCount = $sentGifts->num_rows;
                
                // Get the 3 most recent sent gifts for activity
                $sentGifts->data_seek(0);
                $count = 0;
                while ($gift = $sentGifts->fetch_assoc()) {
                    if ($count >= 3) break;
                    $recentActivity[] = [
                        'type' => 'sent',
                        'amount' => $gift['amount'],
                        'name' => $gift['recipient_name'],
                        'date' => $gift['created_at']
                    ];
                    $count++;
                }
            }
            
            if ($receivedGifts && $receivedGifts->num_rows > 0) {
                $receivedCount = $receivedGifts->num_rows;
                
                // Get the 3 most recent received gifts for activity
                $receivedGifts->data_seek(0);
                $count = 0;
                while ($gift = $receivedGifts->fetch_assoc()) {
                    if ($count >= 3) break;
                    $recentActivity[] = [
                        'type' => 'received',
                        'amount' => $gift['amount'],
                        'name' => $gift['sender_name'],
                        'date' => $gift['created_at']
                    ];
                    $count++;
                }
            }
            
            // Sort recent activity by date (newest first)
            usort($recentActivity, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            
            // Take only the 5 most recent activities
            $recentActivity = array_slice($recentActivity, 0, 5);
            
            $data = [
                'title' => 'E-Gift Cards Hub',
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'sent_count' => $sentCount,
                'received_count' => $receivedCount,
                'recent_activity' => $recentActivity,
                'unread_notifications' => $this->eGiftModel->getUnreadNotificationCount($userId)
            ];
            
            $this->view('gift/hub', $data);
        } catch (Exception $e) {
            $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
    
    // Show send gift form
    public function send() {
        // Only viewers can send gifts
        if ($_SESSION['user_role'] != 'viewer') {
            $_SESSION['errors'] = ['Only viewers can send e-gifts'];
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        try {
            // Get all available users
            $users = $this->eGiftModel->getAvailableUsers($_SESSION['user_id']);
            
            $data = [
                'title' => 'Send E-Gift',
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'users' => $users
            ];
            
            $this->view('gift/send', $data);
        } catch (Exception $e) {
            $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
    
    // Show buy gift form (new dedicated page)
    public function buy() {
        // Only viewers can send gifts
        if ($_SESSION['user_role'] != 'viewer') {
            $_SESSION['errors'] = ['Only viewers can buy e-gifts'];
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        try {
            // Get all available users
            $users = $this->eGiftModel->getAvailableUsers($_SESSION['user_id']);
            
            $data = [
                'title' => 'Buy E-Gift Card',
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'users' => $users
            ];
            
            $this->view('gift/buy', $data);
        } catch (Exception $e) {
            $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
    
    // Process gift send form
    public function process() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('index.php?controller=gift&action=send');
            return;
        }
        
        // Only viewers can send gifts
        if ($_SESSION['user_role'] != 'viewer') {
            $_SESSION['errors'] = ['Only viewers can send e-gifts'];
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        // Get form data
        $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        // Validate data
        $errors = [];
        if ($recipientId <= 0) {
            $errors[] = 'Please select a recipient';
        }
        
        if ($amount <= 0) {
            $errors[] = 'Amount must be greater than 0';
        }
        
        // If no errors, create e-gift
        if (empty($errors)) {
            $senderId = $_SESSION['user_id'];
            
            try {
                $giftId = $this->eGiftModel->createEGift($senderId, $recipientId, $amount, $message);
                
                if ($giftId) {
                    // Create pending payment record
                    $this->paymentModel->createEGiftPayment($senderId, $giftId, $amount);
                    
                    // Redirect to payment page
                    $this->redirect('index.php?controller=payment&action=process&type=egift&id=' . $giftId);
                } else {
                    $errors[] = 'Failed to create e-gift. Please try again.';
                }
            } catch (Exception $e) {
                $errors[] = 'Error: ' . $e->getMessage();
            }
        }
        
        // If there are errors, redirect back to send form
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('index.php?controller=gift&action=send');
        }
    }
    
    // Process payment for e-gift (new dedicated payment process)
    public function processPayment() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('index.php?controller=gift&action=buy');
            return;
        }
        
        // Only viewers can send gifts
        if ($_SESSION['user_role'] != 'viewer') {
            $_SESSION['errors'] = ['Only viewers can buy e-gifts'];
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        // Get form data
        $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        $deliveryDate = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : date('Y-m-d');
        
        // Validate data
        $errors = [];
        if ($recipientId <= 0) {
            $errors[] = 'Please select a recipient';
        }
        
        if ($amount < 10) {
            $errors[] = 'Amount must be at least $10';
        }
        
        // If there are errors, redirect back to buy form
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('index.php?controller=gift&action=buy');
            return;
        }
        
        // Store gift data in session for payment page
        $_SESSION['gift_data'] = [
            'recipient_id' => $recipientId,
            'amount' => $amount,
            'message' => $message,
            'delivery_date' => $deliveryDate
        ];
        
        try {
            // Get recipient name for display
            $recipient = $this->userModel->getUserById($recipientId);
            $recipientName = $recipient ? $recipient['name'] : 'Unknown';
            
            $data = [
                'title' => 'Complete E-Gift Payment',
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'gift_data' => $_SESSION['gift_data'],
                'recipient_name' => $recipientName
            ];
            
            $this->view('gift/payment', $data);
        } catch (Exception $e) {
            $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
            $this->redirect('index.php?controller=gift&action=buy');
        }
    }
    
    // Complete payment and create e-gift
    public function completePayment() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('index.php?controller=gift&action=buy');
            return;
        }
        
        // Only viewers can send gifts
        if ($_SESSION['user_role'] != 'viewer') {
            $_SESSION['errors'] = ['Only viewers can buy e-gifts'];
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        // Check if gift data exists in session
        if (!isset($_SESSION['gift_data'])) {
            $_SESSION['errors'] = ['Gift data not found. Please try again.'];
            $this->redirect('index.php?controller=gift&action=buy');
            return;
        }
        
        $giftData = $_SESSION['gift_data'];
        $senderId = $_SESSION['user_id'];
        $recipientId = $giftData['recipient_id'];
        $amount = $giftData['amount'];
        $message = $giftData['message'];
        $deliveryDate = $giftData['delivery_date'];
        
        try {
            // Create e-gift with scheduled delivery date
            $giftId = $this->eGiftModel->createScheduledEGift($senderId, $recipientId, $amount, $message, $deliveryDate);
            
            if ($giftId) {
                // Create completed payment record with random transaction ID
                $transactionId = 'EGIFT' . date('YmdHis') . rand(1000, 9999);
                $this->paymentModel->completeEGiftPayment($senderId, $giftId, $amount, $transactionId);
                
                // Get recipient name for confirmation page
                $recipient = $this->userModel->getUserById($recipientId);
                $recipientName = $recipient ? $recipient['name'] : 'Unknown';
                
                // Show confirmation page
                $data = [
                    'title' => 'E-Gift Payment Confirmation',
                    'user_name' => $_SESSION['user_name'],
                    'user_role' => $_SESSION['user_role'],
                    'gift_data' => $giftData,
                    'recipient_name' => $recipientName,
                    'transaction_id' => $transactionId
                ];
                
                // Clear gift data from session
                unset($_SESSION['gift_data']);
                
                $this->view('gift/confirmation', $data);
            } else {
                $_SESSION['errors'] = ['Failed to create e-gift. Please try again.'];
                $this->redirect('index.php?controller=gift&action=buy');
            }
        } catch (Exception $e) {
            $_SESSION['errors'] = ['Error: ' . $e->getMessage()];
            $this->redirect('index.php?controller=gift&action=buy');
        }
    }
    
    // Show gift history
    public function history() {
        try {
            $userId = $_SESSION['user_id'];
            
            // Get sent and received gifts
            $sentGifts = $this->eGiftModel->getSentGifts($userId);
            $receivedGifts = $this->eGiftModel->getReceivedGifts($userId);
            
            $data = [
                'title' => 'E-Gift History',
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'sent_gifts' => $sentGifts,
                'received_gifts' => $receivedGifts
            ];
            
            $this->view('gift/history', $data);
        } catch (Exception $e) {
            $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
    
    // Show notifications
    public function notifications() {
        try {
            $userId = $_SESSION['user_id'];
            
            // Get notifications
            $notifications = $this->eGiftModel->getUserNotifications($userId);
            
            $data = [
                'title' => 'Notifications',
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'notifications' => $notifications
            ];
            
            $this->view('gift/notifications', $data);
        } catch (Exception $e) {
            $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
    
    // Mark notification as read
    public function markRead() {
        if (isset($_GET['id'])) {
            $notificationId = (int)$_GET['id'];
            
            try {
                $this->eGiftModel->markNotificationAsRead($notificationId);
            } catch (Exception $e) {
                $_SESSION['errors'] = ['Error: ' . $e->getMessage()];
            }
        }
        
        $this->redirect('index.php?controller=gift&action=notifications');
    }
}
?> 