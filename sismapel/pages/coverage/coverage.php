<?php
session_start();
include __DIR__ . "../../../config.php";

include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";

$branch_id = $_SESSION['branch_id'];

// Ambil data ODP
$sql_odp = "SELECT id, name, port AS port_total, latitude, longitude,
(port - IFNULL((SELECT COUNT(*) FROM customers c WHERE c.odp_id=d.id),0)) AS port_available
FROM distribusi d
WHERE branch_id=? AND type='ODP'";
$stmt = $conn->prepare($sql_odp);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$res_odp = $stmt->get_result();
$odps = [];
while($row = $res_odp->fetch_assoc()){
  $odps[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ODP Coverage Map</title>

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>


  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css">

  <!-- Marker Cluster -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css"/>
  <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

  <!-- Leaflet Draw & Editable -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/leaflet-editable@1.2.0/src/Leaflet.Editable.js"></script>

          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <style>
    body { font-family: 'Poppins', sans-serif; background:#f5f7fa; }
    body,html { margin:0; padding:0; height:100%; }
    #map { height:100vh; width:100%; }

    /* Control Box Modern */
    .control-box {
      position:absolute; top:15px; right:15px; z-index:1000;
      background:#fff; padding:12px; border-radius:10px;
      box-shadow:0 4px 16px rgba(0,0,0,0.15);
      font-family:"Poppins", sans-serif; font-size:14px;
      width:280px; max-width:85vw;
    }
    .control-box h6 { margin:0 0 6px 0; font-weight:600; font-size:14px; }
    .control-box input, .control-box button {
      width:100%; margin:3px 0; padding:6px 8px;
      border:1px solid #ccc; border-radius:6px; font-size:13px;
    }
    .control-box button {
      cursor:pointer; background:#007bff; color:white; border:none;
      transition:0.2s;
    }
    .control-box button:hover { background:#0056b3; }

    /* Marker Blink */
    .blink-odp { animation:blink 1.5s infinite; }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

    .leaflet-popup-content { font-size:13px; line-height:1.4; }
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
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <main class="app-main mt-4">
    <div class="container-fluid">


      <div class="control-box">
        <h6>Pencarian Koordinat</h6>
        <input type="text" id="coordInput" placeholder="Contoh: -6.2,106.8">
        <button onclick="searchLocation()">Cari Lokasi</button>
        <hr>
        <h6>Filter ODP</h6>
        <button onclick="renderODPs(odps)">Semua ODP</button>
        <button onclick="renderODPs(odps.filter(o=>o.port_available>0))">Port Tersedia</button>
        <hr>
        <h6>Panjang Kabel</h6>
        <span id="cableLength">0 m</span>
      </div>

      <div id="map"></div>
    </div>
  </main>

  <script>
    let odps = <?= json_encode($odps) ?>;
    let map = L.map('map',{editable:true});
    let odpCluster = L.markerClusterGroup().addTo(map);
    let cableLayerGroup = L.layerGroup().addTo(map);

// Google Maps Satellite
    L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{
      maxZoom:25, attribution:'&copy; OpenStreetMap & Google'
    }).addTo(map);

    let coverageRadius=10;

// 🔹 Render ODP
    function renderODPs(data){
      odpCluster.clearLayers();
      data.forEach(o=>{
        if(o.latitude && o.longitude){
          let color = (o.port_available>0) ? '#1abc9c' : '#e74c3c';
          let marker = L.circleMarker([o.latitude,o.longitude],{
            radius:8, color:color, fillColor:color, fillOpacity:0.9,
            className:(o.port_available>0?"blink-odp":"")
            }).bindPopup(`
        <b>ODP: ${o.name}</b><br>
        Port Total: ${o.port_total}<br>
        Port Tersedia: ${o.port_available}
            `);
            odpCluster.addLayer(marker);

      // Coverage circle
            let circle = L.circle([o.latitude,o.longitude],{
              radius:coverageRadius, color:color, fillColor:color,
              stroke:false, fillOpacity:0.15
            });
            odpCluster.addLayer(circle);
          }
        });
      if(data.length>0) map.fitBounds(odpCluster.getBounds(),{padding:[40,40]});
      else map.setView([-6.2,106.8],12);
    }

// 🔹 Tambah marker calon customer
    function addCustomerMarker(lat,lng){
      let marker = L.marker([lat,lng],{
        icon:L.icon({iconUrl:'https://cdn-icons-png.flaticon.com/512/149/149071.png',iconSize:[28,28]}),
          draggable:true
        }).addTo(map).bindPopup(`Calon Customer<br>Lat:${lat.toFixed(6)}<br>Lng:${lng.toFixed(6)}`).openPopup();

  // cari ODP terdekat
      let nearest=null, minDist=Infinity;
      odps.forEach(o=>{
        if(o.port_available>0){
          let d=map.distance([lat,lng],[o.latitude,o.longitude]);
          if(d<minDist){ minDist=d; nearest=o; }
        }
      });
      if(!nearest){ alert("Tidak ada ODP dengan port tersedia!"); return; }

  // polyline
      let cable=L.polyline([[lat,lng],[nearest.latitude,nearest.longitude]],{
        color:'blue', weight:3, dashArray:'6,6'
      }).addTo(cableLayerGroup);
      cable.enableEdit();

      function refreshCable(){
        updateCableLength(cable);
        let latlngs=cable.getLatLngs(), total=0;
        for(let i=0;i<latlngs.length-1;i++){ total+=map.distance(latlngs[i],latlngs[i+1]); }
          let center=latlngs[Math.floor(latlngs.length/2)];
        cable.bindPopup("Panjang: "+Math.round(total)+" m").openPopup(center);
      }
      cable.on('editable:vertex:drag',refreshCable);
      cable.on('editable:vertex:deleted',refreshCable);
      cable.on('editable:edited',refreshCable);
      refreshCable();
    }

// 🔹 Hitung panjang kabel
    function updateCableLength(layer){
      let latlngs=layer.getLatLngs(), total=0;
      for(let i=0;i<latlngs.length-1;i++){ total+=map.distance(latlngs[i],latlngs[i+1]); }
        document.getElementById('cableLength').innerText=Math.round(total)+' m';
    }

// 🔹 Pencarian manual koordinat
    function searchLocation(){
      let val=document.getElementById('coordInput').value.trim();
      if(!val.includes(',')) return alert("Format: lat,lng");
      let [lat,lng]=val.split(',').map(Number);
      if(isNaN(lat)||isNaN(lng)) return alert("Koordinat tidak valid!");
      map.setView([lat,lng],18);
      addCustomerMarker(lat,lng);
    }

// 🔹 Event klik map → calon customer
    map.on('click',e=>addCustomerMarker(e.latlng.lat,e.latlng.lng));

// Load awal
    renderODPs(odps);
  </script>

<?php include BASE_PATH . "includes/footer.php"; ?>
