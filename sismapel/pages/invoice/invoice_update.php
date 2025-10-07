<?php
session_start();

include __DIR__ . "../../../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo $invoice_id     = $_POST['invoice_id'];
    echo $invoice_date   = $_POST['invoice_date'];
    echo $due_date       = $_POST['due_date'];
    echo $discount       = $_POST['discount']; // global discount %
    echo $tax            = $_POST['tax'];      // global tax %
    echo $payment_method = $_POST['payment_method'];
    echo $notes          = $_POST['notes'];
    echo $package_id     = $_POST['package_id'];

    // Update data invoice utama
    $sql_invoice = "UPDATE invoices 
                    SET invoice_date=?, due_date=?, total_discount=?, total_tax=?, payment_method=?, notes=?
                    WHERE id=?";
    $stmt = $conn->prepare($sql_invoice);
    $stmt->bind_param("ssddssi", $invoice_date, $due_date, $discount, $tax, $payment_method, $notes, $invoice_id);
    $stmt->execute();

    // Hapus semua item lama
    $sql_delete = "DELETE FROM invoice_items WHERE invoice_id=?";
    $stmt_del = $conn->prepare($sql_delete);
    $stmt_del->bind_param("i", $invoice_id);
    $stmt_del->execute();


    // $sql_invoice = "SELECT customer_id FROM invoices WHERE id=?";
    // $stmt_inv = $conn->prepare($sql_invoice);
    // $stmt_inv->bind_param("i", $invoice_id);
    // $stmt_inv->execute();

    // $sql_cus = "SELECT id FROM customers WHERE id=?";
    // $stmt_cus = $conn->prepare($sql_cus);
    // $stmt_cus->bind_param("i", $stmt_cus['customer_id']);
    // $stmt_cus->execute();

    // Insert ulang item dari form
    if (!empty($_POST['package_name'])) {
        foreach ($_POST['package_name'] as $key => $package) {
            // $item_id = $_POST['item_id'][$key];
            $description = $_POST['description'][$key];
            $qty         = $_POST['qty'][$key];
            $unit_price  = $_POST['unit_price'][$key];
            $subtotal    = $qty * $unit_price;

            $sql_item = "INSERT INTO invoice_items (invoice_id, package_id, description, qty, price)
                         VALUES (?, ?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);
            $stmt_item->bind_param("iisid", $invoice_id, $package_id, $description, $qty, $unit_price);
            $stmt_item->execute();
        }
    }

    // Update total invoice (pakai procedure yang sudah kita buat)
    $conn->query("CALL update_invoice_totals($invoice_id)");

    // Redirect balik ke halaman view
    header("Location: invoice?token=" . $_SESSION['csrf_token']);    exit();
}
?>
