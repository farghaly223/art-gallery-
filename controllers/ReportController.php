<?php
require_once 'models/Report.php';
require_once 'models/User.php';
require_once 'models/Artwork.php';
require_once 'BaseController.php';

class ReportController extends BaseController {
    private $reportModel;
    private $userModel;
    private $artworkModel;
    
    public function __construct() {
        // Require login for all report actions
        $this->requireLogin();
        
        $this->reportModel = new Report();
        $this->userModel = new User();
        $this->artworkModel = new Artwork();
    }
    
    // Display report form
    public function index() {
        $reportType = isset($_GET['type']) ? $_GET['type'] : '';
        $itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $item = null;
        
        // Validate report type and get item details
        if ($reportType === 'user' && $itemId > 0) {
            $item = $this->userModel->getById($itemId);
            if (!$item) {
                $_SESSION['errors'] = ['User not found'];
                $this->redirect('index.php?controller=gallery&action=index');
                return;
            }
        } else if ($reportType === 'artwork' && $itemId > 0) {
            $item = $this->artworkModel->getById($itemId);
            if (!$item) {
                $_SESSION['errors'] = ['Artwork not found'];
                $this->redirect('index.php?controller=gallery&action=index');
                return;
            }
        } else if ($reportType !== 'other') {
            $_SESSION['errors'] = ['Invalid report type'];
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        $data = [
            'title' => 'Submit Report',
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role'],
            'report_type' => $reportType,
            'item_id' => $itemId,
            'item' => $item
        ];
        
        $this->view('report/form', $data);
    }
    
    // Submit report
    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=gallery&action=index');
            return;
        }
        
        $reportType = isset($_POST['report_type']) ? $_POST['report_type'] : '';
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        $reporterId = $_SESSION['user_id'];
        $reportedUserId = null;
        $artworkId = null;
        
        // Validate inputs
        if (empty($reason)) {
            $_SESSION['errors'] = ['Please provide a reason for your report'];
            $this->redirect($_SERVER['HTTP_REFERER']);
            return;
        }
        
        if ($reportType === 'user') {
            $reportedUserId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            if ($reportedUserId <= 0) {
                $_SESSION['errors'] = ['Invalid user'];
                $this->redirect($_SERVER['HTTP_REFERER']);
                return;
            }
            
            // Can't report yourself
            if ($reportedUserId === $reporterId) {
                $_SESSION['errors'] = ['You cannot report yourself'];
                $this->redirect($_SERVER['HTTP_REFERER']);
                return;
            }
        } else if ($reportType === 'artwork') {
            $artworkId = isset($_POST['artwork_id']) ? (int)$_POST['artwork_id'] : 0;
            if ($artworkId <= 0) {
                $_SESSION['errors'] = ['Invalid artwork'];
                $this->redirect($_SERVER['HTTP_REFERER']);
                return;
            }
        } else if ($reportType !== 'other') {
            $_SESSION['errors'] = ['Invalid report type'];
            $this->redirect($_SERVER['HTTP_REFERER']);
            return;
        }
        
        // Create the report
        $result = $this->reportModel->createReport($reporterId, $reportType, $reason, $reportedUserId, $artworkId);
        
        if ($result) {
            $_SESSION['success'] = 'Your report has been submitted and will be reviewed by an administrator';
            $this->redirect('index.php?controller=report&action=myReports');
        } else {
            $_SESSION['errors'] = ['Failed to submit report. Please try again.'];
            $this->redirect($_SERVER['HTTP_REFERER']);
        }
    }
    
    // View user's own reports
    public function myReports() {
        $userId = $_SESSION['user_id'];
        $reports = $this->reportModel->getReportsByReporter($userId);
        
        $data = [
            'title' => 'My Reports',
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role'],
            'reports' => $reports
        ];
        
        $this->view('report/my_reports', $data);
    }
    
    // View report details
    public function viewReport() {
        if (!isset($_GET['id'])) {
            $this->redirect('index.php?controller=report&action=myReports');
            return;
        }
        
        $reportId = (int)$_GET['id'];
        $userId = $_SESSION['user_id'];
        
        // Users can only view their own reports
        $report = $this->reportModel->getReportById($reportId, $userId);
        
        if (!$report) {
            $_SESSION['errors'] = ['Report not found or you do not have permission to view it'];
            $this->redirect('index.php?controller=report&action=myReports');
            return;
        }
        
        $data = [
            'title' => 'Report Details',
            'user_name' => $_SESSION['user_name'],
            'user_role' => $_SESSION['user_role'],
            'report' => $report
        ];
        
        $this->view('report/view', $data);
    }
}
?> 