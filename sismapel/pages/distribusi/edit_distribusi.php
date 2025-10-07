<?php

session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "includes/security_helper.php";

$branch_id = $_SESSION['branch_id']; // otomatis ambil dari session
$id = validate_secure_id($_GET['id']); // decode
if ($id === false) {
    die("Data tidak ditemukan / ID tidak valid");
}

$stmt = $conn->prepare("SELECT * FROM distribusi WHERE id=? AND branch_id=?");
$stmt->bind_param("ii", $id, $branch_id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows == 0){
  echo "Data tidak ditemukan!";
  exit;
}
$data = $result->fetch_assoc();
?>
<!doctype html>
<html lang="en">
<!--begin::Head-->
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>AdminLTE v4 | Dashboard</title>
  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <!--end::Accessibility Meta Tags-->
  <!--begin::Primary Meta Tags-->
  <meta name="title" content="AdminLTE v4 | Dashboard" />
  <meta name="author" content="ColorlibHQ" />
  <meta
  name="description"
  content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance."
  />
  <meta
  name="keywords"
  content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant"
  />
  <!--end::Primary Meta Tags-->
  <!--begin::Accessibility Features-->
  <!-- Skip links will be dynamically added by accessibility.js -->
  <meta name="supported-color-schemes" content="light dark" />
  <link rel="preload" href="<?php echo BASE_URL; ?>css/adminlte.css" as="style" />
  <!--end::Accessibility Features-->
  <!--begin::Fonts-->
  <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
  integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
  crossorigin="anonymous"
  media="print"
  onload="this.media='all'"
  />
  <!--end::Fonts-->
  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
  crossorigin="anonymous"
  />
  <!--end::Third Party Plugin(OverlayScrollbars)-->
  <!--begin::Third Party Plugin(Bootstrap Icons)-->
  <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
  crossorigin="anonymous"
  />
  <!--end::Third Party Plugin(Bootstrap Icons)-->
  <!--begin::Required Plugin(AdminLTE)-->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css" />
  <!--end::Required Plugin(AdminLTE)-->
  <!-- apexcharts -->
  <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
  integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
  crossorigin="anonymous"
  />
  <!-- jsvectormap -->
  <link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
  integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
  crossorigin="anonymous"
  />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <style>
    /* Overlay background */
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

    /* Spinner animasi */
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

  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <style>
    body { font-family: 'Poppins', sans-serif; background:#f5f7fa; }
    #map { height: 400px; margin-bottom: 15px; }
  </style>

  
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div id="loading">
    <div class="spinner"></div>
  </div>

  <main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
      <!--begin::Container-->
      <div class="container-fluid">
        <?php include BASE_PATH . "includes/breadcrumb.php"; ?>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">
          <h4>Edit Distribusi </h4><small style="color:#aaa"><i>ODP, OCD, POP</i></small>
        </div>
        <div class="card-body">
          <form method="POST" action="update_distribusi.php">
            <input type="hidden" name="id" value="<?= $data['id']; ?>">
            <div id="map"></div>

            <div class="row mb-3">
              <div class="col-md-2">
                <label>Koordinat</label>
              </div>
              <div class="col-md-3">
                <input type="text" id="lat" name="latitude" class="form-control" value="<?= htmlspecialchars($data['latitude']) ?>" required>
              </div>
              <div class="col-md-3">
                <input type="text" id="lng" name="longitude" class="form-control" value="<?= htmlspecialchars($data['longitude']) ?>" required>
              </div>
            </div>

            <!-- Hidden Branch ID -->
            <input type="hidden" name="branch_id" value="<?php echo $branch_id; ?>">
            
            <div class="row mb-3">
              <div class="col-md-2">
                <label>Type</label>
              </div>
              <div class="col-md-10">


                <select name="type" class="form-control" required>
                  <option value="">-- Pilih Type --</option>
                  <option value="ODC" <?= $data['type']=="ODC"?"selected":"" ?>>ODC</option>
                  <option value="ODP" <?= $data['type']=="ODP"?"selected":"" ?>>ODP</option>
                  <option value="POP" <?= $data['type']=="POP"?"selected":"" ?>>POP</option>
                  <option value="BTS" <?= $data['type']=="BTS"?"selected":"" ?>>BTS</option>
                  <option value="HTB" <?= $data['type']=="HTB"?"selected":"" ?>>HTB</option>
                  <option value="Other" <?= $data['type']=="Other"?"selected":"" ?>>Other</option>
                </select>
              </div>
              
            </div>

            <div class="row mb-3">
              <div class="col-md-2">
                <label>Nama</label>
              </div>

              <div class="col-md-10">
                <input class="form-control" value="<?= htmlspecialchars($data['name']) ?>" type="text" name="name" required>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-2">
                <label>Code</label>
              </div>
              
              <div class="col-md-10">
                <input class="form-control" value="<?= htmlspecialchars($data['code']) ?>" type="text" name="code" required>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-2">
                <label>Port / Extra Port</label>
              </div>
              <div class="col-md-5">
                <input class="form-control" value="<?= htmlspecialchars($data['port']) ?>" type="number" name="port" placeholder="4" required>

              </div>

            </div>

            <div class="row mb-3">
              <div class="col-md-2">
                <label>Full Address</label>
              </div>
              <div class="col-md-10">
                <input class="form-control" value="<?= htmlspecialchars($data['full_address']) ?>" type="text" name="full_address" required>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-2">
                <label>Description</label>
              </div>
              <div class="col-md-10">
                <input class="form-control" value="<?= htmlspecialchars($data['description']) ?>" type="text" name="description"  required>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-2">
                <label>From</label>
              </div>
              <div class="col-md-10">
                <select name="dropdown_from" class="form-control">
                  <option value="">-- Pilih --</option>
                  <?php
                  $q = $conn->prepare("SELECT id, type, name FROM distribusi WHERE branch_id=? AND id<>?");
                  $q->bind_param("ii", $branch_id, $id);
                  $q->execute();
                  $res = $q->get_result();
                  while($row = $res->fetch_assoc()){
                    $selected = ($row['id'] == $data['dropdown_from']) ? "selected" : "";
                    echo "<option value='{$row['id']}' $selected>{$row['type']} - {$row['name']}</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            
            

          

        </div>
        <div class="card-footer text-end">
          <button type="submit" class="btn btn-outline-primary "><span class="bi bi-save"></span>Update</button>
          <a href="distribusi?token=<?= $_SESSION['csrf_token']?>" class="btn btn-outline-primary"><span class="bi bi-arrow-left"></span> Kembali</a>
        </div>
        </form>
      </div>
    </div>
  </main>




  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    // Lokasi awal dari database
    var lat = <?= $data['latitude']; ?>;
    var lng = <?= $data['longitude']; ?>;

    var map = L.map('map').setView([lat, lng], 20);

    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
      maxZoom: 20
    }).addTo(map);

    // Marker draggable
    var marker = L.marker([lat, lng], {draggable: true}).addTo(map);

    // Update input saat marker digeser
    marker.on('dragend', function (e) {
      var position = marker.getLatLng();
      document.getElementById("lat").value = position.lat;
      document.getElementById("lng").value = position.lng;
    });


  </script>


  <script>
    // Saat halaman selesai load, sembunyikan loading
    window.addEventListener("load", function(){
      document.getElementById("loading").style.display = "none";
      document.getElementById("content").style.display = "block";
    });
  </script>

  



  <?php include BASE_PATH . "includes/footer.php"; //  ?>