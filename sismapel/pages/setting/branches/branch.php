<?php

session_start();

$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];


include __DIR__ . "../../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";


// Total branch (statis)
$branchQuery = $conn->query("SELECT COUNT(*) AS total_branch FROM branches");
$totalBranch = $branchQuery->fetch_assoc()['total_branch'];
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    .stat-card {
      padding:20px; border-radius:12px; color:#fff; display:flex; 
      align-items:center; justify-content:space-between;
      transition: transform .2s;
    }
    .stat-card:hover { transform: translateY(-4px); }
    .card-header { background: #f8f9fa;}

    .card-header {
      background: linear-gradient(45deg, #007bff, #00bcd4);
      color: #fff;
      border-radius: 16px 16px 0 0;
  </style>


  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">


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
   
    <div class="row mb-4">
        <div class="col-md-6 mt-2">
          <div class="stat-card" style="background:linear-gradient(135deg,#1abc9c,#16a085)">
            <div>
              <h4 ><strong class="total-customer"><?= $totalBranch ?></strong></h4>
              <small>Total Branch</small>
            </div>
            <i class="fas fa-users fa-3x"></i>
          </div>
        </div>
        <div class="col-md-6 mt-2">
          <div class="stat-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b)">
            <div>
              
              <h4 id="totalAdminNonaktif">
                <?php
                    // Hitung jumlah admin nonaktif saat page load
                $adminQuery = $conn->query("SELECT COUNT(*) AS total_admin_nonaktif 
                  FROM users u 
                  JOIN roles r ON u.role_id = r.id
                  WHERE r.role_name = 'Admin' AND u.status = 0");
                echo $adminQuery->fetch_assoc()['total_admin_nonaktif'];
                ?>
              </h4>
              <small>Admin Nonaktif</small>
            </div>
            <i class="fas fa-ban fa-3x"></i>
          </div>
        </div>
      </div>

    <div class="card shadow-sm">
      <div class="card-header" style="color:white">
        <h4>Admin Management</h4>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <?php 
          $sql = "SELECT 
          u.id AS user_id,
          u.username,
          u.password,
          u.email AS user_email,
          u.status,
          u.last_activity,
          r.role_name,
          b.nama_branch,
          b.address AS branch_address,
          b.phone AS branch_phone,
          b.email AS branch_email
          FROM users u
          JOIN roles r ON u.role_id = r.id
          JOIN branches b ON u.branch_id = b.id
          WHERE r.role_name = 'Admin'";


          $result = $conn->query($sql);?>

          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
          <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
          <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

          <table class="table" id="adminTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <!-- <th>Password</th> -->
                <th>Status</th>
                <th>Branch Name</th>
                <th>Branch Address</th>
                <th>Branch Phone</th>
                <th>Aksi</th>
                <th>Status Login</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              if ($result->num_rows > 0):
                $no = 1;
                while($row = $result->fetch_assoc()): 
                  $row_class = ($row['status'] == 0) ? 'table-danger' : '';
                  $status_badge = ($row['status'] == 1) 
                  ? '<span class="badge bg-success">Aktif</span>'
                  : '<span class="badge bg-danger">Nonaktif</span>';
                  $checked = ($row['status'] == 1) ? 'checked' : '';
                  ?>
                  <tr class="<?= $row_class ?>">
                    <td><?= $no ?></td>
                    <td><?= $row['username'] ?></td>
                    <!-- <td><?= $row['password'] ?></td> -->
              
                    <td class="status-badge" data-id="<?= $row['user_id'] ?>"><?= $status_badge ?></td>
                    <td><?= $row['nama_branch'] ?></td>
                    <td><?= $row['branch_address'] ?></td>
                    <td><?= $row['branch_phone'] ?></td>
                    <td>
                      <div class="form-check form-switch">
                        <input class="form-check-input toggle-status" type="checkbox" data-id="<?= $row['user_id'] ?>" <?= $checked ?>>
                      </div>
                    </td>
                    <td class="login-status" data-id="<?= $row['user_id'] ?>">
                      <span class="badge bg-secondary">Checking...</span>
                    </td>


                  </tr>
                  <?php 
                  $no++;
                endwhile; 
              else: 
                ?>
                <tr>
                  <td colspan="10" class="text-center">No admin users found</td>
                </tr>
              <?php endif; ?>
            </tbody>

          </table>
        </div>
      </div>
    </div>


</main>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>


<script>
    // Saat halaman selesai load, sembunyikan loading
  window.addEventListener("load", function(){
    document.getElementById("loading").style.display = "none";
    document.getElementById("content").style.display = "block";
  });
</script>


<script>
  
  function updateAdminNonaktif() {
    let total = 0;
    $('.status-badge').each(function(){
        if($(this).text().trim() === 'Nonaktif'){
            total++;
        }
    });
    $('#totalAdminNonaktif').text(total);
}

$(document).ready(function(){
    // DataTables sudah diinisialisasi sebelumnya
    $('#adminTable').DataTable();

    $('.toggle-status').change(function(){
        let checkbox = $(this);
        let userId = checkbox.data('id');
        let currentStatus = checkbox.is(':checked') ? 1 : 0;
        let actionText = currentStatus ? 'mengaktifkan' : 'menonaktifkan';

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: `Anda akan ${actionText} user ini!`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: 'update_status.php',
                    method: 'POST',
                    data: {id: userId, status: currentStatus},
                    success: function(response){
                        let row = checkbox.closest('tr');
                        let badgeCell = $('.status-badge[data-id="'+userId+'"]');

                        if(response.trim() === 'success'){
                            if(currentStatus == 1){
                                badgeCell.html('<span class="badge bg-success">Aktif</span>');
                                row.removeClass('table-danger');
                                Swal.fire('Berhasil!', 'User telah diaktifkan!', 'success');
                            } else {
                                badgeCell.html('<span class="badge bg-danger">Nonaktif</span>');
                                row.addClass('table-danger');
                                Swal.fire('Berhasil!', 'User telah dinonaktifkan!', 'warning');
                            }
                            // Update card summary admin nonaktif
                            updateAdminNonaktif();
                        } else {
                            Swal.fire('Gagal!', 'Gagal mengubah status user!', 'error');
                            checkbox.prop('checked', !currentStatus); // rollback toggle
                        }
                    },
                    error: function(){
                        Swal.fire('Error!', 'Terjadi kesalahan koneksi!', 'error');
                        checkbox.prop('checked', !currentStatus); // rollback toggle
                    }
                });
            } else {
                checkbox.prop('checked', !currentStatus); // rollback toggle
            }
        });
    });
});

</script>

<script>
  function updateLoginStatus() {
    $.ajax({
        url: 'check_online.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('.login-status').each(function(){
                let userId = $(this).data('id');
                if(response[userId] === 'Online'){
                    $(this).html('<span class="badge bg-primary">Online</span>');
                } else {
                    $(this).html('<span class="badge bg-secondary">Offline</span>');
                }
            });
        }
    });
}

// Jalankan pertama kali
updateLoginStatus();

// Update tiap 10 detik
setInterval(updateLoginStatus, 10000);

</script>
<?php include BASE_PATH . "includes/footer.php"; //  ?>