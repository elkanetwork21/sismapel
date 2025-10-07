<?php
include __DIR__ . "../../../config.php";
require_once "routeros_api.class.php";

$id = $_GET['id'] ?? null;
if(!$id){ echo json_encode(["error" => "ID Mikrotik tidak ditemukan"]); exit; }

$q = $conn->query("SELECT * FROM mikrotik WHERE id='$id'");
if($q->num_rows == 0){ echo json_encode(["error" => "Data tidak ada"]); exit; }
$row = $q->fetch_assoc();

$API = new RouterosAPI();
if($API->connect($row['ip_address'], $row['username'], $row['password'], (int)$row['port'])){
    $res = $API->comm("/interface/print", [".proplist"=>"name"]);
    $interfaces = [];
    foreach($res as $iface){
        $interfaces[] = ["name"=>$iface['name']];
    }
    echo json_encode($interfaces);
    $API->disconnect();
} else {
    echo json_encode(["error" => "Gagal koneksi ke Mikrotik"]);
}
