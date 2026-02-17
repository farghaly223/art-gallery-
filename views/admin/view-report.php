<div class="report-details">
    <h2>Report Details</h2>
    
    <div style="display: flex; gap: 20px; margin-bottom: 30px;">
        <div style="flex: 2;">
            <table class="data-table">
                <tr>
                    <th>Report ID</th>
                    <td><?php echo $report['id']; ?></td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td><?php echo ucfirst($report['report_type']); ?></td>
                </tr>
                <tr>
                    <th>Status</th>
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
                </tr>
                <tr>
                    <th>Reported By</th>
                    <td>
                        <?php echo $report['reporter_name']; ?> (<?php echo $report['reporter_email']; ?>)
                        <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=viewUser&id=<?php echo $report['reporter_id']; ?>" class="btn btn-primary btn-sm" style="margin-left: 10px;">View User</a>
                    </td>
                </tr>
                
                <?php if ($report['report_type'] == 'user' && $report['reported_user_id']): ?>
                <tr>
                    <th>Reported User</th>
                    <td>
                        <?php echo $report['reported_user_name']; ?> (<?php echo $report['reported_user_email']; ?>)
                        <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=viewUser&id=<?php echo $report['reported_user_id']; ?>" class="btn btn-primary btn-sm" style="margin-left: 10px;">View User</a>
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php if ($report['report_type'] == 'artwork' && $report['artwork_id']): ?>
                <tr>
                    <th>Reported Artwork</th>
                    <td>
                        <?php echo $report['artwork_title']; ?>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th>Date Reported</th>
                    <td><?php echo date('M d, Y h:i A', strtotime($report['created_at'])); ?></td>
                </tr>
                
                <?php if ($report['updated_at']): ?>
                <tr>
                    <th>Last Updated</th>
                    <td><?php echo date('M d, Y h:i A', strtotime($report['updated_at'])); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <?php if ($report['report_type'] == 'artwork' && $report['artwork_image']): ?>
        <div style="flex: 1;">
            <div style="background-color: #f5f5f5; padding: 10px; border-radius: 4px;">
                <h3>Reported Artwork Image</h3>
                <img src="<?php echo BASE_URL . $report['artwork_image']; ?>" alt="<?php echo $report['artwork_title']; ?>" style="max-width: 100%; border-radius: 4px;">
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div style="margin-bottom: 30px;">
        <h3>Report Reason</h3>
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px;">
            <?php echo nl2br(htmlspecialchars($report['reason'])); ?>
        </div>
    </div>
    
    <?php if ($report['admin_notes']): ?>
    <div style="margin-bottom: 30px;">
        <h3>Admin Notes</h3>
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px;">
            <?php echo nl2br(htmlspecialchars($report['admin_notes'])); ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($report['status'] == 'pending'): ?>
    <div style="margin-bottom: 30px;">
        <h3>Actions</h3>
        
        <form action="<?php echo BASE_URL; ?>index.php?controller=admin&action=updateReport" method="post">
            <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
            
            <div class="form-group">
                <label for="admin_notes">Admin Notes:</label>
                <textarea name="admin_notes" id="admin_notes" class="form-control" rows="5"><?php echo isset($report['admin_notes']) ? $report['admin_notes'] : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" name="status" value="resolved" class="btn btn-success">Mark as Resolved</button>
                <button type="submit" name="status" value="rejected" class="btn btn-danger">Reject Report</button>
                <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=reports" class="btn btn-primary">Back to Reports</a>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div>
        <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=reports" class="btn btn-primary">Back to Reports</a>
    </div>
    <?php endif; ?>
</div> 