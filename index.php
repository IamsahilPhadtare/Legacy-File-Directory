<?php
session_start();
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: public/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>LFD - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function toggleLoginForms() {
            const userForm = document.getElementById('user-login-form');
            const adminForm = document.getElementById('admin-login-form');
            const toggleBtn = document.getElementById('toggle-login-btn');

            if (userForm.style.display === 'block') {
                userForm.style.display = 'none';
                adminForm.style.display = 'block';
                toggleBtn.textContent = 'Switch to User Login';
            } else {
                userForm.style.display = 'block';
                adminForm.style.display = 'none';
                toggleBtn.textContent = 'Switch to Admin Login';
            }
        }

        window.onload = function() {
            document.getElementById('user-login-form').style.display = 'block';
            document.getElementById('admin-login-form').style.display = 'none';
        };

        // Removed the unload event listener to prevent unwanted logout
        /*
        window.addEventListener('unload', function () {
            navigator.sendBeacon('auth/logout.php');
        });
        */
    </script>
</head>
<body>
    <div class="login-container">
        <h2>Legacy File Directory</h2>
        
        <button id="toggle-login-btn" onclick="toggleLoginForms()" class="btn btn-secondary">
            Switch to Admin Login
        </button>

        <!-- User Login Form -->
        <form id="user-login-form" action="auth/login.php" method="post">
            <h3>User Login</h3>
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <input type="hidden" name="login_type" value="user">
            <div class="form-group">
                <input type="submit" value="Login" class="btn btn-primary">
            </div>
        </form>

        <!-- Admin Login Form -->
        <form id="admin-login-form" action="auth/login.php" method="post">
            <h3>Administrator Login</h3>
            <div class="form-group">
                <input type="text" name="admin_username" placeholder="Admin Username" required>
            </div>
            <div class="form-group">
                <input type="password" name="admin_password" placeholder="Admin Password" required>
            </div>
            <input type="hidden" name="login_type" value="admin">
            <div class="form-group">
                <input type="submit" value="Admin Login" class="btn btn-primary">
            </div>
        </form>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-message">
                <?php 
                    switch($_GET['error']) {
                        case 'invalid':
                            echo "Invalid username or password";
                            break;
                        case 'invalid_admin':
                            echo "Invalid administrator credentials";
                            break;
                        case 'not_admin':
                            echo "Access denied: Administrator privileges required";
                            break;
                    }
                ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
