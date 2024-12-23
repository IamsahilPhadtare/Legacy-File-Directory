<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function check_admin() {
    if(!is_admin()) {
        header("location: ../index.php?error=not_admin");
        exit;
    }
}

function is_admin() {
    return isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === 1;
}

function check_login() {
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: ../index.php");
        exit;
    }
}

function get_file_extension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function is_allowed_file_type($extension) {
    $allowed_types = array('pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png');
    return in_array(strtolower($extension), $allowed_types);
}

function generateRandomString($length = 6) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

function get_total_users() {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM users WHERE is_admin = 0";
    $result = mysqli_query($conn, $sql);
    if($row = mysqli_fetch_assoc($result)){
        return $row['total'];
    }
    return 0;
}

function get_all_users() {
    global $conn;
    $sql = "SELECT id, username, clearance_level FROM users WHERE is_admin = 0";
    $result = mysqli_query($conn, $sql);
    $users = [];
    while($row = mysqli_fetch_assoc($result)){
        $users[] = $row;
    }
    return $users;
}

function add_user($username, $password, $clearance) {
    global $conn;
    $sql = "INSERT INTO users (username, password, clearance_level, is_admin) VALUES (?, ?, ?, 0)";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $username, $password, $clearance);
        return mysqli_stmt_execute($stmt);
    }
    return false;
}

function remove_user($userId) {
    global $conn;
    $sql = "DELETE FROM users WHERE id = ? AND is_admin = 0";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        return mysqli_stmt_execute($stmt);
    }
    return false;
}

// Function to handle uploading files to a local directory
function upload_file($fileTmpPath, $fileName) {
    global $conn;
    $uploadDir = '../uploads/'; // Ensure consistency
    $destPath = $uploadDir . $fileName;

    // Ensure the upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Move the uploaded file to the destination directory
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // Insert file information into the database
        $sql = "INSERT INTO files (filename, uploaded_by) VALUES (?, ?)";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $fileName, $_SESSION['username']);
            return mysqli_stmt_execute($stmt);
        }
    }
    return false;
}

function delete_file($fileId) {
    global $conn;
    // First, retrieve the filename to delete from the server
    $sql = "SELECT filename FROM files WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $fileId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $filename);
        if(mysqli_stmt_fetch($stmt)) {
            // Delete the file from the server
            $filePath = "../uploads/" . $filename;
            if(file_exists($filePath)) {
                unlink($filePath);
            }
        }
        mysqli_stmt_close($stmt);
    }

    // Now, delete the file record from the database
    $sql = "DELETE FROM files WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $fileId);
        return mysqli_stmt_execute($stmt);
    }
    return false;
}

// Start session with cookie parameters to expire on browser close
function start_secure_session() {
    $session_name = 'secure_session';
    $secure = false; // Set to true if using HTTPS
    $httponly = true;

    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        header("Location: ../index.php?error=session");
        exit();
    }

    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie expires on browser close
        'path' => $cookieParams["path"],
        'domain' => $cookieParams["domain"],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => 'Strict' // Adjust as needed
    ]);

    session_name($session_name);
    session_start();
    session_regenerate_id(true); // Regenerate session ID to prevent fixation
}
?>
