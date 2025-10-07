<?php
session_start();

include __DIR__ . "/config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";



$username   = $_SESSION['username'];
$branch_id  = $_SESSION['branch_id'];
// $role_login = $_SESSION['role'];

// ================= STATISTIK CEPAT (DB saja) =================
$total = $conn->query("SELECT COUNT(*) as total FROM customers WHERE branch_id=$branch_id")
->fetch_assoc()['total'];



// $stmt = $conn->prepare("
//   SELECT SUM(p.amount) AS total_income_this_month
//   FROM invoices i
//   INNER JOIN payments p ON p.invoice_id = i.invoice_number
//   WHERE MONTH(i.invoice_date) = MONTH(CURDATE())
//   AND YEAR(i.invoice_date) = YEAR(CURDATE()) AND i.branch_id=$branch_id
//   ");
// $stmt->execute();
// $total_income = $stmt->get_result()->fetch_assoc()['total_income_this_month'] ?? 0;

$stmt = $conn->prepare("
  SELECT SUM(amount) AS total_income_this_month
  FROM payments
  WHERE MONTH(created_at) = MONTH(CURDATE())
    AND YEAR(created_at) = YEAR(CURDATE())
    AND branch_id = ?
");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$total_income = $stmt->get_result()->fetch_assoc()['total_income_this_month'] ?? 0;



$sql_today = "SELECT SUM(amount) as total_today FROM payments WHERE DATE(created_at)=CURDATE() AND branch_id=?";
$stmt = $conn->prepare($sql_today);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$total_today = $stmt->get_result()->fetch_assoc()['total_today'] ?? 0;
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />


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


</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

<main class="app-main mt-4">
  <div class="container-fluid">

    <!-- Statistik -->
    <div class="row mb-2">
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="stat-card" style="background: linear-gradient(135deg,#1abc9c,#16a085)">
          <div><h3 class="mb-0"> <strong class="total-customer">0</strong></h3><p>Total Pelanggan</p></div>
          <i class="fas fa-users fa-3x"></i>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="stat-card" style="background: linear-gradient(135deg,#e74c3c,#c0392b)">
          <div><h3 class="mb-0"><strong class="total-isolir">0</strong></h3><p>Isolir</p></div>
          <i class="fas fa-ban fa-3x"></i>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-lg-4">
        <div class="stat-card" style="background: linear-gradient(135deg,#f39c12,#d35400)">
          <div><h3 class="mb-0"> <strong> Rp <?= number_format($total_income, 0, ',', '.') ?></strong></h3><p>Income</p></div>
          <i class="fas fa-money-bill-transfer fa-3x"></i>
        </div>
      </div>
    </div>

    <!-- Resource & System Info -->
    <div class="row mb-2">
      <div class="col-md-4">
        <div class="card" style="height:270px">
          <div class="card-header bg-secondary text-white"><i class="fa fa-microchip"></i> Resource</div>
          <div class="card-body">
            <label>CPU Load</label>
            <div class="progress mb-2"><div id="cpu_bar" class="progress-bar bg-danger" style="width:0%">0%</div></div>

            <label>Memory</label>
            <div class="progress mb-2"><div id="mem_bar" class="progress-bar bg-info" style="width:0%">0%</div></div>

            <label>HDD</label>
            <div class="progress mb-2"><div id="hdd_bar" class="progress-bar bg-primary" style="width:0%">0%</div></div>

            <p class="mt-2">Health: <span id="voltage">-</span>V | <span id="temperature">-</span>°C</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card" style="height:270px">
          <div class="card-header bg-secondary text-white"><i class="bi bi-info-circle"></i> System Info</div>
          <div class="card-body">
            <table class="table table-sm">
              <tr><td>Uptime</td><td id="uptime">-</td></tr>
              <tr><td>Board</td><td id="board">-</td></tr>
              <tr><td>Model</td><td id="model">-</td></tr>
              <tr><td>RouterOS</td><td id="routeros">-</td></tr>
              <tr><td>Arch</td><td id="architecture">-</td></tr>
              <tr><td>CPU</td><td id="cpufreq">-</td></tr>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card" style="height:270px">
          <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <div><i class="fas fa-plug"> </i> Offline Users <span id="offlineCount" class="badge rounded-pill bg-danger">0</span></div>
            
          </div>
          <div class="card-body p-2">
            <div class="table-responsive" style="max-height:200px; overflow-y:auto;">
              <table class="table table-sm" id="offlineTable">
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Traffic -->
    <div class="row mb-2">
      <div class="col-md-12 mt-2">
        <div class="card">
          <div class="card-header bg-secondary text-white"><i class="fas fa-network-wired"></i> Traffic Monitoring</div>
          <div class="card-body"><canvas id="ifaceChart" height="150"></canvas></div>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
 

// Format ukuran
  function formatSize(bytes){
    if(bytes===0) return "0 B";
    const k=1024, sizes=["B","KB","MB","GB","TB"];
    let i=Math.floor(Math.log(bytes)/Math.log(k));
    return (bytes/Math.pow(k,i)).toFixed(2)+" "+sizes[i];
  }

// Update resource
  function updateResource(){
    $.getJSON("pages/dashboard/get_data.php", data=>{
      if(data.error) return console.error(data.error);

      $("#uptime").text(data.uptime);
      $("#board").text(data.board);
      $("#model").text(data.model);
      $("#routeros").text(data.routeros);
      $("#architecture").text(data.architecture);
      $("#cpufreq").text(data.cpufreq);

      $("#cpu_bar").css("width",data.cpu+"%").text(data.cpu+"%");

      let mem_used=data.total_memory-data.free_memory;
      let mem_perc=Math.round(mem_used/data.total_memory*100);
      $("#mem_bar").css("width",mem_perc+"%").text(formatSize(mem_used)+" / "+formatSize(data.total_memory));

      let hdd_used=data.total_hdd-data.free_hdd;
      let hdd_perc=Math.round(hdd_used/data.total_hdd*100);
      $("#hdd_bar").css("width",hdd_perc+"%").text(formatSize(hdd_used)+" / "+formatSize(data.total_hdd));

      $("#voltage").text(data.voltage);
      $("#temperature").text(data.temperature);
    });
  }
  setInterval(updateResource, 3000); updateResource();

// Traffic chart
  let ctx=document.getElementById('ifaceChart').getContext('2d');
  let ifaceChart=new Chart(ctx,{
    type:'line',
    data:{labels:[],datasets:[
      {label:'Rx (Mbps)',data:[],borderColor:'blue',backgroundColor:'rgba(0,0,255,0.1)',fill:true,tension:0.3,pointRadius:0},
      {label:'Tx (Mbps)',data:[],borderColor:'red',backgroundColor:'rgba(255,0,0,0.1)',fill:true,tension:0.3,pointRadius:0}
    ]},
    options:{responsive:true,scales:{y:{beginAtZero:true,title:{display:true,text:'Mbps'}}}}
  });

  function fetchTraffic(){
    fetch("get_iface_traffic.php").then(r=>r.json()).then(data=>{
      if(data.error) return console.error(data.error);
      ifaceChart.data.labels.push(data.time);
      ifaceChart.data.datasets[0].data.push(data.rx);
      ifaceChart.data.datasets[1].data.push(data.tx);
      if(ifaceChart.data.labels.length>20){
        ifaceChart.data.labels.shift();
        ifaceChart.data.datasets[0].data.shift();
        ifaceChart.data.datasets[1].data.shift();
      }
      ifaceChart.update();
    });
  }
  setInterval(fetchTraffic, 3000);
</script>

<script>
  function loadOfflineUsers(){
  $.getJSON("pages/dashboard/get_offline_pppoe.php", function(res){
    let tbody = $("#offlineTable tbody");
    tbody.empty();

    if(res.error){
      tbody.append(`<tr><td colspan="2">${res.html}</td></tr>`);
      $("#offlineCount").text("0").removeClass("bg-success").addClass("bg-danger");
      return;
    }

    if(res.online){
      tbody.append(`<tr><td colspan="2">${res.html}</td></tr>`);
      $("#offlineCount").text("0").removeClass("bg-danger").addClass("bg-success");
      return;
    }

    res.forEach(u=>{
      tbody.append(`<tr><td>${u.name}</td><td>${u.last_logout}</td></tr>`);
    });

    $("#offlineCount").text(res.length).removeClass("bg-success").addClass("bg-danger");
  });
}



setInterval(loadOfflineUsers, 10000); // refresh tiap 10 detik
loadOfflineUsers();

</script>

<script>
  $(document).ready(function(){
   function loadSummary(){
    $.getJSON("pages/customer/customer_api.php?action=summary", function(res){
      if(res.success){
        $(".total-customer").text(res.total);
        $(".total-isolir").text(res.isolir);
      }
     }).fail(function(xhr, status, err){
      console.error("AJAX error:", status, err);
    });
  }
  loadSummary();

  setInterval(loadSummary, 10000);


  });

</script>

<?php if (isset($_SESSION['login_success'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<script>
    Swal.fire({
        title: 'Login Berhasil',
        html: `
          <lottie-player 
            src="https://assets1.lottiefiles.com/packages/lf20_jbrw3hcz.json"  
            background="transparent"  
            speed="1"  
            style="width: 200px; height: 200px; margin:auto;"  
            autoplay>
          </lottie-player>
          <p style="margin-top:10px;"><?php echo $_SESSION['login_success']; ?></p>
        `,
        showConfirmButton: false,
        timer: 2000
    });
</script>
<?php unset($_SESSION['login_success']); endif; ?>

<?php include BASE_PATH."includes/footer.php"; ?>
