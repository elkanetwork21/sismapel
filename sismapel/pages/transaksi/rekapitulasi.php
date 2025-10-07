<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";

$branch_id = $_SESSION['branch_id'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Rekapitulasi Transaksi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css">

  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    
    body { font-family: 'Poppins', sans-serif; background:#f5f7fa; }
    .btn-back {
      position: fixed;
      bottom: 25px;
      left: 25px;
      border-radius: 50px;
      padding: 12px 28px;
      font-size: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 9999;
    }
    </style>

    <style>
  .row-debit {
    color: red !important;
    font-weight: bold;
  }
</style>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

<main class="app-main">
  <div class="container-fluid mt-4">

    <div class="card shadow-lg border-0 rounded-4">
      <!-- <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-back"><i class="bi bi-house"></i> Back to home</a> -->

      <div class="card-header bg-primary text-white">
        <div class="row">
        <div class="col">
          <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i> Rekapitulasi Transaksi</h5>
        </div>
        
      </div>

      </div>
      <div class="card-body">

        <!-- Filter Bulan & Tahun -->
        <form class="row g-3 mb-3" id="filterForm">
          <div class="col-md-5  ">
            <label class="form-label">Bulan</label>
            <select name="bulan" class="form-select">
              <?php 
              for ($i=1; $i<=12; $i++): 
                $selected = (date('m') == $i) ? "selected" : "";
              ?>
                <option value="<?= $i ?>" <?= $selected ?>><?= date("F", mktime(0,0,0,$i,1)) ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-5">
            <label class="form-label">Tahun</label>
            <select name="tahun" class="form-select">
              <?php 
              $tahunSekarang = date("Y");
              for ($t=$tahunSekarang-5; $t<=$tahunSekarang; $t++): 
                $selected = ($tahunSekarang == $t) ? "selected" : "";
              ?>
                <option value="<?= $t ?>" <?= $selected ?>><?= $t ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
          </div>
        </form>

        <div class="row mb-3">
          <div class="col-md-6" style="max-width: 300px; margin: auto;">
            <canvas id="pieChart"></canvas>
          </div>
          <div class="col-md-6">
            <canvas id="lineChart"></canvas>
          </div>
        </div>



        

          

        <!-- Ringkasan Bulanan -->
        <div class="row mb-3">
          
          <div class="col-md-4">
            <div class="p-3 bg-light border rounded">
              <strong>Total Pendapatan:</strong>
              <span id="totalPendapatan">Rp 0</span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 bg-light border rounded">
              <strong>Total Pengeluaran:</strong>
              <span id="totalPengeluaran">Rp 0</span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 bg-light border rounded">
              <strong>Saldo Akhir:</strong>
              <span id="saldoAkhir">Rp 0</span>
            </div>
          </div>
        </div>

        <!-- Table Rekap -->
        <div class="table-responsive">
          <table id="tabelRekap" class="table table-striped table-bordered w-100">
            <thead class="table-light">
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th>Nominal</th>
                <th>Saldo</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

      </div>
    </div>

  </div>
</main>

<script>
let pieChart; // simpan instance Chart.js

function updatePieChart(totalPemasukan, totalPengeluaran) {
    const ctx = document.getElementById('pieChart').getContext('2d');

    // jika chart sudah ada, update datanya
    if(pieChart) {
        pieChart.data.datasets[0].data = [totalPemasukan, totalPengeluaran];
        pieChart.update();
    } else {
        pieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Pemasukan', 'Pengeluaran'],
                datasets: [{
                    data: [totalPemasukan, totalPengeluaran],
                    backgroundColor: ['#4caf50', '#f44336'],
                    borderColor: ['#ffffff', '#ffffff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }
}

// reload ketika filter di-submit
$('#filterForm').on('submit', function(e){
  e.preventDefault();
  table.ajax.reload();
});

// DataTable dengan update pie chart
let table = $('#tabelRekap').DataTable({
  ajax: {
    url: "rekapitulasi_data.php",
    data: function(d){
      d.bulan = $('[name=bulan]').val();
      d.tahun = $('[name=tahun]').val();
    },
    dataSrc: function(json){
      // update ringkasan di luar tabel
      $('#totalPendapatan').text("Rp " + Number(json.total_pemasukan).toLocaleString('id-ID'));
      $('#totalPengeluaran').text("Rp " + Number(json.total_pengeluaran).toLocaleString('id-ID'));
      $('#saldoAkhir').text("Rp " + Number(json.saldo_akhir).toLocaleString('id-ID'));

      // update pie chart
      updatePieChart(json.total_pemasukan, json.total_pengeluaran);

      return json.data; // tetap tampilkan data di tabel
    }
  },
  columns: [
    { data: 0 },
    { data: 1 },
    { data: 2 },
    { data: 3 },
    { data: 4,
      render: function(data){
        if(isNaN(data) || data === "-" || data === null) return "-";
        return "Rp " + Number(data).toLocaleString('id-ID');
      }
    },
    { data: 5,
      render: function(data){
        if(isNaN(data) || data === "-" || data === null) return "-";
        return "Rp " + Number(data).toLocaleString('id-ID');
      }
    }
  ],
  order: [],
  pageLength: 50,

  
  // 🔴 Tambahkan ini untuk tandai debit
  createdRow: function(row, data, dataIndex) {
  if (data[2] && data[2].toLowerCase() === 'debit') {
    $(row).addClass('row-debit');
  }
}
});
</script>

<script>
const ctx = document.getElementById('lineChart').getContext('2d');

fetch('rekapitulasi_line.php')
.then(res => res.json())
.then(data => {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: data.pemasukan,
                    borderColor: '#4caf50',
                    backgroundColor: 'rgba(76, 175, 80, 0.2)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Pengeluaran',
                    data: data.pengeluaran,
                    borderColor: '#f44336',
                    backgroundColor: 'rgba(244, 67, 54, 0.2)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php include BASE_PATH . "includes/footer.php"; //  ?>
