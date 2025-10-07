<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "includes/security_helper.php"; 
$username   = $_SESSION['username'];
$branch_id  = $_SESSION['branch_id'];
// $role_login = $_SESSION['role'];

// --- Ambil data invoice ---
$sql = "SELECT i.id, i.invoice_number, i.customer_id, i.status, c.fullname AS customer_name, 
i.invoice_date, i.grand_total, i.payment_method 
FROM invoices_temp i
JOIN customers c ON i.customer_id = c.id
WHERE i.branch_id=? 
ORDER BY i.invoice_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();

// --- Statistik Pendapatan ---
$currentMonth = date('m');
$currentYear  = date('Y');

// total revenue bulan ini
$stmt = $conn->prepare("SELECT SUM(grand_total) as total_revenue 
  FROM invoices_temp 
  WHERE MONTH(invoice_date)=? AND YEAR(invoice_date)=? AND branch_id=? AND status='aktif'");
$stmt->bind_param("iii", $currentMonth, $currentYear, $branch_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total_revenue'] ?? 0;

// total today
$stmt = $conn->prepare("SELECT SUM(grand_total) as total_today 
  FROM invoices_temp WHERE DATE(created_at)=CURDATE() AND branch_id=? AND status='aktif'");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$total_today = $stmt->get_result()->fetch_assoc()['total_today'] ?? 0;

// total paid bulan ini
$stmt = $conn->prepare("SELECT SUM(grand_total) as total_paid 
  FROM invoices_temp 
  WHERE MONTH(invoice_date)=? AND YEAR(invoice_date)=? AND branch_id=? AND status='aktif'");
$stmt->bind_param("iii", $currentMonth, $currentYear, $branch_id);
$stmt->execute();
$total_paid = $stmt->get_result()->fetch_assoc()['total_paid'] ?? 0;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Sistem Managemen Pelanggan Terintegrasi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap + Icons + AdminLTE -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="<?php echo BASE_URL; ?>css/adminlte.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


  <style>
    /* Biar tinggi Select2 sama dengan Bootstrap */
    .select2-container .select2-selection--single {
      height: 38px !important;
      padding: 6px 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 24px;
    }
  </style>
  <style>
    body { font-family: 'Poppins', sans-serif; background:#f5f7fa; }
    .stat-card {
      padding:20px; border-radius:12px; color:#fff; display:flex; 
      align-items:center; justify-content:space-between;
      transition: transform .2s;
    }
    .stat-card:hover { transform: translateY(-4px); }
    
    .card-header {
      background: linear-gradient(45deg, #007bff, #00bcd4);
      color: #fff;
      border-radius: 16px 16px 0 0;
    </style>
  </head>
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <main class="app-main mt-4">
      <div class="container-fluid">

        <!-- Statistik -->
        <div class="row mb-4">
          <div class="col-md-4 mt-2">
            <div class="stat-card" style="background:linear-gradient(135deg,#1abc9c,#16a085)">
              <div>
                <h4 class="mb-0">Rp <?= number_format($total,0,',','.') ?></h4>
                <small>Pendapatan Bulan Ini</small>
              </div>
              <i class="fas fa-money-bill-transfer fa-3x"></i>
            </div>
          </div>
          <div class="col-md-4 mt-2">
            <div class="stat-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b)">
              <div>
                <h4 class="mb-0">Rp <?= number_format($total_today,0,',','.') ?></h4>
                <small>Pendapatan Hari Ini</small>
              </div>
              <i class="fas fa-calendar-day fa-3x"></i>
            </div>
          </div>
          <div class="col-md-4 mt-2">
            <div class="stat-card" style="background:linear-gradient(135deg,#f39c12,#d35400)">
              <div>
                <h4 class="mb-0">Rp <?= number_format($total_paid,0,',','.') ?></h4>
                <small>Total Paid Bulan Ini</small>
              </div>
              <i class="fas fa-money-bill-trend-up fa-3x"></i>
            </div>
          </div>
        </div>

        <!-- Form Tambah Invoice -->
        <div class="card shadow-sm mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Buat Temporary Invoice</h5>
          </div>
          <div class="card-body">
            <form method="post" action="invoice_temp_save.php" id="invoiceForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Customer</label>
                  <select class="form-select" id="customerSelect" name="customer" required>
                    <option value="">-- Pilih --</option>
                    <?php
                    $cust = $conn->prepare("SELECT id, fullname FROM customers WHERE branch_id=?");
                    $cust->bind_param("i", $branch_id);
                    $cust->execute();
                    $resCust = $cust->get_result();
                    while($cst = $resCust->fetch_assoc()){
                      echo "<option value='{$cst['id']}'>{$cst['fullname']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Tanggal Invoice</label>
                  <input type="date" name="invoice_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Jumlah</label>
                  <input type="number" name="jumlah" class="form-control" min="1000" required>
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
                <div class="col-md-12" id="accountWrapper" style="display:none;">
                  <label class="form-label">Rekening / E-Wallet</label>
                  <select name="account_id" id="accountSelect" class="form-select">
                    <?php
                    $sql = "SELECT * FROM bank_accounts WHERE status='aktif' AND branch_id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $branch_id);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while ($acc = $res->fetch_assoc()) {
                      $type = (stripos($acc['bank_name'],'ovo')!==false || stripos($acc['bank_name'],'dana')!==false || stripos($acc['bank_name'],'gopay')!==false) ? "ewallet" : "bank";
                      echo "<option value='{$acc['id']}' data-type='{$type}'>{$acc['bank_name']} - {$acc['account_number']} a.n {$acc['account_holder']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Keterangan</label>
                  <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
              </div>
              <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-save"></i> Simpan Invoice
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Tabel Invoice -->
        <div class="card shadow-sm">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Daftar Temporary Invoice</h5>
          </div>
          <div class="card-body">
            
            <div class="table-responsive">

              <table class="table table-hover table-striped align-middle" id="invoiceTable">
                <thead class="table-light">
                  <tr>
                    <th>No</th>
                    <th>Customer</th>
                    <th>No. Invoice</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $no=1; 
                  while($row=$result->fetch_assoc()): ?>
                    <tr>
                      <td><?= $no++ ?></td>
                      <td><?= $row['customer_name'] ?></td>

                      <td> <?= $row['invoice_number'] ?>
                        <a href="print_invoice_temp?id=<?= secure_id($row['id']) ?>&token=<?= $_SESSION['csrf_token']?>"><span class="bi bi-printer"></span></a>
                      </td>
                      <td>Rp <?= number_format($row['grand_total'],0,',','.') ?></td>
                      <td><?= date('d-m-Y',strtotime($row['invoice_date'])) ?></td>

                      <td>
                        <form method="post" action="invoice_toggle_status?token=<?= $_SESSION['csrf_token']?>" class="status-form" style="display:inline;">
                          <input type="hidden" name="id" value="<?= $row['id'] ?>">
                          <input type="hidden" name="status" value="<?= $row['status']=="aktif" ? "nonaktif" : "aktif" ?>">
                          <button type="submit" class="btn btn-sm <?= $row['status']=="aktif" ? "btn-success" : "btn-secondary" ?>">
                            <?= $row['status']=="aktif" ? "Aktif" : "Nonaktif" ?>
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
              <div class="table-responsive">

              </div>
            </div>

          </div>
        </main>

<!-- JS -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(function(){
  // Filter rekening sesuai metode
    $("#methodSelect").on("change", function(){
      let selected = $(this).find(":selected").text().toLowerCase();
      if(selected.includes("transfer") || selected.includes("bank")){
        $("#accountWrapper").show();
        $("#accountSelect option").hide();
        $("#accountSelect option[data-type='bank']").show();
      } else if(selected.includes("wallet") || selected.includes("ewallet")){
        $("#accountWrapper").show();
        $("#accountSelect option").hide();
        $("#accountSelect option[data-type='ewallet']").show();
      } else {
        $("#accountWrapper").hide();
        $("#accountSelect").val("");
      }
    }).trigger("change");

  // DataTables
    $('#invoiceTable').DataTable({
      pageLength: 10,
      lengthMenu: [5,10,25,50,100],
      language: { search: "Cari:", lengthMenu: "Tampilkan _MENU_" }
    });
  });
</script>
<script>
  $(function(){
  // Aktifkan Select2 di customer
    $('#customerSelect').select2({
      placeholder: "-- Pilih Customer --",
      allowClear: true,
      width: '100%'
    });

  // Filter rekening sesuai metode
    $("#methodSelect").on("change", function(){
      let selected = $(this).find(":selected").text().toLowerCase();
      if(selected.includes("transfer") || selected.includes("bank")){
        $("#accountWrapper").show();
        $("#accountSelect option").hide();
        $("#accountSelect option[data-type='bank']").show();
      } else if(selected.includes("wallet") || selected.includes("ewallet")){
        $("#accountWrapper").show();
        $("#accountSelect option").hide();
        $("#accountSelect option[data-type='ewallet']").show();
      } else {
        $("#accountWrapper").hide();
        $("#accountSelect").val("");
      }
    }).trigger("change");


  });

</script>


<script>
$(function(){
  // Tangkap submit form status
  $('.status-form').on('submit', function(e){
    e.preventDefault(); // hentikan submit default
    let form = this;

    Swal.fire({
      title: 'Konfirmasi',
      text: 'Apakah Anda yakin ingin mengubah status invoice ini?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, ubah!',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit(); // jika klik Ya, submit form
      }
    });
  });
});
</script>


<?php if (isset($_GET['msg']) && $_GET['msg']=="success") { ?>
    <script>
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Invoice berhasil diupdate!',
          timer: 2000,
          showConfirmButton: false
      }).then(() => {
          window.location.href = "invoice_temp?token=<?php echo $_SESSION['csrf_token']?>";
      });
  </script>
<?php } ?>

<?php if (isset($_GET['msg']) && $_GET['msg']=="error") { ?>
    <script>
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: 'Gagal update!',
          timer: 2000,
          showConfirmButton: false
      }).then(() => {
          window.location.href = "invoice_temp?token=<?php echo $_SESSION['csrf_token']?>";
      });
  </script>
<?php } ?>
<?php include BASE_PATH . "includes/footer.php"; ?>
