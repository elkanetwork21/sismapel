<?php

header('Content-Type: application/json');
include __DIR__ . "../../../config.php";
require_once "routeros_api.class.php";


$id = $_GET['id'] ?? null;
$interface = $_GET['interface'] ?? "ether1";

$q = $conn->query("SELECT * FROM mikrotik WHERE id='$id'");
if($q->num_rows == 0){ echo json_encode(["error"=>"Data Mikrotik tidak ditemukan"]); exit; }
$row = $q->fetch_assoc();

$ip   = $row['ip_address'];
$user = $row['username'];
$pass = $row['password'];
$port = $row['port'] ?: 8728;

// --- ambil trafik via RouterOS API ---
require_once "../../library/routeros_api.class.php"; // atau class API v7 yang kamu pakai
$API = new RouterosAPI();
$API->debug = false;

if ($API->connect($ip, $user, $pass, $port)) {
    $data = $API->comm("/interface/monitor-traffic", [
        "interface" => $interface,
        "once" => ""
    ]);

    $rx = isset($data[0]['rx-bits-per-second']) ? (int)$data[0]['rx-bits-per-second'] : 0;
    $tx = isset($data[0]['tx-bits-per-second']) ? (int)$data[0]['tx-bits-per-second'] : 0;

    echo json_encode(["rx"=>$rx/1024, "tx"=>$tx/1024]); // output dalam kbps
    $API->disconnect();
} else {
    echo json_encode(["error"=>"Gagal konek ke Mikrotik"]);
}
