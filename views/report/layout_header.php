<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <!-- Bootstrap CSS for better styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <h1>Art Gallery</h1>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=index">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=browse">Browse Art</a></li>
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=social&action=search">Search Users</a></li>
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=social&action=friends">My Friends</a></li>
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=report&action=myReports">My Reports</a></li>
                    <?php if($_SESSION['user_role'] == 'viewer'): ?>
                        <li><a href="<?php echo BASE_URL; ?>index.php?controller=social&action=subscriptions">My Subscriptions</a></li>
                    <?php endif; ?>
                    <?php if($_SESSION['user_role'] == 'artist'): ?>
                        <li><a href="<?php echo BASE_URL; ?>index.php?controller=artist&action=dashboard">Artist Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>index.php?controller=social&action=subscribers">My Subscribers</a></li>
                    <?php endif; ?>
                    <li class="user-menu">
                        <span>Welcome, <?php echo $user_name; ?></span>
                        <a href="<?php echo BASE_URL; ?>index.php?controller=auth&action=logout" class="btn-logout">Logout</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
</body>
</html> 