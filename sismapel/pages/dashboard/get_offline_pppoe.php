<?php
session_start();
include __DIR__ . "/../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'];

header('Content-Type: application/json');

$API = getMikrotikConnection($branch_id);

if ($API) {
    // 🔹 Ambil semua PPP secret
    $API->write('/ppp/secret/print');
    $secrets = $API->read();

    // 🔹 Ambil yang sedang aktif
    $API->write('/ppp/active/print');
    $actives = $API->read();
    $active_names = array_column($actives, 'name');

    $offline = [];
    foreach ($secrets as $s) {
        $name = $s['name'];
        if (!in_array($name, $active_names)) {
            $offline[] = [
                "name"        => $name,
                "profile"     => $s['profile'] ?? "-",
                "last_logout" => $s['last-logged-out'] ?? "-"
            ];
        }
    }

    $API->disconnect();

    // 🔹 Kalau semua online, kirim gambar lokal
    if (count($offline) === 0) {
        $imgPath = BASE_URL . "auth/undraw_connected_0xor.svg";
        $html = "
          <div style='text-align:center;padding:20px;'>
            <img src='{$imgPath}' alt='Semua Online' style='width:100px;height:100px;opacity:0.9;'>
            <p class='text-success fw-bold mt-2'>Semua user online</p>
          </div>
        ";
        echo json_encode(["online" => true, "html" => $html]);
        exit;
    }

    echo json_encode($offline);

} else {
    // 🔹 Gagal konek → pakai gambar lokal
    $imgPath = BASE_URL . "auth/undraw_server-down_lxs9.svg";
    $html = "
      <div style='text-align:center;padding:20px;'>
        <img src='{$imgPath}' alt='Mikrotik Error' style='width:100px;height:100px;opacity:0.8;'>
        <p class='text-danger fw-bold mt-2'>Tidak bisa konek ke Mikrotik</p>
      </div>
    ";
    echo json_encode(["error" => true, "html"  => $html]);
}
