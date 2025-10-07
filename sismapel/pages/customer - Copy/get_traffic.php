<?php
header('Content-Type: application/json');

// ====== KONFIGURASI MIKROTIK ======
$MT_HOST = '10.10.0.1';
$MT_USER = 'billing';
$MT_PASS = 'billing';
$MT_PORT = 8728; // API Port, bukan Winbox

// ====== INPUT ======
$interface = isset($_GET['interface']) ? trim($_GET['interface']) : null;
$secret    = isset($_GET['secret']) ? trim($_GET['secret']) : null;

// ====== LIB API ======
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/routeros_api.class.php";

$API = new RouterosAPI();
$API->debug = false;

function findInterfaceBySecret($API, $secret) {
    // 1) Coba tebak nama interface "pppoe-<secret>"
    $guess = "pppoe-" . $secret;

    $res = $API->comm("/interface/print", [
        ".proplist" => "name",
        "?name"     => $guess
    ]);
    if (!empty($res)) {
        return $res[0]['name'];
    }

    // 2) Kalau tidak ada, cari interface yang mengandung secret
    $all = $API->comm("/interface/print", [
        ".proplist" => "name"
    ]);
    foreach ($all as $row) {
        if (isset($row['name']) && stripos($row['name'], $secret) !== false) {
            return $row['name'];
        }
    }

    // 3) Terakhir, cek dari ppp active → ambil 'name' lalu cocokkan ke interface
    $actives = $API->comm("/ppp/active/print", [
        ".proplist" => "name,service,caller-id,address",
        "?name"     => $secret
    ]);
    if (!empty($actives)) {
        $cand = $actives[0]['name']; // sering sama dengan bagian akhir nama interface dinamis
        $all2 = $API->comm("/interface/print", [".proplist" => "name"]);
        foreach ($all2 as $row) {
            if (isset($row['name']) && stripos($row['name'], $cand) !== false) {
                return $row['name'];
            }
        }
    }

    return null;
}

try {
    if (!$API->connect($MT_HOST, $MT_USER, $MT_PASS, $MT_PORT)) {
        echo json_encode(["ok" => false, "error" => "Tidak bisa konek ke MikroTik"]);
        exit;
    }

    if (!$interface && $secret) {
        $interface = findInterfaceBySecret($API, $secret);
    }

    if (!$interface) {
        echo json_encode(["ok" => false, "error" => "Interface PPPoE tidak ditemukan. Kirim ?interface=... atau ?secret=..."]);
        $API->disconnect();
        exit;
    }

    // Baca rx-byte 1
    $r1 = $API->comm("/interface/print", [
        ".proplist" => "name,rx-byte,tx-byte",
        "?name"     => $interface
    ]);
    if (empty($r1)) {
        echo json_encode(["ok" => false, "error" => "Interface tidak ada: $interface"]);
        $API->disconnect();
        exit;
    }
    $rx1 = isset($r1[0]['rx-byte']) ? (int)$r1[0]['rx-byte'] : 0;

    // Tunggu 1 detik
    usleep(1000000); // 1.0s

    // Baca rx-byte 2
    $r2 = $API->comm("/interface/print", [
        ".proplist" => "name,rx-byte,tx-byte",
        "?name"     => $interface
    ]);
    $rx2 = isset($r2[0]['rx-byte']) ? (int)$r2[0]['rx-byte'] : 0;

    // Hitung download bps → Mbps
    $bps = max(0, $rx2 - $rx1) * 8; // bit per detik
    $mbps = $bps / 1_000_000;

    echo json_encode([
        "ok"        => true,
        "interface" => $interface,
        "download"  => round($mbps, 2) // Mbps
    ]);

    $API->disconnect();
} catch (Throwable $e) {
    echo json_encode(["ok" => false, "error" => $e->getMessage()]);
}
