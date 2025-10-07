<?php
session_start();

include __DIR__ . "/../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

$branch_id = $_SESSION['branch_id'];
header('Content-Type: application/json');

$API = getMikrotikConnection($branch_id);
$data = [];
$secret_asal = $_GET['secret'] ?? null;
$secret = "<pppoe-" . $secret_asal . ">";

if ($API && $secret) {
    // ambil queue berdasarkan nama
    $API->write('/queue/simple/print', false);
    $API->write('?name=' . $secret, true);
    $queues = $API->read();

    if (count($queues) > 0) {
        $q = $queues[0];

        // parsing bytes
        $bytes = $q['bytes'] ?? "0/0";
        if (is_string($bytes)) {
            $parts = preg_split('/[\/,]/', $bytes);
            $tx = isset($parts[0]) ? (int)$parts[0] : 0; // upload
            $rx = isset($parts[1]) ? (int)$parts[1] : 0; // download
        } elseif (is_array($bytes)) {
            $tx = isset($bytes[0]) ? (int)$bytes[0] : 0;
            $rx = isset($bytes[1]) ? (int)$bytes[1] : 0;
        } else {
            $tx = $rx = 0;
        }

        
        // cek ppp secret (last_seen)
        $API->write('/ppp/secret/print', false);
        $API->write('?name=' . $secret_asal, true);
        $secrets = $API->read();


        // Ambil uptime & IP dari PPP Active
        $API->write('/ppp/active/print', false);
        $API->write('?name=' . $secret_asal, true);
        $ppp_active = $API->read();

        $uptime = "0s";
        $last_seen = "-";
        $ip_address = "-";

        if (count($ppp_active) > 0) {
            $uptime = $ppp_active[0]['uptime'] ?? "0s";
            $ip_address = $ppp_active[0]['address'] ?? "-";
        } else {

        // jika tidak aktif, last_seen bisa diambil dari waktu sekarang
            $last_seen = date("Y-m-d H:i:s");
        }

        if (!empty($secrets)) {
            $last_seen = $secrets[0]['last-logged-out'] ?? "-";
        }

        $data = [
            'name'           => $q['name'],
            'upload_bytes'   => $tx,
            'download_bytes' => $rx,
            'upload_human'   => formatBytes(round($tx)),
            'download_human' => formatBytes(round($rx)),
            'uptime'         => $uptime,
            'last_seen'      => $last_seen,
            'ip_address'     => $ip_address,
            'active'         => (count($ppp_active) > 0)
        ];
    } else {
        $data = ['error' => 'Queue tidak ditemukan untuk ' . $secret];
    }

    $API->disconnect();
} else {
    $data = ['error' => 'Gagal konek ke Mikrotik atau secret kosong'];
}

echo json_encode($data);
