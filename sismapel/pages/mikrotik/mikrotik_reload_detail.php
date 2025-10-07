<?php
include __DIR__ . "../../../config.php";
require_once "routeros_api.class.php";

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = intval($_GET['id']);
$query = $conn->prepare("SELECT * FROM mikrotik_settings WHERE id=?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$mt = $result->fetch_assoc();

$API = new RouterosAPI();
$API->debug = false;

if ($API->connect($mt['ip_address'], $mt['username'], $mt['password'])) {
    $resource = $API->comm("/system/resource/print");
    $identity = $API->comm("/system/identity/print");
    $API->disconnect();

    $data = $resource[0];
    $data['identity'] = $identity[0]['name'];

    // tampilkan tabel baru
    echo '<table class="table table-bordered table-striped">';
    echo "<tr><th>Identity</th><td>{$data['identity']}</td></tr>";
    echo "<tr><th>Uptime</th><td>{$data['uptime']}</td></tr>";
    echo "<tr><th>Version</th><td>{$data['version']}</td></tr>";
    echo "<tr><th>Architecture</th><td>{$data['architecture-name']}</td></tr>";
    echo "<tr><th>CPU</th><td>{$data['cpu']} ({$data['cpu-frequency']} MHz)</td></tr>";
    echo "<tr><th>CPU Load</th><td>{$data['cpu-load']}%</td></tr>";
    echo "<tr><th>Free Memory</th><td>".round($data['free-memory']/1024/1024)." MB</td></tr>";
    echo "<tr><th>Total Memory</th><td>".round($data['total-memory']/1024/1024)." MB</td></tr>";
    echo "<tr><th>Free HDD</th><td>".round($data['free-hdd-space']/1024/1024)." MB</td></tr>";
    echo "<tr><th>Total HDD</th><td>".round($data['total-hdd-space']/1024/1024)." MB</td></tr>";
    echo '</table>';
} else {
    echo '<div class="alert alert-danger">Tidak bisa terhubung ke Mikrotik</div>';
}
