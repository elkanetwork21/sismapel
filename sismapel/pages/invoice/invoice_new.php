<?php

session_start();

include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";
$branch_id = $_SESSION['branch_id']; // otomatis ambil dari session


// Ambil data invoice
$sql = "SELECT i.*, c.fullname as customer_name 
FROM invoices i 
JOIN customers c ON i.customer_id = c.id 
WHERE i.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();






?>
<!doctype html>
<html lang="en">
<!--begin::Head-->
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Sistem Managemen Pelanggan Terintegrasi</title>
  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <!--end::Accessibility Meta Tags-->
  <!--begin::Primary Meta Tags-->
  <meta name="title" content="AdminLTE v4 | Dashboard" />
  <meta name="author" content="ColorlibHQ" />
  <meta
  name="description"
  content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance."
  />
  <meta
  name="keywords"
  content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant"
  />
  <!--end::Primary Meta Tags-->
  <!--begin::Accessibility Features-->
  <!-- Skip links will be dynamically added by accessibility.js -->
  <meta name="supported-color-schemes" content="light dark" />
  <link rel="preload" href="<?php echo BASE_URL; ?>css/adminlte.css" as="style" />
  <!--end::Accessibility Features-->
  <!--begin::Fonts-->
  <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
  integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
  crossorigin="anonymous"
  media="print"
  onload="this.media='all'"
  />
  <!--end::Fonts-->
  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
  crossorigin="anonymous"
  />
  <!--end::Third Party Plugin(OverlayScrollbars)-->
  <!--begin::Third Party Plugin(Bootstrap Icons)-->
  <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
  crossorigin="anonymous"
  />
  <!--end::Third Party Plugin(Bootstrap Icons)-->
  <!--begin::Required Plugin(AdminLTE)-->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css" />
  <!--end::Required Plugin(AdminLTE)-->
  <!-- apexcharts -->
  <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
  integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
  crossorigin="anonymous"
  />
  <!-- jsvectormap -->
  <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
  integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
  crossorigin="anonymous"
  />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <title><?= $invoice ? "Edit Invoice" : "New Invoice" ?></title>

<!-- Bootstrap & DataTables -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
  .select2-container .select2-selection--single {
    height: 38px !important;  /* biar sejajar dengan input bootstrap */
    padding: 6px 12px;
    background: white;
  }
</style>

