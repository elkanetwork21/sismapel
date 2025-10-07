<?php
session_start();
include __DIR__ . "/../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'] ?? 0;
$API = getMikrotikConnection($branch_id);

header('Content-Type: application/json');

if ($API) {
    $API->write("/system/resource/print");
    $res = $API->read()[0] ?? [];

    $data = [
        "cpu" => $res['cpu-load'] ?? 0,
        "cpu_freq" => $res['cpu-frequency'] ?? 0,
        "cpu_count" => $res['cpu-count'] ?? 0,
        "free_memory" => $res['free-memory'] ?? 0,
        "total_memory" => $res['total-memory'] ?? 1,
        "free_hdd" => $res['free-hdd-space'] ?? 0,
        "total_hdd" => $res['total-hdd-space'] ?? 1,
        "voltage" => $res['voltage'] ?? '-',
        "temperature" => $res['temperature'] ?? '-',
        "uptime"   => $res["uptime"]?? '-',
        "board"    => $res["board-name"] ?? '-',
        "model"    => $res["model"]?? '-',
        "routeros" => $res["version"]?? '-',
        "architecture" => $res["architecture-name"]?? '-',
        "cpufreq" => $res["cpu"].'('.$res["cpu-frequency"].')'?? '-'

    ];

    echo json_encode($data);
    $API->disconnect();
} else {
    echo json_encode(["error" => "Gagal koneksi Mikrotik"]);
}
