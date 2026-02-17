<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <style>
        .search-box {
            margin-bottom: 20px;
        }
        .search-form {
            display: flex;
            max-width: 600px;
        }
        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 4px 0 0 4px;
        }
        .search-button {
            padding: 10px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
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
        .search-results {
            margin-top: 20px;
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
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=social&action=search" class="active">Search Users</a></li>
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

        <section class="search-section">
            <h2>Search Users</h2>
            <p>Find users and artists in our community</p>
            
            <div class="search-box">
                <form action="<?php echo BASE_URL; ?>index.php?controller=social&action=search" method="GET" class="search-form">
                    <input type="hidden" name="controller" value="social">
                    <input type="hidden" name="action" value="search">
                    <input type="text" name="query" placeholder="Search by name or email..." value="<?php echo $query; ?>" class="search-input" required>
                    <button type="submit" class="search-button">Search</button>
                </form>
            </div>
            
            <?php if(!empty($query)): ?>
                <div class="search-results">
                    <h3>Results for "<?php echo htmlspecialchars($query); ?>"</h3>
                    
                    <?php if(isset($results) && is_object($results) && $results->num_rows > 0): ?>
                        <?php while($user = $results->fetch_assoc()): ?>
                            <div class="user-card">
                                <div class="user-image">
                                    <?php if(isset($user['profile_image']) && !empty($user['profile_image'])): ?>
                                        <img src="<?php echo BASE_URL . $user['profile_image']; ?>" alt="<?php echo $user['name']; ?>">
                                    <?php else: ?>
                                        <?php echo substr($user['name'], 0, 1); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <h4>
                                        <?php echo $user['name']; ?>
                                        <span class="user-role role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
                                    </h4>
                                    <p><?php echo $user['email']; ?></p>
                                </div>
                                <div class="user-actions">
                                    <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=viewProfile&id=<?php echo $user['id']; ?>" class="btn-secondary">View Profile</a>
                                    
                                    <?php if($user['role'] == 'artist' && $_SESSION['user_role'] == 'viewer'): ?>
                                        <form action="<?php echo BASE_URL; ?>index.php?controller=social&action=subscribe" method="POST">
                                            <input type="hidden" name="artist_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn-primary">Subscribe</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if($user['id'] != $current_user_id): ?>
                                        <form action="<?php echo BASE_URL; ?>index.php?controller=social&action=addFriend" method="POST">
                                            <input type="hidden" name="friend_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn-primary">Add Friend</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <p>No users found matching your search.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Art Gallery. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 