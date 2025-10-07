<?php

session_start();

include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";
$branch_id = $_SESSION['branch_id'];

header('Content-Type: application/json');


$API = getMikrotikConnection($branch_id);

$data = [];

if ($API) {

    // Ambil data customer dari DB
    $result = $conn->query("SELECT id, fullname, ppp_secret FROM customers");
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        // format interface di mikrotik = "pppoe-<ppp_secret>"
        $row['interface_name'] = "<pppoe-" . $row['ppp_secret'].">";
        $customers[$row['interface_name']] = $row; // key = interface
    }

    // Ambil semua interface dari Mikrotik
    $API->write('/interface/print');
    $interfaces = $API->read();

    foreach ($interfaces as $iface) {
        $ifname = $iface['name']; // contoh: pppoe-aldi@ghs

        if (isset($customers[$ifname])) {
            $cust = $customers[$ifname];

            // Ambil traffic dari interface
            $API->write('/interface/monitor-traffic', false);
            $API->write('=interface=' . $ifname, false);
            $API->write('=once=');
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);

            $rx = isset($ARRAY[0]['rx-bits-per-second']) ? $ARRAY[0]['rx-bits-per-second'] : 0;
            $tx = isset($ARRAY[0]['tx-bits-per-second']) ? $ARRAY[0]['tx-bits-per-second'] : 0;

            $data[] = [
                "id" => $cust['id'],
                "nama" => $cust['fullname'],
                "ppp_secret" => $cust['ppp_secret'],
                "interface" => $ifname,
                "tx" => $tx,
                "rx" => $rx
            ];
        }
    }

    $API->disconnect();
}

echo json_encode($data);
