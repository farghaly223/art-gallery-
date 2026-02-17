<div class="filters" style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=reports" class="btn <?php echo !isset($currentStatus) ? 'btn-primary' : 'btn-default'; ?>" style="margin-right: 10px;">All</a>
    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=reports&status=pending" class="btn <?php echo isset($currentStatus) && $currentStatus == 'pending' ? 'btn-primary' : 'btn-default'; ?>" style="margin-right: 10px;">Pending</a>
    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=reports&status=resolved" class="btn <?php echo isset($currentStatus) && $currentStatus == 'resolved' ? 'btn-primary' : 'btn-default'; ?>" style="margin-right: 10px;">Resolved</a>
    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=reports&status=rejected" class="btn <?php echo isset($currentStatus) && $currentStatus == 'rejected' ? 'btn-primary' : 'btn-default'; ?>">Rejected</a>
</div>

<h2>
    <?php
    if (isset($currentStatus)) {
        echo ucfirst($currentStatus) . ' Reports';
    } else {
        echo 'All Reports';
    }
    ?>
</h2>

<?php if (isset($reports) && $reports->num_rows > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Reported By</th>
                <th>Reported User/Artwork</th>
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
                    <td><?php echo $report['reporter_name']; ?></td>
                    <td>
                        <?php 
                        if ($report['report_type'] == 'user') {
                            echo $report['reported_user_name'];
                        } else if ($report['report_type'] == 'artwork') {
                            echo $report['artwork_title'];
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $statusColor = '';
                        switch ($report['status']) {
                            case 'pending':
                                $statusColor = '#f39c12';
                                break;
                            case 'resolved':
                                $statusColor = '#2ecc71';
                                break;
                            case 'rejected':
                                $statusColor = '#e74c3c';
                                break;
                        }
                        ?>
                        <span style="color: <?php echo $statusColor; ?>; font-weight: bold;">
                            <?php echo ucfirst($report['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=viewReport&id=<?php echo $report['id']; ?>" class="btn btn-primary">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No reports found.</p>
<?php endif; ?> 