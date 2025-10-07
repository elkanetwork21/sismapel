<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "includes/security_helper.php"; 

// echo $invoice_id = validate_secure_id($_GET['invoice']); // decode
// if ($invoice_id === false) {
//     die("Data tidak ditemukan / ID tidak valid");
// }
$invoice_id = $_GET['invoice'] ?? "";
$branch_id  = $_SESSION['branch_id'];

// ambil data invoice
$stmt = $conn->prepare("SELECT * FROM invoices WHERE invoice_number=? AND branch_id=?");
$stmt->bind_param("si", $invoice_id, $branch_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
  die("<div class='alert alert-danger m-4'>❌ Invoice tidak ditemukan</div>");
}

// total pembayaran yang sudah masuk
$stmt_paid = $conn->prepare("SELECT SUM(amount) as total_paid FROM payments WHERE invoice_id=?");
$stmt_paid->bind_param("s", $invoice['invoice_number']);
$stmt_paid->execute();
$result_paid = $stmt_paid->get_result()->fetch_assoc();
$total_paid = $result_paid['total_paid'] ?? 0;

// sisa tagihan
$remaining = max(0, $invoice['grand_total'] - $total_paid);
?>

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

  <style>
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

  <style>
    body {
      font-family: 'Poppins', sans-serif !important;
      background: #f5f7fa;
    }
  </style>



  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">


  
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<main class="app-main">
  <div class="container mt-4">
    <div class="card shadow-lg">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-credit-card-2-front me-2"></i>Pembayaran Invoice #<?= htmlspecialchars($invoice_id) ?></h4>
        <span class="badge bg-light text-dark">Customer: <?= htmlspecialchars($invoice['customer_id']) ?></span>
      </div>
      <div class="card-body">
        
        <!-- Info Tagihan -->
        <div class="alert alert-info">
          <p class="mb-1">Total Invoice: <b>Rp <?= number_format($invoice['grand_total'], 0, ',', '.') ?></b></p>
          <p class="mb-1">Sudah Dibayar: <b>Rp <?= number_format($total_paid, 0, ',', '.') ?></b></p>
          <p class="mb-0">Sisa Tagihan: <b class="text-danger">Rp <?= number_format($remaining, 0, ',', '.') ?></b></p>
        </div>

        <!-- Form Pembayaran -->
        <form action="save_payment.php" method="POST" id="paymentForm">
          <input type="hidden" name="invoice_id" value="<?= htmlspecialchars($invoice_id) ?>">

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Jumlah Bayar</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" class="form-control" name="amount" min="1000" max="<?= $remaining ?>" value="<?= $remaining?>" required>
              </div>
              <div class="form-text">Minimal Rp 1.000, maksimal sisa tagihan.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Metode Pembayaran</label>
              <select name="method_id" id="methodSelect" class="form-select" required>
                <option value="">-- Pilih --</option>
                <?php
                $sql = "SELECT * FROM payment_methods WHERE status='aktif' AND branch_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                  echo "<option value='{$row['id']}' data-type='".strtolower($row['nama_metode'])."'>{$row['nama_metode']}</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <!-- Pilihan Rekening / E-Wallet -->
          <div class="mb-3" id="accountWrapper" style="display:none;">
            <label class="form-label">Pilih Rekening / E-Wallet</label>
            <select name="account_id" id="accountSelect" class="form-select">
              <?php
              $sql = "SELECT * FROM bank_accounts WHERE status='aktif' AND branch_id=?";
              $stmt = $conn->prepare($sql);
              $stmt->bind_param("i", $branch_id);
              $stmt->execute();
              $res = $stmt->get_result();
              if ($res->num_rows > 0) {
                while ($acc = $res->fetch_assoc()) {
                  $type = (stripos($acc['bank_name'], 'ovo') !== false || stripos($acc['bank_name'], 'dana') !== false || stripos($acc['bank_name'], 'gopay') !== false)
                          ? "ewallet" : "bank";
                  echo "<option value='{$acc['id']}' data-type='{$type}'>
                          {$acc['bank_name']} - {$acc['account_number']} a.n {$acc['account_holder']}
                        </option>";
                }
              } else {
                echo "<option value=''>Tidak ada rekening aktif</option>";
              }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Catatan</label>
            <textarea name="note" class="form-control" rows="2" placeholder="Opsional"></textarea>
          </div>

          <!-- Tombol -->
          <div class="d-flex justify-content-between">
            <a href="invoice.php?id=<?= $invoice_id ?>&token=<?= $_SESSION['csrf_token']?>" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left-circle"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary" id="btnSubmit">
              <i class="bi bi-save"></i> Simpan Pembayaran
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
  $("#methodSelect").on("change", function(){
    let selected = $(this).find(":selected").text().toLowerCase();
    if (selected.includes("transfer") || selected.includes("bank")) {
      $("#accountWrapper").show();
      $("#accountSelect option").hide();
      $("#accountSelect option[data-type='bank']").show();
    } else if (selected.includes("wallet") || selected.includes("ewallet")) {
      $("#accountWrapper").show();
      $("#accountSelect option").hide();
      $("#accountSelect option[data-type='ewallet']").show();
    } else {
      $("#accountWrapper").hide();
      $("#accountSelect").val("");
    }
  }).trigger("change");

  // loading state
  $("#paymentForm").on("submit", function(){
    $("#btnSubmit").prop("disabled", true).html("<span class='spinner-border spinner-border-sm me-2'></span>Proses...");
  });
});
</script>

<?php include BASE_PATH . "includes/footer.php"; ?>
