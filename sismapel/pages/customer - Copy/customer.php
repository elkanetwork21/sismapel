<?php
session_start();

$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];

include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";


// total pelanggan
$total_sql = "SELECT COUNT(*) as total FROM customers WHERE branch_id=$branch_id";
$total_res = $conn->query($total_sql);
$total     = $total_res->fetch_assoc()['total'] ?? 0;

// pelanggan isolir
$isolir_sql = "SELECT COUNT(*) as total FROM customers WHERE active_status = 0 AND branch_id=$branch_id";
$isolir_res = $conn->query($isolir_sql);
$isolir     = $isolir_res->fetch_assoc()['total'] ?? 0;

// Ambil data customer
$sql    = "SELECT * FROM customers WHERE branch_id=$branch_id";
$result = $conn->query($sql);
?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Sistem Managemen Pelanggan Terintegrasi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css">

  <style>
    body { background: #f5f6fa; font-family: 'Poppins', sans-serif; }


    .stat-card {
      padding: 15px; border-radius: 12px; color: white;
      display:flex; justify-content:space-between; align-items:center;
      margin-bottom: 15px;
    }

    /* 🔹 Responsive tweaks untuk mobile */
    @media (max-width: 768px) {
      .stat-card h3 { font-size: 1.2rem; }
      .stat-card i { font-size: 1.5rem; }
      .card { margin-bottom: 15px; }
      .card-header { font-size: 0.9rem; padding: 0.5rem 1rem; }
      .card-body { padding: 0.75rem; }
      table { font-size: 0.85rem; }
    }

    .chart-container {
      position: relative;
      width: 100%;
      overflow-x: auto;
    }
    .chart-container canvas {
      width: 100% !important;
      height: 250px !important;
    }

    @media (max-width: 768px) {
      .chart-container canvas {
        height: 180px !important;
      }



    </style>

    <style>
      /* Atur posisi control datatables */
      .dataTables_length {
        float: left;
      }
      .dataTables_filter {
        float: right;
      }
      .dataTables_info {
        float: left;
        margin-top: 10px;
      }
      .dataTables_paginate {
        float: right;
        margin-top: 10px;
      }
/* Overlay semi-transparent */
#loading {
  position: fixed;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.7); /* semi-transparent */
  top:0;
  left:0;
  z-index:9999;
  display:flex;
  flex-direction: column;
  align-items:center;
  justify-content:center;
  font-family: 'Poppins', sans-serif;
  color: #00ffe1;
}

/* WiFi 3D container */
.wifi-3d {
  position: relative;
  width: 150px;
  height: 150px;
  perspective: 800px;
}

/* Center dot (router) */
.dot {
  position: absolute;
  width: 16px;
  height: 16px;
  background: #00ffe1;
  border-radius: 50%;
  left:50%;
  top:50%;
  transform: translate(-50%, -50%);
  box-shadow: 0 0 20px #00ffe1, 0 0 40px #00ffe1;
  animation: dotPulse 1.2s infinite ease-in-out;
}

/* Rings */
.ring {
  position: absolute;
  border: 2px solid #00ffe1;
  border-radius: 50%;
  left:50%;
  top:50%;
  transform: translate(-50%, -50%) scale(0.3);
  opacity:0;
  box-shadow: 0 0 20px #00ffe1, 0 0 40px #00ffe1;
  animation: ringPulse 2s infinite ease-in-out;
}

.ring1 { animation-delay:0s; width:40px; height:40px; }
.ring2 { animation-delay:0.4s; width:70px; height:70px; }
.ring3 { animation-delay:0.8s; width:100px; height:100px; }
.ring4 { animation-delay:1.2s; width:130px; height:130px; }

@keyframes ringPulse {
  0% { transform: translate(-50%, -50%) scale(0.3) rotateX(0deg) rotateY(0deg); opacity:0.5; }
  50% { transform: translate(-50%, -50%) scale(1) rotateX(15deg) rotateY(15deg); opacity:0.2; }
  100% { transform: translate(-50%, -50%) scale(1.5) rotateX(30deg) rotateY(30deg); opacity:0; }
}

@keyframes dotPulse {
  0% { transform: translate(-50%, -50%) scale(0.8); opacity:0.6; }
  50% { transform: translate(-50%, -50%) scale(1); opacity:1; }
  100% { transform: translate(-50%, -50%) scale(0.8); opacity:0.6; }
}

/* Loading text */
.loading-text {
  margin-top: 25px;
  font-size: 18px;
  font-weight: 600;
  color: #00ffe1;
  text-shadow: 0 0 10px #00ffe1, 0 0 20px #00ffe1;
  animation: textGlow 1.5s infinite alternate;
}

