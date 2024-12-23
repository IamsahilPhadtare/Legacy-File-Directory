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

// Fetch total number of users
$totalUsers = get_total_users();

// Handle user removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_user_id'])) {
    $userId = intval($_POST['remove_user_id']);
    remove_user($userId);
    header("Location: admin_panel.php");
    exit();
}

// Handle adding a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_username'])) {
    $username = htmlspecialchars($_POST['add_username']);
    $password = password_hash($_POST['add_password'], PASSWORD_DEFAULT);
    $clearance = intval($_POST['add_clearance']);
    add_user($username, $password, $clearance);
    header("Location: admin_panel.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - LFD</title>
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
    <div class="admin-panel-container">
        <!-- Add navigation buttons -->
        <div class="admin-nav">
            <a href="user_management.php" class="btn btn-secondary">Back to User Management</a>
            <a href="dashboard.php" class="btn btn-secondary">Return to Main Page</a>
        </div>

        <h1 class="admin-title">Admin Panel</h1>
        <p class="total-users">Total Users: <?php echo $totalUsers; ?></p>
        
        <section class="add-user-section">
            <h2>Add New User</h2>
            <form method="post" action="admin_panel.php" class="add-user-form">
                <label for="add_username">Username:</label>
                <input type="text" id="add_username" name="add_username" required>
                
                <label for="add_password">Password:</label>
                <input type="password" id="add_password" name="add_password" required>
                
                <label for="add_clearance">Clearance Level:</label>
                <select id="add_clearance" name="add_clearance" required>
                    <option value="1">User</option>
                    <option value="2">Moderator</option>
                    <option value="3">Administrator</option>
                </select>
                
                <input type="submit" value="Add User" class="btn btn-primary">
            </form>
        </section>

        <section class="manage-users-section">
            <h2>Manage Users</h2>
            <table class="admin-user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Clearance Level</th>
                        <th>Last Login</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = get_all_users();
                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['clearance_level']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['last_login']) . "</td>";
                        echo "<td>
                                <form method='post' action='admin_panel.php' onsubmit=\"return confirm('Are you sure you want to remove this user?');\">
                                    <input type='hidden' name='remove_user_id' value='" . htmlspecialchars($user['id']) . "'>
                                    <input type='submit' value='Remove' class='btn btn-danger'>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
        
        <!-- ...existing code... -->
    </div>
</body>
</html>