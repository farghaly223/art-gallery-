<?php
class BaseController {
    // Load view
    protected function view($view, $data = []) {
        try {
            // Check if file exists
            if (file_exists('views/' . $view . '.php')) {
                // If user is logged in, add notification count to all views
                if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'viewer') {
                    // Add unread notification count if not already set
                    if (!isset($data['unread_notifications'])) {
                        require_once 'models/EGift.php';
                        $eGiftModel = new EGift();
                        $data['unread_notifications'] = $eGiftModel->getUnreadNotificationCount($_SESSION['user_id']);
                    }
                }
                
                // Extract data to make it available in the view
                extract($data);
                
                // Include the view file
                require_once 'views/' . $view . '.php';
            } else {
                // View not found
                $_SESSION['errors'] = ["View not found: {$view}"];
                echo "<div class='alert alert-danger'>View not found: {$view}</div>";
                echo "<p><a href='index.php'>Go to homepage</a></p>";
            }
        } catch (Exception $e) {
            // Log the error
            error_log("Error rendering view {$view}: " . $e->getMessage());
            
            // Display a user-friendly error
            $_SESSION['errors'] = ["Error displaying page: " . $e->getMessage()];
            echo "<div class='alert alert-danger'>Error displaying page. Please try again later.</div>";
            echo "<p><a href='index.php'>Go to homepage</a></p>";
        }
    }
    
    // Redirect to a URL
    protected function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
    
    // Check if user is logged in
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Require login to access page
    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('index.php?controller=auth&action=login');
        }
    }
}
?> 