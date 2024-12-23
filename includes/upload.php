<?php
session_start();
require_once "../config/database.php";
require_once "functions.php";
check_login();

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_dir = "../uploads/"; // Changed from "../assets/uploads/"
    $file = $_FILES["fileToUpload"];
    $is_hidden = isset($_POST["is_hidden"]) ? 1 : 0;
    
    $file_extension = get_file_extension($file["name"]);
    if(!is_allowed_file_type($file_extension)) {
        die("File type not allowed");
    }

    $target_file = $target_dir . time() . '_' . basename($file["name"]);
    
    // Ensure the upload directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    if(move_uploaded_file($file["tmp_name"], $target_file)) {
        $sql = "INSERT INTO files (filename, filepath, uploaded_by, is_hidden) VALUES (?, ?, ?, ?)";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssii", $file["name"], $target_file, $_SESSION["id"], $is_hidden);
            mysqli_stmt_execute($stmt);
            header("location: ../public/dashboard.php");
            exit();
        }
    } else {
        die("Failed to upload file. Please check directory permissions.");
    }
}
?>
