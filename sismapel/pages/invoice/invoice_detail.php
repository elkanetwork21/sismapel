<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "includes/security_helper.php"; 

if (!isset($_SESSION['username'])) {
    header("Location: auth/login.php");
    exit();
}

$username   = $_SESSION['username'];
$branch_id  = $_SESSION['branch_id'];
// $role_login = $_SESSION['role'];
$id = validate_secure_id($_GET['id']); // decode
if ($id === false) {
    die("Data tidak ditemukan / ID tidak valid");
}

// Ambil invoice + customer + total_paid
$sql = "SELECT i.*, c.fullname AS customer_name, c.address, c.phone, c.email, 
        COALESCE(SUM(p.amount),0) AS total_paid
        FROM invoices i
        JOIN customers c ON i.customer_id = c.id
        LEFT JOIN payments p ON p.invoice_id = i.invoice_number
        WHERE i.id = ? 
        GROUP BY i.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

// Ambil invoice items
$sql_items = "SELECT * FROM invoice_items WHERE invoice_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $id);
$stmt_items->execute();
$items = $stmt_items->get_result();

// Ambil branch
$sql_branch = "SELECT * FROM branches WHERE id=?";
$stmt_branch = $conn->prepare($sql_branch);
$stmt_branch->bind_param("i", $branch_id);
$stmt_branch->execute();
$branch = $stmt_branch->get_result()->fetch_assoc();

// Ambil setting
$setting = $conn->query("SELECT * FROM setting_invoice")->fetch_assoc();
$sett_rekening = $setting['rekening'] ?? '';
$sett_syarat   = $setting['syarat'] ?? '';
$sett_support  = $setting['support'] ?? '';

