<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";

// Verify admin is logged in
check_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $new_clearance = intval($_POST['new_clearance']);
    $admin_password = $_POST['admin_password'];

    // Verify admin password
    $admin_id = $_SESSION['id'];
    $sql = "SELECT password FROM users WHERE id = ? AND is_admin = 1";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $admin_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $hashed_password);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (password_verify($admin_password, $hashed_password)) {
            // Update user's clearance level
            $update_sql = "UPDATE users SET clearance_level = ? WHERE id = ? AND is_admin = 0";
            if ($update_stmt = mysqli_prepare($conn, $update_sql)) {
                mysqli_stmt_bind_param($update_stmt, "ii", $new_clearance, $user_id);
                if (mysqli_stmt_execute($update_stmt)) {
                    header("Location: admin_panel.php?success=clearance_updated");
                    exit;
                }
            }
        } else {
            header("Location: admin_panel.php?error=invalid_admin_password");
            exit;
        }
    }
}

header("Location: admin_panel.php?error=update_failed");
exit;
