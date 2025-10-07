<?php
session_start();

include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";

$username  = $_SESSION['username'] ?? '';
$branch_id = $_SESSION['branch_id'] ?? 0;

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM customers WHERE id=? AND branch_id=?");
$stmt->bind_param("ii", $id, $branch_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
  echo "Data tidak ditemukan!";
  exit;
}
$data = $result->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Detail Customer | Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f7fa;
    }

    /* Loading */
    #loading {
      position: fixed;
      inset: 0;
      background: rgba(255,255,255,0.95);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .spinner {
      width: 60px;
      height: 60px;
      border: 6px solid #eee;
      border-top: 6px solid #007bff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg);} }

    /* Status online/offline */
    .status-indicator {
      width: 14px;
      height: 14px;
      border-radius: 50%;
      display: inline-block;
      margin-left: 6px;
    }
    .status-online { background: #28a745; box-shadow: 0 0 8px #28a745; }
    .status-offline { background: #dc3545; box-shadow: 0 0 8px #dc3545; }

    /* Gauge */
    canvas { max-width: 400px; max-height: 400px; }

    /* Table */
    #distribusiTable th {
      background: #f8f9fa;
    }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div id="loading"><div class="spinner"></div></div>

  <main class="app-main">
    <div class="app-content-header">
      <div class="container-fluid">
        <?php include BASE_PATH . "includes/breadcrumb.php"; ?>
      </div>

      <div class="container mt-4">
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Detail Customer</h5>
          </div>

          <div class="card-body">
            <div class="row mb-4">
              <div class="col-md-6 d-flex justify-content-center">
                <canvas id="gaugeDownload" width="400" height="400"></canvas>
              </div>

              <div class="col-md-6">
                <table class="table table-striped">
                  <tr><td><strong>Nama</strong></td>
                    <td><?= htmlspecialchars($data['fullname']); ?>
                      <span id="status-indicator" class="status-indicator"></span>
                    </td>
                  </tr>
                  <tr><td><strong>Alamat</strong></td><td><?= htmlspecialchars($data['address']); ?></td></tr>
                  <tr><td><strong>Phone</strong></td><td><?= htmlspecialchars($data['phone']); ?></td></tr>
                  <tr><td><strong>Email</strong></td><td><?= htmlspecialchars($data['email']); ?></td></tr>
                  <tr><td><strong>Secret</strong></td><td><span id="name">-</span></td></tr>
                  <tr>
                    <td><strong>Trafik</strong></td>
                    <td>
                      Tx: <span id="tx-<?= $data['ppp_secret'] ?>">0 bps</span> | 
                      Rx: <span id="rx-<?= $data['ppp_secret'] ?>">0 bps</span>
                    </td>
                  </tr>
                  <tr><td><strong>Total Byte</strong></td><td>Tx: <span id="download">-</span> | Rx: <span id="upload">-</span></td></tr>
                  <tr><td><strong>Uptime</strong></td><td><span id="uptime">-</span></td></tr>
                  <tr><td><strong>Last Logout</strong></td><td><span id="last_seen">-</span></td></tr>
                  <tr><td><strong>IP Address</strong></td><td><a id="ip_address" href="#" target="_blank">-</a></td></tr>
                </table>
              </div>
            </div>

            <h5 class="mt-4"><i class="bi bi-cash-stack"></i> History Pembayaran</h5>
            <table class="table" id="distribusiTable">
              <thead>
                <tr>
                  <th>No</th><th>No. Invoice</th><th>Tanggal Bayar</th><th>Metode</th>
                  <th>Rekening / E-Wallet</th><th>Jumlah</th><th>Catatan</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $sql = "
                  SELECT p.id, p.amount, p.note, p.created_at,
                         i.invoice_number, ba.bank_name, pm.nama_metode
                  FROM payments p
                  INNER JOIN invoices i on p.invoice_id = i.invoice_number
                  INNER JOIN payment_methods pm on p.method_id = pm.id
                  LEFT JOIN bank_accounts ba on p.account_id = ba.id
                  LEFT JOIN customers c on i.customer_id = c.id
                  WHERE c.id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $res = $stmt->get_result();
                $no = 1;
                while ($row = $res->fetch_assoc()):
                ?>
                <tr>
                  <td><?= $no++; ?></td>
                  <td><?= htmlspecialchars($row['invoice_number']); ?></td>
                  <td><?= date("d-m-Y H:i", strtotime($row['created_at'])); ?></td>
                  <td><?= htmlspecialchars($row['nama_metode']); ?></td>
                  <td><?= htmlspecialchars($row['bank_name']); ?></td>
                  <td>Rp <?= number_format($row['amount'], 0, ',', '.'); ?></td>
                  <td><?= htmlspecialchars($row['note']); ?></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

          <div class="card-footer text-end">
            <a href="customer.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Kembali</a>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js" defer></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js" defer></script>

  <script>
  // Loading
  window.addEventListener("load", ()=> {
    document.getElementById("loading").style.display = "none";
  });

  // DataTables
  document.addEventListener("DOMContentLoaded", ()=>{
    $('#distribusiTable').DataTable({ pageLength: 10 });
  });

  

  // Load Traffic
  function formatSpeed(bits){
    if(bits>=1e6) return (bits/1e6).toFixed(2)+" Mbps";
    if(bits>=1e3) return (bits/1e3).toFixed(2)+" Kbps";
    return bits+" bps";
  }
  async function loadTraffic(){
    let res = await fetch("get_txrx_customer.php");
    let data = await res.json();
    data.forEach(c=>{
      let tx=document.getElementById("tx-"+c.ppp_secret);
      let rx=document.getElementById("rx-"+c.ppp_secret);
      if(tx) tx.textContent=formatSpeed(c.tx);
      if(rx) rx.textContent=formatSpeed(c.rx);
    });
  }
  setInterval(loadTraffic,2000);

  // Load Detail
  async function loadData(){
    let secret="<?= $data['ppp_secret']; ?>";
    let res = await fetch("get_queue_customer.php?secret="+secret);
    let d = await res.json();
    document.getElementById("name").textContent = d.name || "-";
    document.getElementById("upload").textContent = d.upload_human || "-";
    document.getElementById("download").textContent = d.download_human || "-";
    document.getElementById("uptime").textContent = d.uptime || "-";
    document.getElementById("last_seen").textContent = d.last_seen || "-";
    let ip = document.getElementById("ip_address");
    if(d.ip_address && d.ip_address!="-"){ ip.textContent=d.ip_address; ip.href="http://"+d.ip_address;}
    else { ip.textContent="-"; ip.removeAttribute("href");}
    let status = document.getElementById("status-indicator");
    status.className = "status-indicator "+(d.active?"status-online":"status-offline");
  }
  setInterval(loadData,3000); loadData();
  </script>

  <script>
    const canvas = document.getElementById("gaugeDownload");
    const ctx = canvas.getContext("2d");

    let value = 0;
    const min = 0;
    const max = 10;
    const step = 1;

const startAngle = Math.PI * 0.66;  // 120°
const endAngle   = Math.PI * 2.34;  // 420°

function drawGauge(val) {
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  let cx = canvas.width / 2;
  let cy = canvas.height / 2;
  let radius = 160;

  // arc background abu-abu
  ctx.beginPath();
  ctx.arc(cx, cy, radius, startAngle, endAngle, false);
  ctx.lineWidth = 30;
  ctx.strokeStyle = "#ddd";
  ctx.stroke();

  // arc biru sesuai nilai
  let ratio = (val - min) / (max - min);
  let angleVal = startAngle + ratio * (endAngle - startAngle);
  ctx.beginPath();
  ctx.arc(cx, cy, radius, startAngle, angleVal, false);
  ctx.lineWidth = 30;
  ctx.strokeStyle = "#007bff";
  ctx.stroke();

  // angka skala di dalam lingkaran
  for (let v = min; v <= max; v += step) {
    let r = (v - min) / (max - min);
    let a = startAngle + r * (endAngle - startAngle);
    let tx = cx + Math.cos(a) * (radius * 0.8);
    let ty = cy + Math.sin(a) * (radius * 0.8);

    ctx.fillStyle = "#444";
    ctx.font = "16px Arial";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(v.toString(), tx, ty);
  }

  // jarum
  let nx = cx + Math.cos(angleVal) * (radius * 0.7);
  let ny = cy + Math.sin(angleVal) * (radius * 0.7);

  ctx.beginPath();
  ctx.moveTo(cx, cy);
  ctx.lineTo(nx, ny);
  ctx.lineWidth = 9;
  ctx.strokeStyle = "red";
  ctx.stroke();

  // lingkaran kecil di pusat jarum
  ctx.beginPath();
  ctx.arc(cx, cy, 10, 0, Math.PI * 2);
  ctx.fillStyle = "red";
  ctx.fill();

  // nilai
  ctx.fillStyle = "black";
  ctx.font = "40px Arial";
  ctx.textAlign = "center";
  ctx.fillText(val.toFixed(2), cx, cy + radius * 0.9);

  // label Mbps di bawahnya
  ctx.fillStyle = "#666";
  ctx.font = "18px Arial";
  ctx.fillText("Mbps", cx, cy + radius * 1.1);

  // label Mbps di bawahnya
  ctx.fillStyle = "#666";
  ctx.font = "18px Arial";
  ctx.fillText("Download", cx, cy + radius * 0.5);
}


async function fetchMikrotik() {
  try {
    let res = await fetch("get_ppp_trafic.php?ppp_secret=<?php echo $data['ppp_secret']; ?>");
    let data = await res.json();
    if (data.tx !== undefined) {
      return data.tx;

      document.getElementById("rxLabel").innerText = 
      "rx: " + data.rx + " Mbps";
    }
    
  } catch (e) {
    console.error("Error fetch MikroTik:", e);
  }
  return 0;
}

async function animate() {
  let target = await fetchMikrotik();
  let current = value;
  let stepVal = (target - current) / 50;
  let i = 0;

  function frame() {
    if (i < 50) {
      current += stepVal;
      drawGauge(current);
      i++;
      requestAnimationFrame(frame);
    } else {
      value = target;
      setTimeout(animate, 1000);
    }
  }
  frame();
}

animate();
</script>

  <?php include BASE_PATH . "includes/footer.php"; ?>
