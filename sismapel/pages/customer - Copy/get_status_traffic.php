<?php
// ambil data dari mikrotik (API active connections + traffic)
// lalu return JSON untuk secrets yang diminta

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);
$secrets = $input['secrets'] ?? [];

$data = [];
foreach ($secrets as $secret) {
  $data[] = [
    "ppp_secret" => $secret,
    "online" => rand(0,1),   // 👉 ganti dengan hasil mikrotik
    "tx" => rand(1000,50000),
    "rx" => rand(1000,50000)
  ];
}

echo json_encode($data);
