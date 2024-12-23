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

// Handle file-related actions here (e.g., deleting files)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $fileId = intval($_POST['file_id']);
    if(delete_file($fileId)){
        header("Location: file_management.php?delete=success");
        exit();
    } else {
        header("Location: file_management.php?delete=fail");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>File Management - LFD</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        // JavaScript to handle automatic logout on tab/window close
        window.addEventListener('unload', function () {
            navigator.sendBeacon('../auth/logout.php');
        });
    </script>
</head>
<body>
    <div class="admin-panel-container">
        <h1 class="admin-title">File Management</h1>
        
        <!-- Add file management functionalities here -->
        <div class="manage-files-section">
            <h2>Manage Files</h2>
            <!-- Example: List files with options to delete -->
            <table class="admin-user-table">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Uploaded By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Example query to fetch files
                    $sql = "SELECT id, filename, uploaded_by FROM files";
                    $result = mysqli_query($conn, $sql);
                    
                    while($file = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($file['filename']) . "</td>";
                        echo "<td>" . htmlspecialchars($file['uploaded_by']) . "</td>";
                        echo "<td>
                                <a href='../uploads/" . htmlspecialchars($file['filename']) . "' target='_blank'>View</a>
                                <form method='post' action='file_management.php' onsubmit=\"return confirm('Are you sure you want to delete this file?');\">
                                    <input type='hidden' name='file_id' value='" . htmlspecialchars($file['id']) . "'>
                                    <input type='submit' name='delete_file' value='Delete' class='btn btn-danger'>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>