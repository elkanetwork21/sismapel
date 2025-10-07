<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

if (!isset($_GET['file'])) {
    die("File tidak ditemukan");
}

$filename = $_GET['file'];
$branch_id = $_SESSION['branch_id'];

$API = getMikrotikConnection($branch_id);
if ($API) {
    // cari ID file
    $file = $API->comm("/file/print", [".proplist" => ".id", "?name" => $filename]);
    if (!empty($file[0]['.id'])) {
        $API->comm("/file/remove", [".id" => $file[0]['.id']]);
    }
    $API->disconnect();
}

header("Location: backup?token=" . $_SESSION['csrf_token']);
exit();
