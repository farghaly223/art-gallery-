<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Gallery - Login</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-form">
            <h1>Login to Art Gallery</h1>
            
            <?php if(isset($errors) && !empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach($errors as $error): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo BASE_URL; ?>index.php?controller=auth&action=doLogin" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-primary">Login</button>
                </div>
                
                <p class="form-footer">
                    Don't have an account? <a href="<?php echo BASE_URL; ?>index.php?controller=auth&action=register">Register</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html> 