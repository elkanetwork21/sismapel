<?php
session_start();
include __DIR__ . "../../../../config.php";

$branch_id = $_SESSION['branch_id'];
$interface_name = $_POST['interface_name'] ?? '';

if ($interface_name) {
    // cek apakah sudah ada default interface untuk branch ini
    $sql = "SELECT id FROM default_interfaces WHERE branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // sudah ada → update
        $sql = "UPDATE default_interfaces SET interface_name=? WHERE branch_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $interface_name, $branch_id);
    } else {
        // belum ada → insert baru
        $sql = "INSERT INTO default_interfaces (branch_id, interface_name) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $branch_id, $interface_name);
    }
    $stmt->execute();
}

header("Location: general.php?token=" . urlencode($_SESSION['csrf_token']));
exit();
?>
