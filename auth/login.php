<?php
session_start();
require_once "../config/database.php";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_type = $_POST["login_type"];
    
    if($login_type == "admin") {
        $username = mysqli_real_escape_string($conn, $_POST["admin_username"]);
        $password = $_POST["admin_password"];
        
        // Specifically check for admin users
        $sql = "SELECT id, username, password FROM users WHERE username = ? AND is_admin = 1";
    } else {
        $username = mysqli_real_escape_string($conn, $_POST["username"]);
        $password = $_POST["password"];
        
        // Regular user authentication
        $sql = "SELECT id, username, password FROM users WHERE username = ? AND is_admin = 0";
    }
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                if(mysqli_stmt_fetch($stmt)) {
                    if(password_verify($password, $hashed_password)) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;
                        $_SESSION["is_admin"] = ($login_type == "admin") ? 1 : 0;
                        
                        // Update last_login and last_activity timestamps
                        $update_sql = "UPDATE users SET last_login = NOW(), last_activity = NOW() WHERE id = ?";
                        if($update_stmt = mysqli_prepare($conn, $update_sql)) {
                            mysqli_stmt_bind_param($update_stmt, "i", $id);
                            mysqli_stmt_execute($update_stmt);
                            mysqli_stmt_close($update_stmt);
                        }

                        header("location: ../public/dashboard.php");
                        exit;
                    }
                }
            }
            
            // Authentication failed
            if($login_type == "admin") {
                header("location: ../index.php?error=invalid_admin");
            } else {
                header("location: ../index.php?error=invalid");
            }
            exit;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conn);
}
?>
