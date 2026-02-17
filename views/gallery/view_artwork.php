<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <!-- Display error messages if any -->
    <?php if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <?php foreach($_SESSION['errors'] as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- Display success messages if any -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <p><?php echo $_SESSION['success']; ?></p>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="artwork-detail">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body p-0">
                        <img src="<?php echo BASE_URL . $artwork['image_path']; ?>" alt="<?php echo $artwork['title']; ?>" class="img-fluid">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h2><?php echo $artwork['title']; ?></h2>
                    </div>
                    <div class="card-body">
                        <h5 class="artist-name">By: <?php echo $artist['name']; ?></h5>
                        <h4 class="artwork-price text-success fw-bold">$<?php echo number_format($artwork['price'], 2); ?></h4>
                        
                        <?php if(isset($artwork['category_name']) && !empty($artwork['category_name'])): ?>
                            <p class="artwork-category"><strong>Category:</strong> <?php echo $artwork['category_name']; ?></p>
                        <?php endif; ?>
                        
                        <div class="artwork-status mb-3">
                            <strong>Status:</strong> 
                            <?php if (isset($artwork['status'])): ?>
                                <span class="badge bg-<?php echo $artwork['status'] == 'available' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($artwork['status']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">Available</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="artwork-description mb-4">
                            <h5>Description</h5>
                            <p><?php echo $artwork['description']; ?></p>
                        </div>
                        
                        <div class="artwork-actions">
                            <?php if (!isset($artwork['status']) || $artwork['status'] == 'available'): ?>
                                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'viewer'): ?>
                                    <div class="d-grid gap-2 mb-3">
                                        <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=buyArtwork&id=<?php echo $artwork['id']; ?>" class="btn-buy-now">
                                            <i class="fas fa-shopping-cart me-2"></i>
                                            Buy Now - $<?php echo number_format($artwork['price'], 2); ?>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <span class="availability-notice">This artwork is available for purchase.</span>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle me-2"></i>
                                    <strong>This artwork has been sold</strong> and is no longer available for purchase.
                                </div>
                            <?php endif; ?>
                            
                            <div class="row mt-3">
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'viewer'): ?>
                                    <div class="col-6">
                                        <a href="#" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-envelope me-1"></i> Contact Artist
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="col-<?php echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'viewer') ? '6' : '12'; ?>">
                                    <a href="<?php echo BASE_URL; ?>index.php?controller=report&action=index&type=artwork&id=<?php echo $artwork['id']; ?>" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-flag me-1"></i> Report Artwork
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="my-3">
            <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=index" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Gallery
            </a>
        </div>
    </div>
</div>

<?php require_once 'views/layout_footer.php'; ?> 