<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        .profile-container {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        .profile-sidebar {
            flex: 0 0 250px;
        }
        .profile-content {
            flex: 1;
        }
        .profile-image {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            color: #666;
            margin-bottom: 20px;
        }
        .profile-actions {
            margin-top: 20px;
        }
        .profile-actions form {
            margin-bottom: 10px;
        }
        .profile-info {
            margin-bottom: 30px;
        }
        .profile-info h3 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .profile-info-item {
            margin-bottom: 10px;
        }
        .profile-info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        .user-role-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 14px;
            margin-left: 10px;
        }
        .role-artist {
            background-color: #e74c3c;
            color: white;
        }
        .role-viewer {
            background-color: #2ecc71;
            color: white;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 3px;
            display: inline-block;
            margin-top: 10px;
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
            <h2><?php echo $profile['name']; ?>'s Profile <span class="user-role-badge role-<?php echo $profile['role']; ?>"><?php echo ucfirst($profile['role']); ?></span></h2>
            
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-image">
                        <?php if(isset($profile['profile_image']) && !empty($profile['profile_image'])): ?>
                            <img src="<?php echo BASE_URL . $profile['profile_image']; ?>" alt="<?php echo $profile['name']; ?>">
                        <?php else: ?>
                            <?php echo substr($profile['name'], 0, 1); ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-actions">
                        <?php if($profile['id'] != $_SESSION['user_id']): ?>
                            <!-- Friend Request Button (if not already friends) -->
                            <?php if(!$friend_status): ?>
                                <form action="<?php echo BASE_URL; ?>index.php?controller=social&action=addFriend" method="POST">
                                    <input type="hidden" name="friend_id" value="<?php echo $profile['id']; ?>">
                                    <button type="submit" class="btn-primary" style="width: 100%;">Add Friend</button>
                                </form>
                            <?php elseif($friend_status['status'] == 'pending'): ?>
                                <button class="btn-secondary" style="width: 100%;" disabled>Friend Request Pending</button>
                            <?php elseif($friend_status['status'] == 'accepted'): ?>
                                <button class="btn-secondary" style="width: 100%;" disabled>Already Friends</button>
                            <?php endif; ?>
                            
                            <!-- Subscribe/Unsubscribe Button (if viewing an artist) -->
                            <?php if($profile['role'] == 'artist' && $_SESSION['user_role'] == 'viewer'): ?>
                                <?php if(!$is_subscribed): ?>
                                    <form action="<?php echo BASE_URL; ?>index.php?controller=social&action=subscribe" method="POST">
                                        <input type="hidden" name="artist_id" value="<?php echo $profile['id']; ?>">
                                        <button type="submit" class="btn-primary" style="width: 100%;">Subscribe</button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?php echo BASE_URL; ?>index.php?controller=social&action=unsubscribe" method="POST">
                                        <input type="hidden" name="artist_id" value="<?php echo $profile['id']; ?>">
                                        <button type="submit" class="btn-secondary" style="width: 100%;">Unsubscribe</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- Report User Button -->
                            <a href="<?php echo BASE_URL; ?>index.php?controller=report&action=index&type=user&id=<?php echo $profile['id']; ?>" class="btn-danger" style="width: 100%; display: block; text-align: center; margin-top: 10px;">Report User</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="profile-content">
                    <div class="profile-info">
                        <h3>User Information</h3>
                        
                        <div class="profile-info-item">
                            <span class="profile-info-label">Name:</span>
                            <span><?php echo $profile['name']; ?></span>
                        </div>
                        
                        <div class="profile-info-item">
                            <span class="profile-info-label">Email:</span>
                            <span><?php echo $profile['email']; ?></span>
                        </div>
                        
                        <div class="profile-info-item">
                            <span class="profile-info-label">Role:</span>
                            <span><?php echo ucfirst($profile['role']); ?></span>
                        </div>
                        
                        <div class="profile-info-item">
                            <span class="profile-info-label">Joined:</span>
                            <span><?php echo date('F j, Y', strtotime($profile['created_at'])); ?></span>
                        </div>
                    </div>
                    
                    <!-- Display Artist Artworks if an artist -->
                    <?php if($profile['role'] == 'artist'): ?>
                        <div class="profile-info">
                            <h3>Artworks</h3>
                            <p>View <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=browse&artist=<?php echo $profile['id']; ?>">all artworks by <?php echo $profile['name']; ?></a></p>
                        </div>
                    <?php endif; ?>
                </div>
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