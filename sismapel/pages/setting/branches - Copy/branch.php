<?php

session_start();

$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];


include __DIR__ . "../../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";



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
      font-family: 'Poppins', sans-serif;
      background: #f5f7fa;
    }
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
    <?php include BASE_PATH . "includes/breadcrumb.php"; ?>


    <div class="card shadow-sm mb-2">
      <div class="card-header">
        <div class="row">
          <div class="col">
            <h4>Branch Management</h4>
          </div>
         <!--  <div class="col text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
              Tambah Admin
            </button>
          </div> -->
        </div>
      </div>
      <div class="card-body">
        <!-- Form Input -->
       <!--  <form id="branchForm" enctype="multipart/form-data">
          <input type="hidden" name="id" id="id">
          <div class="mb-3">
            <label>Nama Branch</label>
            <input type="text" name="nama_branch" id="nama_branch" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Address</label>
            <textarea name="address" id="address" class="form-control"></textarea>
          </div>
          <div class="row mb-3">
            <div class="col">
              <label>Phone</label>
              <input type="text" name="phone" id="phone" class="form-control">
            </div>
            <div class="col">
              <label>Email</label>
              <input type="email" name="email" id="email" class="form-control">
            </div>
          </div>
          <input type="file" name="logo" id="logo" class="form-control" hidden>
          <button type="submit" class="btn btn-primary">Simpan</button>
          <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
        </form> -->
        <div class="table-responsive">
          <table class="table table-striped mt-3" id="branchTable">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Branch</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Logo</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <!-- Data akan muncul disini via AJAX -->
            </tbody>
          </table>
          <div class="table-responsive">
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header">
        <h4>Admin Management</h4>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <?php 
          $sql = "SELECT 
          u.id AS user_id,
          u.username,
          u.email AS user_email,
          u.status,
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
                <th>Email</th>
                <th>Status</th>
                <th>Role</th>
                <th>Branch Name</th>
                <th>Branch Address</th>
                <th>Branch Phone</th>
                <th>Branch Email</th>
                <th>Aksi</th>
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
                    <td><?= $row['user_email'] ?></td>
                    <td class="status-badge" data-id="<?= $row['user_id'] ?>"><?= $status_badge ?></td>
                    <td><?= $row['role_name'] ?></td>
                    <td><?= $row['nama_branch'] ?></td>
                    <td><?= $row['branch_address'] ?></td>
                    <td><?= $row['branch_phone'] ?></td>
                    <td><?= $row['branch_email'] ?></td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-status" type="checkbox" data-id="<?= $row['user_id'] ?>" <?= $checked ?>>
                        </div>
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

