<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <!-- Display error messages if any -->
    <?php if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php foreach($_SESSION['errors'] as $error): ?>
                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- Display success messages if any -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <p class="mb-0"><?php echo htmlspecialchars($_SESSION['success']); ?></p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <!-- Welcome Section -->
    <section class="welcome-section mb-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white border-0">
                <h2 class="mb-0"><i class="fas fa-palette me-2"></i>Welcome to the Art Gallery</h2>
            </div>
            <div class="card-body py-4">
                <p class="lead mb-0 text-muted">Discover and explore amazing artwork from talented artists in our community.</p>
            </div>
        </div>
    </section>
    
    <?php if(isset($user_role) && $user_role == 'viewer'): ?>
    <!-- E-Gift Section for Viewers -->
    <section class="egift-section mb-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-success text-white border-0 d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="fas fa-gift me-2"></i>E-Gift Cards</h3>
            </div>
            <div class="card-body py-4">
                <p class="mb-4 text-muted">Send or buy e-gift cards for your friends and family to use in our art gallery.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=buy" class="btn btn-success btn-lg">
                        <i class="fas fa-shopping-cart me-2"></i> Buy E-Gift Card
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=send" class="btn btn-info btn-lg">
                        <i class="fas fa-gift me-2"></i> Send E-Gift Card
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Social Section for all users -->
    <section class="social-section mb-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-info text-white border-0">
                <h3 class="mb-0"><i class="fas fa-users me-2"></i>Connect with our Community</h3>
            </div>
            <div class="card-body py-4">
                <p class="mb-4 text-muted">Find friends and follow your favorite artists!</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=search" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i> Search Users
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=friends" class="btn btn-outline-primary">
                        <i class="fas fa-users me-2"></i> My Friends
                    </a>
                    <?php if(isset($user_role) && $user_role == 'viewer'): ?>
                        <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=subscriptions" class="btn btn-outline-primary">
                            <i class="fas fa-star me-2"></i> My Subscriptions
                        </a>
                    <?php endif; ?>
                    <?php if(isset($user_role) && $user_role == 'artist'): ?>
                        <a href="<?php echo BASE_URL; ?>index.php?controller=social&action=subscribers" class="btn btn-outline-primary">
                            <i class="fas fa-users me-2"></i> My Subscribers
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Artwork Section -->
    <section class="featured-art">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white border-0">
                <h2 class="mb-0"><i class="fas fa-images me-2"></i>Featured Artwork</h2>
            </div>
            <div class="card-body py-4">
                <?php 
                $hasArtworks = false;
                $artworksArray = [];
                
                // Handle different artwork data formats
                if (isset($artworks)) {
                    if (is_object($artworks) && isset($artworks->data) && is_array($artworks->data)) {
                        // Artworks converted to object with data array
                        $artworksArray = $artworks->data;
                        $hasArtworks = count($artworksArray) > 0;
                    } elseif (is_object($artworks) && method_exists($artworks, 'fetch_assoc')) {
                        // It's a mysqli_result object
                        $artworks->data_seek(0); // Reset pointer
                        while ($row = $artworks->fetch_assoc()) {
                            $artworksArray[] = $row;
                        }
                        $hasArtworks = count($artworksArray) > 0;
                    } elseif (is_array($artworks)) {
                        // Already an array
                        $artworksArray = $artworks;
                        $hasArtworks = count($artworksArray) > 0;
                    } elseif (is_object($artworks) && isset($artworks->num_rows) && $artworks->num_rows > 0) {
                        // Object with num_rows property
                        if (method_exists($artworks, 'fetch_assoc')) {
                            $artworks->data_seek(0);
                            while ($row = $artworks->fetch_assoc()) {
                                $artworksArray[] = $row;
                            }
                        }
                        $hasArtworks = count($artworksArray) > 0;
                    }
                }
                
                if ($hasArtworks): ?>
                    <div class="row g-4">
                        <?php foreach($artworksArray as $artwork): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card h-100 artwork-card border-0 shadow-sm">
                                    <div class="artwork-image-wrapper position-relative overflow-hidden">
                                        <img src="<?php echo BASE_URL . htmlspecialchars($artwork['image_path'] ?? ''); ?>" 
                                             alt="<?php echo htmlspecialchars($artwork['title'] ?? 'Artwork'); ?>" 
                                             class="card-img-top artwork-image"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22250%22 height=%22250%22%3E%3Crect fill=%22%23ddd%22 width=%22250%22 height=%22250%22/%3E%3Ctext fill=%22%23999%22 font-family=%22sans-serif%22 font-size=%2218%22 dy=%2210.5%22 font-weight=%22bold%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title mb-2"><?php echo htmlspecialchars($artwork['title'] ?? 'Untitled'); ?></h5>
                                        <p class="card-text text-success fw-bold price-tag mb-2 fs-5">$<?php echo number_format($artwork['price'] ?? 0, 2); ?></p>
                                        <p class="card-text artist-name mb-3">
                                            <i class="fas fa-user me-2"></i>
                                            <span><?php echo htmlspecialchars($artwork['artist_name'] ?? 'Unknown Artist'); ?></span>
                                        </p>
                                        <?php if (!empty($artwork['description'])): ?>
                                            <p class="card-text text-muted flex-grow-1 small">
                                                <?php echo htmlspecialchars(substr($artwork['description'], 0, 100)); ?>
                                                <?php echo strlen($artwork['description']) > 100 ? '...' : ''; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-white border-top-0 pt-0">
                                        <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=viewArtwork&id=<?php echo $artwork['id']; ?>" 
                                           class="btn btn-primary w-100">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center py-5 border-0">
                        <i class="fas fa-palette fa-4x mb-3 text-muted"></i>
                        <h4 class="text-muted mb-2">No Artworks Available</h4>
                        <p class="text-muted mb-0">Check back later to discover amazing artwork!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php require_once 'views/layout_footer.php'; ?>
