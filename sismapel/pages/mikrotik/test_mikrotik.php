<?php
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/routeros_api.class.php";

$ip       = $_POST['ip_address'] ?? '';
$port     = $_POST['port'] ?? 8728;
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$API = new RouterosAPI();
$API->debug = false;

if ($API->connect($ip, $username, $password, $port)) {
    echo json_encode([
        "status" => "success",
        "message" => "✅ Koneksi ke Mikrotik berhasil!"
    ]);
    $API->disconnect();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "❌ Gagal konek ke Mikrotik, periksa IP, port, username, dan password!"
    ]);
}
