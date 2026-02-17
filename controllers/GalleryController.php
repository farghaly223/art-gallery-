<?php
require_once 'models/Artwork.php';
require_once 'models/User.php';

class GalleryController extends BaseController {
    private $artworkModel;
    private $userModel;
    private $eGiftModel = null;
    
    public function __construct() {
        // Require login for all gallery actions
        $this->requireLogin();
        
        $this->artworkModel = new Artwork();
        $this->userModel = new User();
    }
    
    // Display the gallery home page
    public function index() {
        try {
            // Get all available artworks
            $artworks = $this->artworkModel->getAvailableArtworks();
            
            // Handle different return types from database query
            if (is_array($artworks)) {
                // Convert array to object format for consistency
                $artworks = (object) ['num_rows' => count($artworks), 'data' => $artworks];
            } elseif (is_object($artworks) && method_exists($artworks, 'fetch_assoc')) {
                // It's a mysqli_result object, which is fine
                // Store the result to iterate later
            } elseif (!$artworks) {
                // No artworks found or error occurred
                $artworks = (object) ['num_rows' => 0, 'data' => []];
            }
            
            $data = [
                'title' => 'Art Gallery - Home',
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'artworks' => $artworks
            ];
            
            // Note: unread_notifications is automatically added by BaseController for viewers
            // No need to manually add it here
            
            $this->view('gallery/index', $data);
        } catch (Exception $e) {
            // Log the error for debugging
            error_log("Error in GalleryController->index: " . $e->getMessage());
            
            // Show the actual error message for debugging
            $_SESSION['errors'] = ['Debug: ' . $e->getMessage()];
            
            $data = [
                'title' => 'Art Gallery - Home',
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'artworks' => null
            ];
            
            $this->view('gallery/index', $data);
        }
    }
    
    // Browse and search artworks
    public function browse() {
        try {
            // Get search and filter parameters
            $search = isset($_GET['search']) ? trim($_GET['search']) : null;
            $categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
            $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
            $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
            
            // Set default values for categories and artworks
            $categories = null;
            $artworks = null;
            
            try {
                // First try to get categories - wrap in separate try/catch to allow browse to work even if categories fail
                $categories = $this->artworkModel->getAllCategories();
                
                // If categories is an array instead of a result resource, handle accordingly
                if (is_array($categories)) {
                    $categories = (object) ['num_rows' => count($categories), 'data' => $categories];
                }
            } catch (Exception $catErr) {
                // Log category error but continue
                error_log("Category error in browse: " . $catErr->getMessage());
            }
            
            try {
                // Search artworks with filters - wrap in separate try/catch
                $artworks = $this->artworkModel->searchArtworks($search, $categoryId, $minPrice, $maxPrice);
                
                // If artworks is an array instead of a result resource, handle accordingly
                if (is_array($artworks)) {
                    $artworks = (object) ['num_rows' => count($artworks), 'data' => $artworks];
                }
            } catch (Exception $artErr) {
                // Log artwork error and show to user
                error_log("Artwork error in browse: " . $artErr->getMessage());
                $_SESSION['errors'] = ['Error loading artworks: ' . $artErr->getMessage()];
            }
            
            $data = [
                'title' => 'Art Gallery - Browse Artworks',
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'artworks' => $artworks,
                'categories' => $categories,
                'search' => $search,
                'category_id' => $categoryId,
                'min_price' => $minPrice,
                'max_price' => $maxPrice
            ];
            
            $this->view('gallery/browse', $data);
        } catch (Exception $e) {
            // Log the main error
            error_log("Major error in GalleryController->browse: " . $e->getMessage());
            
            // Show the actual error message for debugging
            $_SESSION['errors'] = ['Debug error in browse: ' . $e->getMessage()];
            
            $data = [
                'title' => 'Art Gallery - Browse Artworks',
                'user_name' => $_SESSION['user_name'],
                'user_role' => $_SESSION['user_role'],
                'artworks' => null,
                'categories' => null,
                'search' => isset($_GET['search']) ? trim($_GET['search']) : null,
                'category_id' => isset($_GET['category']) ? (int)$_GET['category'] : null,
                'min_price' => isset($_GET['min_price']) ? (float)$_GET['min_price'] : null,
                'max_price' => isset($_GET['max_price']) ? (float)$_GET['max_price'] : null
            ];
            
            $this->view('gallery/browse', $data);
        }
    }
    
