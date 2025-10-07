<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'];
$API = getMikrotikConnection($branch_id);

$response = ["success"=>false,"activeSecrets"=>[],"isolirSecrets"=>[]];

if ($API) {
    $activeSecrets = [];
    $isolirSecrets = [];
    $secrets = [];

    // Ambil semua user online
    $actives = $API->comm("/ppp/active/print");
    foreach ($actives as $a) {
        $activeSecrets[] = $a['name'];
    }

    // Ambil semua secret untuk cek isolir
    $secrets = $API->comm("/ppp/secret/print");
    foreach ($secrets as $s) {
        if (isset($s['profile']) && strtolower($s['profile']) === "isolir") {
            $isolirSecrets[] = $s['name'];
        }
    }

    $API->disconnect();

    $response["success"] = true;
    $response["activeSecrets"] = $activeSecrets;
    $response["isolirSecrets"] = $isolirSecrets;
    $response["summary"] = [
        "total_secret" => count($secrets),
        "online"       => count($activeSecrets),
        "offline"      => count($secrets) - count($activeSecrets),
        "isolir"       => count($isolirSecrets)
    ];
}

header("Content-Type: application/json");
echo json_encode($response);
