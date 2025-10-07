<?php
session_start();

$username   = $_SESSION['username'];
$branch_id  = $_SESSION['branch_id'];

include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";


// Ambil data mikrotik untuk branch 
$sql = "SELECT * FROM mikrotik_settings WHERE branch_id = ?"; $stmt = $conn->prepare($sql); $stmt->bind_param("i", $branch_id); $stmt->execute(); $result = $stmt->get_result(); $mikrotik = $result->fetch_assoc();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Backup Router | Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <style>
    .btn-back {
      position: fixed;
      bottom: 25px;
      right: 25px;
      border-radius: 50px;
      padding: 12px 28px;
      font-size: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 9999;
    }

    #loading {
      position: fixed;
      inset: 0;
      background: #fff;
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .spinner {
      width: 60px; height: 60px;
      border: 6px solid #f3f3f3;
      border-top: 6px solid #3498db;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    @keyframes spin { 
      0% { transform: rotate(0deg);} 
      100% { transform: rotate(360deg);} 
    }
    .card {
      border-radius: 16px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
      border: none;
    }
    .card-header {
      background: linear-gradient(45deg, #007bff, #00bcd4);
      color: #fff;
      border-radius: 16px 16px 0 0;
      


    </style>

    <!-- 🔹 Overlay Loading -->
    <style>
      #backup-loading {
        position: fixed;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.95);
        top: 0;
        left: 0;
        z-index: 9999;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
      }
      .spinner {
        width: 70px;
        height: 70px;
        border: 6px solid #f3f3f3;
        border-top: 6px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 20px;
      }
      @keyframes spin {
        0%   { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      .progress-container {
        width: 60%;
        background: #eee;
        border-radius: 25px;
        overflow: hidden;
        margin-top: 10px;
      }
      .progress-bar {
        height: 20px;
        width: 0%;
        background: #3498db;
        text-align: center;
        color: #fff;
        font-size: 12px;
        line-height: 20px;
        transition: width 0.3s ease;
      }
    </style>

    <style>
      body { font-family: 'Poppins', sans-serif; background: #f5f7fa; }
    </style>
  </head>

  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <!-- Loading Spinner -->
    <div id="loading">
      <div class="spinner"></div>
    </div>

    <div id="backup-loading">
      <div class="spinner"></div>
      <p style="margin:5px 0;font-weight:bold;color:#333;">Sedang membuat backup...</p>
      <div class="progress-container">
        <div class="progress-bar" id="backup-progress">0%</div>
      </div>
    </div>


    <form action="backup_mikrotik.php" method="post">
      <input type="hidden" name="branch_id" value="<?= $branch_id ?>">
      <input type="hidden" name="mikrotik_id" value="<?php echo $mikrotik['id']; ?>">
      <button type="submit" class="btn btn-primary btn-back">
        <i class="bi bi-download"></i> Backup
      </button>
    </form>

    <!-- Main -->
    <main class="app-main">
      <div class="app-content-header">
        <div class="container-fluid">
          <!-- <?php include BASE_PATH . "includes/breadcrumb.php"; ?> -->

          <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0"><i class="bi bi-files"></i> Backup</h5>
            </div>
            
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-3" id="backupTable">
                  <thead class="table-light">
                    <tr>
                      <th>No</th>
                      <th>Nama File</th>
                      <th>Tanggal</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="backupData">
                    <tr><td colspan="4" class="text-center text-muted">Memuat data...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>
    </main>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    // Hilangkan loading setelah semua selesai render
      window.addEventListener("load", function() {
        document.getElementById("loading").style.display = "none";
      });

    // Load data mikrotik pakai AJAX
      $(document).ready(function() {
        $.ajax({
        url: "get_backup_files.php", // endpoint untuk ambil data
        type: "POST",
        data: { branch_id: <?= $branch_id ?> },
        dataType: "json",
        success: function(res) {
          let tbody = "";
          if (res.length > 0) {
            res.forEach((f, i) => {
              tbody += `
                <tr>
                  <td>${i+1}</td>
                  <td>${f.name}</td>
                  <td>${f.date}</td>
                  <td>
                    <a href="delete_backup.php?file=${encodeURIComponent(f.name)}" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Yakin hapus file ${f.name}?')">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
              `;
            });
          } 
          $("#backupData").html(tbody);
          $('#backupTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            ordering: true,
            searching: true,
            destroy: true
          });
        },
        error: function() {
          $("#backupData").html(`<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>`);
        }
      });
      });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?> 
      <script> Swal.fire({ 
        icon: 'success', 
        title: 'Berhasil!', 
        text: 'Backup Mikrotik berhasil dibuat.', 
        showConfirmButton: false, 
        timer: 2000 }).then(() => { window.location.href = 'backup?token=<?php echo $_SESSION['csrf_token']?>'; }); 
      </script> 
    <?php endif; ?>


    <script>
      document.addEventListener("DOMContentLoaded", function(){
        const form = document.querySelector("form[action='backup_mikrotik.php']");
        if(form){
          form.addEventListener("submit", function(){
            const overlay = document.getElementById("backup-loading");
            const progressBar = document.getElementById("backup-progress");
            overlay.style.display = "flex";

            let progress = 0;
            const fakeProgress = setInterval(()=>{
        if(progress < 95){ // berhenti di 95%, nanti server selesai otomatis close
          progress += Math.floor(Math.random() * 10); // naik random biar natural
          if(progress > 95) progress = 95;
          progressBar.style.width = progress + "%";
          progressBar.textContent = progress + "%";
        }
      }, 500);

      // biarkan PHP redirect menutup overlay, jadi JS tidak perlu stop sendiri
      // kalau mau auto-finish simulasi (misal AJAX), clearInterval(fakeProgress) saat sukses
          });
        }
      });
    </script>

    <?php include BASE_PATH . "includes/footer.php"; ?>
