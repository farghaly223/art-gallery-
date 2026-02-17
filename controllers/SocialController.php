<?php
require_once 'models/User.php';
require_once 'BaseController.php';

class SocialController extends BaseController {
    private $userModel;
    
    public function __construct() {
        // Require login for all social actions
        $this->requireLogin();
        
        $this->userModel = new User();
    }
    
    // Search functionality
    public function search() {
        $query = isset($_GET['query']) ? trim($_GET['query']) : '';
        $results = [];
        
        if (!empty($query)) {
            $results = $this->userModel->searchUsers($query, $_SESSION['user_id']);
        }
        
        $data = [
            'title' => 'Search Users',
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role'],
            'query' => $query,
            'results' => $results,
            'current_user_id' => $_SESSION['user_id']
        ];
        
        $this->view('social/search', $data);
    }
    
    // View user profile
    public function viewProfile() {
        if (!isset($_GET['id'])) {
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        $userId = (int)$_GET['id'];
        $currentUserId = $_SESSION['user_id'];
        
        $profile = $this->userModel->getById($userId);
        
        if (!$profile) {
            $_SESSION['errors'] = ['User not found'];
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        // Check friend status
        $friendStatus = $this->userModel->checkFriendStatus($currentUserId, $userId);
        
        // Check subscription status (if viewing an artist)
        $isSubscribed = false;
        if ($profile['role'] == 'artist') {
            $isSubscribed = $this->userModel->isSubscribed($currentUserId, $userId);
        }
        
        $data = [
            'title' => $profile['name'] . '\'s Profile',
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role'],
            'profile' => $profile,
            'friend_status' => $friendStatus,
            'is_subscribed' => $isSubscribed
        ];
        
        $this->view('social/profile', $data);
    }
    
    // Add friend
    public function addFriend() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['friend_id'])) {
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        $friendId = (int)$_POST['friend_id'];
        $userId = $_SESSION['user_id'];
        
        // Get safe redirect URL
        $redirectUrl = $this->getSafeRedirectUrl();
        
        if ($friendId == $userId) {
            $_SESSION['errors'] = ['You cannot add yourself as a friend'];
            $this->redirect($redirectUrl);
            return;
        }
        
        $result = $this->userModel->sendFriendRequest($userId, $friendId);
        
        if ($result) {
            $_SESSION['success'] = 'Friend request sent successfully';
        } else {
            $_SESSION['errors'] = ['Friend request could not be sent. You may already have a pending request or you are already friends.'];
        }
        
        // Redirect back to the referrer page
        $this->redirect($redirectUrl);
    }
    
    // Get safe redirect URL from referer or fallback
    private function getSafeRedirectUrl() {
        // Check if we have a redirect parameter in POST/GET
        if (isset($_POST['redirect']) && !empty($_POST['redirect'])) {
            $redirect = $_POST['redirect'];
            // Validate it's a relative URL
            if (strpos($redirect, 'http') !== 0 && strpos($redirect, '//') !== 0) {
                return $redirect;
            }
        }
        
        if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
            $redirect = $_GET['redirect'];
            // Validate it's a relative URL
            if (strpos($redirect, 'http') !== 0 && strpos($redirect, '//') !== 0) {
                return $redirect;
            }
        }
        
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        
        // If referer is empty, use default
        if (empty($referer)) {
            return 'index.php?controller=gallery&action=index';
        }
        
        // Parse the referer URL
        $parsedUrl = parse_url($referer);
        
        // Check if it's from localhost (our domain)
        $refererHost = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        if ($refererHost && $refererHost != 'localhost' && $refererHost != '127.0.0.1') {
            return 'index.php?controller=gallery&action=index';
        }
        
        // Extract query string to preserve controller/action
        $query = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';
        
        // If we have a valid query string, use it
        if (!empty($query) && (strpos($query, 'controller=') !== false || strpos($query, 'action=') !== false)) {
            return 'index.php?' . $query;
        }
        