$status_badge = [
    'unpaid'   => 'primary',
    'paid'     => 'success',
    'partial'  => 'warning',
    'overdue'  => 'danger'
];
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


  <style>
    body {
      font-family: 'Poppins', sans-serif !important;
      background: #f5f7fa;
    }

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


    .stat-card {
      padding:20px; border-radius:12px; color:#fff; display:flex; 
      align-items:center; justify-content:space-between;
      transition: transform .2s;
    }
    .stat-card:hover { transform: translateY(-4px); }
    .card-header { background: #f8f9fa; }

    .card-header {
      background: linear-gradient(45deg, #007bff, #00bcd4);
      color: #fff;
      border-radius: 16px 16px 0 0;
    </style>



    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  </head>
<!--end::Head-->
<!--begin::Body-->
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<main class="app-main mt-4">
  <!--begin::App Content Header-->
  
  <!--begin::Container-->
  <div class="container-fluid">
    

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>🧾 Invoice #<?= htmlspecialchars($invoice['invoice_number']); ?></h2>
    <div>
        <?php if($invoice['status']!=='paid'): ?>
            <a href="invoice_edit?id=<?= secure_id($invoice['id']) ?>&token=<?= $_SESSION['csrf_token']?>" class="btn btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
            <a href="payment?invoice=<?= $invoice['invoice_number'] ?>&token=<?= $_SESSION['csrf_token']?>" class="btn btn-outline-primary"><i class="bi bi-plus-square"></i> Pembayaran</a>
        <?php endif ?>
        <a href="print_invoice?id=<?= secure_id($id) ?>&token=<?= $_SESSION['csrf_token']?>" class="btn btn-outline-primary"><i class="bi bi-printer"></i> Print</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <span class="badge bg-<?= $status_badge[$invoice['status']] ?? 'secondary' ?>"><?= ucfirst($invoice['status']) ?></span>
                <h4 class="mt-2">Ditagihkan ke :</h4>
                <p>
                    <?= htmlspecialchars($invoice['customer_name']) ?><br>
                    <?= htmlspecialchars($invoice['address']) ?><br>
                    <strong>Phone:</strong> <?= htmlspecialchars($invoice['phone']) ?><br>
                    <strong>Email:</strong> <?= htmlspecialchars($invoice['email']) ?>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <img src="../setting/general/images/<?= $branch['logo'] ?>" height="80"><br>
                <strong><?= htmlspecialchars($branch['nama_branch']) ?></strong><br>
                <i><?= $sett_support ?></i><br>
                <?= htmlspecialchars($branch['address']) ?><br>
                <?= htmlspecialchars($branch['phone']) ?><br>
                <?= htmlspecialchars($branch['email']) ?><br>
                <h3 class="mt-2">Rp <?= number_format($invoice['grand_total'] - $invoice['total_paid'],0,',','.') ?></h3>
            </div>
        </div>

        <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Deskripsi</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            <?php while($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['description']) ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td>Rp <?= number_format($item['price'],0,',','.') ?></td>
                    <td>Rp <?= number_format($item['qty']*$item['price'],0,',','.') ?></td>
                </tr>
            <?php endwhile; ?>
                <tr>
                    <td colspan="3" class="text-end">Sub Total</td>
                    <td>Rp <?= number_format($invoice['subtotal'],0,',','.') ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end">Discount</td>
                    <td>Rp <?= number_format($invoice['total_discount'],0,',','.') ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end">Pajak</td>
                    <td>Rp <?= number_format($invoice['total_tax'],0,',','.') ?></td>
                </tr>
                <tr class="table-success">
                    <td colspan="3" class="text-end"><strong>Grand Total</strong></td>
                    <td><strong>Rp <?= number_format($invoice['grand_total'],0,',','.') ?></strong></td>
                </tr>
            </tbody>
        </table>
        </div>

        <div class="mt-4">
            <h5>Transaksi Terkait</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="paymentTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tanggal Bayar</th>
                            <th>Metode</th>
                            <th>Rekening / E-Wallet</th>
                            <th>Jumlah</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT p.*, pm.nama_metode, ba.bank_name
                                FROM payments p
                                INNER JOIN payment_methods pm ON p.method_id = pm.id
                                LEFT JOIN bank_accounts ba ON p.account_id = ba.id
                                WHERE p.invoice_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $invoice['invoice_number']);
                        $stmt->execute();
                        $payments = $stmt->get_result();
                        if($payments->num_rows > 0){
                            $no=1;
                            while($p = $payments->fetch_assoc()){
                                echo "<tr>
                                <td>{$no}</td>
                                <td>".date("d-m-Y H:i", strtotime($p['created_at']))."</td>
                                <td>{$p['nama_metode']}</td>
                                <td>{$p['bank_name']}</td>
                                <td>Rp ".number_format($p['amount'],0,',','.')."</td>
                                <td>{$p['note']}</td>
                                </tr>";
                                $no++;
                            }
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            <h5>Rekening & Syarat</h5>
            <p><strong>Rekening Pembayaran:</strong> <?= $sett_rekening ?></p>
            <p><strong>Syarat & Ketentuan:</strong> <?= $sett_syarat ?></p>
        </div>
    </div>
</div>

<div class="text-end">
    <a href="invoice?token=<?php echo $_SESSION['csrf_token']?>" class="btn btn-outline-primary"><i class="bi bi-arrow-counterclockwise"></i> Kembali</a>
</div>

</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
    $('#paymentTable').DataTable({pageLength:10,lengthMenu:[5,10,25],ordering:true,searching:true});
});
function confirmDelete(id){
    Swal.fire({
        title:'Yakin hapus?',
        text:'Data tidak bisa dikembalikan!',
        icon:'warning',
        showCancelButton:true,
        confirmButtonColor:'#d33',
        cancelButtonColor:'#3085d6',
        confirmButtonText:'Ya, hapus!'
    }).then((result)=>{
        if(result.isConfirmed) window.location.href="delete_produk.php?id="+id;
    });
}
</script>


<?php include BASE_PATH . "includes/footer.php"; //  ?>