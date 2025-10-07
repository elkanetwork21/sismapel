<?php
session_start();

include __DIR__ . "../../../config.php";
require_once "mikrotik_connect.php";
include BASE_PATH . "includes/sidebar.php";


$branch_id = $_SESSION['branch_id'];


$API = getMikrotikConnection($branch_id);


$secrets = [];
$profiles = [];
$actives = [];

if ($API) {

    // Ambil semua PPP Secret
    $secrets = $API->comm("/ppp/secret/print");

    // Ambil semua Profile
    $profiles = $API->comm("/ppp/profile/print");

    // Ambil PPP Active
    $actives = $API->comm("/ppp/active/print");

    $API->disconnect();
}

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

<style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f7fa;
  }
</style>

<style>
    .status-indicator {
      display: inline-block;
      width: 14px;
      height: 14px;
      border-radius: 50%;
      background-color: #28a745; /* hijau */
      margin-right: 6px;
      animation: blink 1s infinite;
  }

  @keyframes blink {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.3; }
  }

</style>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

</head>
<!--end::Head-->
<!--begin::Body-->
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div id="loading">
    <div class="spinner"></div>
</div>

<main class="app-main">

    <div class="app-content-header">

      <div class="container-fluid">


        <?php include BASE_PATH . "includes/breadcrumb.php"; ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                      <button class="nav-link active" id="secret-tab" data-bs-toggle="tab" data-bs-target="#secret" type="button" role="tab">PPP Secret</button>
                  </li>
                  <li class="nav-item" role="presentation">
                      <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">PPP Profile</button>
                  </li>
                  <li class="nav-item" role="presentation">
                      <button class="nav-link" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">PPP Active</button>
                  </li>
              </ul>

              <div class="tab-content border border-top-0 p-3" id="myTabContent">

                <!-- PPP Secret -->
                <div class="tab-pane fade show active" id="secret" role="tabpanel">
                    <div class="card mb-4">
                        <div class="card-header">Tambah Secret</div>
                        <div class="card-body">
                          <form id="addSecretForm">
                            <div class="row g-3">
                              <div class="col-md-3">
                                <input type="text" class="form-control" name="name" placeholder="Name" required>
                            </div>
                            <div class="col-md-3">
                                <input type="password" class="form-control" name="password" placeholder="Password" required>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="service">
                                  <option value="pppoe">PPPoE</option>
                                  <option value="pptp">PPTP</option>
                                  <option value="l2tp">L2TP</option>
                                  <option value="ovpn">OVPN</option>
                              </select>
                          </div>
                          <div class="col-md-2">
                            <select class="form-select" name="profile">
                              <?php foreach ($profiles as $p): ?>
                                <option value="<?= $p['name'] ?>"><?= $p['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <table class="table table-striped" id="distribusiTable">
        <thead>
            <tr>
                <th>No</th>
                <th>Name</th>
                <th>Password</th>
                <th>Service</th>
                <th>Caller ID</th>
                <th>Profile</th>
                <th>Last Logout</th>
                <th>Last Caller ID</th>
                <th>Last Disconnect Reason</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; foreach ($secrets as $s): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $s['name'] ?? '-' ?></td>
                <td><?= $s['password'] ?? '-' ?></td>
                <td><?= $s['service'] ?? '-' ?></td>
                <td><?= $s['caller-id'] ?? '-' ?></td>
                <td><?= $s['profile'] ?? '-' ?></td>
                <td><?= $s['last-logged-out'] ?? '-' ?></td>
                <td><?= $s['last-caller-id'] ?? '-' ?></td>
                <td><?= $s['last-disconnect-reason'] ?? '-' ?></td>
                <td>
                    <button class="btn btn-sm btn-warning toggleSecret" data-id="<?= $s['.id'] ?>" data-status="<?= $s['disabled'] ?>">Toggle</button>
                    <button class="btn btn-sm btn-danger deleteSecret" data-id="<?= $s['.id'] ?>">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<div class="tab-pane fade" id="profile" role="tabpanel">
    <table class="table table-striped" id="profileTable">
        <thead>
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Local Address</th>
            <th>Remote Address</th>
            <th>Rate Limit</th>
            <th>Only One</th>
            <th>Shared Users</th>
        </tr>
    </thead>
    <tbody>
      <?php $no=1; foreach ($profiles as $p): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= $p['name'] ?? '-' ?></td>
        <td><?= $p['local-address'] ?? '-' ?></td>
        <td><?= $p['remote-address'] ?? '-' ?></td>
        <td><?= $p['rate-limit'] ?? '-' ?></td>
        <td><?= $p['only-one'] ?? '-' ?></td>
        <td><?= $p['shared-users'] ?? '-' ?></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="tab-pane fade" id="active" role="tabpanel">
  <table class="table table-hover" id="activeTable">
    <thead>
      <tr>
        <th>No</th>
        <th>Name</th>
        <th>Service</th>
        <th>Caller ID</th>
        <th>Address</th>
        <th>Uptime</th>
        <th>Encoding</th>
    </tr>
</thead>
<tbody>
  <?php $no=1; foreach ($actives as $a): ?>
  <tr>
    <td><?= $no++ ?></td>
    <td><?= $a['name'] ?? '-' ?></td>
    <td><?= $a['service'] ?? '-' ?></td>
    <td><?= $a['caller-id'] ?? '-' ?></td>
    <td><?= $a['address'] ?? '-' ?></td>
    <td><?= $a['uptime'] ?? '-' ?></td>
    <td><?= $a['encoding'] ?? '-' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

</div>


</div>
</div>
</div>


<script>
    // Saat halaman selesai load, sembunyikan loading
  window.addEventListener("load", function(){
    document.getElementById("loading").style.display = "none";
    document.getElementById("content").style.display = "block";
});
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>

  $(function(){
    const table = $('#distribusiTable').DataTable({
      pageLength: 10,
      lengthMenu: [5,10,25,50,100],
      ordering: true,
      searching: true
  });


});
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
      $('#secretTable').DataTable();
      $('#profileTable').DataTable();
      $('#activeTable').DataTable();
  });
</script>

<script>
    $(document).ready(function() {
      $('#secretTable').DataTable();

  // Tambah Secret
      $('#addSecretForm').submit(function(e) {
        e.preventDefault();
        $.post('ppp_action.php', $(this).serialize() + '&action=add', function(res){
          alert(res.message);
          location.reload();
      }, 'json');
    });

  // Toggle Enable/Disable
      $('.toggleSecret').click(function(){
        let id = $(this).data('id');
        let status = $(this).data('status');
        $.post('ppp_action.php', {id:id, status:status, action:'toggle'}, function(res){
          alert(res.message);
          location.reload();
      }, 'json');
    });

  // Delete Secret
      $('.deleteSecret').click(function(){
        if(confirm('Yakin hapus secret ini?')){
          let id = $(this).data('id');
          $.post('ppp_action.php', {id:id, action:'delete'}, function(res){
            alert(res.message);
            location.reload();
        }, 'json');
      }
  });
  });
</script>


</main>
