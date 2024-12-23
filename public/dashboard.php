<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";
check_login();

if($_SESSION["is_admin"]) {
    $totalUsers = get_total_users();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>LFD - Dashboard</title>
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
    <div class="dashboard-container">
        <nav>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>
                  <?php echo $_SESSION["is_admin"] ? " (Administrator)" : ""; ?></span>
            <div>
                <?php if($_SESSION["is_admin"]): ?>
                    <a href="admin_panel.php">User Management</a>
                    <a href="file_management.php">File Management</a>
                <?php else: ?>
                    <a href="dashboard.php">Dashboard</a>
                <?php endif; ?>
                <a href="../auth/logout.php">Logout</a>
            </div>
        </nav>
        <?php if($_SESSION["is_admin"]): ?>
            <div class="admin-info">
                <p>Total Users: <?php echo $totalUsers; ?></p>
            </div>
        <?php endif; ?>
        
        <?php if(!$_SESSION["is_admin"]): ?>
            <div class="file-upload-section">
                <h3>Upload File</h3>
                <form action="../includes/upload.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="fileToUpload" required>
                    <input type="submit" value="Upload File" class="btn btn-primary">
                </form>
            </div>
        <?php endif; ?>
        
        <div class="file-list-section">
            <?php include "../includes/list_files.php"; ?>
        </div>
    </div>
</body>
</html>
