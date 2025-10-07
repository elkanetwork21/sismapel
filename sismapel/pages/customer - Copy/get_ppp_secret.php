<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'] ?? 0;
$response = ["success"=>false, "data"=>[]];

if ($API = getMikrotikConnection($branch_id)) {
    $pppSecrets = $API->comm("/ppp/secret/print");
    $API->disconnect();

    $response["success"] = true;
    foreach ($pppSecrets as $s) {
        $response["data"][] = [
            "name"    => $s['name'],
            "service" => $s['service'] ?? '-'
        ];
    }
}

header("Content-Type: application/json");
echo json_encode($response);
