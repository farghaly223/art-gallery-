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
                    <li><a href="<?php echo BASE_URL; ?>index.php?controller=artist&action=dashboard" class="active">Dashboard</a></li>
                    <li class="user-menu">
                        <span>Welcome, <?php echo $user_name; ?></span>
                        <a href="<?php echo BASE_URL; ?>index.php?controller=auth&action=logout" class="btn-logout">Logout</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <!-- Display success message if any -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="success-message">
                <p><?php echo $_SESSION['success']; ?></p>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <!-- Display error messages if any -->
        <?php if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
            <div class="error-messages">
                <?php foreach($_SESSION['errors'] as $error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>
        
        <section class="dashboard">
            <h2>Artist Dashboard</h2>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_artworks']; ?></div>
                    <div class="stat-label">Total Artworks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['sold_artworks']; ?></div>
                    <div class="stat-label">Sold Artworks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['subscriber_count']; ?></div>
                    <div class="stat-label">Subscribers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            
            <!-- Add New Artwork Section -->
            <div class="dashboard-section">
                <h3>Add New Artwork</h3>
                <form action="<?php echo BASE_URL; ?>index.php?controller=artist&action=addArtwork" method="POST" enctype="multipart/form-data" class="artwork-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Artwork Title</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Price ($)</label>
                            <input type="number" id="price" name="price" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category_id">
                                <option value="">-- Select Category --</option>
                                <?php if(isset($categories) && $categories->num_rows > 0): ?>
                                    <?php while($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">Artwork Image</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                        <small>Maximum file size: 5MB. Accepted formats: JPG, PNG, GIF.</small>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Add Artwork</button>
                    </div>
                </form>
            </div>
            
            <!-- My Artworks Section -->
            <div class="dashboard-section">
                <h3>My Artworks</h3>
                
                <?php 
                // Handle different artwork data formats
                $artworksArray = [];
                $hasArtworks = false;
                
                if ($artworks) {
                    if (is_object($artworks) && method_exists($artworks, 'fetch_assoc')) {
                        // It's a mysqli_result object
                        $artworks->data_seek(0); // Reset pointer
                        while ($row = $artworks->fetch_assoc()) {
                            $artworksArray[] = $row;
                        }
                        $hasArtworks = count($artworksArray) > 0;
                    } elseif (is_array($artworks)) {
                        $artworksArray = $artworks;
                        $hasArtworks = count($artworksArray) > 0;
                    } elseif (is_object($artworks) && isset($artworks->data) && is_array($artworks->data)) {
                        $artworksArray = $artworks->data;
                        $hasArtworks = count($artworksArray) > 0;
                    }
                }
                
                if ($hasArtworks): ?>
                    <div class="art-grid">
                        <?php foreach($artworksArray as $artwork): 
                            $isSold = isset($artwork['status']) && $artwork['status'] == 'sold';
                        ?>
                            <div class="art-item <?php echo $isSold ? 'art-item-sold' : ''; ?>">
                                <?php if ($isSold): ?>
                                    <div class="sold-badge">SOLD</div>
                                <?php endif; ?>
                                <div class="art-image">
                                    <img src="<?php echo BASE_URL . htmlspecialchars($artwork['image_path'] ?? ''); ?>" 
                                         alt="<?php echo htmlspecialchars($artwork['title'] ?? ''); ?>"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22250%22 height=%22250%22%3E%3Crect fill=%22%23ddd%22 width=%22250%22 height=%22250%22/%3E%3Ctext fill=%22%23999%22 font-family=%22sans-serif%22 font-size=%2218%22 dy=%2210.5%22 font-weight=%22bold%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                                </div>
                                <h3><?php echo htmlspecialchars($artwork['title'] ?? 'Untitled'); ?></h3>
                                <p class="art-price">$<?php echo number_format($artwork['price'] ?? 0, 2); ?></p>
                                <p class="art-status">
                                    <strong>Status:</strong> 
                                    <?php if (isset($artwork['status'])): ?>
                                        <span class="badge badge-<?php echo $artwork['status'] == 'sold' ? 'danger' : 'success'; ?>">
                                            <?php echo ucfirst($artwork['status']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Available</span>
                                    <?php endif; ?>
                                </p>
                                <?php if(isset($artwork['category_name']) && !empty($artwork['category_name'])): ?>
                                    <p class="art-category"><strong>Category:</strong> <?php echo htmlspecialchars($artwork['category_name']); ?></p>
                                <?php endif; ?>
                                <p class="art-description"><?php echo htmlspecialchars(substr($artwork['description'] ?? '', 0, 100)); ?><?php echo strlen($artwork['description'] ?? '') > 100 ? '...' : ''; ?></p>
                                <div class="art-actions">
                                    <?php if (!$isSold): ?>
                                        <a href="<?php echo BASE_URL; ?>index.php?controller=artist&action=deleteArtwork&id=<?php echo $artwork['id']; ?>" 
                                           class="btn-secondary" 
                                           onclick="return confirm('Are you sure you want to delete this artwork?')">
                                            Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="sold-notice-text">This artwork has been sold</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>You haven't added any artworks yet. Use the form above to add your first artwork.</p>
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