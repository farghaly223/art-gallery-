<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <!-- Page title and notifications counter -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-gift text-purple me-2"></i> E-Gift Cards Hub</h2>
        <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=notifications" class="btn btn-outline-primary position-relative">
            <i class="fas fa-bell me-2"></i> Notifications
            <?php if(isset($unread_notifications) && $unread_notifications > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $unread_notifications; ?>
                </span>
            <?php endif; ?>
        </a>
    </div>
    
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
    
    <div class="row">
        <!-- Left column: Main actions -->
        <div class="col-lg-8">
            <!-- Buy E-Gift Card section -->
            <div class="card mb-4 border-purple shadow-sm">
                <div class="card-header bg-purple text-white">
                    <h3 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Buy E-Gift Card</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4>Purchase an E-Gift Card</h4>
                            <p>E-Gift cards are perfect for giving the gift of art to your friends and family. Recipients can use them to purchase any artwork on our platform.</p>
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success me-2"></i> Instant or scheduled delivery
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success me-2"></i> Personalized messages
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-check-circle text-success me-2"></i> Safe and secure payment
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <svg width="120" height="90" viewBox="0 0 120 90" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="0" y="0" width="120" height="90" rx="8" fill="#6b46c1"/>
                                    <rect x="4" y="4" width="112" height="82" rx="6" fill="#9f7aea" stroke="white" stroke-width="1"/>
                                    <text x="60" y="30" font-family="Arial" font-size="12" font-weight="bold" fill="white" text-anchor="middle">ART GALLERY</text>
                                    <text x="60" y="50" font-family="Arial" font-size="18" font-weight="bold" fill="white" text-anchor="middle">GIFT CARD</text>
                                    <rect x="20" y="60" width="80" height="16" rx="4" fill="rgba(255,255,255,0.3)"/>
                                    <path d="M30,68 L52,68 M58,68 L90,68" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                    <circle cx="92" cy="20" r="10" fill="#f6e05e"/>
                                    <path d="M92,16 L92,20 L96,20" stroke="#805ad5" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=buy" class="btn-buy-now">
                                <i class="fas fa-shopping-cart me-2"></i> Buy Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Send E-Gift section (legacy option) -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="mb-0"><i class="fas fa-paper-plane me-2"></i> Send E-Gift</h3>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4>Send an E-Gift to Someone</h4>
                            <p>Send an e-gift card to another user in our community. Perfect for birthdays, holidays, or just to show appreciation!</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=send" class="btn-egift">
                                <i class="fas fa-paper-plane me-2"></i> Send E-Gift
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right column: Activity & Quick stats -->
        <div class="col-lg-4">
            <!-- Gift Stats -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h3 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Your E-Gift Stats</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 rounded bg-light">
                                <h3 class="text-primary mb-0"><?php echo isset($sent_count) ? $sent_count : '0'; ?></h3>
                                <p class="text-muted mb-0">Sent</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 rounded bg-light">
                                <h3 class="text-success mb-0"><?php echo isset($received_count) ? $received_count : '0'; ?></h3>
                                <p class="text-muted mb-0">Received</p>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h5 class="mb-3">Recent Activity</h5>
                    
                    <?php if(isset($recent_activity) && count($recent_activity) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($recent_activity as $activity): ?>
                                <li class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <?php if($activity['type'] == 'sent'): ?>
                                                <span class="badge bg-primary"><i class="fas fa-paper-plane"></i></span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><i class="fas fa-gift"></i></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ms-3">
                                            <p class="mb-0">
                                                <?php if($activity['type'] == 'sent'): ?>
                                                    Sent $<?php echo number_format($activity['amount'], 2); ?> to <?php echo $activity['name']; ?>
                                                <?php else: ?>
                                                    Received $<?php echo number_format($activity['amount'], 2); ?> from <?php echo $activity['name']; ?>
                                                <?php endif; ?>
                                            </p>
                                            <small class="text-muted"><?php echo date('M j, Y', strtotime($activity['date'])); ?></small>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p class="mb-0">No recent activity to display.</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=history" class="btn btn-outline-primary w-100">
                            <i class="fas fa-history me-2"></i> View Full History
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Help section -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h3 class="mb-0"><i class="fas fa-question-circle me-2"></i> E-Gift Help</h3>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordionHelp">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    How do E-Gift Cards work?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionHelp">
                                <div class="accordion-body">
                                    E-Gift cards allow you to send store credit to other users. Recipients can use the gift amount to purchase artwork on our platform.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Do E-Gift Cards expire?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionHelp">
                                <div class="accordion-body">
                                    No, our E-Gift cards never expire and can be used at any time after they are received.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Can I schedule a gift for a future date?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionHelp">
                                <div class="accordion-body">
                                    Yes! When purchasing an E-Gift card, you can select a future delivery date for special occasions like birthdays or holidays.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS for accordion functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .bg-purple {
        background-color: #6b46c1 !important;
    }
    
    .text-purple {
        color: #6b46c1 !important;
    }
    
    .border-purple {
        border-color: #6b46c1 !important;
    }
</style>

<?php require_once 'views/layout_footer.php'; ?> 