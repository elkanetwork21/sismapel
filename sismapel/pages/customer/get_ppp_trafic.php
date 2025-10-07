<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";
// include BASE_PATH . "/pages/mikrotik/routeros_api.class.php";

header('Content-Type: application/json');

if (!isset($_GET['ppp_secret'])) {
    echo json_encode(['error' => 'ppp_secret tidak ditemukan']);
    exit;
}
$branch_id = $_SESSION['branch_id'];
$ppp_secret = $_GET['ppp_secret'];
$API = getMikrotikConnection($branch_id);
// $API->debug = false;


if ($API) {

    
$interface = "<pppoe-" . $ppp_secret .">";

    // cek dulu apakah interface ini ada
    $check = $API->comm("/interface/print", [
        "?name" => $interface
    ]);

    if (count($check) > 0) {
        $traffic = $API->comm("/interface/monitor-traffic", [
            "interface" => $interface,
            "once" => ""
        ]);

        echo json_encode([
            'ppp_secret' => $ppp_secret,
            'interface' => $interface,
            'rx' => $traffic[0]['rx-bits-per-second']  / 1024 / 1024,
            'tx' => $traffic[0]['tx-bits-per-second']  / 1024 / 1024,
        ]);
    } else {
        echo json_encode(['error' => 'Interface tidak ditemukan: ' . $interface]);
    }

    $API->disconnect();
} else {
    echo json_encode(['error' => 'Gagal koneksi ke Mikrotik']);
}