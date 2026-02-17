<h2>All Users</h2>

<?php if (isset($users) && $users->num_rows > 0): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Artworks</th>
                <th>Status</th>
                <th>Date Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td><?php echo $user['artwork_count']; ?></td>
                    <td>
                        <?php if ($user['is_banned'] > 0): ?>
                            <span style="color: #e74c3c; font-weight: bold;">Banned</span>
                        <?php else: ?>
                            <span style="color: #2ecc71;">Active</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=viewUser&id=<?php echo $user['id']; ?>" class="btn btn-primary">View</a>
                        
                        <?php if ($user['role'] != 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=makeAdmin&id=<?php echo $user['id']; ?>" class="btn btn-success" onclick="return confirm('Are you sure you want to make this user an admin?');">Make Admin</a>
                            
                            <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=deleteUser&id=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user? This cannot be undone.');">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No users found.</p>
<?php endif; ?> 