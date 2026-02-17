<?php
// Set base path
$base_path = dirname(__DIR__) . '/';

// Include required files with absolute paths
require_once $base_path . 'models/Database.php';
require_once $base_path . 'models/BaseModel.php';
require_once $base_path . 'models/Admin.php';
require_once $base_path . 'models/User.php';
require_once $base_path . 'models/Artwork.php';
require_once $base_path . 'controllers/BaseController.php';

class AdminController extends BaseController {
    private $adminModel;
    private $userModel;
    private $artworkModel;
    private $viewData = [];
    
    public function __construct() {
        // Initialize models
        $this->adminModel = new Admin();
        $this->userModel = new User();
        $this->artworkModel = new Artwork();
        
        // Restrict access to admin role only, except for the login page
        if ($this->getAction() !== 'login' && $this->getAction() !== 'register' && !$this->isAdminLoggedIn()) {
            $this->redirect('admin', 'login');
        }
    }
    
    // Set data for the view
    protected function setViewData($key, $value) {
        $this->viewData[$key] = $value;
    }
    
    // Render a view with the view data
    protected function render($view) {
        // Extract view data to make it available in the view
        extract($this->viewData);
        
        // Check if file exists
        if (file_exists('views/' . $view . '.php')) {
            // Include the layout header
            require_once 'views/admin/layout.php';
            
            // Store the output of the view in a variable to insert it in the right place
            ob_start();
            require_once 'views/' . $view . '.php';
            $viewOutput = ob_get_clean();
            
            // Output the view content
            echo $viewOutput;
            
            // Include layout footer if it exists
            if (file_exists('views/admin/layout_footer.php')) {
                require_once 'views/admin/layout_footer.php';
            }
        } else {
            // View not found
            echo "<div class='alert alert-danger'>View not found: {$view}</div>";
            echo "<p><a href='index.php'>Go to homepage</a></p>";
        }
    }
    
