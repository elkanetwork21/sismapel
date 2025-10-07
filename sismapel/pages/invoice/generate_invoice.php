<?php
session_start();

include __DIR__ . "/../../config.php";

$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];
// $role_login = $_SESSION['role'];


// Tentukan bulan & tahun sekarang
$current_month = date("m");
$current_year  = date("Y");

// Ambil semua customer + paket
$sql = "SELECT c.id AS customer_id, c.fullname, c.paket_id, p.harga_final, p.nama_paket AS paket_name 
        FROM customers c 
        JOIN paket_langganan p ON c.paket_id = p.id WHERE c.active_status=1 AND c.branch_id='$branch_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $created = 0;
    $skipped = 0;

    while($cust = $result->fetch_assoc()) {
        $customer_id = $cust['customer_id'];
        $paket_id    = $cust['paket_id'];
        $paket_name  = $cust['paket_name'];
        $paket_price = $cust['harga_final'];

        // Cek apakah sudah ada invoice bulan ini
        $check = $conn->prepare("SELECT id FROM invoices 
                                 WHERE customer_id = ? 
                                 AND MONTH(invoice_date) = ? 
                                 AND YEAR(invoice_date) = ?");
        $check->bind_param("iii", $customer_id, $current_month, $current_year);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $skipped++;
            continue; // sudah ada → lewati
        }

        // Data invoice baru
        $invoice_number = "INV-" . date("Ym") . "-" . $customer_id;
        $invoice_date   = date("Y-m-d");
        $due_date       = date("Y-m-d", strtotime("+9 days"));

        $subtotal = $paket_price;
        $discount = 0;
        $tax      = 0; // default pajak 0
        $total    = $subtotal - $discount + $tax;
        $payment_method = "";
        $status = "UNPAID";

        // Insert ke invoices
        $stmt = $conn->prepare("INSERT INTO invoices 
            (customer_id, branch_id, invoice_number, invoice_date, due_date, subtotal, total_discount, total_tax, grand_total, payment_method, status) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("iisssddddss", 
            $customer_id, 
            $branch_id,
            $invoice_number, 
            $invoice_date, 
            $due_date, 
            $subtotal, 
            $discount, 
            $tax, 
            $total, 
            $payment_method, 
            $status
        );

        if($stmt->execute()){
            $invoice_id = $stmt->insert_id;

            // Insert ke invoice_items (dengan package_id & tax default 0)
            $stmt_item = $conn->prepare("INSERT INTO invoice_items 
                (invoice_id, package_id, description, qty, price) 
                VALUES (?,?,?,?,?)");
            
            $qty = 1;
            $item_total = $paket_price; // karena pajak 0, total = price

            $stmt_item->bind_param("iisid", 
                $invoice_id, 
                $paket_id,
                $paket_name, 
                $qty, 
                $item_total
             );
            $stmt_item->execute();

            $created++;


            // Update Status Pembayaran Customer menjadi 0
            // $stmt_pembayaran = $conn->prepare("UPDATE customers SET payment_status=0 WHERE branch_id=$branch_id AND active_status=1");
            // $stmt_pembayaran->execute();
        }
    }

    echo "<script>alert('Invoice berhasil digenerate! Baru: $created, Dilewati: $skipped'); window.location='invoice?token={$_SESSION['csrf_token']}';</script>";
} else {
    echo "<script>alert('Tidak ada customer yang ditemukan.'); window.location='invoice?token={$_SESSION['csrf_token']}';</script>";
}
