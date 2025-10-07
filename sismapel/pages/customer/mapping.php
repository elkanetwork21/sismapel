<?php
session_start();


include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";
// include BASE_PATH . "/pages/mikrotik/routeros_api.class.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'];

// 🔹 Ambil data ODP
$sql_odp = "SELECT id, name, port, latitude, longitude 
FROM distribusi 
WHERE branch_id=? AND type='ODP'";
$stmt = $conn->prepare($sql_odp);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$res_odp = $stmt->get_result();
$odps = [];
while($row = $res_odp->fetch_assoc()){
  $row['total_cust'] = 0;
  $row['online_cust'] = 0;
  $odps[$row['id']] = $row;
}

// 🔹 Ambil data Customer
$sql_cust = "SELECT id, ppp_secret, fullname, address, phone, latitude, longitude, odp_id 
FROM customers 
WHERE branch_id=?";
$stmt = $conn->prepare($sql_cust);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$res_cust = $stmt->get_result();
$customers = [];
while($row = $res_cust->fetch_assoc()){
  $row['odp_name'] = isset($odps[$row['odp_id']]) ? $odps[$row['odp_id']]['name'] : '-';
  $row['online'] = 0;
  $customers[$row['ppp_secret']] = $row;
  if (isset($odps[$row['odp_id']])) {
    $odps[$row['odp_id']]['total_cust']++;
  }
}

// // 🔹 Koneksi MikroTik
// $API = getMikrotikConnection($branch_id);
// // $API->debug = false;

// if ($API) {
//   $actives = $API->comm("/ppp/active/print");
//   foreach($actives as $a){
//     $secret = $a['name'];
//     if(isset($customers[$secret])){
//       $customers[$secret]['online'] = 1;
//       if (isset($odps[$customers[$secret]['odp_id']])) {
//         $odps[$customers[$secret]['odp_id']]['online_cust']++;
//       }
//     }
//   }
//   $API->disconnect();
// }

$customers = array_values($customers);
$odps = array_values($odps);

