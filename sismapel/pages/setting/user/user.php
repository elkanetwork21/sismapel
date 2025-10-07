<?php

session_start();

$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];


include __DIR__ . "../../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";


// Ambil data sesuai halaman
$sql = "SELECT 
u.id AS user_id,
u.username,
u.email,
u.status,
u.branch_id,
r.id AS role_id,
r.role_name
FROM users u
INNER JOIN roles r 
ON u.role_id = r.id WHERE u.branch_id=$branch_id";
$result = $conn->query($sql);

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
   

      <div class="card shadow-sm">

        <div class="card-header">
          <div class="row">
            <div class="col-md-4">
              <h5><span class="bi bi-people"></span> Managemen User</h5>
            </div>

            <div class="col-md-8 text-end">
              <button class="btn btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                Tambah User
              </button>
            </div>
          </div>
          
          
        </div>
        <div class="card-body">
          <div class="table-responsive">

            <table class="table table-hover align-middle mb-3" id="distribusiTable">
              <thead class="table">
                <tr>
                  <th scope="col">No</th>
                  <th scope="col">Username</th>
                  <th scope="col">Email</th>
                  <th scope="col">Role</th>
                  <th scope="col">Status</th>
                  <th scope="col">Aksi</th>
                </tr>
              </thead>
              <?php if ($result->num_rows > 0): 
                $no = 0;

                ?>

                <?php while($row = $result->fetch_assoc()): 
                  $no++;?>
                  <tr>
                    <td><?=$no ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['role_name']) ?></td>
                    
                  </td>


                  <td>
                    <div class="form-check form-switch">
                      <input class="form-check-input toggle-status" type="checkbox"
                      data-id="<?= $row['user_id'] ?>"
                      <?= $row['status'] == 1 ? 'checked' : '' ?>>
                      <span class="badge <?= $row['status'] == 1 ? 'bg-success' : 'bg-danger' ?>">
                        <?= $row['status'] == 1 ? 'Aktif' : 'Tidak Aktif' ?>
                      </span>
                    </div>
                  </td>

                  <td>
                    <a href="#" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['user_id']; ?>)">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>

                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center">Tidak ada data</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
        <div class="table-responsive">


        </div>
      </div>
    </div>


  </div>
</main>

<!-- Modal Tambah User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" action="add_user.php" method="post" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Tambah User Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-2">
          <div class="mb-2">
            <label>Username </label>
            <input type="text" id="usernameFinal" name="username" class="form-control" required>
          </div>
          
        </div>
        <div class="mb-2">
          <label>Password</label>
          <div class="input-group">
            <input type="password" name="password" id="passwordInput" class="form-control" required>
            <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="bi bi-eye"></i></button>
          </div>
        </div>

        <div class="mb-2">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <input type="hidden" name="branch_id" value="<?= $branch_id ?>">
        <div class="mb-2">
          <label>Role</label>
          <select name="role_id" class="form-control" required>
            <option value="">-- Pilih Role --</option>
            <?php
            $roles = $conn->query("SELECT * FROM roles WHERE role_name IN ('User','Admin')");
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      window.location.href = 'user?token=<?php echo $_SESSION['csrf_token']?>';
    });
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Terjadi kesalahan saat menambahkan user.',
      showConfirmButton: true
    }).then(() => {
      window.location.href = 'user?token=<?php echo $_SESSION['csrf_token']?>';
    });
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Terhapus!',
      text: 'User berhasil dihapus.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'user?token=<?php echo $_SESSION['csrf_token']?>';
    });
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'duplicate'): ?>
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Username/Email sudah terdaftar.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'user?token=<?php echo $_SESSION['csrf_token']?>';
    });
  <?php endif; ?>
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
        window.location.href = "delete_user.php?id=" + id;
      }
    });
  }
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  $(document).ready(function(){
    $(".toggle-status").on("change", function(){
      let userId = $(this).data("id");
      let status = $(this).is(":checked") ? 1 : 0;
      let badge = $(this).siblings("span");

      $.ajax({
        url: "update_status.php",
        type: "POST",
        data: { id: userId, status: status },
        success: function(res){
          if(res.trim() === "success"){
            if(status == 1){
              badge.removeClass("bg-danger").addClass("bg-success").text("Aktif");
              Swal.fire({
                icon: 'success',
                title: 'User diaktifkan',
                timer: 1200,
                showConfirmButton: false
              });
            } else {
              badge.removeClass("bg-success").addClass("bg-danger").text("Tidak Aktif");
              Swal.fire({
                icon: 'warning',
                title: 'User dinonaktifkan',
                timer: 1200,
                showConfirmButton: false
              });
            }
          } else {
            Swal.fire("Error!", "Gagal update status.", "error");
          }
        }
      });
    });
  });
</script>

<script>
  $(document).ready(function(){
    $(".change-role").on("change", function(){
      let userId = $(this).data("id");
      let role = $(this).val();

      $.ajax({
        url: "update_role.php",
        type: "POST",
        data: { id: userId, role: role },
        success: function(res){
          if(res.trim() === "success"){
            Swal.fire({
              icon: 'success',
              title: 'Role berhasil diubah',
              timer: 1200,
              showConfirmButton: false
            });
          } else {
            Swal.fire("Error!", "Gagal update role.", "error");
          }
        }
      });
    });
  });
</script>

<script>
  document.getElementById("togglePassword").addEventListener("click", function () {
    const passwordInput = document.getElementById("passwordInput");
    const icon = this.querySelector("i");

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      icon.classList.remove("bi-eye");
      icon.classList.add("bi-eye-slash");
    } else {
      passwordInput.type = "password";
      icon.classList.remove("bi-eye-slash");
      icon.classList.add("bi-eye");
    }
  });
</script>

<script>
    // Saat halaman selesai load, sembunyikan loading
  window.addEventListener("load", function(){
    document.getElementById("loading").style.display = "none";
    document.getElementById("content").style.display = "block";
  });
</script>


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

<script>
function generateUniqueCode(length = 4) {
    const chars = 'abcdefghijklmnopqrstuvwxyz123456789!@#$%^&*';
    let code = '';
    for(let i=0;i<length;i++){
        code += chars.charAt(Math.floor(Math.random()*chars.length));
    }
    return code;
}

// Update username final saat user mengetik
const usernameInput = document.getElementById('usernameInput');
const usernameFinal = document.getElementById('usernameFinal');

usernameInput.addEventListener('input', () => {
    let base = usernameInput.value.trim() || 'user';
    usernameFinal.value = base + '.' + generateUniqueCode();
});

// Inisialisasi pertama
usernameFinal.value = 'user_' + generateUniqueCode();
</script>

<?php include BASE_PATH . "includes/footer.php"; //  ?>