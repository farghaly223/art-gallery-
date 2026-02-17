<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3>My Reports</h3>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
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
            
            <div class="mb-3">
                <a href="<?php echo BASE_URL; ?>index.php?controller=report&action=index&type=other" class="btn btn-primary">Submit New Report</a>
            </div>
            
            <?php if (isset($reports) && $reports->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Reported Item</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($report = $reports->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $report['id']; ?></td>
                                    <td><?php echo ucfirst($report['report_type']); ?></td>
                                    <td>
                                        <?php if ($report['report_type'] === 'user' && isset($report['reported_user_name'])): ?>
                                            <?php echo $report['reported_user_name']; ?>
                                        <?php elseif ($report['report_type'] === 'artwork' && isset($report['artwork_title'])): ?>
                                            <?php echo $report['artwork_title']; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusClass = '';
                                        switch ($report['status']) {
                                            case 'pending':
                                                $statusClass = 'text-warning fw-bold';
                                                break;
                                            case 'resolved':
                                                $statusClass = 'text-success fw-bold';
                                                break;
                                            case 'rejected':
                                                $statusClass = 'text-danger fw-bold';
                                                break;
                                        }
                                        ?>
                                        <span class="<?php echo $statusClass; ?>">
                                            <?php echo ucfirst($report['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>index.php?controller=report&action=viewReport&id=<?php echo $report['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p>You haven't submitted any reports yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'views/layout_footer.php'; ?> 