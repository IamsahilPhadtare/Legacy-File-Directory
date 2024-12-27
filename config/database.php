<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'lfd_system');

// Create connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
if(!$conn) die("Connection failed: " . mysqli_connect_error());

// Create database if not exists
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS " . DB_NAME);
mysqli_select_db($conn, DB_NAME);

// Essential database structure setup
$tables = [
    // Users table
    "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_admin BOOLEAN DEFAULT FALSE,
        clearance_level ENUM('read', 'upload', 'edit') DEFAULT 'read',
        last_login TIMESTAMP NULL DEFAULT NULL,
        last_activity TIMESTAMP(3) NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Files table
    "CREATE TABLE IF NOT EXISTS files (
        id INT PRIMARY KEY AUTO_INCREMENT,
        filename VARCHAR(255) NOT NULL,
        filepath VARCHAR(255) NOT NULL,
        uploaded_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (uploaded_by) REFERENCES users(id)
    )",
    
    // Create activity index
    "CREATE INDEX IF NOT EXISTS idx_last_activity ON users(last_activity)"
];

// Execute table creation
foreach($tables as $sql) {
    mysqli_query($conn, $sql);
}

// Add composite index for better performance on activity queries
$sql = "CREATE INDEX IF NOT EXISTS idx_activity_composite 
        ON users(last_activity, is_admin, username)";
mysqli_query($conn, $sql);

// Create user_activity table
$sql = "CREATE TABLE IF NOT EXISTS user_activity (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    last_activity TIMESTAMP(3) NULL DEFAULT NULL,
    UNIQUE KEY unique_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
mysqli_query($conn, $sql);

// Add index for performance
$sql = "CREATE INDEX IF NOT EXISTS idx_user_activity 
        ON user_activity(last_activity)";
mysqli_query($conn, $sql);

// Create default admin if not exists
$admin_username = "admin";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (username, password, is_admin, clearance_level) VALUES (?, ?, 1, 'edit')";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ss", $admin_username, $admin_password);
    mysqli_stmt_execute($stmt);
}
?>
