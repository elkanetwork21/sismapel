<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'] ?? 0;


// Ambil paket langganan
$paket = $conn->query("SELECT id, nama_paket, rate_limit FROM paket_langganan WHERE branch_id='$branch_id' ORDER BY nama_paket ASC");

// Ambil ODP
$odp = $conn->query("SELECT id, name FROM distribusi WHERE type='ODP' AND branch_id='$branch_id' ORDER BY name ASC");
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Tambah Customer Baru</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- AdminLTE -->
  <link rel="stylesheet" href="<?= BASE_URL; ?>css/adminlte.css">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- jQuery (wajib untuk Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js" defer></script>

  <style>
    body { font-family: 'Poppins', sans-serif; background: #f5f7fa; }
    .card { border-radius: 15px; }
    .card-header { background: #fff; border-bottom: 1px solid #eee; }
    .form-label { font-weight: 500; }
    #map { height: 350px; border-radius: 12px; margin-bottom: 15px; }
    .option-group { display: flex; flex-wrap: wrap; gap: 10px; }
    .option-group input[type="radio"] { display: none; }
    .option-group label {
      padding: 10px 16px; border: 1px solid #2986cc; border-radius: 10px;
      cursor: pointer; transition: 0.3s; color: #2986cc;
    }
    .option-group input[type="radio"]:checked + label,
    .option-group label:hover {
      background: #2986cc; color: #fff;
    }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <main class="app-main">
    <div class="app-content-header">
      <div class="container-fluid">
        <?php include BASE_PATH . "includes/breadcrumb.php"; ?>
        <div class="card shadow-sm">
          <div class="card-header">
            <h5><i class="bi bi-person-plus"></i> Tambah Customer Baru</h5>
          </div>
          <div class="card-body">
            <form action="customer_save.php" method="POST" class="row g-3" novalidate>
              <!-- Map -->
              <div id="map"></div>
              <div class="row mb-3">
                <div class="col-md-3"><label class="form-label">Koordinat</label></div>
                <div class="col-md-4">
                  <input class="form-control" type="text" id="lat" name="latitude" readonly required>
                </div>
                <div class="col-md-5">
                  <input class="form-control" type="text" id="lng" name="longitude" readonly required>
                </div>
              </div>

              <!-- Secret + Paket -->
              <div class="row mb-3">
                <div class="col-md-3"><label class="form-label">PPP Secret (Mikrotik)</label></div>
                <div class="col-md-4">
                  <select name="ppp_secret" id="ppp_secret" class="form-select" required>
                    <option value="">Loading...</option>
                </select>
            </div>

                <div class="col-md-1"><label class="form-label">Paket</label></div>
                <div class="col-md-4">
                  <select class="form-select" name="paket_id" required>
                    <option value="">-- Pilih Paket --</option>
                    <?php while($row = $paket->fetch_assoc()): ?>
                      <option value="<?= $row['id']; ?>">
                        <?= $row['nama_paket']." - ".$row['rate_limit']; ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>

              <!-- Identitas -->
              <div class="row mb-3">
                <div class="col-md-3"><label class="form-label">Nama Lengkap</label></div>
                <div class="col-md-9">
                  <input type="text" name="fullname" class="form-control" required>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3"><label class="form-label">Alamat</label></div>
                <div class="col-md-9">
                  <textarea name="address" class="form-control" required></textarea>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-3"><label class="form-label">No HP</label></div>
                <div class="col-md-3">
                  <input type="text" name="phone" class="form-control">
                </div>
                <div class="col-md-1"><label class="form-label">Email</label></div>
                <div class="col-md-5">
                  <input type="email" name="email" class="form-control">
                </div>
              </div>

              <!-- ODP -->
              <div class="row mb-3">
                <div class="col-md-3"><label class="form-label">Pilih ODP</label></div>
                <div class="col-md-9">
                  <div class="option-group">
                    <?php while($o = $odp->fetch_assoc()): ?>
                      <input type="radio" id="odp<?= $o['id'] ?>" name="odp_id" value="<?= $o['id'] ?>" required>
                      <label for="odp<?= $o['id'] ?>"><?= $o['name'] ?></label>
                    <?php endwhile; ?>
                  </div>
                </div>
              </div>

              <!-- Keterangan -->
              <div class="row mb-3">
                <div class="col-md-3"><label class="form-label">Keterangan</label></div>
                <div class="col-md-9">
                  <textarea name="keterangan" class="form-control"></textarea>
                </div>
              </div>

              <!-- Buttons -->
              <div class="row mb-3">
                <div class="col-md-12 text-end">
                  <a href="customer.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                  </a>
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Map JS -->
  <script>
  document.addEventListener("DOMContentLoaded", () => {
    let defaultLat = -6.2, defaultLng = 106.8;
    let map = L.map('map').setView([defaultLat, defaultLng], 15);

    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
      maxZoom: 20,
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let marker = L.marker([defaultLat, defaultLng], {draggable:true}).addTo(map);

    function updateInputs(lat, lng){
      document.getElementById('lat').value = lat;
      document.getElementById('lng').value = lng;
    }
    updateInputs(defaultLat, defaultLng);

    marker.on('dragend', e=>{
      let pos = marker.getLatLng();
      updateInputs(pos.lat, pos.lng);
    });
    map.on('click', e=>{
      marker.setLatLng(e.latlng);
      updateInputs(e.latlng.lat, e.latlng.lng);
    });

    if(navigator.geolocation){
      navigator.geolocation.getCurrentPosition(pos=>{
        let lat = pos.coords.latitude, lng = pos.coords.longitude;
        map.setView([lat,lng], 16);
        marker.setLatLng([lat,lng]);
        updateInputs(lat,lng);
      },()=>console.warn("Gagal ambil lokasi"));
    }
  });
  </script>

 <script>
$(document).ready(function() {
  // 🔹 Inisialisasi Select2
  $('#ppp_secret').select2({
    placeholder: "-- Pilih Secret --",
    allowClear: true,
    width: '100%'
  });

  // 🔹 Load data dari API
  fetch("get_ppp_secret.php")
    .then(res => res.json())
    .then(res => {
      if(res.success){
        let select = $('#ppp_secret');
        select.empty().append('<option value=""></option>'); // clear dulu
        res.data.forEach(s => {
          let option = new Option(`${s.name} (${s.service})`, s.name, false, false);
          select.append(option);
        });
        select.trigger('change'); // refresh select2
      } else {
        $('#ppp_secret').empty().append('<option value="">Gagal ambil data</option>');
      }
    })
    .catch(err => {
      console.error(err);
      $('#ppp_secret').empty().append('<option value="">Error</option>');
    });
});
</script>



  <?php include BASE_PATH . "includes/footer.php"; ?>
