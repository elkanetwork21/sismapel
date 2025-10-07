<?php

session_start();

include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "includes/security_helper.php";

// Ambil ID invoice
if (!isset($_GET['id'])) {
    die("Invoice ID tidak ditemukan.");
}
$invoice_id = validate_secure_id($_GET['id']); // decode
if ($invoice_id === false) {
    die("Data tidak ditemukan / ID tidak valid");
}
// $invoice_id = $_GET['id'];

// Ambil data invoice
$sql = "SELECT i.*, c.fullname as customer_name 
FROM invoices i 
JOIN customers c ON i.customer_id = c.id 
WHERE i.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

// Ambil data invoice items
$sql_items = "SELECT ii.*, p.nama_paket as package_name 
FROM invoice_items ii
LEFT JOIN paket_langganan p ON ii.package_id = p.id
WHERE ii.invoice_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $invoice_id);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>
<!doctype html>
<html lang="en">
<!--begin::Head-->
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>AdminLTE v4 | Dashboard</title>
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

  <title>Edit Invoice</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script>
    function calculateTotals() {
        let rows = document.querySelectorAll("#itemsTable tbody tr");
        let subtotal = 0;

        rows.forEach((row) => {
            let qty = parseFloat(row.querySelector("input[name='qty[]']").value) || 0;
            let unit_price = parseFloat(row.querySelector("input[name='unit_price[]']").value) || 0;
            let sub = qty * unit_price;
            row.querySelector("input[name='subtotal[]']").value = sub.toFixed(2);
            subtotal += sub;
        });

        let discount = parseFloat(document.querySelector("input[name='discount']").value) || 0;
        let tax = parseFloat(document.querySelector("input[name='tax']").value) || 0;

        let afterDiscount = subtotal -  discount;
        let afterTax = afterDiscount + (afterDiscount * tax / 100);

        document.getElementById("subtotal_display").innerText = subtotal.toFixed(2);
        document.getElementById("grand_total_display").innerText = afterTax.toFixed(2);
    }

    function addRow() {
        let tbody = document.querySelector("#itemsTable tbody");
        let newRow = document.createElement("tr");

        newRow.innerHTML = `
            <td>
                <input type="hidden" name="item_id[]" value="new">
                <input type="text" name="package_name[]" class="form-control" placeholder="Package">
            </td>
            <td><input type="text" name="description[]" class="form-control" placeholder="Description"></td>
            <td><input type="number" name="qty[]" class="form-control" value="1"></td>
            <td><input type="number" step="0.01" name="unit_price[]" class="form-control" value="0"></td>
            <td>
                <div class="d-flex">
                    <input type="number" step="0.01" name="subtotal[]" class="form-control" value="0" readonly>
                    <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)">X</button>
                </div>
            </td>
        `;
        tbody.appendChild(newRow);
        bindInputs(newRow);
        calculateTotals();
    }

    function removeRow(btn) {
        btn.closest("tr").remove();
        calculateTotals();
    }

    function bindInputs(row) {
        row.querySelectorAll("input").forEach(input => {
            input.addEventListener("input", calculateTotals);
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        calculateTotals();
        document.querySelectorAll("#itemsTable tbody tr").forEach(bindInputs);
        document.querySelectorAll("input[name='discount'], input[name='tax']").forEach(input => {
            input.addEventListener("input", calculateTotals);
        });
    });
</script>

<style>
    body { font-family: 'Poppins', sans-serif !important; background:#f5f7fa; }
    /* Overlay background */
    #loading {
      position: fixed;
      width: 100%;
      height: 100%;
      background: #fff;
      top: 0;
      left: 0;
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
  }

  /* Spinner animasi */
  .spinner {
      width: 60px;
      height: 60px;
      border: 6px solid #f3f3f3;
      border-top: 6px solid #3498db;
      border-radius: 50%;
      animation: spin 1s linear infinite;
  }

  @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
  }
</style>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">



</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
 <!--  <div id="loading">
    <div class="spinner"></div>
</div> -->
<!--begin::App Main-->
<main class="app-main">
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
          <?php include BASE_PATH . "includes/breadcrumb.php"; ?>
      </div>
      <div class="container mt-4">

          <div class="card">
            <div class="card-header">
                <h3>Edit Invoice #<?= $invoice['invoice_number']; ?></h3>
            </div>
            <div class="card-body">
                <form method="post" action="invoice_update.php">
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
                            <label>Discount (Rp)</label>
                            <input type="number" step="0.01" name="discount" class="form-control" value="<?= $invoice['total_discount']; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Tax (%)</label>
                            <input type="number" step="0.01" name="tax" class="form-control" value="<?= $invoice['total_tax']; ?>">
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
                    <table class="table table-bordered" id="itemsTable">
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
                                        <input type="hidden" name="package_id" value="<?= $item['package_id']; ?>">
                                        <input type="text" name="package_name[]" class="form-control" value="<?= $item['package_name']; ?>">
                                    </td>
                                    <td><input type="text" name="description[]" class="form-control" value="<?= $item['description']; ?>"></td>
                                    <td><input type="number" name="qty[]" class="form-control" value="<?= $item['qty']; ?>"></td>
                                    <td><input type="number" step="0.01" name="unit_price[]" class="form-control" value="<?= $item['price']; ?>"></td>
                                    <td>
                                        <div class="d-flex">
                                            <input type="number" step="0.01" name="subtotal[]" class="form-control" value="<?= $item['price']*$item['qty']; ?>" readonly>
                                            <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)">X</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-outline-primary" onclick="addRow()">+ Tambah Item</button>

                    <div class="row text-end mb-4">

                            <h6>Subtotal : <span id="subtotal_display">0.00</span></h6>
                            <h3>Grand Total : <span id="grand_total_display"> 0.00</span></h3>
                    </div>

                    <div class="text-end">
                        
                    

                    <button type="submit" class="btn btn-outline-primary"><span class="bi bi-save"></span> Update Invoice</button>
                    <a href="invoice?token=<?php echo $_SESSION['csrf_token']?>" class="btn btn-outline-primary"><span class="bi bi-arrow-counterclockwise"></span> Cancel</a>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Saat halaman selesai load, sembunyikan loading
  window.addEventListener("load", function(){
    document.getElementById("loading").style.display = "none";
    document.getElementById("content").style.display = "block";
});
</script>

<?php include BASE_PATH . "includes/footer.php"; //  ?>