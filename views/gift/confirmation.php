<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h2>E-Gift Payment Confirmation</h2>
        </div>
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success fa-5x"></i>
            </div>
            
            <h3 class="mb-4">Your E-Gift Payment Has Been Completed!</h3>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5>E-Gift Details</h5>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-6 text-start">
                                    <strong>Recipient:</strong>
                                </div>
                                <div class="col-sm-6 text-end">
                                    <?php echo isset($recipient_name) ? $recipient_name : 'Unknown'; ?>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-6 text-start">
                                    <strong>Amount:</strong>
                                </div>
                                <div class="col-sm-6 text-end text-success fw-bold">
                                    $<?php echo isset($gift_data['amount']) ? number_format($gift_data['amount'], 2) : '0.00'; ?>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-6 text-start">
                                    <strong>Delivery Date:</strong>
                                </div>
                                <div class="col-sm-6 text-end">
                                    <?php echo isset($gift_data['delivery_date']) ? date('F j, Y', strtotime($gift_data['delivery_date'])) : 'Today'; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-sm-6 text-start">
                                    <strong>Transaction ID:</strong>
                                </div>
                                <div class="col-sm-6 text-end">
                                    <?php echo isset($transaction_id) ? $transaction_id : 'N/A'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(isset($gift_data['delivery_date']) && strtotime($gift_data['delivery_date']) > strtotime(date('Y-m-d'))): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Your e-gift will be delivered to the recipient on <?php echo date('F j, Y', strtotime($gift_data['delivery_date'])); ?>.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-paper-plane me-2"></i>
                            Your e-gift has been sent to the recipient immediately.
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=history" class="btn-egift">
                            <i class="fas fa-history me-2"></i> View Gift History
                        </a>
                        <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=index" class="btn btn-outline-secondary mt-3">
                            <i class="fas fa-home me-2"></i> Return to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layout_footer.php'; ?> 