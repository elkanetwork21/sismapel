<?php
include __DIR__ . "/../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";
// include BASE_PATH . "/pages/mikrotik/routeros_api.class.php";

$API = new RouterosAPI();
$API->debug = false;

if ($API->connect("vtek.vpnbersama.us:51266", "billing", "billing")) {
    // ambil semua secret dari Mikrotik
    $secrets = $API->comm("/ppp/secret/print");

    // ambil daftar paket dari DB
    $paketMap = [];
    $result = $conn->query("SELECT id, nama_paket FROM paket_langganan");
    while ($row = $result->fetch_assoc()) {
        $paketMap[$row['id']] = strtolower($row['nama_paket']); // mapping paket_id -> profile mikrotik
    }

    foreach ($secrets as $secret) {
        $username = $secret['name'];
        $profile  = isset($secret['profile']) ? strtolower($secret['profile']) : "";

        // cari data customer + paket_id
        $stmt = $conn->prepare("SELECT id, paket_id FROM customers WHERE ppp_secret=? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $cust = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($cust) {
            $paket_id   = $cust['paket_id'];
            $expectedProfile = isset($paketMap[$paket_id]) ? $paketMap[$paket_id] : "";

            if ($profile === "isolir") {
                $status = 0; // nonaktif
            } elseif ($expectedProfile !== "" && $profile === $expectedProfile) {
                $status = 1; // aktif
            } else {
                // jika profil tidak sesuai dengan paket, kita anggap nonaktif
                $status = 0;
            }

            // update ke DB
            $upd = $conn->prepare("UPDATE customers SET active_status=? WHERE id=?");
            $upd->bind_param("ii", $status, $cust['id']);
            $upd->execute();
            $upd->close();
        }
    }

    $API->disconnect();
}

echo "Sync selesai pada " . date("Y-m-d H:i:s") . "\n";
