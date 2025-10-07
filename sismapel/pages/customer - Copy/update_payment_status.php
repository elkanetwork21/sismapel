<?php
include __DIR__ . "../../../config.php";

header('Content-Type: application/json');

try {
    // reset semua payment_status = 0
    $conn->query("UPDATE customers SET payment_status = 0");

    // update payment_status = 1 kalau ada pembayaran bulan ini
    $conn->query("
      UPDATE customers c
        JOIN (
          SELECT i.customer_id
          FROM invoices i
          JOIN payments p ON p.invoice_id = i.invoice_number
          WHERE MONTH(p.created_at) = MONTH(CURDATE())
          AND YEAR(p.created_at) = YEAR(CURDATE())
          GROUP BY i.customer_id
        ) t ON c.id = t.customer_id
      SET c.payment_status = 1
    ");

    echo json_encode(["success"=>true,"message"=>"Payment status updated"]);
} catch (Exception $e) {
    echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
}
