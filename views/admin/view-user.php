<div class="user-details">
    <h2>User Details</h2>
    
    <div style="display: flex; gap: 20px; margin-bottom: 30px;">
        <div style="flex: 1;">
            <table class="data-table">
                <tr>
                    <th>ID</th>
                    <td><?php echo $user['id']; ?></td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td><?php echo $user['name']; ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo $user['email']; ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td><?php echo ucfirst($user['role']); ?></td>
                </tr>
                <tr>
                    <th>Date Joined</th>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <?php if ($isBanned): ?>
                            <span style="color: #e74c3c; font-weight: bold;">Banned</span>
                        <?php else: ?>
                            <span style="color: #2ecc71;">Active</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($isBanned && isset($banInfo)): ?>
                <tr>
                    <th>Ban Reason</th>
                    <td><?php echo $banInfo['reason']; ?></td>
                </tr>
                <tr>
                    <th>Banned By</th>
                    <td><?php echo $banInfo['banned_by_name']; ?></td>
                </tr>
                <tr>
                    <th>Ban Date</th>
                    <td><?php echo date('M d, Y', strtotime($banInfo['ban_date'])); ?></td>
                </tr>
                <tr>
                    <th>Ban Type</th>
                    <td>
                        <?php if ($banInfo['is_permanent']): ?>
                            Permanent
                        <?php else: ?>
                            Temporary (Until <?php echo date('M d, Y', strtotime($banInfo['unban_date'])); ?>)
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <div style="flex: 1;">
            <?php if (isset($user['profile_image']) && !empty($user['profile_image'])): ?>
                <img src="<?php echo BASE_URL . $user['profile_image']; ?>" alt="<?php echo $user['name']; ?>" style="max-width: 100%; border-radius: 4px;">
            <?php else: ?>
                <div style="background-color: #f5f5f5; height: 300px; display: flex; justify-content: center; align-items: center; border-radius: 4px;">
                    <p>No profile image</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($user['role'] != 'admin'): ?>
    <div style="margin-bottom: 30px;">
        <h3>Actions</h3>
        
        <?php if ($isBanned): ?>
            <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=unbanUser&id=<?php echo $user['id']; ?>" class="btn btn-success" onclick="return confirm('Are you sure you want to unban this user?');">Unban User</a>
        <?php else: ?>
            <button class="btn btn-danger" onclick="document.getElementById('banForm').style.display = 'block';">Ban User</button>
        <?php endif; ?>
        
        <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=makeAdmin&id=<?php echo $user['id']; ?>" class="btn btn-primary" onclick="return confirm('Are you sure you want to make this user an admin?');">Make Admin</a>
        
        <a href="<?php echo BASE_URL; ?>index.php?controller=admin&action=deleteUser&id=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user? This cannot be undone.');">Delete User</a>
    </div>
    
    <!-- Ban User Form (Hidden by default) -->
    <div id="banForm" style="display: none; margin-bottom: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 4px;">
        <h3>Ban User</h3>
        
        <form action="<?php echo BASE_URL; ?>index.php?controller=admin&action=banUser" method="post">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div class="form-group">
                <label for="reason">Reason for ban:</label>
                <textarea name="reason" id="reason" class="form-control" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Ban Type:</label>
                <div>
                    <label>
                        <input type="radio" name="ban_type" value="permanent" checked> Permanent
                    </label>
                    <label style="margin-left: 20px;">
                        <input type="radio" name="ban_type" value="temporary"> Temporary
                    </label>
                </div>
            </div>
            
            <div class="form-group" id="banDaysGroup" style="display: none;">
                <label for="ban_days">Ban Duration (days):</label>
                <input type="number" name="ban_days" id="ban_days" class="form-control" min="1" value="30">
            </div>
            
            <button type="submit" class="btn btn-danger">Ban User</button>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('banForm').style.display = 'none';">Cancel</button>
        </form>
    </div>
    
    <script>
        // Show/hide ban days input based on ban type
        const banTypeRadios = document.querySelectorAll('input[name="ban_type"]');
        const banDaysGroup = document.getElementById('banDaysGroup');
        
        banTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'temporary') {
                    banDaysGroup.style.display = 'block';
                } else {
                    banDaysGroup.style.display = 'none';
                }
            });
        });
    </script>
    <?php endif; ?>
    
    <?php if ($user['role'] == 'artist'): ?>
    <h3>User's Artworks</h3>
    
    <?php if (isset($artworks) && $artworks->num_rows > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title</th>
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
        <p>This user has no artworks.</p>
    <?php endif; ?>
    <?php endif; ?>
</div> 