<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";
require_once "mikrotik_connect.php";
include BASE_PATH . "includes/security_helper.php"; 

$branch_id = $_SESSION['branch_id'];

// Ambil data mikrotik
$sql = "SELECT * FROM mikrotik_settings WHERE branch_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();
$mikrotik = $result->fetch_assoc();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Konfigurasi Router</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <style>
    body { font-family: 'Poppins', sans-serif; background: #f5f7fa; }
    .status-indicator { width: 14px; height: 14px; border-radius: 50%; display: inline-block; margin-right: 6px; }
    .status-online { background: #28a745; animation: blink 1s infinite; }
    .status-offline { background: #dc3545; }
    @keyframes blink { 0%,100% {opacity:1} 50% {opacity:.3} }

    /* Mobile adjust */
    .card { margin-bottom: 1rem; }
    .table td, .table th { white-space: nowrap; }

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
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">

      <!-- <?php include BASE_PATH . "includes/breadcrumb.php"; ?> -->

      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="m-0"><i class="bi bi-hdd-network"></i> Konfigurasi Router</h5>
        </div>
        <div class="card-body">
          <div class="row gy-3">

            <!-- Form Konfigurasi -->
            <div class="col-12 col-md-4 border-end">
              <form action="mikrotik_save.php" method="POST">
                <div class="mb-3">
                  <label class="form-label">Server</label>
                  <input type="text" name="server" class="form-control" required>
                </div>
                <div class="row">
                  <div class="col-6 mb-3">
                    <label class="form-label">IP Address</label>
                    <input type="text" id="ip_address" name="ip_address" class="form-control" required>
                  </div>
                  <div class="col-6 mb-3">
                    <label class="form-label">Port</label>
                    <input type="number" id="port" name="port" value="8728" class="form-control" required>
                  </div>
                </div>
                <div class="row">
                  <div class="col-6 mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                  </div>
                  <div class="col-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                  </div>
                </div>
                <div class="d-grid gap-2 d-md-flex">
                  <button type="button" id="btnTest" class="btn btn-outline-primary"><i class="bi bi-plugin"></i> Tes Koneksi</button>
                  <button type="submit" class="btn btn-outline-primary"><i class="bi bi-save"></i> Simpan</button>
                </div>
              </form>
              <div id="result" class="mt-3"></div>
            </div>

            <!-- Status Router -->
            <div class="col-12 col-md-8">
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Server</th>
                      <th>Host</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr data-bs-toggle="collapse" data-bs-target="#collapseStatus" style="cursor:pointer">
                      <td><?= htmlspecialchars($mikrotik['nama'] ?? '') ?></td>
                      <td><?= htmlspecialchars($mikrotik['ip_address'] ?? '') ?></td>
                      <td><div id="mikrotikStatus2">Memuat...</div></td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="collapse show" id="collapseStatus">
                <div class="card-body p-3">
                  <div id="mikrotikStatus" class="mb-3"><span class="text-muted">Memuat status...</span></div>
                  <?php if ($mikrotik): ?>
                    <div class="d-flex flex-wrap gap-2">
                      <a class="btn btn-primary btn-sm" href="detail_mikrotik?id=<?=secure_id ((int)$mikrotik['id'])?>&token=<?php echo $_SESSION['csrf_token']?>"><i class="bi bi-view-list"></i> View More</a>
                      <a href="#" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= (int)$mikrotik['id'] ?>)"><i class="bi bi-trash"></i> Hapus</a>
                    </div>
                  <?php endif; ?>
                  <p class="mt-2 small text-muted"><i>Klik view more untuk melihat detail mikrotik</i></p>
                </div>
              </div>
            </div>

          </div><!-- row -->
        </div>
      </div>
    </div>
  </div>
</main>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function loadStatus(){
  fetch("get_status.php?id=<?= $mikrotik['id'] ?? 0 ?>")
  .then(r=>r.json())
  .then(data=>{
    let box=document.getElementById("mikrotikStatus");
    let box2=document.getElementById("mikrotikStatus2");
    if(data.error){
      box.innerHTML=`<span class="badge bg-danger">OFFLINE</span> ${data.error}`;
      box2.innerHTML=`<span class="badge bg-danger">OFFLINE</span>`;
    }else{
      box.innerHTML=`
        <div class="row mb-2">
          <div class="col-12 col-md-6 mb-2">
            <div style="background: linear-gradient(135deg, #1abc9c, #16a085); padding:20px; border-radius:8px; color:white; display:flex; align-items:center; justify-content:space-between;">
        <div>
                <h4 class="m-0"><strong>${data.cpu}%</strong></h4><p class="m-0">CPU Load</p>
                
              </div>
              <i class="fas fa-microchip fa-3x"></i>


              
            </div>
          </div>
          <div class="col-12 col-md-6 mb-2">
            <div style="background: linear-gradient(135deg, #e74c3c, #c0392b); padding:20px; border-radius:8px; color:white; display:flex; align-items:center; justify-content:space-between;">
        <div>
              <h4 class="m-0"><strong>${data.uptime}</strong></h4><p class="m-0">Uptime</p>
            </div>
              <i class="fas fa-clock fa-3x"></i>

          </div>
        </div>
        <small><b>Identity:</b> ${data.identity}<br>
        <b>Free Memory:</b> ${data.free_mem} MB / ${data.total_mem} MB<br>
        <b>Version:</b> ${data.version}</small>
      `;
      box2.innerHTML=`<span class="badge bg-success">CONNECTED</span>`;
    }
  });
}
loadStatus();
setInterval(loadStatus,5000);

function confirmDelete(id){
  Swal.fire({
    title:"Yakin hapus Router?",
    icon:"warning",showCancelButton:true,
    confirmButtonColor:"#d33",cancelButtonColor:"#3085d6",
    confirmButtonText:"Ya, hapus!"
  }).then(res=>{
    if(res.isConfirmed){ window.location.href="delete_mikrotik.php?id="+id; }
  });
}

// Tes koneksi
$("#btnTest").click(function(){
  let data={
    ip_address:$("#ip_address").val(),
    port:$("#port").val(),
    username:$("#username").val(),
    password:$("#password").val()
  };
  Swal.fire({title:"Mengecek koneksi...",allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
  $.post("test_mikrotik.php",data,function(resp){
    let res=JSON.parse(resp); Swal.close();
    if(res.status==="success"){
      Swal.fire({icon:"success",title:"Berhasil!",text:res.message});
    }else{
      Swal.fire({icon:"error",title:"Gagal!",text:res.message});
    }
  }).fail(()=>{Swal.close();Swal.fire({icon:"error",title:"Error!",text:"Gagal menghubungi server."});});
});
</script>
<?php include BASE_PATH."includes/footer.php"; ?>
