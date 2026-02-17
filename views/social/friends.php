<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        .friends-container {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        .friends-section {
            flex: 1;
        }
        .friend-requests-section {
            flex: 1;
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
        .user-role {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 5px;
            background-color: #eee;
        }
        .role-artist {
            background-color: #e74c3c;
            color: white;
        }
        .role-viewer {
            background-color: #2ecc71;
            color: white;
        }
        .empty-list {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 4px;
            text-align: center;
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
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=social&action=friends" class="active">My Friends</a></li>
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
            <h2>My Friends</h2>
            
            <div class="friends-container">
                <div class="friends-section">
                    <h3>Friends</h3>
                    
                    <?php if(isset($friends) && is_object($friends) && $friends->num_rows > 0): ?>
                        <?php while($friend = $friends->fetch_assoc()): ?>
                            <div class="user-card">
                                <div class="user-image">
                                    <?php if(isset($friend['profile_image']) && !empty($friend['profile_image'])): ?>
                                        <img src="<?php echo BASE_URL . $friend['profile_image']; ?>" alt="<?php echo $friend['name']; ?>">
                                    <?php else: ?>
                                        <?php echo substr($friend['name'], 0, 1); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <h4>
                                        <?php echo $friend['name']; ?>
                                        <span class="user-role role-<?php echo $friend['role']; ?>"><?php echo ucfirst($friend['role']); ?></span>
                                    </h4>
                                    <p><?php echo $friend['email']; ?></p>
                                </div>
                                <div class="user-actions">
                                    <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=viewProfile&id=<?php echo $friend['id']; ?>" class="btn-secondary">View Profile</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-list">
                            <p>You don't have any friends yet. <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=search">Search</a> for users to add as friends.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="friend-requests-section">
                    <h3>Friend Requests</h3>
                    
                    <?php if(isset($friend_requests) && is_object($friend_requests) && $friend_requests->num_rows > 0): ?>
                        <?php while($request = $friend_requests->fetch_assoc()): ?>
                            <div class="user-card">
                                <div class="user-image">
                                    <?php if(isset($request['profile_image']) && !empty($request['profile_image'])): ?>
                                        <img src="<?php echo BASE_URL . $request['profile_image']; ?>" alt="<?php echo $request['name']; ?>">
                                    <?php else: ?>
                                        <?php echo substr($request['name'], 0, 1); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <h4>
                                        <?php echo $request['name']; ?>
                                        <span class="user-role role-<?php echo $request['role']; ?>"><?php echo ucfirst($request['role']); ?></span>
                                    </h4>
                                    <p><?php echo $request['email']; ?></p>
                                </div>
                                <div class="user-actions">
                                    <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=acceptFriend&id=<?php echo $request['id']; ?>" class="btn-primary">Accept</a>
                                    <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=rejectFriend&id=<?php echo $request['id']; ?>" class="btn-secondary">Reject</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-list">
                            <p>You don't have any pending friend requests.</p>
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