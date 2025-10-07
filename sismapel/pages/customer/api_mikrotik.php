<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'];

header("Content-Type: application/json");

$API = getMikrotikConnection($branch_id);
$online = [];

if ($API) {
    $actives = $API->comm("/ppp/active/print");
    foreach($actives as $a){
        $online[] = $a['name']; // kirim ppp_secret yang online
    }
    $API->disconnect();
}

echo json_encode($online);
