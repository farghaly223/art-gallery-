<?php
require_once 'models/Payment.php';
require_once 'models/Artwork.php';
require_once 'models/EGift.php';

class PaymentController extends BaseController {
    private $paymentModel;
    private $artworkModel;
    private $eGiftModel;
    
    public function __construct() {
        // Require login for all payment actions
        $this->requireLogin();
        
        $this->paymentModel = new Payment();
        $this->artworkModel = new Artwork();
        $this->eGiftModel = new EGift();
    }
    
    // Process a payment
    public function process() {
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (empty($type) || $id <= 0) {
            $_SESSION['errors'] = ['Invalid payment request'];
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $paymentData = [];
            
            if ($type == 'artwork') {
                // Get artwork details
                $artwork = $this->artworkModel->getById($id);
                
                if (!$artwork) {
                    $_SESSION['errors'] = ['Artwork not found'];
                    $this->redirect('index.php?controller=gallery&action=index');
                    return;
                }
                
                // Check if artwork is already sold
                if (isset($artwork['status']) && $artwork['status'] == 'sold') {
                    $_SESSION['errors'] = ['This artwork has already been sold and is no longer available for purchase.'];
                    $this->redirect('index.php?controller=gallery&action=viewArtwork&id=' . $id);
                    return;
                }
                
                $paymentData = [
                    'title' => 'Payment - ' . $artwork['title'],
                    'amount' => $artwork['price'],
                    'item_name' => $artwork['title'],
                    'item_id' => $artwork['id'],
                    'type' => 'artwork'
                ];
            } elseif ($type == 'egift') {
                // Get e-gift details
                $gift = $this->eGiftModel->getById($id);
                
                if (!$gift || $gift['sender_id'] != $userId) {
                    $_SESSION['errors'] = ['E-gift not found or you are not authorized'];
                    $this->redirect('index.php?controller=gallery&action=index');
                    return;
                }
                
                // Get recipient details
                $recipient = $this->userModel->getById($gift['recipient_id']);
                
                $paymentData = [
                    'title' => 'Payment - E-Gift for ' . $recipient['name'],
                    'amount' => $gift['amount'],
                    'item_name' => 'E-Gift for ' . $recipient['name'],
                    'item_id' => $gift['id'],
                    'type' => 'egift'
                ];
            } else {
                $_SESSION['errors'] = ['Invalid payment type'];
                $this->redirect('index.php?controller=gallery&action=index');
                return;
            }
            
            $data = [
                'title' => $paymentData['title'],
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'payment' => $paymentData
            ];
            
            $this->view('payment/process', $data);
        } catch (Exception $e) {
            $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
    
    // Complete payment
    public function complete() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        // Get form data
        $type = isset($_POST['type']) ? $_POST['type'] : '';
        $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        
        // Validate data
        if (empty($type) || $itemId <= 0 || $amount <= 0) {
            $_SESSION['errors'] = ['Invalid payment data'];
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            
            // Create and process payment
            if ($type == 'artwork') {
                // Get artwork details
                $artwork = $this->artworkModel->getById($itemId);
                
                if (!$artwork) {
                    $_SESSION['errors'] = ['Artwork not found'];
                    $this->redirect('index.php?controller=gallery&action=index');
                    return;
                }
                
                // Check if artwork is already sold
                if (isset($artwork['status']) && $artwork['status'] == 'sold') {
                    $_SESSION['errors'] = ['This artwork has already been sold and is no longer available for purchase.'];
                    $this->redirect('index.php?controller=gallery&action=viewArtwork&id=' . $itemId);
                    return;
                }
                
                // Create payment record (returns new payment ID)
                $paymentId = $this->paymentModel->createArtworkPayment($userId, $itemId, $amount);
                
                // Process payment immediately - mark artwork as sold and record sale
                if ($paymentId && is_numeric($paymentId)) {
                    $this->paymentModel->processPayment((int) $paymentId);
                    
                    $_SESSION['success'] = 'Payment successful! You have purchased the artwork.';
                    $this->redirect('index.php?controller=gallery&action=viewArtwork&id=' . $itemId);
                } else {
                    $_SESSION['errors'] = ['Payment processing failed. Please try again.'];
                    $this->redirect('index.php?controller=payment&action=process&type=artwork&id=' . $itemId);
                }
            } elseif ($type == 'egift') {
                // Get e-gift details
                $gift = $this->eGiftModel->getById($itemId);
                
                if (!$gift || $gift['sender_id'] != $userId) {
                    $_SESSION['errors'] = ['E-gift not found or you are not authorized'];
                    $this->redirect('index.php?controller=gallery&action=index');
                    return;
                }
                
                // Create payment record
                $paymentId = $this->paymentModel->createEGiftPayment($userId, $itemId, $amount);
                
                // Process payment immediately (in a real system, this would be async)
                if ($paymentId) {
                    $this->paymentModel->processPayment($paymentId);
                    
                    $_SESSION['success'] = 'Payment successful! Your e-gift has been sent.';
                    $this->redirect('index.php?controller=gift&action=history');
                } else {
                    $_SESSION['errors'] = ['Payment processing failed'];
                    $this->redirect('index.php?controller=payment&action=process&type=egift&id=' . $itemId);
                }
            } else {
                $_SESSION['errors'] = ['Invalid payment type'];
                $this->redirect('index.php?controller=gallery&action=index');
            }
        } catch (Exception $e) {
            $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
    
    // Show payment history
    public function history() {
        try {
            $userId = $_SESSION['user_id'];
            
            // Get payment history
            $payments = $this->paymentModel->getUserPaymentHistory($userId);
            
            $data = [
                'title' => 'Payment History',
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'payments' => $payments
            ];
            
            $this->view('payment/history', $data);
        } catch (Exception $e) {
            $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
}
?> 