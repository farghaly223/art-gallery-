<!-- Dashboard Stats -->
<div class="stats-container">
    <div class="stat-card">
        <h3>Total Users</h3>
        <div class="value"><?php echo $stats['total_users']; ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Artists</h3>
        <div class="value"><?php echo $stats['total_artists']; ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Total Artworks</h3>
        <div class="value"><?php echo $stats['total_artworks']; ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Sold Artworks</h3>
        <div class="value"><?php echo $stats['sold_artworks']; ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Pending Reports</h3>
        <div class="value"><?php echo $stats['pending_reports']; ?></div>
    </div>
    
    <div class="stat-card">
        <h3>Banned Users</h3>
        <div class="value"><?php echo $stats['banned_users']; ?></div>
    </div>
</div>

<!-- Recent Reports -->
<h2>Recent Reports</h2>

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
                    <td><?php echo ucfirst($report['status']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=viewReport&id=<?php echo $report['id']; ?>" class="btn btn-primary">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=reports" class="btn btn-primary">View All Reports</a>
<?php else: ?>
    <p>No pending reports.</p>
<?php endif; ?>

<!-- Quick Links -->
<h2>Quick Links</h2>
<div style="display: flex; gap: 15px;">
    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=users" class="btn btn-primary">Manage Users</a>
    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=artworks" class="btn btn-primary">Manage Artworks</a>
    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=addArtwork" class="btn btn-success">Add Artwork</a>
</div> 