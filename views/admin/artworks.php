<div class="actions" style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=addArtwork" class="btn btn-success">Add New Artwork</a>
</div>

<h2>All Artworks</h2>

<?php if (isset($artworks) && $artworks->num_rows > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Title</th>
                <th>Artist</th>
                <th>Price</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($artwork = $artworks->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $artwork['id']; ?></td>
                    <td>
                        <img src="<?php echo BASE_URL . $artwork['image_path']; ?>" alt="<?php echo $artwork['title']; ?>" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                    </td>
                    <td><?php echo $artwork['title']; ?></td>
                    <td><?php echo $artwork['artist_name']; ?></td>
                    <td>$<?php echo number_format($artwork['price'], 2); ?></td>
                    <td><?php echo ucfirst($artwork['status']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($artwork['created_at'])); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=deleteArtwork&id=<?php echo $artwork['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this artwork?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No artworks found.</p>
<?php endif; ?> 