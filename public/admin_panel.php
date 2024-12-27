<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";
check_login();
update_user_activity();

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
    <!-- Remove the automatic logout script -->
</head>
<body>
    <div class="admin-panel-container">
        <!-- Update navigation buttons -->
        <div class="admin-nav">
            <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
            <a href="file_management.php" class="btn btn-secondary">File Management</a>
            <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
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
                        echo "<td>" . htmlspecialchars($user['last_login'] ?? 'Never') . "</td>";
                        echo "<td>
                                <button onclick=\"openClearanceModal(" . htmlspecialchars($user['id']) . ", '" . htmlspecialchars($user['username']) . "')\" class='btn btn-secondary'>Change Clearance</button>
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

        <!-- Clearance Change Modal -->
        <div id="clearanceModal" class="modal" style="display:none;">
            <div class="modal-content">
                <h3>Change Clearance Level</h3>
                <p>User: <span id="modalUsername"></span></p>
                <form id="clearanceForm" method="post" action="update_clearance.php">
                    <input type="hidden" id="userId" name="user_id">
                    <div class="form-group">
                        <label for="newClearance">New Clearance Level:</label>
                        <select id="newClearance" name="new_clearance" required>
                            <option value="1">Level 1 (Basic)</option>
                            <option value="2">Level 2 (Moderate)</option>
                            <option value="3">Level 3 (High)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="adminPassword">Admin Password:</label>
                        <input type="password" id="adminPassword" name="admin_password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Clearance</button>
                        <button type="button" onclick="closeClearanceModal()" class="btn btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            width: 400px;
        }
        </style>

        <script>
        function openClearanceModal(userId, username) {
            document.getElementById('clearanceModal').style.display = 'flex';
            document.getElementById('modalUsername').textContent = username;
            document.getElementById('userId').value = userId;
        }

        function closeClearanceModal() {
            document.getElementById('clearanceModal').style.display = 'none';
        }
        </script>

        <section class="online-users-section">
            <h2>Online Users</h2>
            <div class="online-users-list">
                <!-- Dynamic content will be inserted here -->
            </div>
        </section>

        <style>
        .online-users-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .online-users-list {
            margin-top: 10px;
        }
        .online-users-list ul {
            list-style: none;
            padding: 0;
        }
        .online-users-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .last-active {
            color: #666;
            font-size: 0.9em;
        }
        .admin-badge {
            background-color: #007bff;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.8em;
            margin-left: 5px;
        }
        </style>

        <script>
        const evtSource = new EventSource('stream_online_users.php');
        let lastData = '';
        
        evtSource.onmessage = function(event) {
            // Only update DOM if data has changed
            if (lastData !== event.data) {
                const users = JSON.parse(event.data);
                const usersList = document.querySelector('.online-users-list');
                
                if (users.length === 0) {
                    usersList.innerHTML = "<p>No users currently online</p>";
                } else {
                    let html = "<ul>";
                    users.forEach(user => {
                        const isAdmin = parseInt(user.is_admin) === 1;
                        const adminBadge = isAdmin ? ' <span class="admin-badge">Admin</span>' : '';
                        const activeClass = user.last_active === 'active now' ? ' class="user-active"' : '';
                        html += `<li${activeClass}>
                            ${escapeHtml(user.username)}${adminBadge}
                            <span class="last-active">(${user.last_active})</span>
                        </li>`;
                    });
                    html += "</ul>";
                    usersList.innerHTML = html;
                }
                lastData = event.data;
            }
        };
        
        // Reconnect if connection is lost
        evtSource.onerror = function() {
            evtSource.close();
            setTimeout(() => {
                evtSource = new EventSource('stream_online_users.php');
            }, 1000);
        };

        function escapeHtml(unsafe) {
            return unsafe
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }
        </script>

        <style>
        /* Add this to your existing styles */
        .user-active {
            background: #e8f5e9;
            border-left: 3px solid #28a745;
            padding-left: 5px !important;
        }
        </style>
    </div>
</body>
</html>