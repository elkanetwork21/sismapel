<?php
include "../config.php";

// Ambil ID invoice
if (!isset($_GET['id'])) {
    die("Invoice ID tidak ditemukan.");
}
$invoice_id = $_GET['id'];

// Ambil data invoice
$sql = "SELECT i.*, c.name as customer_name 
        FROM invoices i 
        JOIN customers c ON i.customer_id = c.id 
        WHERE i.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

// Ambil data invoice items
$sql_items = "SELECT ii.*, p.name as package_name 
              FROM invoice_items ii
              LEFT JOIN packages p ON ii.package_id = p.id
              WHERE ii.invoice_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $invoice_id);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Invoice</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

<h3>Edit Invoice #<?= $invoice['invoice_number']; ?></h3>
<form method="post" action="update_invoice.php">
    <input type="hidden" name="invoice_id" value="<?= $invoice['id']; ?>">

    <div class="mb-3">
        <label>Customer</label>
        <input type="text" class="form-control" value="<?= $invoice['customer_name']; ?>" disabled>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Invoice Date</label>
            <input type="date" name="invoice_date" class="form-control" value="<?= $invoice['invoice_date']; ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label>Due Date</label>
            <input type="date" name="due_date" class="form-control" value="<?= $invoice['due_date']; ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label>Discount (%)</label>
            <input type="number" step="0.01" name="discount" class="form-control" value="<?= $invoice['discount']; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label>Tax (%)</label>
            <input type="number" step="0.01" name="tax" class="form-control" value="<?= $invoice['tax']; ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label>Payment Method</label>
            <select name="payment_method" class="form-control">
                <option value="cash" <?= $invoice['payment_method']=='cash'?'selected':''; ?>>Cash</option>
                <option value="transfer" <?= $invoice['payment_method']=='transfer'?'selected':''; ?>>Transfer</option>
                <option value="ewallet" <?= $invoice['payment_method']=='ewallet'?'selected':''; ?>>E-Wallet</option>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label>Notes</label>
        <textarea name="notes" class="form-control"><?= $invoice['notes']; ?></textarea>
    </div>

    <h5>Invoice Items</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Package</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()) { ?>
            <tr>
                <td>
                    <input type="hidden" name="item_id[]" value="<?= $item['id']; ?>">
                    <input type="text" class="form-control" value="<?= $item['package_name']; ?>" disabled>
                </td>
                <td><input type="text" name="description[]" class="form-control" value="<?= $item['description']; ?>"></td>
                <td><input type="number" name="qty[]" class="form-control" value="<?= $item['qty']; ?>"></td>
                <td><input type="number" step="0.01" name="unit_price[]" class="form-control" value="<?= $item['unit_price']; ?>"></td>
                <td><input type="number" step="0.01" name="subtotal[]" class="form-control" value="<?= $item['subtotal']; ?>" readonly></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <button type="submit" class="btn btn-primary">Update Invoice</button>
    <a href="invoices.php" class="btn btn-secondary">Cancel</a>
</form>

</body>
</html>
