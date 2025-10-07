<?php
session_start();
include __DIR__ . "../../../config.php";
// include BASE_PATH . "includes/auth_check.php"; 

$branch_id = $_SESSION['branch_id'];

// Ambil data dari POST
$customer_id = intval($_POST['customer']);
$invoice_date = $_POST['invoice_date'];
$due_date = $_POST['due_date'];
$discount = floatval($_POST['discount']);
$tax = floatval($_POST['tax']);
$payment_method = intval($_POST['payment_method']);
$notes = $_POST['notes'];

$invoice_number = "INV-" . date("Ym") . "-" . $customer_id;


// Item arrays
$packages = $_POST['package'] ?? [];
$descriptions = $_POST['description'] ?? [];
$qtys = $_POST['qty'] ?? [];
$unit_prices = $_POST['unit_price'] ?? [];
$subtotals = $_POST['subtotal'] ?? [];

// Hitung subtotal & grand total
$subtotal_total = array_sum($subtotals);
$grand_total = ($subtotal_total - $discount) * (1 + $tax/100);

// Insert invoice header
$stmt = $conn->prepare("
    INSERT INTO invoices (branch_id, customer_id, invoice_number, invoice_date, due_date, total_discount, total_tax, subtotal, grand_total, payment_method, notes, status)
    VALUES (?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, 'unpaid')
");

$stmt->bind_param("iisssddddis",
    $branch_id,
    $customer_id,
    $invoice_number,
    $invoice_date,
    $due_date,
    $discount,
    $tax,
    $subtotal_total,
    $grand_total,
    $payment_method,
    $notes
);

if($stmt->execute()){
    $invoice_id = $stmt->insert_id;

    // Insert items
    $stmt_item = $conn->prepare("
        INSERT INTO invoice_items (invoice_id, package_id, description, qty, price)
        VALUES (?, ?, ?, ?, ?)
    ");

    for($i=0; $i<count($descriptions); $i++){
        $pkg_id = intval($packages[$i] ?? 0);
        $desc = $descriptions[$i];
        $qty = floatval($qtys[$i]);
        $price = floatval($unit_prices[$i]);
        $sub = floatval($subtotals[$i]);

        $stmt_item->bind_param("iisdd", $invoice_id, $pkg_id, $desc, $qty, $price);
        $stmt_item->execute();
    }

    $stmt_item->close();
    $stmt->close();

    // Redirect ke detail invoice
    header("Location: invoice_detail.php?id=$invoice_id&msg=created&token=" . $_SESSION['csrf_token']);
    exit;

} else {
    echo "Terjadi kesalahan: " . $stmt->error;
}
?>
