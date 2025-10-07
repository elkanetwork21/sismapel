<?php
session_start();
$branch_id = $_SESSION['branch_id'];

include __DIR__ . "../../../../config.php";

if (isset($_POST['role_name'])) {
    $role_name = trim($_POST['role_name']);

    // cek duplikat
    $check = $conn->prepare("SELECT id FROM roles WHERE role_name=?");
    $check->bind_param("s", $role_name);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO roles (role_name) VALUES (?)");
        $stmt->bind_param("s", $role_name);
        $stmt->execute();
    }

    header("Location: role_manage.php?msg=role_added&token=" . urlencode($_SESSION['csrf_token'])); 
    exit;
}