        // Default fallback
        return 'index.php?controller=gallery&action=index';
    }
    
    // Subscribe to artist
    public function subscribe() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['artist_id'])) {
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        $artistId = (int)$_POST['artist_id'];
        $userId = $_SESSION['user_id'];
        
        // Get safe redirect URL
        $redirectUrl = $this->getSafeRedirectUrl();
        
        // Check if the target is actually an artist
        $artist = $this->userModel->getById($artistId);
        if (!$artist || $artist['role'] != 'artist') {
            $_SESSION['errors'] = ['Invalid artist'];
            $this->redirect($redirectUrl);
            return;
        }
        
        $result = $this->userModel->subscribeToArtist($userId, $artistId);
        
        if ($result) {
            $_SESSION['success'] = 'Subscribed successfully';
        } else {
            $_SESSION['errors'] = ['You are already subscribed to this artist'];
        }
        
        // Redirect back to the referrer page
        $this->redirect($redirectUrl);
    }
    
    // Unsubscribe from artist
    public function unsubscribe() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['artist_id'])) {
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        $artistId = (int)$_POST['artist_id'];
        $userId = $_SESSION['user_id'];
        
        // Get safe redirect URL
        $redirectUrl = $this->getSafeRedirectUrl();
        
        $result = $this->userModel->unsubscribeFromArtist($userId, $artistId);
        
        if ($result) {
            $_SESSION['success'] = 'Unsubscribed successfully';
        } else {
            $_SESSION['errors'] = ['Error unsubscribing'];
        }
        
        // Redirect back to the referrer page
        $this->redirect($redirectUrl);
    }
    
    // View friends
    public function friends() {
        $userId = $_SESSION['user_id'];
        
        // Get friends and friend requests
        $friends = $this->userModel->getFriends($userId);
        $friendRequests = $this->userModel->getFriendRequests($userId);
        
        $data = [
            'title' => 'My Friends',
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role'],
            'friends' => $friends,
            'friend_requests' => $friendRequests
        ];
        
        $this->view('social/friends', $data);
    }
    
    // Accept friend request
    public function acceptFriend() {
        if (!isset($_GET['id'])) {
            $this->redirect('index.php?controller=social&action=friends');
            return;
        }
        
        $requestId = (int)$_GET['id'];
        $result = $this->userModel->acceptFriendRequest($requestId);
        
        if ($result) {
            $_SESSION['success'] = 'Friend request accepted';
        } else {
            $_SESSION['errors'] = ['Could not accept friend request'];
        }
        
        $this->redirect('index.php?controller=social&action=friends');
    }
    
    // Reject friend request
    public function rejectFriend() {
        if (!isset($_GET['id'])) {
            $this->redirect('index.php?controller=social&action=friends');
            return;
        }
        
        $requestId = (int)$_GET['id'];
        $result = $this->userModel->rejectFriendRequest($requestId);
        
        if ($result) {
            $_SESSION['success'] = 'Friend request rejected';
        } else {
            $_SESSION['errors'] = ['Could not reject friend request'];
        }
        
        $this->redirect('index.php?controller=social&action=friends');
    }
    
    // View subscriptions
    public function subscriptions() {
        $userId = $_SESSION['user_id'];
        $subscriptions = $this->userModel->getSubscriptions($userId);
        
        $data = [
            'title' => 'My Subscriptions',
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role'],
            'subscriptions' => $subscriptions
        ];
        
        $this->view('social/subscriptions', $data);
    }
    
    // View subscribers (for artists only)
    public function subscribers() {
        $userId = $_SESSION['user_id'];
        
        // Only artists can see their subscribers
        if ($_SESSION['user_role'] != 'artist') {
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        $subscribers = $this->userModel->getSubscribers($userId);
        
        $data = [
            'title' => 'My Subscribers',
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role'],
            'subscribers' => $subscribers
        ];
        
        $this->view('social/subscribers', $data);
    }
}
?> 