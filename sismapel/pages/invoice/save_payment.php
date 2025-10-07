<?php
session_start();
include __DIR__ . "../../../config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    echo $invoice_number = $_POST['invoice_id'] ?? '';?><br><?php
    echo $amount         = $_POST['amount'] ?? 0;?><br><?php
    echo $method_id      = $_POST['method_id'] ?? '';?><br><?php
    echo $account_id     = $_POST['account_id'] ?? null;?><br><?php
    echo $note           = $_POST['note'] ?? null;?><br><?php
    echo $branch_id      = $_SESSION['branch_id'] ?? 0;?><br><?php

    if (!$invoice_number || !$amount || !$method_id) {
        die("Data tidak lengkap.");
    }

    // ambil invoice untuk validasi
    $stmt = $conn->prepare("SELECT customer_id, id, grand_total FROM invoices WHERE invoice_number=? AND branch_id=?");
    $stmt->bind_param("si", $invoice_number, $branch_id);
    $stmt->execute();
    $invoice = $stmt->get_result()->fetch_assoc();

    if (!$invoice) {
        die("Invoice tidak ditemukan.");
    }

    // simpan pembayaran
    $stmt = $conn->prepare("
        INSERT INTO payments (invoice_id, branch_id, amount, method_id, account_id, note, created_at)
        VALUES (?, ?, ?, ?, ?,?, NOW())
        ");
    $stmt->bind_param(
        "sidiis",
        $invoice_number,
        $branch_id,
        $amount,
        $method_id,
        $account_id,
        $note
    );

    if ($stmt->execute()) {
        // update data customer


        $sql = "SELECT 
        c.ppp_secret, c.id
        FROM customers c
        JOIN invoices i on c.id = i.customer_id
        WHERE i.invoice_number = ?
        LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $invoice_number);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();
        $id = $row['id'];
        $ppp_secret = $row['ppp_secret'];            

        $stmt = $conn->prepare("UPDATE customers SET active_status=1, payment_status=1 WHERE id=$id");
        $stmt->execute();


        // trigger di database akan otomatis update status invoice
header("Location: invoice?id=" . $invoice_number . "&msg=success&token=" . $_SESSION['csrf_token']);        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
