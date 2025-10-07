<?php
header('Content-Type: application/json');

// setting MikroTik
$ip     = "10.10.0.1";
$user   = "billing";
$pass   = "billing";
$port   = "8728";
$ppp_secret = "alya@jgwn"; // ganti dengan nama interface PPPoE

include __DIR__ . "../../config.php";
include BASE_PATH . "/pages/mikrotik/routeros_api.class.php";


$API = new RouterosAPI();

if ($API->connect('10.10.0.1', 'billing', 'billing', '8728')) {

    
$interface = "<pppoe-" . $ppp_secret .">";
// $interface = "ether6-Aris";

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
            // 'ppp_secret' => $ppp_secret,
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