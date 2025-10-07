<?php
session_start();
include __DIR__ . "../../../../config.php";

$onlineTimeout = 300; // 5 menit

$sql = "SELECT id, last_activity FROM users";
$result = $conn->query($sql);

$response = [];
$now = time();

while ($row = $result->fetch_assoc()) {
    if (empty($row['last_activity'])) {
        $isOnline = false; // logout, pasti offline
    } else {
        $last_activity = strtotime($row['last_activity']);
        $isOnline = ($now - $last_activity) <= $onlineTimeout;
    }

    $response[$row['id']] = $isOnline ? 'Online' : 'Offline';
}

echo json_encode($response);
