<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <h1>Art Gallery</h1>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=index">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=browse" class="active">Browse Art</a></li>
                    <?php if(isset($user_role) && $user_role == 'artist'): ?>
                        <li><a href="<?php echo BASE_URL; ?>index.php?controller=artist&action=dashboard">Artist Dashboard</a></li>
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
        <!-- Display error messages if any -->
        <?php if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
            <div class="error-messages">
                <?php foreach($_SESSION['errors'] as $error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <section class="browse-section">
            <h2>Browse Artworks</h2>
            
            <!-- Search and filter form -->
            <div class="filter-container">
                <form action="<?php echo BASE_URL; ?>index.php" method="GET" class="search-form">
                    <input type="hidden" name="controller" value="gallery">
                    <input type="hidden" name="action" value="browse">
                    
                    <div class="filter-group">
                        <div class="search-box">
                            <input type="text" name="search" placeholder="Search artworks or artists..." value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>">
                            <button type="submit" class="btn-search">Search</button>
                        </div>
                        
                        <div class="filters">
                            <div class="filter-item">
                                <label for="category">Category:</label>
                                <select name="category" id="category">
                                    <option value="">All Categories</option>
                                    <?php if(isset($categories) && $categories->num_rows > 0): ?>
                                        <?php while($category = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo $category['name']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="filter-item">
                                <label for="min_price">Min Price:</label>
                                <input type="number" name="min_price" id="min_price" min="0" step="0.01" value="<?php echo isset($min_price) ? $min_price : ''; ?>">
                            </div>
                            
                            <div class="filter-item">
                                <label for="max_price">Max Price:</label>
                                <input type="number" name="max_price" id="max_price" min="0" step="0.01" value="<?php echo isset($max_price) ? $max_price : ''; ?>">
                            </div>
                            
                            <button type="submit" class="btn-filter">Apply Filters</button>
                            <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=browse" class="btn-reset">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Display search results -->
            <div class="search-results">
                <?php if(isset($search) && !empty($search)): ?>
                    <p class="search-info">Search results for: <strong><?php echo htmlspecialchars($search); ?></strong></p>
                <?php endif; ?>
                
                <?php if(isset($category_id) && !empty($category_id)): ?>
                    <?php 
                    $categoryName = "Unknown";
                    if(isset($categories) && $categories->num_rows > 0) {
                        $categories->data_seek(0); // Reset result pointer
                        while($category = $categories->fetch_assoc()) {
                            if($category['id'] == $category_id) {
                                $categoryName = $category['name'];
                                break;
                            }
                        }
                    }
                    ?>
                    <p class="filter-info">Category: <strong><?php echo $categoryName; ?></strong></p>
                <?php endif; ?>
                
                <?php if(isset($min_price) && !empty($min_price)): ?>
                    <p class="filter-info">Min Price: <strong>$<?php echo $min_price; ?></strong></p>
                <?php endif; ?>
                
                <?php if(isset($max_price) && !empty($max_price)): ?>
                    <p class="filter-info">Max Price: <strong>$<?php echo $max_price; ?></strong></p>
                <?php endif; ?>
            </div>
            
            <!-- Artwork grid -->
            <?php if(isset($artworks) && is_object($artworks) && $artworks->num_rows > 0): ?>
                <div class="art-grid">
                    <?php while($artwork = $artworks->fetch_assoc()): ?>
                        <div class="art-item">
                            <div class="art-image">
                                <img src="<?php echo BASE_URL . $artwork['image_path']; ?>" alt="<?php echo $artwork['title']; ?>">
                            </div>
                            <h3><?php echo $artwork['title']; ?></h3>
                            <p class="art-price">$<?php echo number_format($artwork['price'], 2); ?></p>
                            <p>By: <?php echo $artwork['artist_name']; ?></p>
                            <?php if(isset($artwork['category_name']) && !empty($artwork['category_name'])): ?>
                                <p class="art-category">Category: <?php echo $artwork['category_name']; ?></p>
                            <?php endif; ?>
                            <p><?php echo substr($artwork['description'], 0, 100); ?>...</p>
                            <div class="art-actions">
                                <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=viewArtwork&id=<?php echo $artwork['id']; ?>" class="btn-primary" style="width: auto; display: inline-block;">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-artworks">
                    <p>No artworks found matching your criteria. Try adjusting your search or filters.</p>
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