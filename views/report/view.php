<?php require_once 'views/layout_header.php'; ?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3>Report Details</h3>
            <a href="<?php echo BASE_URL; ?>index.php?controller=report&action=myReports" class="btn btn-light">Back to My Reports</a>
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
                <div class="col-md-6">
                    <h4>Report Information</h4>
                    <table class="table">
                        <tr>
                            <th>Report ID:</th>
                            <td><?php echo $report['id']; ?></td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td><?php echo ucfirst($report['report_type']); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
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
                        </tr>
                        <tr>
                            <th>Date Submitted:</th>
                            <td><?php echo date('M d, Y h:i A', strtotime($report['created_at'])); ?></td>
                        </tr>
                        <?php if (isset($report['updated_at']) && $report['updated_at']): ?>
                        <tr>
                            <th>Last Updated:</th>
                            <td><?php echo date('M d, Y h:i A', strtotime($report['updated_at'])); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <?php if ($report['report_type'] === 'user' && isset($report['reported_user_name'])): ?>
                        <h4>Reported User</h4>
                        <table class="table">
                            <tr>
                                <th>Name:</th>
                                <td><?php echo $report['reported_user_name']; ?></td>
                            </tr>
                        </table>
                    <?php elseif ($report['report_type'] === 'artwork' && isset($report['artwork_title'])): ?>
                        <h4>Reported Artwork</h4>
                        <table class="table">
                            <tr>
                                <th>Title:</th>
                                <td><?php echo $report['artwork_title']; ?></td>
                            </tr>
                        </table>
                        <?php if (isset($report['artwork_image'])): ?>
                            <img src="<?php echo BASE_URL . $report['artwork_image']; ?>" alt="<?php echo $report['artwork_title']; ?>" class="img-thumbnail mb-3" style="max-height: 200px;">
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-4">
                <h4>Reason for Report</h4>
                <div class="p-3 bg-light rounded">
                    <?php echo nl2br(htmlspecialchars($report['reason'])); ?>
                </div>
            </div>
            
            <?php if (isset($report['admin_notes']) && $report['admin_notes'] && $report['status'] !== 'pending'): ?>
            <div class="mt-4">
                <h4>Administrator Response</h4>
                <div class="p-3 bg-light rounded">
                    <?php echo nl2br(htmlspecialchars($report['admin_notes'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'views/layout_footer.php'; ?> 