    // Override the redirect method to handle controller and action parameters
    protected function redirect($controller, $action = '', $params = []) {
        $url = 'index.php?controller=' . $controller;
        
        if (!empty($action)) {
            $url .= '&action=' . $action;
        }
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $url .= '&' . $key . '=' . $value;
            }
        }
        
        // Store any success or error messages in the session
        if (isset($this->viewData['success'])) {
            $_SESSION['success'] = $this->viewData['success'];
        }
        
        if (isset($this->viewData['error'])) {
            $_SESSION['error'] = $this->viewData['error'];
        }
        
        header('Location: ' . $url);
        exit;
    }
    
    // Get the current action from the URL
    private function getAction() {
        return isset($_GET['action']) ? $_GET['action'] : '';
    }
    
    // Check if admin is logged in
    private function isAdminLoggedIn() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            return false;
        }
        
        return $_SESSION['user_role'] === 'admin';
    }
    
    // Admin login page
    public function login() {
        // If admin is already logged in, redirect to dashboard
        if ($this->isAdminLoggedIn()) {
            $this->redirect('admin', 'dashboard');
        }
        
        // Check if any admin users exist
        $adminExists = $this->adminModel->adminExists();
        $this->setViewData('adminExists', $adminExists);
        
        // Process login form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            if (empty($email) || empty($password)) {
                $this->setViewData('error', 'Email and password are required');
            } else {
                // Try to login
                if ($this->userModel->login($email, $password)) {
                    // Check if the logged in user is an admin
                    if ($_SESSION['user_role'] === 'admin') {
                        $this->redirect('admin', 'dashboard');
                    } else {
                        // Not an admin, logout and show error
                        $this->userModel->logout();
                        $this->setViewData('error', 'You do not have admin privileges');
                    }
                } else {
                    $this->setViewData('error', 'Invalid email or password');
                }
            }
        }
        
        $this->render('admin/login');
    }
    
    // Admin registration page
    public function register() {
        // Only existing admins can create new admin accounts
        if (!$this->isAdminLoggedIn()) {
            // Check if there are any admins in the system
            $adminExists = $this->adminModel->adminExists();
            
            // If there's at least one admin, require login
            if ($adminExists) {
                $this->setViewData('error', 'You must be logged in as an admin to register new admins');
                $this->redirect('admin', 'login');
                return;
            }
            // If no admins exist yet, allow registration of first admin
        }
        
        // Process registration form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
            
            // Validate inputs
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            } elseif ($this->userModel->emailExists($email)) {
                $errors[] = 'Email already exists';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            } elseif (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters long';
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }
            
            if (empty($errors)) {
                // Create new admin account
                $result = $this->userModel->register($name, $email, $password, 'admin');
                
                if ($result) {
                    $this->setViewData('success', 'Admin account created successfully');
                    $this->redirect('admin', 'login');
                } else {
                    $this->setViewData('error', 'Failed to create admin account');
                }
            } else {
                $this->setViewData('errors', $errors);
                $this->setViewData('name', $name);
                $this->setViewData('email', $email);
            }
        }
        
        $this->render('admin/register');
    }
    
    // Admin logout
    public function logout() {
        $this->userModel->logout();
        $this->redirect('admin', 'login');
    }
    
    // Admin dashboard
    public function dashboard() {
        // Get dashboard stats
        $stats = $this->adminModel->getDashboardStats();
        $this->setViewData('stats', $stats);
        
        // Get recent reports
        $recentReports = $this->adminModel->getAllReports('pending');
        $this->setViewData('reports', $recentReports);
        
        $this->render('admin/dashboard');
    }
    
    // Manage users
    public function users() {
        // Get all users
        $users = $this->adminModel->getAllUsers();
        $this->setViewData('users', $users);
        
        $this->render('admin/users');
    }
    
    // View user details
    public function viewUser() {
        $userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($userId <= 0) {
            $this->redirect('admin', 'users');
        }
        
        // Get user details
        $user = $this->userModel->getUserById($userId);
        
        if (!$user) {
            $this->setViewData('error', 'User not found');
            $this->redirect('admin', 'users');
        }
        
        $this->setViewData('user', $user);
        
        // Get user's artworks
        $artworks = $this->artworkModel->getArtworksByUser($userId);
        $this->setViewData('artworks', $artworks);
        
        // Check if user is banned
        $isBanned = $this->adminModel->isUserBanned($userId);
        $this->setViewData('isBanned', $isBanned);
        
        if ($isBanned) {
            $banInfo = $this->adminModel->getUserBanInfo($userId);
            $this->setViewData('banInfo', $banInfo);
        }
        
        $this->render('admin/view-user');
    }
    
    // Ban user
    public function banUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin', 'users');
        }
        
        $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
        $banType = isset($_POST['ban_type']) ? $_POST['ban_type'] : 'permanent';
        $banDays = isset($_POST['ban_days']) ? intval($_POST['ban_days']) : 0;
        
        if ($userId <= 0 || empty($reason)) {
            $this->setViewData('error', 'User ID and reason are required');
            $this->redirect('admin', 'viewUser', ['id' => $userId]);
        }
        
        // Check if user exists and is not an admin
        $user = $this->userModel->getUserById($userId);
        
        if (!$user) {
            $this->setViewData('error', 'User not found');
            $this->redirect('admin', 'users');
        }
        
        if ($user['role'] === 'admin') {
            $this->setViewData('error', 'Cannot ban an admin user');
            $this->redirect('admin', 'viewUser', ['id' => $userId]);
        }
        
        // Process ban
        $isPermanent = ($banType === 'permanent');
        $unbanDate = null;
        
        if (!$isPermanent && $banDays > 0) {
            $unbanDate = date('Y-m-d H:i:s', strtotime("+{$banDays} days"));
        }
        
        if ($this->adminModel->banUser($userId, $reason, $_SESSION['user_id'], $isPermanent, $unbanDate)) {
            $this->setViewData('success', 'User has been banned successfully');
        } else {
            $this->setViewData('error', 'Failed to ban user');
        }
        
        $this->redirect('admin', 'viewUser', ['id' => $userId]);
    }
    
    // Unban user
    public function unbanUser() {
        $userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($userId <= 0) {
            $this->redirect('admin', 'users');
        }
        
        if ($this->adminModel->unbanUser($userId)) {
            $this->setViewData('success', 'User has been unbanned successfully');
        } else {
            $this->setViewData('error', 'Failed to unban user');
        }
        
        $this->redirect('admin', 'viewUser', ['id' => $userId]);
    }
    
    // Delete user
    public function deleteUser() {
        $userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($userId <= 0) {
            $this->redirect('admin', 'users');
        }
        
        // Check if user exists and is not an admin
        $user = $this->userModel->getUserById($userId);
        
        if (!$user) {
            $this->setViewData('error', 'User not found');
            $this->redirect('admin', 'users');
        }
        
        if ($user['role'] === 'admin') {
            $this->setViewData('error', 'Cannot delete an admin user');
            $this->redirect('admin', 'users');
        }
        
        if ($this->adminModel->deleteUser($userId)) {
            $this->setViewData('success', 'User has been deleted successfully');
        } else {
            $this->setViewData('error', 'Failed to delete user');
        }
        
        $this->redirect('admin', 'users');
    }
    
    // Manage artworks
    public function artworks() {
        // Get all artworks
        $artworks = $this->adminModel->getAllArtworks();
        $this->setViewData('artworks', $artworks);
        
        $this->render('admin/artworks');
    }
    
    // Delete artwork
    public function deleteArtwork() {
        $artworkId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($artworkId <= 0) {
            $this->redirect('admin', 'artworks');
        }
        
        if ($this->adminModel->deleteArtwork($artworkId)) {
            $this->setViewData('success', 'Artwork has been deleted successfully');
        } else {
            $this->setViewData('error', 'Failed to delete artwork');
        }
        
        $this->redirect('admin', 'artworks');
    }
    
    // Add artwork
    public function addArtwork() {
        // Get categories
        $categories = $this->artworkModel->getAllCategories();
        $this->setViewData('categories', $categories);
        
        // Get artists
        $artists = $this->userModel->getAllArtists();
        $this->setViewData('artists', $artists);
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = isset($_POST['title']) ? $_POST['title'] : '';
            $description = isset($_POST['description']) ? $_POST['description'] : '';
            $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
            $category = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
            $artist = isset($_POST['artist_id']) ? intval($_POST['artist_id']) : 0;
            
            // Validate inputs
            if (empty($title) || $price <= 0 || $artist <= 0) {
                $this->setViewData('error', 'Title, price and artist are required');
            } else {
                // Process image upload
                $imagePath = '';
                
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $imagePath = $this->artworkModel->uploadImage($_FILES['image']);
                    
                    if (!$imagePath) {
                        $this->setViewData('error', 'Failed to upload image');
                        $this->render('admin/add-artwork');
                        return;
                    }
                } else {
                    $this->setViewData('error', 'Image is required');
                    $this->render('admin/add-artwork');
                    return;
                }
                
                // Create artwork
                $result = $this->artworkModel->createArtwork($title, $description, $imagePath, $price, $artist, $category);
                
                if ($result) {
                    $this->setViewData('success', 'Artwork has been added successfully');
                    $this->redirect('admin', 'artworks');
                } else {
                    $this->setViewData('error', 'Failed to add artwork');
                }
            }
        }
        
        $this->render('admin/add-artwork');
    }
    
    // Manage reports
    public function reports() {
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        
        // Get reports
        $reports = $this->adminModel->getAllReports($status);
        $this->setViewData('reports', $reports);
        $this->setViewData('currentStatus', $status);
        
        $this->render('admin/reports');
    }
    
    // View report
    public function viewReport() {
        $reportId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($reportId <= 0) {
            $this->redirect('admin', 'reports');
        }
        
        // Get report details
        $report = $this->adminModel->getReportById($reportId);
        
        if (!$report) {
            $this->setViewData('error', 'Report not found');
            $this->redirect('admin', 'reports');
        }
        
        $this->setViewData('report', $report);
        
        $this->render('admin/view-report');
    }
    
    // Update report
    public function updateReport() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin', 'reports');
        }
        
        $reportId = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $notes = isset($_POST['admin_notes']) ? $_POST['admin_notes'] : '';
        
        if ($reportId <= 0 || empty($status)) {
            $this->setViewData('error', 'Report ID and status are required');
            $this->redirect('admin', 'reports');
        }
        
        if ($this->adminModel->updateReportStatus($reportId, $status, $notes)) {
            $this->setViewData('success', 'Report has been updated successfully');
        } else {
            $this->setViewData('error', 'Failed to update report');
        }
        
        $this->redirect('admin', 'viewReport', ['id' => $reportId]);
    }
    
    // Make user an admin
    public function makeAdmin() {
        $userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($userId <= 0) {
            $this->redirect('admin', 'users');
        }
        
        if ($this->adminModel->makeAdmin($userId)) {
            $this->setViewData('success', 'User has been made an admin successfully');
        } else {
            $this->setViewData('error', 'Failed to make user an admin');
        }
        
        $this->redirect('admin', 'users');
    }
}
?> 