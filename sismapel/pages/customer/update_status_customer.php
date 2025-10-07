<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";
$branch_id = $_SESSION['branch_id'];

$username = $_POST['username'];
$status   = $_POST['status']; // 1 aktif, 0 nonaktif

$sql = "
    SELECT c.paket_id, p.nama_paket 
    FROM customers c
    JOIN paket_langganan p ON c.paket_id = p.id
    WHERE c.ppp_secret = ?
    LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success"=>false,"message"=>"User tidak ditemukan di database"]);
    exit;
}
$row = $result->fetch_assoc();
$PROFILE_NORMAL = $row['nama_paket'];   // profile asli user dari paket_langganan
$PROFILE_ISOLIR = "Isolir";             // profile isolir di Mikrotik

$API = getMikrotikConnection($branch_id);


if ($API) {
    $secret = $API->comm("/ppp/secret/print", [ "?name" => $username ]);
    if(count($secret) > 0){
        $id = $secret[0]['.id'];
        $API->comm("/ppp/secret/set", [
            ".id" => $id,
            // "disabled" => ($status == 1 ? "no" : "yes"),
            "profile" => ($status == 1 ? $PROFILE_NORMAL : $PROFILE_ISOLIR),
            "comment" => ($status == 1 ? "" : "ISOLIR")
        ]);
    }

    // 🔑 Selalu remove active connection agar profile baru langsung berlaku
    $active = $API->comm("/ppp/active/print", [ "?name" => $username ]);
    if (count($active) > 0) {
        $active_id = $active[0]['.id'];
        $API->comm("/ppp/active/remove", [ ".id" => $active_id ]);
    }

    $API->disconnect();
}

// update DB
$stmt = $conn->prepare("UPDATE customers SET active_status=? WHERE ppp_secret=?");
$stmt->bind_param("is", $status, $username);
if($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Status berhasil diperbarui"]);
} else {
    echo json_encode(["success"=>false,"message"=>"Gagal update database"]);
}
