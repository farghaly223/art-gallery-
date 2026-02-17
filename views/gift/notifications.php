<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h2>Notifications</h2>
            <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=history" class="btn btn-light">View Gift History</a>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>
            
            <?php if (isset($notifications) && $notifications->num_rows > 0): ?>
                <div class="list-group">
                    <?php while ($notification = $notifications->fetch_assoc()): ?>
                        <div class="list-group-item list-group-item-action <?php echo $notification['is_read'] ? '' : 'fw-bold bg-light'; ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">
                                    <?php if ($notification['type'] == 'gift'): ?>
                                        <i class="fas fa-gift text-success me-2"></i>
                                    <?php elseif ($notification['type'] == 'sale'): ?>
                                        <i class="fas fa-money-bill-wave text-primary me-2"></i>
                                    <?php else: ?>
                                        <i class="fas fa-bell text-secondary me-2"></i>
                                    <?php endif; ?>
                                    <?php echo $notification['message']; ?>
                                </h5>
                                <small><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></small>
                            </div>
                            
                            <?php if (!$notification['is_read']): ?>
                                <div class="mt-2">
                                    <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=markRead&id=<?php echo $notification['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                        Mark as Read
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">You have no notifications at this time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'views/layout_footer.php'; ?> 