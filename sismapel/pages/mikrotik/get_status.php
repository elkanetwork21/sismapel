<?php
header('Content-Type: application/json');
require_once __DIR__ . "../../../config.php";
require_once "routeros_api.class.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(["error" => "ID Mikrotik tidak ditemukan"]);
    exit;
}

// 🔹 Query pakai prepared statement biar aman
$stmt = $conn->prepare("SELECT * FROM mikrotik_settings WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["error" => "Data Mikrotik tidak ada"]);
    exit;
}
$row = $result->fetch_assoc();

$ip   = $row['ip_address'];
$user = $row['username'];
$pass = $row['password'];
$port = $row['port'] ?: 8728;

$API = new RouterosAPI();
$API->debug = false;

/**
 * Convert uptime string dari Mikrotik (misal: "1w2d3h4m5s")
 * jadi format lebih manusiawi
 */
function formatUptime(string $uptimeStr): string {
    $weeks = $days = $hours = $minutes = $seconds = 0;

    if (preg_match('/(\d+)w/', $uptimeStr, $m)) $weeks   = (int)$m[1];
    if (preg_match('/(\d+)d/', $uptimeStr, $m)) $days    = (int)$m[1];
    if (preg_match('/(\d+)h/', $uptimeStr, $m)) $hours   = (int)$m[1];
    if (preg_match('/(\d+)m(?!s)/', $uptimeStr, $m)) $minutes = (int)$m[1]; // hindari "ms"
    if (preg_match('/(\d+)s/', $uptimeStr, $m)) $seconds = (int)$m[1];

    $totalDays = ($weeks * 7) + $days;

    return "{$totalDays}d {$hours}h {$minutes}m";
}

// 🔹 Connect Mikrotik
if ($API->connect($ip, $user, $pass, $port)) {
    $resource = $API->comm("/system/resource/print");
    $identity = $API->comm("/system/identity/print");
    $API->disconnect();

    $res = $resource[0] ?? [];

    echo json_encode([
        "identity"   => $identity[0]['name'] ?? "-",
        "uptime"     => isset($res['uptime']) ? formatUptime($res['uptime']) : "-",
        "cpu"        => $res['cpu-load'] ?? 0,
        "free_mem"   => isset($res['free-memory']) ? round($res['free-memory'] / 1024 / 1024, 1) : 0,
        "total_mem"  => isset($res['total-memory']) ? round($res['total-memory'] / 1024 / 1024, 1) : 0,
        "version"    => $res['version'] ?? "-"
    ]);
} else {
    echo json_encode(["error" => "Tidak bisa terhubung ke Mikrotik"]);
}
