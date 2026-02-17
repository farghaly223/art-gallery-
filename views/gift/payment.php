<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h2>Complete E-Gift Payment</h2>
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
            
            <div class="row">
                <div class="col-md-7">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h4 class="mb-0">Payment Details</h4>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo BASE_URL; ?>index.php?controller=gift&action=completePayment" method="POST" class="payment-form">
                                <!-- Hidden fields to carry over gift details -->
                                <input type="hidden" name="recipient_id" value="<?php echo isset($gift_data['recipient_id']) ? $gift_data['recipient_id'] : ''; ?>">
                                <input type="hidden" name="amount" value="<?php echo isset($gift_data['amount']) ? $gift_data['amount'] : ''; ?>">
                                <input type="hidden" name="message" value="<?php echo isset($gift_data['message']) ? htmlspecialchars($gift_data['message']) : ''; ?>">
                                <input type="hidden" name="delivery_date" value="<?php echo isset($gift_data['delivery_date']) ? $gift_data['delivery_date'] : ''; ?>">
                                
                                <div class="mb-3">
                                    <label for="card_name" class="form-label">Name on Card</label>
                                    <input type="text" id="card_name" name="card_name" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                        <input type="text" id="card_number" name="card_number" class="form-control" placeholder="XXXX XXXX XXXX XXXX" required 
                                               pattern="[0-9 ]{13,19}" maxlength="19" autocomplete="cc-number">
                                    </div>
                                    <div class="form-text">Spaces are allowed. Enter a valid card number.</div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="expiry_date" class="form-label">Expiry Date</label>
                                        <input type="text" id="expiry_date" name="expiry_date" class="form-control" placeholder="MM/YY" required 
                                               pattern="(0[1-9]|1[0-2])\/[0-9]{2}" maxlength="5" autocomplete="cc-exp">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="security_code" class="form-label">Security Code</label>
                                        <input type="password" id="security_code" name="security_code" class="form-control" placeholder="CVV" required 
                                               pattern="[0-9]{3,4}" maxlength="4" autocomplete="cc-csc">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="billing_address" class="form-label">Billing Address</label>
                                    <textarea id="billing_address" name="billing_address" class="form-control" rows="3" required></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn-buy-now">
                                        <i class="fas fa-lock me-2"></i> Complete Payment - $<?php echo isset($gift_data['amount']) ? number_format($gift_data['amount'], 2) : '0.00'; ?>
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>index.php?controller=gift&action=buy" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Back to E-Gift Details
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-shield-alt me-2 fa-lg"></i>
                            <h5 class="mb-0">Secure Payment</h5>
                        </div>
                        <p class="mb-0">All payment information is encrypted and securely processed. We do not store your card details.</p>
                    </div>
                </div>
                
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h4 class="mb-0">Order Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h5>E-Gift Details</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Recipient</td>
                                        <td class="text-end"><?php echo isset($recipient_name) ? $recipient_name : 'Unknown'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Amount</td>
                                        <td class="text-end text-success fw-bold">$<?php echo isset($gift_data['amount']) ? number_format($gift_data['amount'], 2) : '0.00'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Delivery Date</td>
                                        <td class="text-end"><?php echo isset($gift_data['delivery_date']) ? date('F j, Y', strtotime($gift_data['delivery_date'])) : 'Today'; ?></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6>Message</h6>
                                    <p class="fst-italic mb-0"><?php echo isset($gift_data['message']) && !empty($gift_data['message']) ? htmlspecialchars($gift_data['message']) : 'No message provided'; ?></p>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total</span>
                                <span class="text-success">$<?php echo isset($gift_data['amount']) ? number_format($gift_data['amount'], 2) : '0.00'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Format the credit card number with spaces
    document.getElementById('card_number').addEventListener('input', function (e) {
        const input = e.target;
        let value = input.value.replace(/\s+/g, '');
        if (value.length > 0) {
            value = value.match(new RegExp('.{1,4}', 'g')).join(' ');
        }
        input.value = value;
    });
    
    // Format the expiry date with a slash
    document.getElementById('expiry_date').addEventListener('input', function (e) {
        const input = e.target;
        let value = input.value.replace(/\D/g, '');
        
        if (value.length > 2) {
            value = value.slice(0, 2) + '/' + value.slice(2, 4);
        }
        
        input.value = value;
    });
</script>

<?php require_once 'views/layout_footer.php'; ?> 