    // View artwork details
    public function viewArtwork() {
        if (isset($_GET['id'])) {
            $artworkId = (int)$_GET['id'];
            
            try {
                // Get artwork details
                $artwork = $this->artworkModel->getById($artworkId);
                
                if ($artwork) {
                    // Get artist details
                    $artist = $this->userModel->getById($artwork['user_id']);
                    
                    // Get category name if category_id exists
                    if (isset($artwork['category_id']) && $artwork['category_id']) {
                        // Use a proper method to get category information instead of accessing db directly
                        $categoryInfo = $this->artworkModel->getCategoryNameById($artwork['category_id']);
                        if ($categoryInfo) {
                            $artwork['category_name'] = $categoryInfo;
                        }
                    }
                    
                    // Check if current user is an artist or viewer
                    $canBuy = ($_SESSION['user_role'] == 'viewer' && (!isset($artwork['status']) || $artwork['status'] == 'available'));
                    
                    $data = [
                        'title' => 'Art Gallery - ' . $artwork['title'],
                        'user_name' => $_SESSION['user_name'],
                        'user_role' => $_SESSION['user_role'],
                        'artwork' => $artwork,
                        'artist' => $artist,
                        'can_buy' => $canBuy
                    ];
                    
                    $this->view('gallery/view_artwork', $data);
                } else {
                    $_SESSION['errors'] = ['Artwork not found'];
                    $this->redirect('index.php?controller=gallery&action=index');
                }
            } catch (Exception $e) {
                $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
                $this->redirect('index.php?controller=gallery&action=index');
            }
        } else {
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
    
    // Buy artwork
    public function buyArtwork() {
        if (isset($_GET['id'])) {
            $artworkId = (int)$_GET['id'];
            
            // Only viewers can buy artworks
            if ($_SESSION['user_role'] != 'viewer') {
                $_SESSION['errors'] = ['Only viewers can purchase artworks'];
                $this->redirect('index.php?controller=gallery&action=viewArtwork&id=' . $artworkId);
                return;
            }
            
            try {
                // Get artwork details
                $artwork = $this->artworkModel->getById($artworkId);
                
                if ($artwork) {
                    // Check if artwork is already sold
                    if (isset($artwork['status']) && $artwork['status'] == 'sold') {
                        $_SESSION['errors'] = ['This artwork has already been sold and is no longer available for purchase.'];
                        $this->redirect('index.php?controller=gallery&action=viewArtwork&id=' . $artworkId);
                        return;
                    }
                    
                    // Check if artwork is available
                    if (isset($artwork['status']) && $artwork['status'] != 'available') {
                        $_SESSION['errors'] = ['This artwork is no longer available for purchase'];
                        $this->redirect('index.php?controller=gallery&action=viewArtwork&id=' . $artworkId);
                        return;
                    }
                    
                    // Redirect to payment page
                    $this->redirect('index.php?controller=payment&action=process&type=artwork&id=' . $artworkId);
                } else {
                    $_SESSION['errors'] = ['Artwork not found'];
                    $this->redirect('index.php?controller=gallery&action=index');
                }
            } catch (Exception $e) {
                $_SESSION['errors'] = ['An error occurred: ' . $e->getMessage()];
                $this->redirect('index.php?controller=gallery&action=index');
            }
        } else {
            $this->redirect('index.php?controller=gallery&action=index');
        }
    }
}
?> 