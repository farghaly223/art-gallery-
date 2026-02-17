<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h2>Buy E-Gift Card</h2>
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
                    <h4>Purchase an E-Gift Card</h4>
                    <p>E-Gift cards are perfect for giving the gift of art to your friends and family. Recipients can use them to purchase any artwork on our platform.</p>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i> You'll be able to send this gift immediately or schedule it for a future date.
                    </div>
                    
                    <form action="<?php echo BASE_URL; ?>index.php?controller=gift&action=processPayment" method="POST" class="gift-form mt-4">
                        <div class="mb-3">
                            <label for="recipient_id" class="form-label">Select Recipient</label>
                            <select id="recipient_id" name="recipient_id" class="form-select" required>
                                <option value="">-- Select a recipient --</option>
                                <?php if(isset($users) && $users->num_rows > 0): ?>
                                    <?php while($user = $users->fetch_assoc()): ?>
                                        <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?> (<?php echo ucfirst($user['role']); ?>)</option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="amount" class="form-label">Gift Amount ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="amount" name="amount" class="form-control" min="10" step="5" value="25" required>
                            </div>
                            <div class="form-text">Minimum amount: $10. Suggested amounts: $25, $50, $100</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="delivery_date" class="form-label">Delivery Date</label>
                            <input type="date" id="delivery_date" name="delivery_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" required>
                            <div class="form-text">The e-gift will be delivered on this date. Select today for immediate delivery.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label">Personal Message (optional)</label>
                            <textarea id="message" name="message" class="form-control" rows="4" placeholder="Enter your personal message here..."></textarea>
                            <div class="form-text">Add a personal message to make your gift more meaningful.</div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn-buy-now">
                                <i class="fas fa-shopping-cart me-2"></i> Proceed to Payment - $<span id="display-amount">25</span>
                            </button>
                            <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=index" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="col-md-5">
                    <div class="card bg-light h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">E-Gift Preview</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <i class="fas fa-gift fa-4x text-success mb-3"></i>
                                <h4>Art Gallery E-Gift Card</h4>
                                <div class="display-6 text-success my-3">$<span id="preview-amount">25</span></div>
                                <p class="text-muted">For: <span id="preview-recipient">Select a recipient</span></p>
                                <p class="fst-italic mt-3" id="preview-message">Your message will appear here...</p>
                            </div>
                            
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i> E-Gift cards never expire and can be used on any artwork in our gallery!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Update the displayed amount and preview when the amount input changes
    document.getElementById('amount').addEventListener('input', function() {
        const amount = this.value;
        document.getElementById('display-amount').textContent = amount;
        document.getElementById('preview-amount').textContent = amount;
    });
    
    // Update the preview recipient when selection changes
    document.getElementById('recipient_id').addEventListener('change', function() {
        const select = this;
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption.value) {
            document.getElementById('preview-recipient').textContent = selectedOption.text;
        } else {
            document.getElementById('preview-recipient').textContent = 'Select a recipient';
        }
    });
    
    // Update the preview message when the message input changes
    document.getElementById('message').addEventListener('input', function() {
        const message = this.value.trim();
        document.getElementById('preview-message').textContent = message || 'Your message will appear here...';
    });
</script>

<?php require_once 'views/layout_footer.php'; ?> 