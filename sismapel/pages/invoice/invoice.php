<?php

session_start();

include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "includes/security_helper.php"; 



$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];
// $role_login = $_SESSION['role'];


$sql = "SELECT i.id, i.invoice_number, i.customer_id, i.status, c.fullname AS customer_name, 
i.invoice_date, i.due_date, i.grand_total, i.payment_method 
FROM invoices i
JOIN customers c ON i.customer_id = c.id
WHERE i.branch_id='$branch_id'
ORDER BY i.invoice_date DESC ";

$result = $conn->query($sql);




// Ambil bulan & tahun berjalan
$currentDay   = date('d');
$currentMonth = date('m');
$currentYear  = date('Y');

// Total pendapatan bulan ini
$sql_total = "SELECT SUM(grand_total) as total_revenue 
FROM invoices 
WHERE MONTH(invoice_date) = ? AND YEAR(invoice_date) = ? AND branch_id=?";
$stmt = $conn->prepare($sql_total);
$stmt->bind_param("iii", $currentMonth, $currentYear, $branch_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total_revenue'] ?? 0;

// Ambil tanggal, bulan, tahun hari ini
$sql_today = "SELECT SUM(amount) as total_today 
FROM payments 
WHERE DATE(created_at) = CURDATE() 
AND branch_id = ?";
$stmt = $conn->prepare($sql_today);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$total_today = $stmt->get_result()->fetch_assoc()['total_today'] ?? 0;



// Total paid
$sql_paid = "SELECT SUM(grand_total) as total_paid 
FROM invoices 
WHERE status = 'paid' AND MONTH(invoice_date) = ? AND YEAR(invoice_date) = ? AND branch_id=?";
$stmt = $conn->prepare($sql_paid);
$stmt->bind_param("iii", $currentMonth, $currentYear, $branch_id);
$stmt->execute();
$total_paid = $stmt->get_result()->fetch_assoc()['total_paid'] ?? 0;

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
  <div id="loading">
    <div class="spinner"></div>
  </div>
<!--begin::App Main-->
<main class="app-main mt-4">
  <!--begin::App Content Header-->
  
  <!--begin::Container-->
  <div class="container-fluid">
    <!-- <?php include BASE_PATH . "includes/breadcrumb.php"; ?> -->



    <div class="row mb-4">
      <div class="col-md-4 mt-2">
        <div class="stat-card" style="background:linear-gradient(135deg,#1abc9c,#16a085)">
          <div>
            <!-- <h3>Rp. 12.000.000</h3> -->
            <h3 class="mb-0"><strong>Rp <?= number_format($total, 0, ',', '.') ?></strong></h3>
            <p >Pendapatan Bulan ini</p>
          </div>
          <i class="fas fa-money-bill-transfer fa-3x"></i>

        </div>
      </div>


      <div class="col-md-4 mt-2">
        <!--begin::Small Box Widget 2-->
        <div class="stat-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b)">
          <div >
            <!-- <h3>Rp. 3.000.000</h3> -->
            <h3 class="mb-0"><strong>Rp <?= number_format($total_today, 0, ',', '.') ?></strong></h3>
            <p >Pendapatan Hari Ini</p>
          </div>
          <i class="fas fa-money-bill fa-3x"></i>

        </div>
      </div>

      <div class="col-md-4 mt-2">

        <?php 

        $stmt = $conn->prepare("
          SELECT SUM(p.amount) AS total_income_this_month
          FROM invoices i
          INNER JOIN payments p ON p.invoice_id = i.invoice_number
          WHERE MONTH(i.invoice_date) = MONTH(CURDATE())
          AND YEAR(i.invoice_date) = YEAR(CURDATE()) AND i.branch_id = $branch_id
          ");
        $stmt->execute();
        $result2 = $stmt->get_result()->fetch_assoc();

        $total_income = $result2['total_income_this_month'] ?? 0;

          // echo "Total Pendapatan Bulan Ini: Rp " . number_format($total_income, 0, ',', '.');

        ?>
        <div class="stat-card" style="background:linear-gradient(135deg,#f39c12,#d35400)">
          <div>
            <!-- <h3>Rp. 9.000.000</h3> -->
            <h3 class="mb-0"><strong>Rp <?= number_format($total_income, 0, ',', '.') ?></strong></h3>
            <p >Total Paid</p>
          </div>
          <i class="fas fa-money-bill-trend-up fa-3x"></i>

        </div>
      </div>



    </div>


    <div class="card">
      <div class="card-header">
        <div class="row mt-2">
          <div class="col">
            <h5 style="color:white;"><i class="fas fa-file-invoice"></i> Invoice </h5>
          </div>
          <div class="col text-end">
            <form method="POST" action="generate_invoice.php">
              <button type="submit" name="generate" class="btn btn-outline-light"> <span class="bi bi-database-fill-gear"></span> Generate
              </button>
            </form>
          </div>
        </div>

      </div>
      <div class="card-body">
        <div class="table-responsive">

          <table class="table align-middle mb-3 table-hover" id="distribusiTable">
            <thead>
              <tr>
                <th width="5%">No</th>
                <th width="10%">Customer</th>
                <th width="10%">No. Invoice</th>
                <th width="10%">Jumlah</th>
                <th width="10%">Tanggal Invoice</th>
                <th width="10%">Jatuh Tempo</th>
                <th width="10%">Status</th>
                <th width="15%">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($result->num_rows > 0): 
                $no = 1;
                while($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['customer_name'] ?></td>
                    <td><?= $row['invoice_number'] ?></td>
                    <td>Rp <?= number_format($row['grand_total'],0,',','.') ?></td>
                    <td><?= $row['invoice_date'] ?></td>
                    <td><?= $row['due_date'] ?></td>
                    <td>

                      <?php if ($row['status']=="unpaid"): 

                        ?>
                        <label class="badge bg-primary">Unpaid</label>

                      <?php elseif ($row['status']=="paid"):
                        ?>
                        <label class="badge bg-success">Paid</label>

                      <?php elseif ($row['status']=="partial"):
                        ?>
                        <label class="badge bg-warning">Partial</label>
                      <?php elseif ($row['status']=="overdue"):
                        ?>
                        <label class="badge bg-danger">Over Due</label>




                      <?php endif ?>


                    </td>
                    <td>
                      <a href="invoice_detail?id=<?= secure_id($row['id']) ?>&token=<?= $_SESSION['csrf_token']?>" class="btn btn-outline-primary"><span class="bi bi-eye"></span></a>
                      <!-- <a href="invoice_edit.php?id=<?= $row['id'] ?>" class="btn-outline-primary"><span class="bi bi-pencil"></span></a> -->

                      <a href="print_invoice?id=<?= secure_id($row['id']) ?>&token=<?= $_SESSION['csrf_token']?>" class="btn btn-outline-primary"><span class="bi bi-printer"></span></a>
                      <a href="payment?invoice=<?= $row['invoice_number']; ?>&token=<?= $_SESSION['csrf_token']?>" class="btn btn-outline-primary"><span class="bi bi-currency-dollar"></span> </a>
                    </td>
                  </tr>
                <?php endwhile; endif; ?>
              </tbody>

            </table>
            <div class="table-responsive">

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


    <script>
      function confirmDelete(id) {
        Swal.fire({
          title: 'Yakin hapus user ini?',
          text: "Data tidak bisa dikembalikan lagi!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = "delete_produk.php?id=" + id;
          }
        });
      }
    </script>


    <script>
      <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        Swal.fire({
          icon: 'success',
          title: 'Payment',
          text: 'Pembayaran berhasil',
          showConfirmButton: false,
          timer: 2000
        }).then(() => {
          window.location.href = 'invoice?token=<?php echo $_SESSION['csrf_token']?>';
        });
      <?php endif; ?>
    </script>

    <script>
      <?php if (isset($_GET['msg']) && $_GET['msg'] == 'invalid'): ?>
        Swal.fire({
          icon: 'danger',
          title: 'Update!',
          text: 'Data tidak berhasil diupdate.',
          showConfirmButton: false,
          timer: 2000
        }).then(() => {
          window.location.href = 'invoice?token=<?php echo $_SESSION['csrf_token']?>';
        });
      <?php endif; ?>
    </script>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>

      $(function(){
        const table = $('#distribusiTable').DataTable({
          pageLength: 10,
          lengthMenu: [5,10,25,50,100],
          ordering: true,
          searching: true
        });


      });
    </script>

    <?php include BASE_PATH . "includes/footer.php"; //  ?>