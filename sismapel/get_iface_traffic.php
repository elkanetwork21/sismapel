<?php
session_start();
include __DIR__ . "/config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'];

// ambil default interface dari DB
$sql = "SELECT interface_name FROM default_interfaces WHERE branch_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$iface = $row['interface_name'] ?? null;

if(!$iface){
    echo json_encode(["error"=>"Belum ada default interface"]);
    exit;
}

$API = getMikrotikConnection($branch_id);

if ($API) {
    $data = $API->comm("/interface/monitor-traffic", [
        "interface" => $iface,
        "once" => ""
    ]);
    $API->disconnect();

    $rx = ($data[0]["rx-bits-per-second"] ?? 0) / 1000000; // Mbps
    $tx = ($data[0]["tx-bits-per-second"] ?? 0) / 1000000; // Mbps

    echo json_encode([
        "rx" => round($rx, 2),
        "tx" => round($tx, 2),
        "iface" => $iface,
        "time" => date("H:i:s")
    ]);
} else {
    echo json_encode(["error"=>"Tidak bisa konek Mikrotik"]);
}