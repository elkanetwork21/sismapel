  <?php

session_start();


include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/routeros_api.class.php";
include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";
include BASE_PATH . "includes/security_helper.php"; 

$id = validate_secure_id($_GET['id']); // decode
if ($id === false) {
    die("Data tidak ditemukan / ID tidak valid");
}

$branch_id = $_SESSION['branch_id']; // otomatis ambil dari session

// koneksi ke mikrotik
$API = getMikrotikConnection($branch_id);
$pppSecrets = [];

if ($API) {
  $pppProfiles = $API->comm("/ppp/profile/print");
  $API->disconnect();
}


// Ambil data produk dari database
$sql = "SELECT * FROM paket_langganan WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produk = $result->fetch_assoc();

?>


<!doctype html>
<html lang="en">
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


  <!-- loading animasi -->
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
  <!-- end loading animasi -->

  <!-- Font Style -->
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f7fa;
    }
  </style>


  <!-- Custom Radio Button -->
  <style>
    .option-group {
      display: flex;
      gap: 10px;
    }

    .option-group input[type="radio"] {
      display: none;
      color: #2986cc;
      border-color: #2986cc;
    }

    .option-group label {
      padding: 10px 20px;
      border: 1px solid #ccc;
      border-radius: 10px;
      cursor: pointer;
      transition: 0.3s;
      user-select: none;
      color: #2986cc;
      border-color: #2986cc;
    }

    .option-group input[type="radio"]:checked + label {
      background-color: #2986cc;
      color: white;
      border-color: #2986cc;
    }

    .option-group label:hover {
      background-color: #2986cc;
      color: white;
      border-color: #2986cc;
    }
  </style>

  
</head>
<!--end::Head-->
<!--begin::Body-->
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div id="loading">
    <div class="spinner"></div>
  </div>
<!--begin::App Main-->
<main class="app-main">
  <!--begin::App Content Header-->
  <div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
      <!-- <?php include BASE_PATH . "includes/breadcrumb.php"; ?> -->
    </div>
    <div class="container mt-4">

      <div class="card">
        <div class="card-header">
          <h5><span class="bi bi-pencil"></span> Edit Profile</h5>
        </div>

        <div class="card-body">
          <form action="produk_update.php" method="post">
            <div class="row mb-4">
              <input type="hidden" name="id_produk" value="<?= htmlspecialchars($id); ?>">
              <div class="col-md-3">
                <label>Profile Type</label>
              </div>

              <div class="col-md-9">

               <div class="option-group">


                <input type="radio" id="simple"  class="btn-check" name="profile_type" id="simple" value="simple" <?php if($produk['profile_type']=='simple') echo 'checked'; ?>>
                <label class="btn btn-outline-primary" for="simple">Simple Queue</label>

                <input type="radio" id="ppp" class="btn-check" name="profile_type" id="ppp" value="ppp" <?php if($produk['profile_type']=='ppp') echo 'checked'; ?>>
                <label class="btn btn-outline-primary" for="ppp">PPP Profile</label>

              </div>
            </div>

          </div>

          <div class="row mb-4">
            <div class="col-md-3">
              <label class="form-label">Profile</label>
            </div>


            <div class="col-md-8">
              <div class="tab-content" id="pills-tabContent">
                <!-- Simple -->
                <div class="tab-pane fade show active" id="pills-manual" role="tabpanel">
                  <div class="row">
                    <div class="col-md-3">
                      <label class="form-label">Nama</label>
                      <input type="text" class="form-control" name="manual_name" 
                      value="<?= htmlspecialchars($produk['nama_paket'])?>">
                    </div>
                    <div class="col-md-9">
                      <label class="form-label">Rate Limit</label>
                      <input type="text" class="form-control" name="manual_limit" value="<?= htmlspecialchars($produk['rate_limit'])?>">
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <div class="row mb-4">
            <div class="col-md-3">
              <label class="form-label">Harga</label>
            </div>
            <div class="col-md-4">
              <input type="number" class="form-control" name="harga" value="<?= ($produk['harga_asli'])?>" >
            </div>
            <div class="col-md-5">

              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="pajak" id="pajak" value="1" <?php if($produk['pajak']==1) echo 'checked'; ?>>
                <label class="form-check-label" for="pajak">Pajak (11%)</label>
              </div>

            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-3">
              <label>Keterangan</label>
            </div>
            <div class="col-md-9">
              <textarea name="keterangan" class="form-control"><?= htmlspecialchars($produk['description'])?></textarea>
            </div>
          </div>

          <div class="col">

            <div class="text-end">
              <a href="produk?token=<?php echo $_SESSION['csrf_token']?>" class="btn btn-outline-primary"><span class="bi bi-arrow-left"></span> Cancel</a>
              <button type="submit" class="btn btn-primary"><span class="bi bi-save"></span> Update</button>
            </div>
          </div>
          

        </form>
      </div>
    </div>
  </div>
</div>
</div>

</main>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (isset($_GET['msg']) && $_GET['msg']=="success") { ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: 'Data berhasil ditambahkan!',
      timer: 2000,
      showConfirmButton: false
    }).then(() => {
      window.location.href = "produk.php";
    });
  </script>
<?php } ?>



<script>
    // Saat halaman selesai load, sembunyikan loading
  window.addEventListener("load", function(){
    document.getElementById("loading").style.display = "none";
    document.getElementById("content").style.display = "block";
  });
</script>



<script>
  document.getElementById("profile").addEventListener("change", function(){
    let selected = this.options[this.selectedIndex];
    let rateLimit = selected.getAttribute("data-ratelimit");
    document.getElementById("rate_limit").value = rateLimit || "";
  });
</script>

<?php include BASE_PATH . "includes/footer.php"; //  ?>