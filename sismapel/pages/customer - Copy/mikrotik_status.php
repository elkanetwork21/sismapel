<?php
session_start();
header("Content-Type: application/json");
include __DIR__ . "/../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'];
$API = getMikrotikConnection($branch_id);

$data=[];
if($API){
  $active = $API->comm("/ppp/active/print");
  $secrets = $API->comm("/ppp/secret/print");
  $actives = array_column($active,"name");

  foreach($secrets as $s){
    $ppp = $s['name'];
    $profile = $s['profile'] ?? "";
    $isOnline = in_array($ppp,$actives);
    $tx=0; $rx=0;

    foreach($active as $a){
      if($a['name']===$ppp){
        $tx = $a['tx-bits-per-second'] ?? 0;
        $rx = $a['rx-bits-per-second'] ?? 0;
      }
    }
    $data[]=[
      "ppp_secret"=>$ppp,
      "profile"=>$profile,
      "online"=>$isOnline,
      "tx"=>formatSpeed($tx),
      "rx"=>formatSpeed($rx)
    ];
  }
  $API->disconnect();
}

echo json_encode($data);

function formatSpeed($bits){
  if($bits>=1000000) return round($bits/1000000,2)." Mbps";
  if($bits>=1000) return round($bits/1000,2)." Kbps";
  return $bits." bps";
}
