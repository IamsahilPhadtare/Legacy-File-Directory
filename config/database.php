<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'lfd_system');

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
if(!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if(!mysqli_query($conn, $sql)) {
    die("Error creating database: " . mysqli_error($conn));
}

mysqli_select_db($conn, DB_NAME);

// Basic users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql);

// Add admin column to users table
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_admin BOOLEAN DEFAULT FALSE";
mysqli_query($conn, $sql);

// Add clearance_level column to users table
$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS clearance_level ENUM('read', 'upload', 'edit') DEFAULT 'read'";
mysqli_query($conn, $sql);

// Add default admin user
$admin_username = "admin";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (username, password, is_admin) VALUES (?, ?, 1)";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ss", $admin_username, $admin_password);
    mysqli_stmt_execute($stmt);
}

// Update default admin user to have 'edit' clearance
$sql = "UPDATE users SET clearance_level = 'edit' WHERE is_admin = 1";
mysqli_query($conn, $sql);

// Basic files table
$sql = "CREATE TABLE IF NOT EXISTS files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(255) NOT NULL,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
)";
mysqli_query($conn, $sql);
?>
