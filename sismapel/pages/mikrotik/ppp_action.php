<?php

include __DIR__ . "../../../config.php";
require_once "mikrotik_connect.php";

header('Content-Type: application/json');

$branch_id = $_SESSION['branch_id'];


$API = getMikrotikConnection($branch_id);
$API->debug = false;

if ($API) {

    $action = $_POST['action'] ?? '';

    // Tambah Secret
    if ($action == "add") {
        $name     = $_POST['name'];
        $password = $_POST['password'];
        $service  = $_POST['service'];
        $profile  = $_POST['profile'];

        $API->comm("/ppp/secret/add", [
            "name" => $name,
            "password" => $password,
            "service" => $service,
            "profile" => $profile
        ]);
        $response = ["status"=>true, "message"=>"Secret berhasil ditambahkan"];
    }

    // Toggle Enable/Disable
    elseif ($action == "toggle") {
        $id = $_POST['id'];
        $status = $_POST['status'];
        $newStatus = ($status == 'true') ? 'no' : 'yes';
        $API->comm("/ppp/secret/set", [
            ".id" => $id,
            "disabled" => $newStatus
        ]);
        $response = ["status"=>true, "message"=>"Status secret berhasil diubah"];
    }

    // Delete Secret
    elseif ($action == "delete") {
        $id = $_POST['id'];
        $API->comm("/ppp/secret/remove", [".id"=>$id]);
        $response = ["status"=>true, "message"=>"Secret berhasil dihapus"];
    }

    $API->disconnect();
}

echo json_encode($response);
