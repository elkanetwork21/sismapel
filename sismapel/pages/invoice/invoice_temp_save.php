<?php
session_start();


$branch_id = $_SESSION['branch_id'];

include __DIR__ . "/../../config.php";

$customer_id = $_POST['customer'];
$invoice_date = $_POST['invoice_date'];
$payment_method = $_POST['method_id'] ?? '';?><br><?php
$notes = $_POST['notes'];
$total    = $_POST['jumlah'];
// $method_id      = $_POST['method_id'] ?? '';?><br><?php
$account_id     = $_POST['account_id'] ?? 0 ;?><br><?php


$invoice_number = "INV-TEMP-" . date("Ym") . "-" . $customer_id;
$status = "AKTIF";


$stmt = $conn->prepare("INSERT INTO invoices_temp
    (customer_id, branch_id, invoice_number, invoice_date, grand_total, payment_method, account_id, status) VALUES (?,?,?,?,?,?,?,?)");
$stmt->bind_param("iissdiis", 
    $customer_id, 
    $branch_id,
    $invoice_number, 
    $invoice_date, 
    $total, 
    $payment_method, 
    $account_id,
    $status
);
if($stmt->execute()){


echo "<script>
    alert('Invoice berhasil dibuat !'); 
    window.location='invoice_temp.php?token=" . $_SESSION['csrf_token'] . "';
</script>";

}

?>