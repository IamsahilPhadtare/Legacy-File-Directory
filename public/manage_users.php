<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";
check_login();
check_admin();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_clearance'])) {
    $user_id = $_POST['user_id'];
    $clearance = $_POST['clearance_level'];
    
    $sql = "UPDATE users SET clearance_level = ? WHERE id = ? AND is_admin = 0";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $clearance, $user_id);
        mysqli_stmt_execute($stmt);
    }
}

// Handle user removal using the remove_user function
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    // Prevent admin from deleting themselves
    if($user_id == $_SESSION['id']) {
        $error_message = "You cannot delete your own account.";
    } else {
        if(remove_user($user_id)){
            $success_message = "User deleted successfully.";
        } else {
            $error_message = "Failed to delete user.";
        }
    }
}

// Handle adding a new user using the add_user function
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_member'])) {
    // Auto-generate username and password
    $new_username = generateRandomString(6);
    $new_password_plain = generateRandomString(8); // Plain password to display
    $new_password_hashed = password_hash($new_password_plain, PASSWORD_DEFAULT);
    $initial_clearance = 'read'; // Default clearance level
    
    if(add_user($new_username, $new_password_hashed, $initial_clearance)){
        $success_message = "User added successfully.<br>Username: " . htmlspecialchars($new_username) . "<br>Password: " . htmlspecialchars($new_password_plain);
    } else {
        $error_message = "Failed to add user.";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>LFD - Manage Users</title>
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
        <h1 class="admin-title">Admin Panel - Manage Users</h1>
        
        <!-- Back Button -->
        <div class="back-button">
            <a href="admin_panel.php" class="btn btn-secondary">Back to Admin Panel</a>
        </div>
        
        <div class="add-member-section">
            <h3>Add New Member</h3>
            <?php if(isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if(isset($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <form method="post" class="add-member-form">
                <div class="form-group">
                    <button type="submit" name="add_member" class="btn btn-primary">Add Member</button>
                </div>
            </form>
        </div>

        <div class="users-list">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Clearance Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, username, clearance_level FROM users WHERE is_admin = 0";
                    $result = mysqli_query($conn, $sql);
                    
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['clearance_level']) . "</td>";
                        echo "<td>
                                <form method='post' class='actions-form'>
                                    <input type='hidden' name='user_id' value='" . $row['id'] . "'>
                                    <select name='clearance_level' class='clearance-select'>";
                        echo "<option value='read'" . ($row['clearance_level'] == 'read' ? ' selected' : '') . ">Read Only</option>";
                        echo "<option value='upload'" . ($row['clearance_level'] == 'upload' ? ' selected' : '') . ">Upload</option>";
                        echo "<option value='edit'" . ($row['clearance_level'] == 'edit' ? ' selected' : '') . ">Edit</option>";
                        echo "</select>
                                    <button type='submit' name='update_clearance' class='btn btn-success'>Update</button>
                                    <button type='submit' name='delete_user' class='btn btn-danger' onclick=\"return confirm('Are you sure you want to delete this user?');\">Delete</button>
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
