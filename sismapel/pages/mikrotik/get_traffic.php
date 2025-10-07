<?php
session_start();
include __DIR__ . "../../../config.php";
require_once "mikrotik_connect.php";

// $API = new RouterosAPI();
// $API->debug = false;

$branch_id = $_SESSION['branch_id'];

$iface = $_GET['iface'] ?? 'ether1-Backbone';

$API = getMikrotikConnection($branch_id);
if ($API) {
    $traffic = $API->comm("/interface/print", [
        "?name" => $iface
    ]);

    if (isset($traffic[0])) {
        echo json_encode([
            "interface" => $iface,
            "rx" => $traffic[0]['rx-byte'],
            "tx" => $traffic[0]['tx-byte']
        ]);
    } else {
        echo json_encode(["interface"=>$iface,"rx"=>0,"tx"=>0]);
    }

    $API->disconnect();
} else {
    echo json_encode(["error"=>"cannot connect"]);
}
?>
