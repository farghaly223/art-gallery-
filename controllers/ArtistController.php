<?php
require_once 'models/User.php';
require_once 'models/Artwork.php';

class ArtistController extends BaseController {
    private $userModel;
    private $artworkModel;
    
    public function __construct() {
        // Require login for all artist actions
        $this->requireLogin();
        $this->requireArtistRole();
        
        $this->userModel = new User();
        $this->artworkModel = new Artwork();
    }
    
    // Display artist dashboard
    public function dashboard() {
        $userId = $_SESSION['user_id'];
        
        try {
            // Get artist's artworks
            $artworks = $this->artworkModel->getArtworksByUser($userId);
            
            // Get dashboard statistics
            $totalArtworks = $this->artworkModel->countArtworksByArtist($userId);
            $soldArtworks = $this->artworkModel->countSoldArtworksByArtist($userId);
            $subscriberCount = $this->userModel->countSubscribers($userId);
            $totalRevenue = $this->artworkModel->getTotalRevenue($userId);
            
            // Get all categories for the dropdown
            $categories = $this->artworkModel->getAllCategories();
            
            $data = [
                'title' => 'Artist Dashboard',
                'user_name' => $_SESSION['user_name'],
                'artworks' => $artworks,
                'categories' => $categories,
                'stats' => [
                    'total_artworks' => $totalArtworks,
                    'sold_artworks' => $soldArtworks,
                    'subscriber_count' => $subscriberCount,
                    'total_revenue' => $totalRevenue
                ]
            ];
            
            $this->view('artist/dashboard', $data);
        } catch (Exception $e) {
            // If any error occurs, display error message
            $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
            $data = [
                'title' => 'Artist Dashboard',
                'user_name' => $_SESSION['user_name'],
                'artworks' => null,
                'categories' => null,
                'stats' => [
                    'total_artworks' => 0,
                    'sold_artworks' => 0,
                    'subscriber_count' => 0,
                    'total_revenue' => 0
                ]
            ];
            $this->view('artist/dashboard', $data);
        }
    }
    
    // Add a new artwork
    public function addArtwork() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Get form data
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $price = trim($_POST['price']);
            $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $userId = $_SESSION['user_id'];
            
            // Validate inputs
            $errors = [];
            
            if (empty($title)) {
                $errors[] = 'Title is required';
            }
            
            if (empty($description)) {
                $errors[] = 'Description is required';
            }
            
            if (empty($price) || !is_numeric($price) || $price <= 0) {
                $errors[] = 'Price must be a positive number';
            }
            
            // Handle file upload
            $imagePath = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $imagePath = $this->artworkModel->uploadImage($_FILES['image']);
                if (!$imagePath) {
                    $errors[] = 'Failed to upload image. Please ensure it is a valid image file (JPG, PNG, GIF) and less than 5MB.';
                }
            } else {
                $errors[] = 'Artwork image is required';
            }
            
            // If no errors, add artwork
            if (empty($errors)) {
                try {
                    if ($this->artworkModel->createArtwork($title, $description, $imagePath, $price, $userId, $categoryId)) {
                        // Artwork added successfully, redirect to dashboard
                        $_SESSION['success'] = 'Artwork added successfully!';
                        $this->redirect('index.php?controller=artist&action=dashboard');
                    } else {
                        // Failed to add artwork
                        $errors[] = 'Failed to add artwork. Please try again.';
                    }
                } catch (Exception $e) {
                    $errors[] = 'Error: ' . $e->getMessage();
                }
            }
            
            // If there are errors, redirect back to dashboard with errors
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $this->redirect('index.php?controller=artist&action=dashboard');
            }
        } else {
            // If not POST request, redirect to dashboard
            $this->redirect('index.php?controller=artist&action=dashboard');
        }
    }
    
    // Delete an artwork
    public function deleteArtwork() {
        if (isset($_GET['id'])) {
            $artworkId = (int)$_GET['id'];
            $userId = $_SESSION['user_id'];
            
            try {
                if ($this->artworkModel->deleteArtwork($artworkId, $userId)) {
                    $_SESSION['success'] = 'Artwork deleted successfully';
                } else {
                    $_SESSION['errors'] = ['Failed to delete artwork'];
                }
            } catch (Exception $e) {
                $_SESSION['errors'] = ['Error: ' . $e->getMessage()];
            }
        }
        
        $this->redirect('index.php?controller=artist&action=dashboard');
    }
    
    // Ensure user has artist role
    private function requireArtistRole() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'artist') {
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
}
?> 