<!-- Modal Tambah User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" action="add_user.php" method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Admin Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-2">
          <div class="col-md-6">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label>Password</label>
            <div class="input-group">
              <input type="password" name="password" id="passwordInput" class="form-control" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="bi bi-eye"></i></button>
            </div>
          </div>
        </div>
        <div class="mb-2">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Branch</label>
          <select name="branch_id" class="form-control" required>
            <option value="">-- Pilih Role --</option>
            <?php
            $branch = $conn->query("SELECT id, nama_branch FROM branches");
            while($branches = $branch->fetch_assoc()):
              ?>
              <option value="<?= $branches['id'] ?>"><?= htmlspecialchars($branches['nama_branch']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Role</label>
          <select name="role_id" class="form-control" required>
            <option value="">-- Pilih Role --</option>
            <?php
            $roles = $conn->query("SELECT id, role_name FROM roles");
            while($role = $roles->fetch_assoc()):
              ?>
              <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Foto</label>
          <input type="file" name="foto" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>
</main>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function(){

  // Hide loading
    $('#loading').fadeOut();

    let table = $('#branchTable').DataTable();

  // Preview logo
    $('#logo').on('change', function(){
      const file = this.files[0];
      if(file){
        const reader = new FileReader();
        reader.onload = function(e){
          $('#logoPreview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(file);
      }
    });

    fetchData();

    function fetchData(){
      $.ajax({
        url: 'branch_action.php',
        type: 'POST',
        data: {action: 'fetch'},
        dataType: 'json',
        success: function(data){
          let html = '';
          data.forEach(function(branch){
            html += `<tr>
                        <td>${branch.id}</td>
                        <td>${branch.nama_branch}</td>
                        <td>${branch.address}</td>
                        <td>${branch.phone}</td>
                        <td>${branch.email}</td>
                        <td>${branch.logo ? '<img src="uploads/'+branch.logo+'" width="50">' : ''}</td>
                        <td>
                            <button class="btn btn-sm btn-info editBtn" data-id="${branch.id}">Edit</button>
                            <button class="btn btn-sm btn-danger deleteBtn" data-id="${branch.id}">Delete</button>
                        </td>
          </tr>`;
        });
          $('#branchTable tbody').html(html);
        }
      });
    }

    // Simpan / Add / Update
    $('#branchForm').submit(function(e){
      e.preventDefault();
      let formData = new FormData(this);
      formData.append('action', $('#id').val() ? 'update' : 'add');

      $.ajax({
        url: 'branch_action.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(res){
          if(res.status == 'success'){
            fetchData();
            $('#branchForm')[0].reset();
            $('#id').val('');
            Swal.fire({
              icon: 'success',
              title: 'Sukses',
              text: $('#id').val() ? 'Branch berhasil diupdate' : 'Branch berhasil ditambahkan',
              timer: 1500,
              showConfirmButton: false
            });
          } else {
            Swal.fire('Error', res.message || 'Terjadi kesalahan', 'error');
          }
        }
      });
    });


    $('#branchTable').on('click', '.editBtn', function(){
      let id = $(this).data('id');
      $.ajax({
        url: 'branch_action.php',
        type: 'POST',
        data: {action:'edit', id:id},
        dataType: 'json',
        success: function(res){
          $('#id').val(res.id);
          $('#nama_branch').val(res.nama_branch);
          $('#address').val(res.address);
          $('#phone').val(res.phone);
          $('#email').val(res.email);
        }
      });
    });

    // Delete
    $('#branchTable').on('click', '.deleteBtn', function(){
      if(confirm('Hapus branch ini?')){
        let id = $(this).data('id');
        $.ajax({
          url: 'branch_action.php',
          type: 'POST',
          data: {action:'delete', id:id},
          dataType: 'json',
          success: function(res){
            if(res.status == 'success'){
              fetchData();
              Swal.fire({
                icon: 'success',
                title: 'Sukses',
                text: 'Branch berhasil dihapus',
                timer: 1500,
                showConfirmButton: false
              });
            } else {
              Swal.fire('Error', res.message || 'Terjadi kesalahan', 'error');
            }
          }
        });
      }
    });


    $('#resetBtn').click(function(){
      $('#branchForm')[0].reset();
      $('#id').val('');
    });

  });

</script>


<script>
    // Saat halaman selesai load, sembunyikan loading
  window.addEventListener("load", function(){
    document.getElementById("loading").style.display = "none";
    document.getElementById("content").style.display = "block";
  });
</script>

<script>
  <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'User berhasil ditambahkan.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
    // Hapus query string dari URL setelah alert selesai
      window.location.href = 'user.php';
    });
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Terjadi kesalahan saat menambahkan user.',
      showConfirmButton: true
    }).then(() => {
      window.location.href = 'user.php';
    });
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Terhapus!',
      text: 'User berhasil dihapus.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'user.php';
    });
  <?php endif; ?>
</script>


<script>
$(document).ready(function(){
  $('#adminTable').DataTable();

    $('.toggle-status').change(function(){
        let checkbox = $(this);
        let userId = checkbox.data('id');
        let currentStatus = checkbox.is(':checked') ? 1 : 0;
        let actionText = currentStatus ? 'mengaktifkan' : 'menonaktifkan';

        // Tampilkan konfirmasi SweetAlert
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: `Anda akan ${actionText} user ini!`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if(result.isConfirmed){
                // Jika konfirmasi, update via AJAX
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
                // Jika batal, rollback toggle
                checkbox.prop('checked', !currentStatus);
            }
        });
    });
});
</script>
<?php include BASE_PATH . "includes/footer.php"; //  ?>