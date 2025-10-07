<?php
session_start();

$username  = $_SESSION['username'] ?? '';
$branch_id = $_SESSION['branch_id'] ?? 0;

include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "includes/security_helper.php"; 

// Ambil data paket
$stmt = $conn->prepare("SELECT * FROM paket_langganan WHERE branch_id = ?");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$paket = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Paket Langganan | Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Fonts & CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">

  <!-- Custom Style -->
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f7fa;
    }

    /* Loading Overlay */
    #loading {
      position: fixed;
      inset: 0;
      background: rgba(255, 255, 255, 0.9);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: opacity 0.3s ease;
    }
    .spinner {
      width: 60px;
      height: 60px;
      border: 6px solid #eaeaea;
      border-top: 6px solid #3498db;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* Card Paket */
    .paket-card {
      border: none;
      border-radius: 16px;
      transition: transform 0.2s ease, box-shadow 0.3s ease;
    }
    .paket-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 24px rgba(0,0,0,0.08);
    }
    .paket-header {
      background: linear-gradient(135deg, #3498db, #6dd5fa);
      color: #fff;
      border-radius: 16px 16px 0 0;
      padding: 20px;
    }
    .paket-footer h4 {
      color: #2c3e50;
      font-weight: bold;
    }
    .btn-icon {
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .btn-icon:hover {
      transform: scale(1.1);
    }

    .btn-tambah {
      position: fixed;
      bottom: 25px;
      right: 25px;
      border-radius: 50px;
      padding: 12px 28px;
      font-size: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 9999;
    }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

  <div id="loading">
    <div class="spinner"></div>
  </div>

  <main class="app-main content">
    <div class="app-content-header">
      <div class="container-fluid">
        <!-- <?php include BASE_PATH . "includes/breadcrumb.php"; ?> -->
      </div>
      <a href="tambah_produk?token=<?php echo $_SESSION['csrf_token']?>" class="btn btn-primary btn-tambah">
        <i class="bi bi-plus-circle"></i> Add
      </a>

      
      <div class="card shadow-sm">
        <div class="card-header bg-white border-0">
          <div class="row align-items-center">
            <div class="col">
              <h5 class="mb-0"><i class="bi bi-journals"></i> Paket Langganan</h5>
            </div>


            
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <?php foreach ($paket as $row): ?>
              <div class="col-md-4 mb-4">
                <div class="card paket-card h-100">
                  <div class="paket-header text-center">
                    <h5 class="mb-0"><?= htmlspecialchars($row['nama_paket']); ?></h5>
                  </div>
                  <div class="card-body text-center">
                    <p><strong>Rate Limit:</strong> <?= htmlspecialchars($row['rate_limit']); ?> Mbps</p>
                    <p><?= nl2br(htmlspecialchars($row['description'])); ?></p>
                  </div>
                  <div class="card-footer text-center paket-footer">
                    <h4>Rp <?= number_format($row['harga_final'], 0, ',', '.'); ?>/bulan</h4>
                    <a href="edit_produk.php?id=<?= secure_id (htmlspecialchars($row['id'])); ?>" class="btn btn-outline-primary btn-icon me-2">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <button class="btn btn-outline-danger btn-icon" onclick="confirmDelete(<?= (int)$row['id']; ?>)">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
            <?php if (empty($paket)): ?>
              <div class="col-12 text-center py-4">
                <p class="text-muted">Belum ada paket langganan yang tersedia.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    
  </main>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  window.addEventListener("load", function(){
    document.getElementById("loading").style.opacity = "0";
    setTimeout(()=> document.getElementById("loading").style.display = "none", 300);
  });

  function confirmDelete(id) {
    Swal.fire({
      title: 'Yakin hapus paket ini?',
      text: "Data tidak bisa dikembalikan!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e74c3c',
      cancelButtonColor: '#3498db',
      confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = "delete_produk.php?id=" + id;
      }
    });
  }
</script>

<script>
  <?php if ($_GET['msg'] === 'deleted'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Terhapus!',
      text: 'Data berhasil dihapus.',
      timer: 2000,
      showConfirmButton: false
    }).then(() => window.location.href = 'produk?token=<?php echo $_SESSION['csrf_token']?>');
  <?php elseif ($_GET['msg']  === 'updated'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Update!',
      text: 'Data berhasil diupdate.',
      timer: 2000,
      showConfirmButton: false
    }).then(() => window.location.href = 'produk?token=<?php echo $_SESSION['csrf_token']?>');
  
  <?php elseif ($_GET['msg']  === 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Simpan!',
      text: 'Data berhasil disimpan.',
      timer: 2000,
      showConfirmButton: false
    }).then(() => window.location.href = 'produk?token=<?php echo $_SESSION['csrf_token']?>');
  <?php endif; ?>
</script>

<?php include BASE_PATH . "includes/footer.php"; ?>
