# Simple Art Gallery Web Application

A basic art gallery web application built using PHP with MVC architecture. This application allows users to register, login, and view artwork.

## Features

- User registration and login system
- Basic MVC architecture
- OOP principles
- XAMPP integration
- Responsive design

## Requirements

- XAMPP (PHP 7.0+ and MySQL)
- Web browser

## Installation

1. Clone or download this repository to your XAMPP htdocs folder (e.g., `C:\xampp\htdocs\project se`).
2. Start the Apache and MySQL services in XAMPP.
3. Navigate to `http://localhost/project%20se/setup_database.php` in your web browser to set up the database.
4. After the database setup is complete, visit `http://localhost/project%20se/` to start using the application.

## Structure

The application follows the MVC (Model-View-Controller) architecture:

- **Models**: Handle database operations and business logic
- **Views**: Display the user interface
- **Controllers**: Process requests and coordinate between models and views

### Directory Structure

```
project_se/
│
├── config/
│   └── config.php       # Configuration settings
│
├── controllers/
│   ├── AuthController.php     # Handles authentication
│   ├── BaseController.php     # Base controller class
│   └── GalleryController.php  # Handles gallery features
│
├── models/
│   ├── BaseModel.php    # Base model class
│   ├── Database.php     # Database connection
│   └── User.php         # User model
│
├── public/
│   └── css/
│       └── style.css    # Main stylesheet
│
├── views/
│   ├── auth/
│   │   ├── login.php    # Login form
│   │   └── register.php # Registration form
│   │
│   └── gallery/
│       └── index.php    # Gallery homepage
│
├── index.php            # Application entry point
├── setup_database.php   # Database setup script
└── README.md            # This file
```

## Usage

1. Register a new account
2. Login with your credentials
3. Browse the gallery (this will be expanded in future versions)

## Next Steps

- Add the ability to upload artwork
- Implement artwork categories and tags
- Add user profiles
- Add commenting and favoriting functionalities

## License

This project is open-source and free to use for educational purposes. 