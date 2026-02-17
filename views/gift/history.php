<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h2>E-Gift History</h2>
            <div>
                <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=send" class="btn btn-light me-2">Send New E-Gift</a>
                <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=notifications" class="btn btn-light">Notifications</a>
            </div>
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
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <p class="mb-0"><?php echo $_SESSION['success']; ?></p>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <ul class="nav nav-tabs mb-4" id="giftTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab" aria-controls="sent" aria-selected="true">
                        <i class="fas fa-paper-plane me-2"></i> Sent Gifts
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="received-tab" data-bs-toggle="tab" data-bs-target="#received" type="button" role="tab" aria-controls="received" aria-selected="false">
                        <i class="fas fa-gift me-2"></i> Received Gifts
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="giftTabsContent">
                <!-- Sent Gifts Tab -->
                <div class="tab-pane fade show active" id="sent" role="tabpanel" aria-labelledby="sent-tab">
                    <?php if (isset($sent_gifts) && $sent_gifts->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Recipient</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($gift = $sent_gifts->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $gift['recipient_name']; ?></td>
                                            <td>$<?php echo number_format($gift['amount'], 2); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                switch ($gift['status']) {
                                                    case 'pending':
                                                        $statusClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'delivered':
                                                        $statusClass = 'bg-success text-white';
                                                        break;
                                                    case 'redeemed':
                                                        $statusClass = 'bg-info text-white';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($gift['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $gift['message'] ? $gift['message'] : '<em>No message</em>'; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($gift['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p class="mb-0">You have not sent any e-gifts yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Received Gifts Tab -->
                <div class="tab-pane fade" id="received" role="tabpanel" aria-labelledby="received-tab">
                    <?php if (isset($received_gifts) && $received_gifts->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>From</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($gift = $received_gifts->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $gift['sender_name']; ?></td>
                                            <td>$<?php echo number_format($gift['amount'], 2); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                switch ($gift['status']) {
                                                    case 'pending':
                                                        $statusClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'delivered':
                                                        $statusClass = 'bg-success text-white';
                                                        break;
                                                    case 'redeemed':
                                                        $statusClass = 'bg-info text-white';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($gift['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $gift['message'] ? $gift['message'] : '<em>No message</em>'; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($gift['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p class="mb-0">You have not received any e-gifts yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layout_footer.php'; ?> 