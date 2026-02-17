<?php require_once 'views/layout_header.php'; ?>

<section class="payment-section">
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2>Payment</h2>
            </div>
            <div class="card-body">
                <div class="payment-details">
                    <h3><?php echo $payment['item_name']; ?></h3>
                    <p class="payment-amount">Amount: $<?php echo number_format($payment['amount'], 2); ?></p>
                    
                    <!-- In a real application, you would integrate with a payment gateway here -->
                    <div class="payment-form">
                        <form action="<?php echo BASE_URL; ?>index.php?controller=payment&action=complete" method="POST">
                            <input type="hidden" name="type" value="<?php echo $payment['type']; ?>">
                            <input type="hidden" name="item_id" value="<?php echo $payment['item_id']; ?>">
                            <input type="hidden" name="amount" value="<?php echo $payment['amount']; ?>">
                            
                            <div class="form-group mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="expiry" class="form-label">Expiry Date</label>
                                    <input type="text" id="expiry" name="expiry" class="form-control" placeholder="MM/YY" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" id="cvv" name="cvv" class="form-control" placeholder="123" required>
                                </div>
                            </div>
                            
                            <div class="form-group mb-4">
                                <label for="name_on_card" class="form-label">Name on Card</label>
                                <input type="text" id="name_on_card" name="name_on_card" class="form-control" required>
                            </div>
                            
                            <div class="payment-actions d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-success">Complete Payment</button>
                                <?php if($payment['type'] == 'artwork'): ?>
                                    <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=viewArtwork&id=<?php echo $payment['item_id']; ?>" class="btn btn-secondary">Cancel</a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=index" class="btn btn-secondary">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    
                    <div class="payment-note alert alert-info mt-4">
                        <p class="mb-0"><strong>Note:</strong> This is a simulation. No actual payment will be processed.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'views/layout_footer.php'; ?> 