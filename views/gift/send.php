<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2>Send an E-Gift</h2>
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
            
            <form action="<?php echo BASE_URL; ?>index.php?controller=gift&action=process" method="POST" class="gift-form">
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
                        <input type="number" id="amount" name="amount" class="form-control" min="1" step="0.01" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="message" class="form-label">Personal Message (optional)</label>
                    <textarea id="message" name="message" class="form-control" rows="4"></textarea>
                    <div class="form-text">Add a personal message to make your gift more meaningful.</div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?php echo BASE_URL; ?>index.php?controller=gallery&action=index" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn-egift">Continue to Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'views/layout_footer.php'; ?> 