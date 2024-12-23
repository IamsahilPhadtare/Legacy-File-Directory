<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";
check_login();

if (!$_SESSION["is_admin"]) {
    // Redirect non-admin users
    header("Location: dashboard.php");
    exit();
}

// Redirect to admin_panel.php
header("Location: admin_panel.php");
exit();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - LFD</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        // Removed the unload event listener to prevent unwanted logout
        /*
        window.addEventListener('unload', function () {
            navigator.sendBeacon('../auth/logout.php');
        });
        */
    </script>
</head>
<body>
    <!-- ...existing HTML content... -->
</body>
</html>
