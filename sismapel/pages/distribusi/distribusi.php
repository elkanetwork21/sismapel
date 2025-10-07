<?php
session_start();

$username  = $_SESSION['username'] ?? null;
$branch_id = $_SESSION['branch_id'] ?? 0;

include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php";
include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "includes/security_helper.php";

// Ambil data distribusi menggunakan prepared statement
$sql = "SELECT id, type, name, code, latitude, longitude, full_address, port, available_port, from_id, description 
FROM distribusi
WHERE branch_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
  // pastikan numeric lat/lng valid atau kosong
  $row['latitude'] = is_null($row['latitude']) ? null : $row['latitude'];
  $row['longitude'] = is_null($row['longitude']) ? null : $row['longitude'];

  $data[] = $row;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Management Distribusi — Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <!-- AdminLTE css (if needed) -->
  <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL, ENT_QUOTES); ?>css/adminlte.css" />

  <!-- Leaflet & MarkerCluster -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />

  <style>
    body { font-family: 'Poppins', sans-serif; background:#f5f7fa; }
    .map-card { min-height: 520px; }
    #map { width:100%; height:520px; border-radius: .5rem; }
    .leaflet-container { border-radius: .5rem; }
    .spinner { width:48px; height:48px; border:5px solid rgba(0,0,0,0.08); border-top-color:#0d6efd; border-radius:50%; animation:spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .control-row { gap: .5rem; }
    .badge-port { font-size: .85rem; }
    /* small helper */
    .muted-small { font-size:.85rem; color:#6c757d; }
    /* table compact */
    table.dataTable tbody td { vertical-align: middle; }

    .details-control { cursor: pointer; text-align:center; font-weight:bold; }
    .child-card { background:#fff; border-radius:8px; padding:12px; box-shadow:0 1px 4px rgba(0,0,0,.1); margin-top:4px; }

  </style>

  <style>
      .btn-back {
      position: fixed;
      bottom: 25px;
      right: 25px;
      border-radius: 50px;
      padding: 12px 28px;
      font-size: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 9999;
    }
    .card-header {
      background: linear-gradient(45deg, #007bff, #00bcd4);
      color: #fff;
      border-radius: 16px 16px 0 0;
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open">

  <main class="app-main container-fluid my-4">
    
    <a href="tambah_distribusi?token=<?php echo $_SESSION['csrf_token']?>" class="btn btn-primary btn-sm btn-back"><i class="bi bi-plus-lg"></i> Tambah Data</a>

    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div class="col">
              <h5 class="mb-0"><i class="bi bi-map me-2"></i> Management Distribusi</h5>
              <div style="color:white" class="muted-small">Kelola POP / ODC / ODP & hubungan parent-child</div>
            </div>
            <div class="d-flex align-items-center control-row">
              
              <button id="btnClearAll" class="btn btn-outline-light"><i class="bi bi-x-lg"></i> Clear Lines</button>

           <!--  <div class="input-group input-group-sm" style="max-width:280px;">
              <input id="searchBox" class="form-control form-control-sm" placeholder="Cari nama / kode ..." aria-label="Search"/>
              <button id="btnSearch" class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i></button>
            </div> -->
            
          </div>
        </div>

        <div class="card-body">
          <div class="row g-3 mb-4">
            <div class="col-lg-8">
              <div class="card map-card border-0">
                <div class="card-body p-2">
                  <div id="map"></div>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="card border-0 mb-3">
                <div class="card-body">
                  <h6 class="card-title">Ringkasan</h6><br>
                  <p class="muted-small mb-2">Klik marker untuk opsi tracing atau menampilkan child nodes.</p>
                  <ul class="list-group list-group-flush" id="summaryList">
                    <!-- diisi oleh JS -->
                  </ul>
                </div>
              </div>

              
            </div> <!-- col-lg-4 -->
          </div> <!-- row -->

          <div class="row g-3">
            <div class="col-lg-12">
              <div class="card border-0">
                <div class="card-body ">
                  <div class="table-responsive">
                    <?php
                    $sql = "SELECT 
                    d.id,
                    d.name,
                    d.code,
                    d.type,
                    d.port,
                    d.available_port,
                    d.full_address,
                    d.from_id,
                    d.description,
                    p.name AS from_name,

                    CASE 
                    WHEN d.type = 'POP' THEN 
                    (SELECT GROUP_CONCAT(o.name SEPARATOR ', ') 
                     FROM distribusi o 
                     WHERE o.from_id = d.id AND o.type = 'ODC')
                    WHEN d.type = 'ODC' THEN 
                    (SELECT GROUP_CONCAT(o.name SEPARATOR ', ') 
                     FROM distribusi o 
                     WHERE o.from_id = d.id AND o.type = 'ODP')
                    WHEN d.type = 'ODP' THEN 
                    (SELECT GROUP_CONCAT(c.fullname SEPARATOR ', ') 
                      FROM customers c 
                      WHERE c.odp_id = d.id)
                    ELSE NULL
                  END AS children
                  FROM distribusi d
                  LEFT JOIN distribusi p ON d.from_id = p.id WHERE d.branch_id=$branch_id;";
                  $result = $conn->query($sql); ?>
                  <table id="distribusiTable" class="table table-hover table-sm">
                    <thead class="table-light">
                      <tr>


                        <th style="width:3%">#</th>
                        <th style="width:28%">Nama</th>
                        <th style="width:12%">Kode</th>
                        <th style="width:27%">Lokasi</th>
                        <th style="width:10%">Port</th>
                        <th style="width:10%">From</th>
                        <th style="width:10%">Aksi</th>
                        <th style="width:3%"></th>
                      </tr>
                    </thead>
                    <?php 
                    $no = 0;
                    while($row = $result->fetch_assoc()): 
                      $no++;

                      $h = fn($s)=>htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

                      if ($row['available_port'] == 0) {
                    $rowClass = "table-danger"; // merah
                  } elseif ($row['available_port'] > 4) {
        $rowClass = "table-success"; // hijau
      } else {
        $rowClass = "table-warning"; // oranye
      }

      ?>

      <tr class="<?= $rowClass ?>"
        data-id="<?= $h($row['id']) ?>"
        data-desc="<?= $h($row['description']) ?>"
        data-parent="<?= $h($row['from_name']) ?>"
        data-children="<?= $h($row['children']) ?>"
        data-alamat="<?= $h($row['full_address']) ?>">


      <!-- <tr 
      data-bs-toggle="collapse" 
      data-bs-target="#collapse<?= $no ?>" 
      aria-expanded="false" 
      aria-controls="collapse<?= $no ?>" 
      style="cursor:pointer;" 
      class="<?= $rowClass ?>"?> -->

      

      <td><?= $no?></td>
      <td><?= $row['name'] ?></td>
      <td><?= $row['code'] ?></td>
      <td><?= $row['description'] ?></td>
      <td><?= $row['available_port'],'/', $row['port'] ?></td>
      <td><?= $row['from_name'] ?? '-' ?></td>
      <td>
        <!-- <a href="#" class="bi bi-eye"></a> -->
        <a href= edit_distribusi?id=<?= secure_id($row['id'])?>&token=<?= $_SESSION['csrf_token']?> class="bi bi-pencil"></a>
        <a href="#" class="bi bi-trash text-danger btn-hapus" data-id="<?= $row['id']?>"></a>
        <!-- <a href="#" class="bi bi-trash" onclick='hapusData(<?php echo $row['id']?>)'></a> -->
        <!-- <button class='btn btn-outline-primary' onclick='hapusData(<?php echo $row['id']?>)'><i class="nav-icon bi bi-trash"></i></button> -->
      </td>
      <td class="details-control text-center" style="cursor:pointer">+</td>
    </tr>



  <?php endwhile; ?>

</table>
</div> <!-- .table-responsive -->
</div>
</div>
</div>
</div>



</div> <!-- card-body -->


</div> <!-- card -->
</div>
</div>
</main>

<!-- SCRIPTS (defer / load at bottom) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js" defer></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js" defer></script>

<script>
  // Safe-encoded data from PHP
  const distribusiData = <?php echo json_encode($data, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;

  // util escape (for innerHTML safe sections)
  function esc(s){ return String(s || '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;'); }

  document.addEventListener('DOMContentLoaded', function() {

    // builder konten detail
    function formatDetail(tr){
      const desc = tr.data('desc') || '-';
      const parent = tr.data('parent') || '-';
      const children = tr.data('children') || '-';
      const alamat = tr.data('alamat') || '-';
      return `
      <div class="card card-body">
        <div class="row g-3">

          <div class="col-md-4"><strong>Parent:</strong><br>${parent}</div>
          <div class="col-md-8"><strong>Alamat:</strong><br>${alamat || '-'}</div>
          <div class="col-12"><strong>Child:</strong><br>${children || '-'}</div>
        </div>
      </div>`;
    }

    $(function(){
      const table = $('#distribusiTable').DataTable({
        pageLength: 10,
        lengthMenu: [5,10,25,50,100],
        ordering: true,
        searching: true
      });

    // toggle child row
      $('#distribusiTable tbody').on('click', 'td.details-control', function () {
        const tr = $(this).closest('tr');
        const row = table.row(tr);

        if (row.child.isShown()) {
          row.child.hide();
          tr.removeClass('shown');
          $(this).text('+');
        } else {
          row.child( formatDetail(tr) ).show();
          tr.addClass('shown');
          $(this).text('−');
        }
      });
    });

    // ---- Map init ----
    const defaultCenter = distribusiData.length && distribusiData[0].latitude && distribusiData[0].longitude ?
    [parseFloat(distribusiData[0].latitude), parseFloat(distribusiData[0].longitude)] : [-6.200000, 106.816666];

    const map = L.map('map', { preferCanvas: true }).setView(defaultCenter, 13);

    // Tile layer (OpenStreetMap). Opsi: ganti ke tile server internal jika ada.
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // Marker cluster group (performa untuk banyak marker)
    const markers = L.markerClusterGroup({ chunkedLoading: true });
    const markerIndex = {}; // id => marker
    const coordsIndex = {}; // id => [lat,lng]
    const parentIndex = {}; // id => from_id

    // Only draw lightweight markers first; draw lines on demand
    distribusiData.forEach(item => {
      const id = item.id;
      parentIndex[id] = item.from_id;
      const lat = parseFloat(item.latitude);
      const lng = parseFloat(item.longitude);

      coordsIndex[id] = (isFinite(lat) && isFinite(lng)) ? [lat, lng] : null;

      if (coordsIndex[id]) {
        const marker = L.marker(coordsIndex[id], { title: item.name });

        let popupHtml = `<div style="min-width:200px">
            <b>${esc(item.type)} — ${esc(item.name)}</b><br>
            <small class="muted-small">Code: ${esc(item.code)}</small><br>
            <div style="margin-top:.5rem;">
              <button class="btn btn-sm btn-outline-primary" onclick="traceParent(${id})">Trace Parent</button>
              <button class="btn btn-sm btn-outline-success" onclick="showChildren(${id})">Show Children</button>
              <button class="btn btn-sm btn-outline-secondary" onclick="panToMarker(${id})">Center</button>
            </div>
      </div>`;

      marker.bindPopup(popupHtml);
      markers.addLayer(marker);
      markerIndex[id] = marker;
    }
  });

    map.addLayer(markers);

    // ---- Lazy polyline layers (draw only one set at a time) ----
    let activeLines = L.layerGroup().addTo(map);

    function clearActiveLines(){
      activeLines.clearLayers();
    }

    // Clear All Lines button
    document.getElementById('btnClearAll').addEventListener('click', function(){
      clearActiveLines();
    });

    // pan to marker
    window.panToMarker = function(id){
      const coords = coordsIndex[id];
      if(coords) {
        map.setView(coords, 16, { animate:true });
        const m = markerIndex[id];
        if(m) m.openPopup();
      } else {
        alert('Koordinat tidak tersedia untuk node ini.');
      }
    };

    // trace parent chain
    window.traceParent = function(id) {
      clearActiveLines();

      let curId = id;
      const chainCoords = [];

      // build chain from child -> parent -> parent...
      while(curId && parentIndex[curId]) {
        const curCoords = coordsIndex[curId];
        const parId = parentIndex[curId];
        const parCoords = coordsIndex[parId];

        if(!curCoords || !parCoords) break;
        chainCoords.push([curCoords, parCoords]);
        curId = parId;
      }

      // draw polylines
      if(chainCoords.length) {
        chainCoords.forEach(pair => {
          const line = L.polyline(pair, { color: '#ff7f0e', weight: 3, opacity: 0.9 }).addTo(activeLines);
        });
        // fit bounds to visible lines
        map.fitBounds(activeLines.getBounds(), { padding: [40,40] });
      } else {
        alert('Tidak ada parent chain (atau koordinat parent tidak tersedia).');
      }
    };

    // show direct children
    window.showChildren = function(id) {
      clearActiveLines();
      const parentCoords = coordsIndex[id];
      if(!parentCoords) { alert('Koordinat parent tidak tersedia.'); return; }

      // find children
      const children = distribusiData.filter(d => String(d.from_id) === String(id) && d.latitude && d.longitude);

      if(children.length === 0) {
        alert('Tidak ada child nodes untuk node ini.');
        return;
      }

      const lines = [];
      children.forEach(c => {
        const childCoords = coordsIndex[c.id];
        if(childCoords) {
          const l = L.polyline([parentCoords, childCoords], { color: '#20c997', weight: 2, dashArray: '6,4' }).addTo(activeLines);
          lines.push(l);
        }
      });

      if(lines.length) {
        const group = L.featureGroup(lines);
        map.fitBounds(group.getBounds(), { padding: [30,30] });
      }
    };

    // ---- Summary list (right column) ----
    const summaryEl = document.getElementById('summaryList');
    function buildSummary(){
      summaryEl.innerHTML = '';
      const counts = { POP:0, ODC:0, ODP:0, others:0 };
      distribusiData.forEach(d => {
        if(d.type in counts) counts[d.type]++; else counts.others++;
      });

      const summaryHtml = `
        <li class="list-group-item d-flex justify-content-between align-items-center">
          Total Node
          <span class="badge bg-primary rounded-pill">${distribusiData.length}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          POP
          <span class="badge bg-secondary rounded-pill">${counts.POP}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          ODC
          <span class="badge bg-secondary rounded-pill">${counts.ODC}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          ODP
          <span class="badge bg-secondary rounded-pill">${counts.ODP}</span>
        </li>
      `;
      summaryEl.innerHTML = summaryHtml;
    }
    buildSummary();

    // ---- Search / focus ----
    const searchBox = document.getElementById('searchBox');
    document.getElementById('btnSearch').addEventListener('click', function(){
      const q = searchBox.value.trim().toLowerCase();
      if(!q) return;
      // find first matching node by name or code
      const found = distribusiData.find(d => (d.name && d.name.toLowerCase().includes(q)) || (d.code && d.code.toLowerCase().includes(q)));
      if(found) {
        if(coordsIndex[found.id]) {
          panToMarker(found.id);
        } else {
          alert('Node ditemukan, namun koordinat tidak tersedia.');
        }
      } else {
        alert('Tidak ditemukan.');
      }
    });

    // Press Enter in search triggers search
    searchBox.addEventListener('keypress', function(e){
      if(e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('btnSearch').click();
      }
    });

    // Clicking table row centers the marker and opens popup
    $('#distribusiTable tbody').on('click', 'tr', function(){
      const id = $(this).data('id');
      if(id) panToMarker(id);
    });

    // // hapusData function (with confirm)
    // window.hapusData = function(id){
    //   // use native confirm or SweetAlert if included
    //   if(confirm('Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.')) {
    //     window.location.href = 'delete_distribution.php?id=' + encodeURIComponent(id);
    //   }
    // };

    // reduce initial heavy rendering (if many items)
    // MarkerCluster chunkedLoading handles chunking for markers.

    // END DOMContentLoaded
  }); // end DOMContentLoaded
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.addEventListener('DOMContentLoaded', function(){

  function bindDeleteButtons(){
    document.querySelectorAll('.btn-hapus').forEach(el => {
      el.addEventListener('click', function(e){
        e.preventDefault();
        const id = this.getAttribute('data-id');

        Swal.fire({
          title: 'Yakin hapus data ini?',
          text: "Data yang dihapus tidak bisa dikembalikan.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Ya, hapus!',
          cancelButtonText: 'Batal'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = 'delete_distribution.php?id=' 
              + encodeURIComponent(id) 
              + '&token=<?= $_SESSION['csrf_token']?>';
          }
        });
      });
    });
  }

  // panggil pertama kali
  bindDeleteButtons();

  // panggil ulang jika DataTable redraw
  $('#distribusiTable').on('draw.dt', function () {
    bindDeleteButtons();
  });

});
</script>

<script>
  <?php if ($_GET['msg']  === 'success'): ?>
  Swal.fire({
    icon: 'success',
    title: 'Simpan!',
    text: 'Data berhasil disimpan.',
    timer: 3000,
    showConfirmButton: false
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'distribusi?token=<?php echo $_SESSION['csrf_token']?>';
    }
  });


<?php elseif ($_GET['msg']  === 'deleted'): ?>
  Swal.fire({
    icon: 'success',
    title: 'Terhapus!',
    text: 'Data berhasil dihapus.',
    timer: 3000,
    showConfirmButton: false
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'distribusi?token=<?php echo $_SESSION['csrf_token']?>';
    }
  });


<?php elseif ($_GET['msg']  === 'used_distribusi'): ?>
  Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: 'Node sudah dipakai oleh node lain.',
    timer: 3000,
    showConfirmButton: false
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'distribusi?token=<?php echo $_SESSION['csrf_token']?>';
    }
  });

<?php elseif ($_GET['msg']  === 'used_customer'): ?>
  Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    text: 'Ada customer yang aktif di node tersebut.',
    timer: 3000,
    showConfirmButton: false
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'distribusi?token=<?php echo $_SESSION['csrf_token']?>';
    }
  });


<?php elseif ($_GET['msg']  === 'error'): ?>
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'Terjadi Kesalahan pada saat penyimpanan.',
    timer: 3000,
    showConfirmButton: false
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'distribusi?token=<?php echo $_SESSION['csrf_token']?>';
    }
  });
<?php endif; ?>

</script>


<?php include BASE_PATH . "includes/footer.php"; ?>
