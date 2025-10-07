<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if(!$id || !in_array($status, ['aktif','nonaktif'])){
    $_SESSION['error'] = "Data tidak valid!";
    header("Location: invoice_temp?msg=error&token=" . urlencode($_SESSION['csrf_token']));
    exit;
}

// Update status
$stmt = $conn->prepare("UPDATE invoices_temp SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);

if($stmt->execute()){
    header("Location: invoice_temp?msg=success&token=" . urlencode($_SESSION['csrf_token']));
    exit;
} else {
    $err = urlencode($conn->error);
    header("Location: invoice_temp?msg=error&token=" . urlencode($_SESSION['csrf_token']));
    exit;
}
exit;
