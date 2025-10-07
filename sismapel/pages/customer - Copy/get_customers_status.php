<?php
session_start();
include __DIR__ . "/../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'];
$API = getMikrotikConnection($branch_id);

$response = [];

if ($API) {
    // ambil semua secret + active
    $secrets = $API->comm("/ppp/secret/print");
    $actives = $API->comm("/ppp/active/print");

    $activeSecrets = array_column($actives, 'name'); // list nama yang online

    foreach ($secrets as $s) {
        $username = $s['name'];
        $profile  = strtolower($s['profile'] ?? "");
        $status   = in_array($username, $activeSecrets) ? "online" : "offline";
        $isolir   = ($profile === "isolir") ? 1 : 0;

        $response[] = [
            "ppp_secret" => $username,
            "status"     => $status,
            "isolir"     => $isolir,
        ];
    }
    $API->disconnect();
}

header("Content-Type: application/json");
echo json_encode($response);
