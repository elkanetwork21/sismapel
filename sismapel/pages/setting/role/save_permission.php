<?php
session_start();
$branch_id = $_SESSION['branch_id'];

include __DIR__ . "../../../../config.php";

$role_id = $_POST['role_id'];
$pages   = isset($_POST['pages']) ? $_POST['pages'] : [];

// hapus dulu semua izin lama
$conn->query("DELETE FROM role_permissions WHERE role_id=$role_id");

// insert ulang
foreach ($pages as $p) {
    $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, page) VALUES (?,?)");
    $stmt->bind_param("is", $role_id, $p);
    $stmt->execute();
}

header("Location: role_manage.php?role_id=".$role_id."&msg=saved&token=" . urlencode($_SESSION['csrf_token']));
exit;