@keyframes textGlow {
  0% { text-shadow: 0 0 10px #00ffe1, 0 0 20px #00ffe1; }
  50% { text-shadow: 0 0 20px #00ffe1, 0 0 40px #00ffe1; }
  100% { text-shadow: 0 0 10px #00ffe1, 0 0 20px #00ffe1; }
}
</style>

<style>
  .switch { position: relative; display: inline-block; width: 50px; height: 24px; }
  .switch input {display:none;}
  .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #ccc; transition: .4s; border-radius: 24px; }
  .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; transition: .4s; border-radius: 50%; }
  input:checked + .slider { background: #4CAF50; }
  input:checked + .slider:before { transform: translateX(26px); }
  .tx-text { font-size: 12px; font-weight: bold; color: green; }
  .rx-text { font-size: 12px; font-weight: bold; color: red; }


</style>

</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

  <div id="loading">
    <div class="wifi-3d">
      <div class="dot"></div>
      <div class="ring ring1"></div>
      <div class="ring ring2"></div>
      <div class="ring ring3"></div>
      <div class="ring ring4"></div>
    </div>
    <div class="loading-text">Loading ...</div>
  </div>

  <main class="app-main mt-4">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="stat-card" style="background: linear-gradient(135deg,#1abc9c,#16a085)">
            <div>
              <h3 style="margin:0;"><strong><?= $total ?></strong></h3>
              <p style="margin:0;">Total Pelanggan</p>
            </div>
            <i class="fas fa-users fa-3x"></i>

            
          </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="stat-card" style="background: linear-gradient(135deg,#e74c3c,#c0392b)">
            <div>
              <h3 style="margin:0;"><strong><?= $isolir ?></strong></h3>
              <p style="margin:0;">Isolir</p>
            </div>
            <i class="fas fa-ban fa-3x"></i>
          </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="stat-card" style="background: linear-gradient(135deg,#f39c12,#d35400)">
            <div>
              <h3 style="margin:0;"><strong><?= $total_offline ?? 0 ?></strong></h3>
              <p style="margin:0;">Offline</p>
            </div>
            <i class="fas fa-plug fa-3x"></i>

          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <div class="row align-items-center">
            <div class="col">
              <h5><i class="bi bi-people"></i> Customer List</h5>
              <p><i>Untuk menonaktifkan customer (Isolir), klik toggle pada kolom Isolir</i></p>
            </div>
            <div class="col text-end">
              <a href="tambah_customer.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Data
              </a>
            </div>
          </div>
        </div>

      
        <div class="card-body">
          <div class="table-responsive">

            <table class="table align-middle mb-3" id="customerTable">
              <thead>
                <tr>
                  <th width="1%">No</th>
                  <th width="5%">Nama</th>
                  <th width="5%">Alamat</th>
                  <th width="5%">Tx/Rx</th>
                  <th width="2%">Status</th>
                  <th width="2%">Isolir</th>
                  <th width="7%">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($result->num_rows > 0): $no=0; while($row=$result->fetch_assoc()): $no++;

                  ?>
                  <tr>
                    <td><?= $no ?></td>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars(substr($row['address'],0,30)) ?><br>Phone : <?= htmlspecialchars($row['phone']) ?></td>
                    <td>
                      <label class="tx-text">TX:</label><span class="tx-text" id="tx-<?= $row['ppp_secret'] ?>">0 bps</span><br>
                      <label class="rx-text">RX:</label><span class="rx-text" id="rx-<?= $row['ppp_secret'] ?>">0 bps</span>
                    </td>
                    <td >
                     <span class="status-col" id="status-<?= $row['ppp_secret'] ?>"></span>

                     <span class="isolir-col" id="isolir-<?= $row['ppp_secret'] ?>">Loading...</span>
                   </td>

                   <td>
                    <label class="switch">
                      <input type="checkbox" class="toggleStatus" data-username="<?= $row['ppp_secret']; ?>" <?= ($row['active_status'] == 1) ? 'checked' : ''; ?>>
                      <span class="slider"></span>
                    </label>
                  </td>
                  <td>
                    <a href="customer_detail.php?id=<?= $row['id']?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye"></i></a>
                    <a href="customer_edit.php?id=<?= $row['id']?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                    <a href="#" class="btn btn-outline-primary btn-sm" onclick="confirmDelete(<?= $row['id']?>, <?= $row['odp_id']?>)"><i class="bi bi-trash"></i></a>
                  </td>
                </tr>
              <?php endwhile; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">
        <label>Legend:</label>
        <span class="badge bg-primary">Aktif</span> Customer Aktif
        <span class="badge bg-danger">Isolir</span> Customer Isolir
        <span class="badge bg-success">Online</span> Perangkat Online
        <span class="badge bg-secondary">Offline</span> Perangkat Offline
      </div>
    </div>
  </div>
</main>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>

  $(function(){
    const table = $('#customerTable').DataTable({
      pageLength: 10,
      lengthMenu: [5,10,25,50,100],
      ordering: true,
      searching: true
    });


  });

  function confirmDelete(id, odp_id) {
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
        window.location.href = "delete_customer.php?id=" + id + "&odp=" + odp_id;
      }
    });
  }

  document.querySelectorAll(".toggleStatus").forEach(function(toggle){
    toggle.addEventListener("change", function(){
      var username = this.getAttribute("data-username");
      var status   = this.checked ? 1 : 0;

      Swal.fire({title:'Memproses...',text:'Sedang update status',allowOutsideClick:false,didOpen:()=>{Swal.showLoading();}});

      fetch("update_status_customer.php", {
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"username="+username+"&status="+status
      }).then(res=>res.json()).then(data=>{
        Swal.close();
        if(data.success){ Swal.fire("Sukses",data.message,"success").then(()=>location.reload()); }
        else { Swal.fire("Error",data.message,"error"); }
      }).catch(err=>{
        Swal.close();
        Swal.fire("Error",err,"error");
      });
    });
  });

  function formatSpeed(bits) {
    if (bits >= 1000000) return (bits/1000000).toFixed(2) + " Mbps";
    if (bits >= 1000) return (bits/1000).toFixed(2) + " Kbps";
    return bits + " bps";
  }

  function loadTraffic() {
    fetch("get_txrx_customer.php").then(res=>res.json()).then(data=>{
      data.forEach(cust=>{
        let txEl=document.getElementById("tx-"+cust.ppp_secret);
        let rxEl=document.getElementById("rx-"+cust.ppp_secret);
        if (txEl) txEl.textContent=formatSpeed(cust.tx);
        if (rxEl) rxEl.textContent=formatSpeed(cust.rx);
      });
    });
  }
  setInterval(loadTraffic,2000);
  loadTraffic();
