<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <!-- Bootstrap CSS first -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Base application styles -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <!-- Custom button styles that won't be overridden -->
    <style>
        /* Button styles that won't be overridden */
        .btn-buy-now {
            display: block !important;
            width: 100% !important;
            padding: 12px 15px !important;
            background-color: #28a745 !important;
            color: white !important;
            border: none !important;
            border-radius: 4px !important;
            font-size: 18px !important;
            font-weight: bold !important;
            text-align: center !important;
            text-decoration: none !important;
            cursor: pointer !important;
            transition: background-color 0.3s !important;
        }
        
        .btn-buy-now:hover {
            background-color: #218838 !important;
            text-decoration: none !important;
            color: white !important;
        }
        
        .btn-egift {
            display: block !important;
            width: 100% !important;
            padding: 10px 15px !important;
            background-color: #17a2b8 !important;
            color: white !important;
            border: none !important;
            border-radius: 4px !important;
            font-size: 16px !important;
            font-weight: bold !important;
            text-align: center !important;
            text-decoration: none !important;
            cursor: pointer !important;
            transition: background-color 0.3s !important;
        }
        
        .btn-egift:hover {
            background-color: #138496 !important;
            text-decoration: none !important;
            color: white !important;
        }
        
        /* New header e-gift button styling */
        #header-egift-btn {
            background: linear-gradient(45deg, #6b46c1, #9f7aea) !important;
            color: white !important;
            font-weight: bold !important;
            padding: 10px 18px !important;
            border-radius: 25px !important;
            border: 2px solid white !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
            transition: all 0.3s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            position: relative !important;
            margin-left: 15px !important;
        }
        
        #header-egift-btn:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
            background: linear-gradient(45deg, #5a32ab, #8655e9) !important;
            text-decoration: none !important;
        }
        
        #header-egift-btn i {
            margin-right: 8px !important;
            font-size: 1.2em !important;
        }
        
        .notification-dot {
            position: absolute !important;
            top: -5px !important;
            right: -5px !important;
            width: 18px !important;
            height: 18px !important;
            background-color: #e53e3e !important;
            color: white !important;
            font-size: 12px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 2px solid white !important;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Art Gallery</h1>
                
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'viewer'): ?>
                <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=hub" id="header-egift-btn">
                    <i class="fas fa-gift"></i> E-Gift Cards
                    <?php if(isset($unread_notifications) && $unread_notifications > 0): ?>
                    <span class="notification-dot"><?php echo ($unread_notifications > 9) ? '9+' : $unread_notifications; ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
            </div>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=index">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=browse">Browse Art</a></li>
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=report&action=myReports">My Reports</a></li>
                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'viewer'): ?>
                        <li><a href="<?php echo BASE_URL; ?>index.php?controller=social&action=subscriptions">My Subscriptions</a></li>
                        <li><a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=history">Gift History</a></li>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'artist'): ?>
                        <li><a href="<?php echo BASE_URL; ?>index.php?controller=artist&action=dashboard">Artist Dashboard</a></li>
                    <?php endif; ?>
                    <li class="user-menu">
                        <span>Welcome, <?php echo isset($user_name) ? $user_name : 'Guest'; ?></span>
                        <a href="<?php echo BASE_URL; ?>index.php?controller=auth&action=logout" class="btn-logout">Logout</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
</body>
</html> 