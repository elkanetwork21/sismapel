<?php
header('Content-Type: application/json');
require('../pages/mikrotik/routeros_api.class.php'); // sesuaikan path

$API = new RouterosAPI();
if ($API->connect('10.25.1.90', 'billing', 'billing')) {
    $interface = isset($_GET['iface']) ? $_GET['iface'] : 'ether1';

    // Ambil traffic
    $API->write('/interface/print', false);
    $API->write('?name=' . $interface, true);
    $iface = $API->read();

    if (isset($iface[0]['tx-byte']) && isset($iface[0]['rx-byte'])) {
        // ubah ke Mbps
        $tx = round(($iface[0]['tx-byte'] * 8) / 1024 / 1024, 2);
        $rx = round(($iface[0]['rx-byte'] * 8) / 1024 / 1024, 2);

        echo json_encode([
            "interface" => $interface,
            "tx" => $tx,
            "rx" => $rx,
            "speed" => max($tx, $rx) // ambil yang lebih besar buat speedometer
        ]);
    } else {
        echo json_encode(["error" => "Data tidak ditemukan"]);
    }

    $API->disconnect();
} else {
    echo json_encode(["error" => "Gagal konek ke MikroTik"]);
}
