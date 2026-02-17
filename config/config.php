<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'art_gallery');

// URL configuration
define('BASE_URL', 'http://localhost/project%20se/');

// Check for required files before including them
$models_dir = dirname(__DIR__) . '/models/';
$controllers_dir = dirname(__DIR__) . '/controllers/';

// Include the database connection class - this must exist
require_once $models_dir . 'Database.php';

// Include the base model and controller if they exist
if (file_exists($models_dir . 'BaseModel.php')) {
    require_once $models_dir . 'BaseModel.php';
}

if (file_exists($controllers_dir . 'BaseController.php')) {
    require_once $controllers_dir . 'BaseController.php';
}
?> 