<style>
body { font-family: 'Poppins', sans-serif; background: #f5f7fa; }
.table th, .table td { vertical-align: middle; }
#itemsTable input { width: 100%; }
.card-header h3 { margin: 0; }
.text-end h6, .text-end h3 { margin: 0.2rem 0; }
.btn-add-item { margin-bottom: 1rem; }
.card-header {
      background: linear-gradient(45deg, #007bff, #00bcd4);
      color: #fff;
      border-radius: 16px 16px 0 0;
</style>

<script>
function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll("#itemsTable tbody tr").forEach(row => {
        const qty = parseFloat(row.querySelector("input[name='qty[]']").value) || 0;
        const price = parseFloat(row.querySelector("input[name='unit_price[]']").value) || 0;
        const sub = qty * price;
        row.querySelector("input[name='subtotal[]']").value = sub.toFixed(2);
        subtotal += sub;
    });
    const discount = parseFloat(document.querySelector("input[name='discount']").value) || 0;
    const tax = parseFloat(document.querySelector("input[name='tax']").value) || 0;

    const afterDiscount = subtotal - discount;
    const afterTax = afterDiscount + (afterDiscount * tax / 100);

    document.getElementById("subtotal_display").innerText = subtotal.toLocaleString('id-ID', {minimumFractionDigits:2});
    document.getElementById("grand_total_display").innerText = afterTax.toLocaleString('id-ID', {minimumFractionDigits:2});
}

function addRow(packageList) {
    const tbody = document.querySelector("#itemsTable tbody");
    const tr = document.createElement("tr");
    tr.innerHTML = `
        <td>
            <select name="package[]" class="form-control package-select">
                <option value="">-- Pilih --</option>
                ${packageList.map(p => `<option value="${p.id}">${p.nama_paket}</option>`).join('')}
            </select>
        </td>
        <td><input type="text" name="description[]" class="form-control"></td>
        <td><input type="number" name="qty[]" class="form-control" value="1"></td>
        <td><input type="number" step="0.01" name="unit_price[]" class="form-control"></td>
        <td><input type="number" step="0.01" name="subtotal[]" class="form-control" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="bi bi-x"></i></button></td>
    `;
    tbody.appendChild(tr);
    bindRowEvents(tr);
    calculateTotals();
}

function removeRow(btn) {
    btn.closest("tr").remove();
    calculateTotals();
}

function bindRowEvents(row) {
    row.querySelectorAll("input, select").forEach(input => {
        input.addEventListener("input", calculateTotals);
    });

    // AJAX fetch harga saat package dipilih
    const select = row.querySelector(".package-select");
    select?.addEventListener("change", function() {
        const id = this.value;
        const priceInput = row.querySelector("input[name='unit_price[]']");
        if (!id) { priceInput.value = ''; calculateTotals(); return; }
        fetch("get_price.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + id
        })
        .then(res => res.text())
        .then(price => { priceInput.value = parseFloat(price).toFixed(2); calculateTotals(); });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    calculateTotals();
    document.querySelectorAll("#itemsTable tbody tr").forEach(bindRowEvents);
    document.querySelectorAll("input[name='discount'], input[name='tax']").forEach(input => {
        input.addEventListener("input", calculateTotals);
    });

    // Load package list
    window.packageList = <?php
        $packages = $conn->query("SELECT id, nama_paket FROM paket_langganan WHERE branch_id='$branch_id'");
        $arr = [];
        while($p = $packages->fetch_assoc()) $arr[] = $p;
        echo json_encode($arr);
    ?>;

    // Add initial row if none
    if(document.querySelectorAll("#itemsTable tbody tr").length == 0) addRow(window.packageList);
});
</script>


</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <main class="app-main mt-4">
    <div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><?= $invoice ? "Edit Invoice" : "New Invoice" ?></h4>
        </div>
        <div class="card-body">
            <form method="post" action="invoice_new_save.php">
                <div class="mb-3">
    <label>Customer</label>
    <select class="form-control" id="customer" name="customer" required>
        <option value="">-- Pilih --</option>
        <?php
        $cust = $conn->query("SELECT * FROM customers WHERE branch_id='$branch_id'");
        while($cst = $cust->fetch_assoc()){
            $sel = ($invoice && $invoice['customer_id']==$cst['id']) ? "selected" : "";
            echo "<option value='{$cst['id']}' $sel>{$cst['fullname']}</option>";
        }
        ?>
    </select>
</div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Invoice Date</label>
                        <input type="date" name="invoice_date" class="form-control" value="<?= $invoice['invoice_date'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="<?= $invoice['due_date'] ?? '' ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Discount (Rp)</label>
                        <input type="number" step="0.01" name="discount" class="form-control" value="<?= $invoice['total_discount'] ?? 0 ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Tax (%)</label>
                        <input type="number" step="0.01" name="tax" class="form-control" value="<?= $invoice['total_tax'] ?? 0 ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="">-- Pilih --</option>
                            <?php
                            $pm = $conn->query("SELECT * FROM payment_methods WHERE branch_id='$branch_id'");
                            while($paym = $pm->fetch_assoc()){
                                $sel = ($invoice && $invoice['payment_method']==$paym['id']) ? "selected" : "";
                                echo "<option value='{$paym['id']}' $sel>{$paym['nama_metode']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control"><?= $invoice['notes'] ?? '' ?></textarea>
                </div>

                <h5>Invoice Items</h5>
                <button type="button" class="btn btn-success btn-sm btn-add-item" onclick="addRow(window.packageList)">
                    <i class="bi bi-plus-circle"></i> Add Item
                </button>
                          <div class="table-responsive">

                <table class="table table-bordered" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Package</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows akan diisi JS -->
                    </tbody>
                </table>
                          <div class="table-responsive">


                <div class="row text-end mb-4">
                    <h6>Subtotal: <span id="subtotal_display">0.00</span></h6>
                    <h3>Grand Total: <span id="grand_total_display">0.00</span></h3>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Submit</button>
                    <a href="invoice?token=<?php echo $_SESSION['csrf_token']?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#customer').select2({
        placeholder: "-- Pilih Customer --",
        allowClear: true,
        width: '100%'
    });
});
</script>


<?php include BASE_PATH . "includes/footer.php"; //  ?>