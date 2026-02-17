<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        .subscriptions-container {
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
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=social&action=subscriptions" class="active">My Subscriptions</a></li>
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
            <h2>My Subscriptions</h2>
            <p>Artists you are subscribed to</p>
            
            <div class="subscriptions-container">
                <?php if(isset($subscriptions) && is_object($subscriptions) && $subscriptions->num_rows > 0): ?>
                    <?php while($artist = $subscriptions->fetch_assoc()): ?>
                        <div class="user-card">
                            <div class="user-image">
                                <?php if(isset($artist['profile_image']) && !empty($artist['profile_image'])): ?>
                                    <img src="<?php echo BASE_URL . $artist['profile_image']; ?>" alt="<?php echo $artist['name']; ?>">
                                <?php else: ?>
                                    <?php echo substr($artist['name'], 0, 1); ?>
                                <?php endif; ?>
                            </div>
                            <div class="user-info">
                                <h4><?php echo $artist['name']; ?></h4>
                                <p><?php echo $artist['email']; ?></p>
                                <p class="subscription-date">Subscribed since: <?php echo date('F j, Y', strtotime($artist['created_at'])); ?></p>
                            </div>
                            <div class="user-actions">
                                <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=viewProfile&id=<?php echo $artist['id']; ?>" class="btn-secondary">View Profile</a>
                                <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=browse&artist=<?php echo $artist['id']; ?>" class="btn-secondary">View Artwork</a>
                                <form action="<?php echo BASE_URL; ?>index.php?controller=social&action=unsubscribe" method="POST">
                                    <input type="hidden" name="artist_id" value="<?php echo $artist['id']; ?>">
                                    <button type="submit" class="btn-secondary">Unsubscribe</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-list">
                        <p>You are not subscribed to any artists yet. <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=search">Search</a> for artists to subscribe to.</p>
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