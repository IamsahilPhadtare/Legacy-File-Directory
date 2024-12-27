<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";

check_admin();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Set time limit to prevent script timeout
set_time_limit(0);
// Disable apache buffer
@apache_setenv('no-gzip', 1);
ini_set('output_buffering', 0);
ini_set('implicit_flush', 1);
ob_implicit_flush(1);

while (true) {
    $online_users = get_online_users();
    $data = [];
    
    foreach ($online_users as $user) {
        $last_active = strtotime($user['last_activity']);
        $seconds_ago = time() - $last_active;
        $activity_text = ($seconds_ago < 30) ? "active now" : floor($seconds_ago / 60) . " min ago";
        
        $data[] = [
            'username' => $user['username'],
            'last_active' => $activity_text,
            'is_admin' => $user['is_admin']
        ];
    }

    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
    
    // Reduce interval to 1 second for more frequent updates
    usleep(1000000); // 1 second
}
