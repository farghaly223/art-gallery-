<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        .subscribers-container {
            margin-top: 20px;
        }
        .user-card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            background-color: #f9f9f9;
        }
        .user-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #666;
        }
        .user-info {
            flex: 1;
        }
        .user-actions {
            display: flex;
            gap: 10px;
        }
        .empty-list {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 4px;
            text-align: center;
            color: #666;
        }
        .subscription-date {
            font-size: 12px;
            color: #666;
        }
    </style>
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
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=artist&action=dashboard">Artist Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=social&action=subscribers" class="active">My Subscribers</a></li>
                    <li class="user-menu">
                        <span>Welcome, <?php echo $user_name; ?></span>
                        <a href="<?php echo BASE_URL; ?>index.php?controller=auth&action=logout" class="btn-logout">Logout</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <!-- Display error/success messages if any -->
        <?php if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
            <div class="error-messages">
                <?php foreach($_SESSION['errors'] as $error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="success-message">
                <p class="success"><?php echo $_SESSION['success']; ?></p>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <section>
            <h2>My Subscribers</h2>
            <p>Users who are subscribed to your artwork</p>
            
            <div class="subscribers-container">
                <?php if(isset($subscribers) && is_object($subscribers) && $subscribers->num_rows > 0): ?>
                    <?php while($subscriber = $subscribers->fetch_assoc()): ?>
                        <div class="user-card">
                            <div class="user-image">
                                <?php if(isset($subscriber['profile_image']) && !empty($subscriber['profile_image'])): ?>
                                    <img src="<?php echo BASE_URL . $subscriber['profile_image']; ?>" alt="<?php echo $subscriber['name']; ?>">
                                <?php else: ?>
                                    <?php echo substr($subscriber['name'], 0, 1); ?>
                                <?php endif; ?>
                            </div>
                            <div class="user-info">
                                <h4><?php echo $subscriber['name']; ?></h4>
                                <p><?php echo $subscriber['email']; ?></p>
                                <p class="subscription-date">Subscribed since: <?php echo date('F j, Y', strtotime($subscriber['created_at'])); ?></p>
                            </div>
                            <div class="user-actions">
                                <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=viewProfile&id=<?php echo $subscriber['id']; ?>" class="btn-secondary">View Profile</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-list">
                        <p>You don't have any subscribers yet. Keep adding more artwork to attract subscribers!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Art Gallery. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 