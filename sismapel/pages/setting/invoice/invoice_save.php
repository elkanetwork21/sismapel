<?php

session_start();

$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];
$role_login = $_SESSION['role'];
include __DIR__ . "../../../../config.php";


$rekening = $conn->real_escape_string($_POST['rekening']);
$snk = $conn->real_escape_string($_POST['snk']);
$support = $conn->real_escape_string($_POST['support']);

$sql = "SELECT * FROM setting_invoice WHERE branch_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();
$setting = $result->fetch_assoc();

if ($setting) {

    $sql = "UPDATE setting_invoice SET rekening=?, syarat=?, support=? WHERE branch_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $rekening, $snk, $support, $branch_id);
    if ($stmt->execute()) {
        header("Location: invoice.php?msg=update&token=" . urlencode($_SESSION['csrf_token']));
        exit;
    };

} else {
    $sql = "INSERT INTO setting_invoice (branch_id, rekening, syarat, support) VALUES ($branch_id, '$rekening','$snk', '$support')";
    if ($conn->query($sql) === TRUE) {
        header("Location: invoice.php?msg=success&token=" . urlencode($_SESSION['csrf_token']));
    } else {
        header("Location: invoice.php?msg=error&token=" . urlencode($_SESSION['csrf_token']));
    }



}