$total_cust = count($customers);
$total_online = 0; // nanti dihitung via ajax
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Sistem Managemen Pelanggan Terintegrasi</title>
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
    .switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 24px;
    }
    .switch input {display:none;}
    .slider {
      position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
      background-color: #ccc; transition: .4s; border-radius: 24px;
    }
    .slider:before {
      position: absolute; content: ""; height: 18px; width: 18px; 
      left: 3px; bottom: 3px; background-color: white; 
      transition: .4s; border-radius: 50%;
    }
    input:checked + .slider { background-color: #4CAF50; }
    input:checked + .slider:before { transform: translateX(26px); }
  </style>

  <style>
    .tx-text {
      font-size: 12px;   /* contoh lebih kecil */
      font-weight: bold; /* biar lebih jelas */
      color: green;    /* optional: kasih warna */
    }
    .rx-text {
      font-size: 12px;   /* contoh lebih kecil */
      font-weight: bold; /* biar lebih jelas */
      color: red;    /* optional: kasih warna */
    }
  </style>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f7fa;
    }
  </style>

  <style>
    body { background: #f5f6fa; }
    .stat-card { color: white; text-align: center; padding: 20px; border-radius: 15px; }
    .bg-red { background: #e74c3c; }
    .bg-orange { background: #f39c12; }
    .bg-teal { background: #1abc9c; }
    .stat-value { font-size: 32px; font-weight: bold; }
    .stat-label { font-size: 16px; }
    .chart-container { height: 250px; }
  </style>

  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  <link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen/Control.FullScreen.css"/>
  <script src="https://unpkg.com/leaflet.fullscreen/Control.FullScreen.js"></script>

  <style>
    body { margin:0; padding:0; }
    #map { height: 70vh; }
    .leaflet-control-custom {
      background: white;
      padding: 6px;
      margin: 2px;
      border-radius: 4px;
      cursor: pointer;
      box-shadow: 0 2px 6px rgba(0,0,0,0.3);
      font-size: 13px;
    }
    .control-group {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }
  </style>

  

</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <!-- Loading overlay -->
  


  <main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
      <!--begin::Container-->
      <div class="container-fluid">
        <!-- <?php include BASE_PATH . "includes/breadcrumb.php"; ?> -->

        
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="leaflet-control-custom">
              <input type="text" id="searchInput" placeholder="Cari customer..." style="width:150px;">
              <button onclick="searchCustomer()">Cari</button>
            </div>
            <div id="map"></div>
          </div>
        </div>
      </div>
    </div>
    
  </main>
</body>

<script>
  let customers = <?=json_encode($customers)?>;
  let odps = <?=json_encode($odps)?>;


  let map = L.map('map', { fullscreenControl: true }).setView([-6.2, 106.8], 18);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  // L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {

    maxZoom: 25,
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  let customerLayers = [];
  let odpLayers = [];

// 🔹 Tombol Clear Lines
  let clearControl = L.control({position:'topright'});
  clearControl.onAdd = function(map){
    let div = L.DomUtil.create('div','leaflet-control-custom');
    div.innerHTML = 'Clear Lines';
    div.onclick = function(){
      customerLayers.forEach(l=>map.removeLayer(l));
      customerLayers = [];
    }
    return div;
  };
  clearControl.addTo(map);

  function searchCustomer(){
    let q = document.getElementById("searchInput").value.toLowerCase();
    if(!q) return;

    let found = customers.find(c =>
      (c.fullname && c.fullname.toLowerCase().includes(q)) ||
      (c.phone && c.phone.toLowerCase().includes(q))
      );

    if(found && found.latitude && found.longitude){
      map.setView([found.latitude, found.longitude], 16);
      let popupHtml = `<b>${found.fullname}</b><br>${found.address}<br>ODP: ${found.odp_name}`;
      L.popup().setLatLng([found.latitude, found.longitude]).setContent(popupHtml).openOn(map);
    } else {
      alert("Customer tidak ditemukan!");
    }
  }



// //Summary

//   let statsControl = L.control({position:'bottomleft'});
//   statsControl.onAdd = function(){
//     let div = L.DomUtil.create('div','leaflet-control-custom');
//     div.innerHTML = `Total: <?=$total_cust?><br>Online: <?=$total_online?>`;
//     return div;
//   };
//   statsControl.addTo(map);



// 🔹 Filter Online / Offline / All
  let filterControl = L.control({position:'topright'});
  filterControl.onAdd = function(map){
    let div = L.DomUtil.create('div','leaflet-control-custom control-group');
    div.innerHTML = `
    <button class="btn btn-outline-secondary btn-sm" id="showAll">All</button>
    <button class="btn btn-outline-secondary btn-sm" id="showOnline">Online</button>
    <button class="btn btn-outline-secondary btn-sm" id="showOffline">Offline</button>
    `;
    return div;
  };
  filterControl.addTo(map);

  document.addEventListener("click", e=>{
    if(e.target.id=="showAll") renderCustomers(customers);
    if(e.target.id=="showOnline") renderCustomers(customers.filter(c=>c.online==1));
    if(e.target.id=="showOffline") renderCustomers(customers.filter(c=>c.online==0));
  });

// 🔹 Render ODP
  function renderODPs(data){
    odpLayers.forEach(l=>map.removeLayer(l));
    odpLayers = [];

    data.forEach(o=>{
      if(o.latitude && o.longitude){
        let marker = L.circleMarker([o.latitude, o.longitude], {
          radius: 6, color:'orange', fillColor:'orange', fillOpacity:0.9
          }).bindPopup(`
        <b>ODP: ${o.name}</b><br>
        Port: ${o.port}<br>
        Total Customer: ${o.total_cust}<br>
        Online: ${o.online_cust} / ${o.total_cust}
          `).addTo(map);
          odpLayers.push(marker);
        }
      });
  }

// 🔹 Render Customer
  function renderCustomers(data){
    customerLayers.forEach(l=>map.removeLayer(l));
    customerLayers = [];
    let bounds = [];

    data.forEach(c=>{
      if(c.latitude && c.longitude){
        let style = (c.online==1) ? {color:'blue', fillColor:'blue'} : {color:'red', fillColor:'red'};
        let marker = L.circleMarker([c.latitude, c.longitude], {
          radius:4, color:style.color, fillColor:style.fillColor, fillOpacity:0.8
          }).bindPopup(`
        <b>${c.fullname}</b><br>
        ${c.address}<br>
        ODP: ${c.odp_name}<br>
        Status: ${(c.online==1)?'ONLINE':'OFFLINE'}
          `).addTo(map);
          customerLayers.push(marker);
          bounds.push([c.latitude, c.longitude]);

          if(c.odp_id){
            let odp = odps.find(o=>o.id==c.odp_id);
            if(odp && odp.latitude && odp.longitude){
              let latlngs=[[odp.latitude,odp.longitude],[c.latitude,c.longitude]];
              if(c.online==1){
                let dashed=L.polyline(latlngs,{color:"blue",dashArray:"10,10",weight:1.5}).addTo(map);
                customerLayers.push(dashed);
                let offset=0;
                setInterval(()=>{
                  if(map.hasLayer(dashed)){
                    offset=(offset+1)%20;
                    dashed.setStyle({dashOffset:offset});
                  }
                },150);
              }else{
                let dashed=L.polyline(latlngs,{color:"red",dashArray:"5,10",weight:1.5}).addTo(map);
                customerLayers.push(dashed);
              }
            }
          }
        }
      });

    if(bounds.length>0){
      map.fitBounds(bounds,{padding:[50,50]});
    }
  }



// Load awal
  renderODPs(odps);
  renderCustomers(customers);
</script>




<script>
  // buat control summary sekali saja
  let statsControl = L.control({ position: "bottomleft" });
  statsControl.onAdd = function (map) {
    let div = L.DomUtil.create("div", "leaflet-control leaflet-control-custom");
    div.id = "statsBox";
    div.innerHTML = `Total: ${customers.length}<br>Online: 0`;
    return div;
  };
  statsControl.addTo(map);

  function updateOnlineStatus() {
    fetch("api_mikrotik.php")
    .then(res => res.json())
    .then(onlineSecrets => {
        // reset
      customers.forEach(c => c.online = 0);
      odps.forEach(o => o.online_cust = 0);

        // update berdasarkan secrets online
      customers.forEach(c => {
        if (onlineSecrets.includes(c.ppp_secret)) {
          c.online = 1;
          let odp = odps.find(o => o.id == c.odp_id);
          if (odp) odp.online_cust++;
        }
      });

        // render ulang peta
      renderODPs(odps);
      renderCustomers(customers);

        // update summary di box control
      let onlineCount = customers.filter(c => c.online == 1).length;
      document.getElementById("statsBox").innerHTML =
    `Total: ${customers.length}<br>Online: ${onlineCount}`;
  })
    .catch(err => console.error("MikroTik fetch error", err));
  }

  // jalankan sekali saat load
  updateOnlineStatus();

  // refresh otomatis tiap 30 detik
  setInterval(updateOnlineStatus, 30000);
</script>

<style>
  .leaflet-control-custom {
    background: white;
    padding: 6px 10px;
    border-radius: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.3);
    font-size: 13px;
    line-height: 1.4;
  }
</style>

<?php include BASE_PATH . "includes/footer.php"; //  ?>