</script>


<script>
// Hide loading after page fully loaded
  window.addEventListener('load', () => {
    const loader = document.getElementById('loading');
    loader.style.transition = 'opacity 0.5s ease';
    loader.style.opacity = '0';
    setTimeout(() => loader.style.display = 'none', 600);
  });
</script>


<script>
  function loadStatus() {
    fetch("load_data.php").then(res=>res.json()).then(data=>{
      if (data.success) {
        let active = data.activeSecrets;
        let isolir = data.isolirSecrets;

      // Update row status
        $("#customerTable tbody tr").each(function(){
          let secret = $(this).find(".toggleStatus").data("username");

        // update status online/offline
          let statusEl = document.getElementById("status-"+secret);
          if (statusEl) {
            if (active.includes(secret)) {
              statusEl.innerHTML = "<span class='badge bg-success'>Online</span>";
            } else {
              statusEl.innerHTML = "<span class='badge bg-secondary'>Offline</span>";
            }
          }

        // update isolir badge
          let isolirEl = document.getElementById("isolir-"+secret);
          if (isolirEl) {
            if (isolir.includes(secret)) {
              isolirEl.innerHTML = "<span class='badge bg-danger'>Isolir</span>";
            } else {
              isolirEl.innerHTML = "<span class='badge bg-primary'>Aktif</span>";
            }
          }
        });

      // 🔹 Update summary card
        document.getElementById("sum-total").textContent   = data.summary.total_secret;
        document.getElementById("sum-online").textContent  = data.summary.online;
        document.getElementById("sum-offline").textContent = data.summary.offline;
        document.getElementById("sum-isolir").textContent  = data.summary.isolir;
      }
    });
  }

setInterval(loadStatus, 5000); // refresh tiap 5 detik
loadStatus();

</script>


<script>
  <?php if ($_GET['msg'] ?? '' === 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Simpan!',
      text: 'Data berhasil disimpan.',
      timer: 2000,
      showConfirmButton: false
    }).then(() => window.location.href = 'customer.php');
  <?php elseif ($_GET['msg'] ?? '' === 'updated'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Update!',
      text: 'Data berhasil diupdate.',
      timer: 2000,
      showConfirmButton: false
    }).then(() => window.location.href = 'customer.php');
  <?php endif; ?>
</script>

<script>
  function updatePaymentStatus() {
  fetch("update_payment_status.php")
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        console.log("✅ Payment status updated");
      } else {
        console.error("❌ Gagal update:", data.message);
      }
    })
    .catch(err => console.error("❌ Error:", err));
}

// panggil sekali setelah halaman siap
document.addEventListener("DOMContentLoaded", updatePaymentStatus);

// bisa juga otomatis jalan tiap jam (3600000 ms)
setInterval(updatePaymentStatus, 3600000);

</script>
