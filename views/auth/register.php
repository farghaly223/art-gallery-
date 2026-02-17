<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Gallery - Register</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <h1>Register for Art Gallery</h1>
            
            <?php if(isset($errors) && !empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach($errors as $error): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo BASE_URL; ?>index.php?controller=auth&action=doRegister" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Password must be at least 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <label>Account Type</label>
                    <div class="role-selection">
                        <label class="role-option">
                            <input type="radio" name="role" value="viewer" <?php echo (!isset($role) || $role == 'viewer') ? 'checked' : ''; ?>>
                            <div class="role-content">
                                <h3>Viewer</h3>
                                <p>Browse and purchase artwork</p>
                            </div>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="artist" <?php echo (isset($role) && $role == 'artist') ? 'checked' : ''; ?>>
                            <div class="role-content">
                                <h3>Artist</h3>
                                <p>Upload and sell your artwork</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-primary">Register</button>
                </div>
                
                <p class="form-footer">
                    Already have an account? <a href="<?php echo BASE_URL; ?>index.php?controller=auth&action=login">Login</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html> 