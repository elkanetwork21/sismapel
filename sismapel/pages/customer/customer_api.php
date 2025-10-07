<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

header('Content-Type: application/json');

$branch_id = $_SESSION['branch_id'];
$API = getMikrotikConnection($branch_id);


$action = $_GET['action'] ?? '';


// CREATE customer 
// ==================================================
if ($action == 'add') {
    $ppp_secret = $_POST['ppp_secret'] ?? '';
    $fullname   = $_POST['fullname'] ?? '';
    $address    = $_POST['address'] ?? '';
    $phone      = $_POST['phone'] ?? '';
    $email      = $_POST['email'] ?? '';
    $paket_id   = $_POST['paket_id'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $lat        = $_POST['latitude'] ?? '';
    $long       = $_POST['longitude'] ?? '';
    $odp_id     = $_POST['odp_id'] ?? '';

    $payment_status = '1';
    $active_status  = '1';

    // cek available port
    $cek = $conn->query("SELECT available_port FROM distribusi WHERE id = '$odp_id'")->fetch_assoc();

    if ($cek && $cek['available_port'] > 0) {
        $ok = $conn->query("INSERT INTO customers 
            (ppp_secret, fullname, address, phone, email, paket_id, keterangan, latitude, longitude, payment_status, active_status, odp_id, branch_id)  
            VALUES 
            ('$ppp_secret', '$fullname', '$address', '$phone', '$email', '$paket_id', '$keterangan', '$lat', '$long', '$payment_status', '$active_status', '$odp_id', '$branch_id')");

        if ($ok) {
            // kurangi available port
            $conn->query("UPDATE distribusi SET available_port = available_port - 1 WHERE id = '$odp_id'");
            echo json_encode(["success"=>true,"message"=>"Customer berhasil ditambah"]);
        } else {
            echo json_encode(["success"=>false,"message"=>"Gagal simpan customer"]);
        }
    } else {
        echo json_encode(["success"=>false,"message"=>"Port ODP penuh"]);
    }
    exit;
}

// Load table
if ($action == 'read') {
    $sql = "
        SELECT 
            c.id, 
            c.fullname, 
            c.ppp_secret, 
            c.address, 
            c.phone, 
            c.email,
            c.odp_id,
            c.paket_id,
            p.nama_paket
        FROM customers c
        LEFT JOIN paket_langganan p 
            ON c.paket_id = p.id
        WHERE c.branch_id = '$branch_id'
    ";
    $result = $conn->query($sql);

    // Connect Mikrotik
    if ($API) {
        // Ambil user aktif
        $activeUsers = $API->comm("/ppp/active/print");
        $onlineList = array_column($activeUsers, 'name');

        // Ambil secret (cek isolir)
        $secrets = $API->comm("/ppp/secret/print");
        $secretStatus = [];
        foreach ($secrets as $s) {
            $secretStatus[$s['name']] = (
                (isset($s['profile']) && strtolower($s['profile']) == "isolir") ||
                (isset($s['comment']) && strtolower($s['comment']) == "isolir")
            ) ? "isolir" : "aktif";
        }

        $API->disconnect();
    } else {
        $onlineList   = [];
        $secretStatus = [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        // Status Online
        $row['status'] = in_array($row['ppp_secret'], $onlineList) ? "Online" : "Offline";

        // Status Isolir
        $row['isolir'] = $secretStatus[$row['ppp_secret']] ?? "aktif";

        $rows[] = $row;
    }

    echo json_encode(["data" => $rows]);
    exit;
}



if($action == "update"){
    $id       = $_POST['id'] ?? '';
    $fullname     = $_POST['fullname'];
    $paket_id    = $_POST['paket'];
    $address   = $_POST['address'];
    $phone    = $_POST['phone'];
    $email    = $_POST['email'];

    
        $sql = "UPDATE customers SET fullname='$fullname', address='$address', phone='$phone', email='$email',paket_id='$paket_id' WHERE id=$id";
        $ok = $conn->query($sql);
        echo json_encode(["success"=>$ok,"message"=>$ok?"Data berhasil diupdate":"Gagal update"]); exit;
    
}

// Delete pelanggan
if($action == "delete"){
    $id = $_POST['id'];
    $odp_id = $_POST['odp_id'];
    $ok = $conn->query("DELETE FROM customers WHERE id=$id");

    $conn->query("UPDATE distribusi SET available_port = available_port + 1 WHERE id = '$odp_id'");
    echo json_encode(["success"=>$ok,"message"=>$ok?"Data dihapus":"Gagal hapus"]); exit;
}

// Togle untuk isolir
if($action == "toggle"){
    $id     = $_POST['id'];
    $status = $_POST['status'];
    $ok = $conn->query("UPDATE customers SET active_status=$status WHERE id=$id");
    echo json_encode(["success"=>$ok,"message"=>$ok?"Status isolir diubah":"Gagal update"]); exit;
}

// Ambil paket untuk dropdown edit user

if($action == "paket"){
    $result = $conn->query("SELECT id, nama_paket FROM paket_langganan ORDER BY nama_paket ASC");
    $rows = [];
    while($row = $result->fetch_assoc()){
        $rows[] = $row;
    }
    echo json_encode(["success"=>true,"data"=>$rows]);
    exit;
}


// Summary Total Pelanggan, Isolir, Offline

if ($action == "summary") {
    // Hitung total customer
    $totalCustomer = $conn->query("SELECT COUNT(*) as total FROM customers WHERE branch_id=$branch_id")->fetch_assoc()['total'];

    $isolirCount = 0;
    $offlineCount = 0;

    if ($API) {
        // Ambil user aktif
        $activeUsers = $API->comm("/ppp/active/print");
        $onlineList = array_column($activeUsers, 'name');

        // Ambil secret (cek isolir)
        $secrets = $API->comm("/ppp/secret/print");
        $secretStatus = [];
        foreach ($secrets as $s) {
            $secretStatus[$s['name']] = (
                (isset($s['profile']) && strtolower($s['profile']) == "isolir") ||
                (isset($s['comment']) && strtolower($s['comment']) == "isolir")
            ) ? "isolir" : "aktif";
        }

        // Ambil semua customer untuk cek isolir & offline
        $cust = $conn->query("SELECT ppp_secret FROM customers WHERE branch_id=$branch_id");
        while ($c = $cust->fetch_assoc()) {
            $secret = $c['ppp_secret'];
            // Isolir
            if (isset($secretStatus[$secret]) && $secretStatus[$secret] == "isolir") {
                $isolirCount++;
            }
            // Offline (tidak ada di active list)
            if (!in_array($secret, $onlineList)) {
                $offlineCount++;
            }
        }

        $API->disconnect();
    }

    echo json_encode([
        "success" => true,
        "total"   => $totalCustomer,
        "isolir"  => $isolirCount,
        "offline" => $offlineCount
    ]);
    exit;
}

if($API = getMikrotikConnection($branch_id)) {
    $pppSecrets = $API->comm("/ppp/secret/print");
    $API->disconnect();

    $data = [];
    foreach ($pppSecrets as $s) {
        $data[] = [
            "name"    => $s['name'],
            "service" => $s['service'] ?? '-'
        ];
    }
    echo json_encode(["success"=>true,"data"=>$data]);
    exit;
}



echo json_encode(["success"=>false,"message"=>"Invalid action"]);
