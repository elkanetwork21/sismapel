<?php
session_start();

include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/security_helper.php"; 


require_once "routeros_api.class.php";
include BASE_PATH . "includes/sidebar.php";

$id = validate_secure_id($_GET['id']); // decode
if ($id === false) {
    die("Data tidak ditemukan / ID tidak valid");
}

$query = $conn->prepare("SELECT * FROM mikrotik_settings WHERE id=?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$mt = $result->fetch_assoc();

if (!$mt) {
  die("Data tidak ditemukan");
}

$API = new RouterosAPI();
$API->debug = false;

$data = [];
if ($API->connect($mt['ip_address'], $mt['username'], $mt['password'])) {
  $resource   = $API->comm("/system/resource/print");
  $identity   = $API->comm("/system/identity/print");
  $interfaces = $API->comm("/interface/print");
  $API->disconnect();

  $data = $resource[0];
  $data['identity'] = $identity[0]['name'];
} else {
  $error = "Tidak bisa terhubung ke Mikrotik";
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Detail Router | AdminLTE v4</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <link rel="preload" href="<?php echo BASE_URL; ?>css/adminlte.css" as="style" />

  <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />


  <!-- Plugins -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css" crossorigin="anonymous" />

  <style>
    body { font-family: 'Poppins', sans-serif; background: #f5f7fa; }
    /* Overlay loading */
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

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

  <!-- Spinner -->
  <div id="loading">
    <div class="spinner"></div>
  </div>

  <!-- Content -->
  <main class="app-main" id="content" style="display:none;">
    <div class="app-content-header">
      <div class="container-fluid">

        <!-- <?php include BASE_PATH . "includes/breadcrumb.php"; ?> -->

        <div class="card shadow-sm">
          <div class="card-header">
            <h5><span class="bi bi-router"></span> Detail Router</h5>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="card-body col-md-4 border-end">
                <label for="interface">Pilih Interface:</label>
                <select id="interface">
                  <?php foreach($interfaces as $iface): ?>
                    <option value="<?= $iface['name']; ?>"><?= $iface['name']; ?></option>
                  <?php endforeach; ?>
                </select>

                <canvas class="mb-3" id="trafficChart" height="170px"></canvas>

                <div id="mikrotikStatus">
                  <span class="text-muted">Memuat status...</span>
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer text-end">
            <a href="mikrotik?token=<?php echo $_SESSION['csrf_token']?>" class="btn btn-outline-primary">
              <span class="bi bi-arrow-left"></span> Kembali
            </a>
          </div>
        </div>

      </div>
    </div>
  </main>

  <!-- Script Spinner -->
  <script>
    window.addEventListener("load", function(){
      document.getElementById("loading").style.display = "none";
      document.getElementById("content").style.display = "block";
    });
  </script>

  <!-- Script Reload -->
  <script>
    $("#reloadBtn").click(function() {
      $("#reloadBtn").prop("disabled", true).text("⏳ Reloading...");
      $.get("mikrotik_reload_detail.php?id=<?= $id ?>", function(data) {
        $("#detailData").html(data);
        $("#reloadBtn").prop("disabled", false).text("🔄 Reload");
      });
    });
  </script>

  <!-- Script Status -->
  <script>
    function loadStatus() {
      fetch("get_status.php?id=<?= $id ?>")
        .then(r => r.json())
        .then(data => {
          let box = document.getElementById("mikrotikStatus");
          if (data.error) {
            box.innerHTML = `<span class="badge bg-danger">OFFLINE</span> ${data.error}`;
          } else {
            box.innerHTML = `
              <table class="table table-bordered table-striped">
                <tr><th>Identity</th><td>${data.identity}</td></tr>
                <tr><th>Uptime</th><td>${data.uptime}</td></tr>
                <tr><th>Version</th><td>${data.version}</td></tr>
                <tr><th>CPU Load</th><td>${data.cpu}%</td></tr>
                <tr><th>Free Memory</th><td>${data.free_mem} MB</td></tr>
                <tr><th>Total Memory</th><td>${data.total_mem} MB</td></tr>
              </table>
            `;
          }
        });
    }
    loadStatus();
    setInterval(loadStatus, 3000);
  </script>

  <!-- Script Traffic -->
  <script>
    let ctx = document.getElementById('trafficChart').getContext('2d');
    let trafficChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [],
        datasets: [
          { label: 'Download (Mbps)', borderColor: 'blue', backgroundColor: 'rgba(0,0,255,0.1)', data: [], fill: true, tension: 0.3 },
          { label: 'Upload (Mbps)', borderColor: 'red', backgroundColor: 'rgba(255,0,0,0.1)', data: [], fill: true, tension: 0.3 }
        ]
      },
      options: {
        responsive: true,
        animation: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: v => v + ' Mbps' }
          },
          x: {
            ticks: { maxRotation: 90, minRotation: 45 }
          }
        }
      }
    });

    let lastRx = 0, lastTx = 0, lastTime = Date.now();

    function updateTraffic() {
      let iface = $("#interface").val();
      $.getJSON("get_traffic.php?iface=" + iface, function(data) {
        let now = Date.now();
        let dt = (now - lastTime) / 1000;

        let rx = Number(data.rx);
        let tx = Number(data.tx);

        if (lastRx > 0 && lastTx > 0) {
          let rxRate = (rx - lastRx) * 8 / dt / 1000000;
          let txRate = (tx - lastTx) * 8 / dt / 1000000;

          let timeLabel = new Date().toLocaleTimeString();
          trafficChart.data.labels.push(timeLabel);
          trafficChart.data.datasets[0].data.push(rxRate);
          trafficChart.data.datasets[1].data.push(txRate);

          if (trafficChart.data.labels.length > 10) {
            trafficChart.data.labels.shift();
            trafficChart.data.datasets[0].data.shift();
            trafficChart.data.datasets[1].data.shift();
          }
          trafficChart.update();
        }

        lastRx = rx;
        lastTx = tx;
        lastTime = now;
      });
    }

    // default interface
    $("#interface").val("ether1-Backbone");
    setInterval(updateTraffic, 3000);
  </script>

  <?php include BASE_PATH . "includes/footer.php"; ?>
