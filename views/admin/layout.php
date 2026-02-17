<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Art Gallery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="body-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">Art Gallery</div>
            </div>
            
            <ul class="sidebar-nav">
                <li>
                    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=users">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=artworks">
                        <i class="fas fa-paint-brush"></i>
                        <span>Artworks</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=reports">
                        <i class="fas fa-flag"></i>
                        <span>Reports</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>
                    <?php 
                    $action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
                    echo ucfirst($action);
                    ?>
                </h1>
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-info">
                    <span class="username"><?php echo $_SESSION['user_name']; ?></span>
                    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=logout" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="content">
                <!-- Main content will be injected here -->
            </div>
        </div>
    </div>
</body>
</html> 