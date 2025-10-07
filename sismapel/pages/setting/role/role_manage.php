<?php
session_start();
include __DIR__ . "../../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";

$branch_id = $_SESSION['branch_id'];

// //cek apakah login & role admin
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
//     header("Location: /login.php");
//     exit;
// }

// ambil role list
$roles = $conn->query("SELECT * FROM roles");

// daftar halaman aplikasi (static bisa, atau generate otomatis)
$pages = [
    "index.php" => "Dashboard",

    //Mikrotik
    "mikrotik.php" => "Router Management",
    "detail_mikrotik.php" => "Detail Mikrotik",
    "backup.php" => "Backup Mikrotik",

    //Produk
    "produk.php" => "Produk Management",
    "tambah_produk.php" => "Tambah Produk",

    //Distribusi
    "distribusi.php" => "Distribusi",
    "tambah_distribusi.php" => "Add Distribusi",
    "edit_distribusi.php" => "Edit Distribusi",
    "coverage.php" => "Coverage Area",

    //Customer
    "customer.php" => "Customer",
    "tambah_customer.php" => "Management Customer",
    "customer_detail.php" => "Detail Customer",
    "customer_edit.php" => "Edit Customer",
    "mapping.php" => "Mapping Customer",

    //Invoice
    "invoice.php" => "Invoice",
    "invoice_detail.php" => "Detail Invoice",
    "print_invoice.php" => "Print Invoice",
    "payment.php" => "Payment",
    "invoice_edit.php" => "Edit Invoice",
    "invoice_new.php" => "Manual Invoice",
    "invoice_temp.php" => "Temporary Invoice",
    "print_invoice_temp.php" => "Temporary Invoice Print",
    "invoice_toggle_status.php" => "Ubah Temp Status",

    //Transaksi
    "transaksi.php" => "Transaksi",
    "rekapitulasi.php" => "Rekapitulasi",
    "pengeluaran.php" => "Pengeluaran",
    "pendapatan.php" => "Pendapatan Lainnya",

    //Setting 
    "general.php" => "General Setting",
    "branch.php" => "Branch Manager",
    "invoice.php" => "Invoice",
    "user.php" => "User Management",
    "payment" => "Payment Gateway",
    "otomatisasi.php" => "Automatisasi",
    "role_manage.php" => "Role Manage"
    
];

$role_id = isset($_GET['role_id']) ? (int)$_GET['role_id'] : 0;
$allowed = [];

if ($role_id) {
    $res = $conn->query("SELECT page FROM role_permissions WHERE role_id=$role_id");
    while($r = $res->fetch_assoc()) {
        $allowed[] = $r['page'];
    }
}
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
        font-family: 'Poppins', sans-serif;
        background: #f5f7fa;
    }
</style>
</head>
<!--end::Head-->
<!--begin::Body-->
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

<!--begin::App Main-->
<main class="app-main mt-4">
  <!--begin::App Content Header-->
  <!--begin::Container-->
  <div class="container-fluid">
      <!-- <?php include BASE_PATH . "includes/breadcrumb.php"; ?> -->
      

      <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 border-end">
                    <h4>Tambah Role Baru</h4>
                    <form method="post" action="save_role.php" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="role_name" class="form-control" placeholder="Nama Role Baru" required>
                            <button type="submit" class="btn btn-primary">Tambah Role</button>
                        </div>
                    </form>
                </div>

                <div class="col-md-8">
                    
                    <h4>Pengaturan Role & Akses</h4>

                    <form method="get" class="mb-3">
    <input type="hidden" name="token" value="<?= $_SESSION['csrf_token'] ?>">
    <select name="role_id" onchange="this.form.submit()" class="form-select" style="width:300px;">
        <option value="">-- pilih role --</option>
        <?php while($r = $roles->fetch_assoc()){ ?>
            <option value="<?= $r['id'] ?>" <?= $role_id==$r['id']?'selected':'' ?>>
                <?= ucfirst($r['role_name']) ?>
            </option>
        <?php } ?>
    </select>
</form>

                    <?php if ($role_id) { ?>
                        <form method="post" action="save_permission.php">
                            <input type="hidden" name="role_id" value="<?= $role_id ?>">
                            <table class="table table-striped border">
                                <thead> 
                                    <tr>
                                        <th>Halaman</th>
                                        <th>Izinkan?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pages as $file => $label) { ?>
                                        <tr>
                                            <td><?= $label ?> (<?= $file ?>)</td>
                                            <td>
                                                <input class="form-check-input" type="checkbox" name="pages[]" value="<?= $file ?>" 
                                                <?= in_array($file,$allowed)?'checked':'' ?>>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col text-end">

                                    <button type="submit" class="btn btn-outline-primary">Simpan</button>
                                </div>
                            </div>
                        </form>
                    <?php } ?>
                </div>
                

            </div>

            
        </div>  

    </div>
</div>


</div>


</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (isset($_GET['msg']) && $_GET['msg']=="role_added") { ?>
    <script>
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Role baru berhasil ditambahkan!',
          timer: 2000,
          showConfirmButton: false
      }).then(() => {
          window.location.href = "role_manage?token=<?php echo $_SESSION['csrf_token']?>";
      });
  </script>
<?php } ?>

<?php if (isset($_GET['msg']) && $_GET['msg']=="saved") { ?>
    <script>
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Role berhasil diupdate!',
          timer: 2000,
          showConfirmButton: false
      }).then(() => {
          window.location.href = "role_manage?token=<?php echo $_SESSION['csrf_token']?>";
      });
  </script>
<?php } ?>

<?php include BASE_PATH . "includes/footer.php"; //  ?>

