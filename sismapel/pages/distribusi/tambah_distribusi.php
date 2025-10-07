<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";

$branch_id = $_SESSION['branch_id']; 
$result = $conn->query("SELECT id, type, name FROM distribusi ORDER BY id DESC");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Tambah Distribusi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Fonts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <!-- Bootstrap & AdminLTE -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" />

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  <style>
    body { font-family: 'Poppins', sans-serif; background: #f5f7fa; }
    #map { height: 350px; border-radius: 12px; }
    .card { border-radius: 12px; }
    .form-label { font-weight: 500; }
    .spinner { width: 50px; height: 50px; border: 5px solid #f3f3f3; border-top: 5px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }
    #loading { position: fixed; inset:0; background:#fff; display:flex; align-items:center; justify-content:center; z-index:9999; }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

<div id="loading"><div class="spinner"></div></div>

<main class="app-main">
  <div class="container-fluid py-3">
    <!-- <?php include BASE_PATH . "includes/breadcrumb.php"; ?> -->

    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white fw-semibold">
        <i class="bi bi-diagram-3"></i> Tambah Distribusi Baru
      </div>
      <div class="card-body">
        <form method="POST" action="save_distribusi.php" class="row g-3">

          <!-- Hidden Branch -->
          <input type="hidden" name="branch_id" value="<?= $branch_id; ?>">

          <!-- Map -->
          <div class="col-12">
            <label class="form-label">Lokasi Koordinat</label>
            <div id="map"></div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Latitude</label>
            <input class="form-control" id="lat" name="latitude" readonly required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Longitude</label>
            <input class="form-control" id="lng" name="longitude" readonly required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Type</label>
            <select class="form-select" name="type" id="typeSelect" required>
              <option value="">-- Pilih --</option>
              <option value="ODC">ODC</option>
              <option value="ODP">ODP</option>
              <option value="POP">POP</option>
              <option value="BTS">BTS</option>
              <option value="HTB">HTB</option>
              <option value="Others">Others</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Nama</label>
            <input class="form-control" type="text" name="name" placeholder="Nama distribusi" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Code</label>
            <input class="form-control" type="text" name="code" placeholder="Kode unik" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Port / Extra Port</label>
            <input class="form-control" type="number" name="port" min="1" placeholder="4" required>
          </div>

          <div class="col-md-12">
            <label class="form-label">Full Address</label>
            <textarea class="form-control" name="full_address" rows="2"></textarea>
          </div>

          <div class="col-md-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="2" placeholder="Deskripsi teknis" required></textarea>
          </div>

          <div class="col-md-12">
            <label class="form-label">From</label>
            <select class="form-select" name="from_id">
              <option value="">-- Pilih --</option>
              <?php while($row = $result->fetch_assoc()){ ?>
                <option value="<?= $row['id']; ?>"><?= $row['type']." - ".$row['name']; ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="col-12 text-end">
            <a href="distribusi?token=<?php echo $_SESSION['csrf_token']?>" class="btn btn-light border"><i class="bi bi-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>

<script>
  // init map
  const defaultLat = -6.2, defaultLng = 106.8;
  const map = L.map('map').setView([defaultLat, defaultLng], 13);
  L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {maxZoom: 20}).addTo(map);
  const marker = L.marker([defaultLat, defaultLng], {draggable:true}).addTo(map);

  function updateInputs(lat, lng){ 
    document.getElementById('lat').value = lat.toFixed(6); 
    document.getElementById('lng').value = lng.toFixed(6); 
  }
  updateInputs(defaultLat, defaultLng);

  marker.on('dragend', e => updateInputs(marker.getLatLng().lat, marker.getLatLng().lng));
  map.on('click', e => { marker.setLatLng(e.latlng); updateInputs(e.latlng.lat, e.latlng.lng); });

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(pos => {
      const lat = pos.coords.latitude, lng = pos.coords.longitude;
      map.setView([lat, lng], 15);
      marker.setLatLng([lat, lng]);
      updateInputs(lat, lng);
    });
  }

  // loading overlay
  window.addEventListener("load", ()=> document.getElementById("loading").style.display="none");
</script>

<?php include BASE_PATH . "includes/footer.php"; ?>
  