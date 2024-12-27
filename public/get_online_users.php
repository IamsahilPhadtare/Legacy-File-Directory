<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";

// Verify admin is logged in
check_admin();

$online_users = get_online_users();
if(empty($online_users)) {
    echo "<p>No users currently online</p>";
} else {
    echo "<ul>";
    foreach($online_users as $user) {
        $last_active = strtotime($user['last_activity']);
        $seconds_ago = time() - $last_active;
        $activity_text = ($seconds_ago < 60) ? "just now" : floor($seconds_ago / 60) . " min ago";
        
        echo "<li>" . htmlspecialchars($user['username']) . 
             " <span class='last-active'>(" . $activity_text . ")</span></li>";
    }
    echo "</ul>";
}
