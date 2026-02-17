<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3>Submit Report</h3>
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
            
            <form action="<?php echo BASE_URL; ?>index.php?controller=report&action=submit" method="post">
                <input type="hidden" name="report_type" value="<?php echo $report_type; ?>">
                
                <?php if ($report_type === 'user' && isset($item)): ?>
                    <input type="hidden" name="user_id" value="<?php echo $item['id']; ?>">
                    <div class="mb-3">
                        <h5>Reporting User: <?php echo $item['name']; ?></h5>
                        <p>Email: <?php echo $item['email']; ?></p>
                        <p>Role: <?php echo ucfirst($item['role']); ?></p>
                    </div>
                <?php elseif ($report_type === 'artwork' && isset($item)): ?>
                    <input type="hidden" name="artwork_id" value="<?php echo $item['id']; ?>">
                    <div class="mb-3">
                        <h5>Reporting Artwork: <?php echo $item['title']; ?></h5>
                        <div class="row">
                            <div class="col-md-4">
                                <img src="<?php echo BASE_URL . $item['image_path']; ?>" alt="<?php echo $item['title']; ?>" class="img-thumbnail">
                            </div>
                            <div class="col-md-8">
                                <p><strong>Price:</strong> $<?php echo number_format($item['price'], 2); ?></p>
                                <?php if (isset($item['description'])): ?>
                                    <p><strong>Description:</strong> <?php echo $item['description']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php elseif ($report_type === 'other'): ?>
                    <div class="mb-3">
                        <h5>Submit a General Report</h5>
                        <p>Please provide details about the issue you'd like to report.</p>
                    </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason for Report *</label>
                    <textarea name="reason" id="reason" class="form-control" rows="5" required></textarea>
                    <div class="form-text">Please provide as much detail as possible to help administrators understand and address the issue.</div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?php echo isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL; ?>" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'views/layout_footer.php'; ?> 