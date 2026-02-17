<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Art Gallery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/styles.css">
</head>
<body class="auth-container">
    <div class="auth-panel">
        <div class="auth-logo">Art Gallery Admin</div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($adminExists) && !$adminExists): ?>
        <div class="alert alert-success">
            <p><strong>No admin accounts found!</strong></p>
            <p>You need to create the first admin account to use the system.</p>
            <a href="<?php echo BASE_URL; ?>admin_first_setup.php" class="btn btn-success">Create First Admin</a>
        </div>
        <?php endif; ?>
        
        <div class="auth-form">
            <h2>Admin Login</h2>
            <form action="<?php echo BASE_URL; ?>index.php?controller=admin&action=login" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
        
        <?php if (!isset($adminExists) || !$adminExists): ?>
        <div class="auth-links">
            <a href="<?php echo BASE_URL; ?>admin_first_setup.php">First Time Setup - Create Admin Account</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 