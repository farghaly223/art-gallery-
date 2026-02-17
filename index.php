<?php
// Main entry point for our Art Gallery application

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session
session_start();

// Include the config file
require_once 'config/config.php';

// Try to initialize the database and check if it works
try {
    // Get database instance
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed: " . $db->getError());
    }
    
    // Check if admin users exist
    $adminExistsCheck = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    if ($adminExistsCheck && $adminExistsCheck->num_rows > 0) {
        $adminRow = $adminExistsCheck->fetch_assoc();
        if ($adminRow['count'] == 0) {
            // No admin users exist, show setup notice
            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Art Gallery - Admin Setup Required</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                    .setup-container { max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #27ae60; border-radius: 5px; background-color: #d5f5e3; }
                    h1 { color: #27ae60; }
                    .btn { display: inline-block; background: #27ae60; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin-top: 15px; }
                    .btn:hover { background: #219653; }
                </style>
            </head>
            <body>
                <div class="setup-container">
                    <h1>Admin Setup Required</h1>
                    <p>No administrator accounts have been created yet. You need to set up an admin account to use the system.</p>
                    <p>Click the button below to create the first admin account:</p>
                    <a href="admin_first_setup.php" class="btn">Create First Admin Account</a>
                </div>
            </body>
            </html>';
            exit;
        }
    }
    
    // Simple routing
    $controller = isset($_GET['controller']) ? $_GET['controller'] : 'auth';
    $action = isset($_GET['action']) ? $_GET['action'] : 'login';
    
    // Load the controller
    $controllerName = ucfirst($controller) . 'Controller';
    $controllerFile = 'controllers/' . $controllerName . '.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        
        // Check if the class exists
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            
            // Check if the action method exists
            if (method_exists($controller, $action)) {
                $controller->$action();
            } else {
                // Action not found, use default action
                if (method_exists($controller, 'index')) {
                    $controller->index();
                } else {
                    throw new Exception("Action not found: $action");
                }
            }
        } else {
            throw new Exception("Controller class not found: $controllerName");
        }
    } else {
        // If no controller found, redirect to auth controller
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
} catch (Exception $e) {
    // Display user-friendly error message
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Art Gallery - Error</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
            .error-container { max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #e74c3c; border-radius: 5px; }
            h1 { color: #e74c3c; }
            .btn { display: inline-block; background: #3498db; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>Application Error</h1>
            <p>There was a problem with the application:</p>
            <p><strong>' . $e->getMessage() . '</strong></p>
            <p>Please try the following:</p>
            <ul>
                <li>Make sure XAMPP\'s Apache and MySQL services are running</li>
                <li>Visit the <a href="setup_database.php">database setup page</a> to initialize the database</li>
                <li>Try refreshing this page</li>
            </ul>
            <p><a href="index.php" class="btn">Retry</a></p>
        </div>
    </body>
    </html>';
}
?> 