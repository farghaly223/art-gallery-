<?php
require_once 'models/User.php';

class AuthController extends BaseController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    // Display login form
    public function login() {
        // Check if user is already logged in
        if ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
        }
        
        // Load login view
        $this->view('auth/login');
    }
    
    // Process login form
    public function doLogin() {
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get form data
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            
            // Validate inputs
            $errors = [];
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            }
            
            // If no errors, try to login
            if (empty($errors)) {
                if ($this->userModel->login($email, $password)) {
                    // Login successful, redirect based on role
                    $this->redirectBasedOnRole();
                } else {
                    // Login failed
                    $errors[] = 'Invalid email or password';
                }
            }
            
            // If there are errors, display them
            $data = [
                'errors' => $errors,
                'email' => $email
            ];
            
            $this->view('auth/login', $data);
        } else {
            // If not POST request, redirect to login page
            $this->redirect('index.php?controller=auth&action=login');
        }
    }
    
    // Display register form
    public function register() {
        // Check if user is already logged in
        if ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
        }
        
        // Load register view
        $this->view('auth/register');
    }
    
    // Process register form
    public function doRegister() {
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get form data
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);
            $role = isset($_POST['role']) ? trim($_POST['role']) : 'viewer';
            
            // Validate inputs
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email is not valid';
            } elseif ($this->userModel->emailExists($email)) {
                $errors[] = 'Email is already taken';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }
            
            if ($password != $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }
            
            // Ensure role is valid
            if (!in_array($role, ['viewer', 'artist'])) {
                $role = 'viewer';
            }
            
            // If no errors, register user
            if (empty($errors)) {
                if ($this->userModel->register($name, $email, $password, $role)) {
                    // Registration successful, redirect to login
                    $this->redirect('index.php?controller=auth&action=login');
                } else {
                    // Registration failed
                    $errors[] = 'Something went wrong';
                }
            }
            
            // If there are errors, display them
            $data = [
                'errors' => $errors,
                'name' => $name,
                'email' => $email,
                'role' => $role
            ];
            
            $this->view('auth/register', $data);
        } else {
            // If not POST request, redirect to register page
            $this->redirect('index.php?controller=auth&action=register');
        }
    }
    
    // Logout user
    public function logout() {
        $this->userModel->logout();
        $this->redirect('index.php?controller=auth&action=login');
    }
    
    // Redirect based on user role
    private function redirectBasedOnRole() {
        if (isset($_SESSION['user_role'])) {
            switch ($_SESSION['user_role']) {
                case 'artist':
                    $this->redirect('index.php?controller=artist&action=dashboard');
                    break;
                case 'admin':
                    $this->redirect('index.php?controller=admin&action=dashboard');
                    break;
                case 'viewer':
                default:
                    $this->redirect('index.php?controller=gallery&action=index');
                    break;
            }
        } else {
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
}
?> 