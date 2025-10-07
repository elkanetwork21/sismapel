<?php

session_start();


$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];




include __DIR__ . "../../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 

include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$API = getMikrotikConnection($branch_id);


$interfaces = [];
if ($API) {
    $interfaces = $API->comm("/interface/print");
    $API->disconnect();
}

// ambil default yg tersimpan di DB
$sql = "SELECT interface_name FROM default_interfaces WHERE branch_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$res = $stmt->get_result();
$default_iface = $res->fetch_assoc();
$default_name = $default_iface['interface_name'] ?? null;

?>
<!doctype html>
<html lang="en">
<!--begin::Head-->
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
    .dropzone {
      border: 2px dashed #007bff;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      color: #999;
      cursor: pointer;
    }
    .dropzone.dragover {
      background-color: #eaf4ff;
      border-color: #0056b3;
    }
    .dropzone img {
      max-width: 150px;
      margin-top: 10px;
    }
  </style>

  <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
  <style>
    .editor {
      min-height: 200px;
      border: 1px solid #ddd;
      padding: 10px;
    }
  </style>

  <style>
    table { border-collapse: collapse; width: 100%; margin-top:20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align:left; }
    th { background:#f4f4f4; }
    input, select { margin:5px; padding:5px; }
    button { padding:5px 10px; cursor:pointer; }
    td[contenteditable="true"] { background:#fafafa; }
  </style>
</head>
<!--end::Head-->
<!--begin::Body-->
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <div id="loading">
    <div class="spinner"></div>
  </div>
<!--begin::App Main-->
  <main class="app-main mt-4">
  <!--begin::App Content Header-->
    <div class="container-fluid">
    <!--begin::Container-->
      <!-- <?php include BASE_PATH . "includes/breadcrumb.php"; ?> -->
   
      <div class="row">

        <div class="col-md-6"> 

          <div class="card mb-4">
            <div class="card-header">
              <h5><i class="nav-icon bi bi-gear"></i> Branch Setting </h5>
            </div>
            <div class="card-body">
              <?php $result = $conn->query("SELECT * FROM branches WHERE id =$branch_id");

              while($row = $result->fetch_assoc()) {

                $logo      = $row['logo'] ?? "";

                ?>

                <form method="post" action="general_save.php" enctype="multipart/form-data">
                  <div class="form-group">
                    <label>Nama</label>
                    <input class="form-control mb-4" type="text" name="nama" value="<?= $row['nama_branch'];?>" required>
                  </div>

                  <div class="form-group mb-4">
                    <label>Alamat</label>
                    <textarea name="address" id="address"><?= $row['address'];?></textarea> 
                  </div>

                  <div class="form-group">
                    <div class="row">
                      <div class="col-md-6">
                        <label>Telephone :</label>
                        <input class="form-control mb-4" type="text" name="phone" value="<?= $row['phone'];?>" required>
                      </div>

                      <div class="col-md-6">
                        <label>Email :</label>
                        <input class="form-control" type="Email" name="email" value="<?= $row['email'];?>" required>
                      </div>


                    </div>
                  </div>


                  <div class="form-group">

                    <div id="dropzone" class="dropzone">
                      Drop logo here or click to upload
                      <input type="file" id="logo" name="logo" accept="image/*" hidden>
                      <div id="preview">
                        <?php if (!empty($logo) && file_exists(__DIR__ . "/images/" . $logo)): ?>
                        <img src="images/<?= $logo ?>" alt="Logo Preview">
                      <?php endif; ?>
                    </div>
                  </div>

                  <button type="submit" class="btn btn-outline-primary mt-4"><span class="bi bi-save"></span> Simpan</button>
                </form>
              </div>
            <?php }?>


          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h5><i class="nav-icon bi bi-bank"></i> Account </h5>
          </div>
          <div class="card-body">
            <button id="addAccountBtn" class="btn btn-outline-primary">Tambah Rekening</button>
            <div class="table-responsive">
            <table border="1" id="accountTable">
              <thead>
                <tr>
                  <th>Bank</th>
                  <th>No. Rekening</th>
                  <th>Atas Nama</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
            </div>

            



          </div>
        </div>


      </div>


   
      <div class="col-md-6">
        <div class="row">
          <div class="card mb-4">
            <div class="card-header">
              <h5><i class="nav-icon bi bi-gear"></i> Metode Pembayaran </h5>
            </div>
            <div class="card-body">
              <form id="addForm">

                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-4">
                        <input class="form-control" type="text" name="nama" placeholder="Nama" required>
                      </div>
                      <div class="col-md-4">
                        <input class="form-control" type="text" name="deskripsi" placeholder="Deskripsi">

                      </div>

                      <div class="col-md-4">
                        <select class="form-control" name="status">
                          <option value="aktif">Aktif</option>
                          <option value="nonaktif">Nonaktif</option>
                        </select>
                      </div>
                    </div>





                  </div>
                  <div class="card-footer text-end">
                    <button class="btn btn-outline-primary" type="submit">Tambah</button>
                  </div>
                </div>
              </form>
              <table id="paymentTable" class="">
                <thead>
                  <tr>
                    <th>Nama </th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>



            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h5><i class="nav-icon bi bi-gear"></i> Default Interface Monitoring </h5>
            </div>
            <div class="card-body">
              <form method="post" action="save_default_interface.php">
                <label>Pilih Default Interface:</label>
                <select name="interface_name" class="form-control" required>
                  <option value="">-- Pilih Interface --</option>
                  <?php foreach ($interfaces as $iface): ?>
                    <option value="<?= $iface['name'] ?>" <?= ($iface['name'] == $default_name ? 'selected' : '') ?>>
                      <?= $iface['name'] ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary mt-2">Simpan</button>
              </form>

            



            </div>
          </div>
        </div>
      </div>





</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Saat halaman selesai load, sembunyikan loading
  window.addEventListener("load", function(){
    document.getElementById("loading").style.display = "none";
    document.getElementById("content").style.display = "block";
  });
</script>


<script>
  <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Simpan!',
      text: 'Data berhasil disimpan.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'general?token=<?php echo $_SESSION['csrf_token']?>';
    });
  <?php endif; ?>
</script>

<script>
  <?php if (isset($_GET['msg']) && $_GET['msg'] == 'update'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Update!',
      text: 'Data berhasil diupdate.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      window.location.href = 'general?token=<?php echo $_SESSION['csrf_token']?>';
    });
  <?php endif; ?>
</script>

<script>
  ClassicEditor
  .create(document.querySelector('#address'))
  .catch(error => {
    console.error(error);
  });

</script>
<style>
  .dropzone {
    border: 2px dashed #007bff;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    color: #999;
    cursor: pointer;
  }
  .dropzone.dragover {
    background-color: #eaf4ff;
    border-color: #0056b3;
  }
  .dropzone img {
    max-width: 150px;
    margin-top: 10px;
  }
</style>

<script>
  const dropzone = document.getElementById("dropzone");
  const fileInput = document.getElementById("logo");
  const preview = document.getElementById("preview");

  dropzone.addEventListener("click", () => fileInput.click());
  dropzone.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropzone.classList.add("dragover");
  });
  dropzone.addEventListener("dragleave", () => {
    dropzone.classList.remove("dragover");
  });
  dropzone.addEventListener("drop", (e) => {
    e.preventDefault();
    dropzone.classList.remove("dragover");
    if (e.dataTransfer.files.length) {
      fileInput.files = e.dataTransfer.files;
      showPreview(fileInput.files[0]);
    }
  });
  fileInput.addEventListener("change", () => {
    if (fileInput.files.length) {
      showPreview(fileInput.files[0]);
    }
  });
  function showPreview(file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      preview.innerHTML = `<img src="${e.target.result}" alt="Logo Preview">`;
    };
    reader.readAsDataURL(file);
  }
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Load data
  function loadTable() {
    $.get("ajax.php?action=read", function(data) {
    console.log("AJAX Response:", data); // cek isi response
    $("#paymentTable tbody").html(data);
  }).fail(function(xhr, status, error) {
    console.error("AJAX Error:", status, error);
    console.error("Response Text:", xhr.responseText);
  });
}
// Insert data
$("#addForm").on("submit", function(e) {
  e.preventDefault();
  $.post("ajax.php?action=insert", $(this).serialize(), function() {
    loadTable();
    $("#addForm")[0].reset();
  });
});

// Delete data
$(document).on("click", ".deleteBtn", function() {
  if(confirm("Yakin hapus metode ini?")) {
    let id = $(this).data("id");
    $.post("ajax.php?action=delete", {id:id}, function() {
      loadTable();
    });
  }
});

// Update data on blur
$(document).on("blur", ".editable", function() {
  let id    = $(this).data("id");
  let field = $(this).data("field");
  let value = $(this).text();

  $.post("ajax.php?action=update", {id:id, field:field, value:value}, function(res) {
    console.log("Updated:", res);
  });
});

// Load awal
loadTable();
</script>


<script>
  // === CRUD akun bank ===
  function loadAccounts() {
    $.get("ajax_accounts.php?action=read", function(data) {
      $("#accountTable tbody").html(data);
    });
  }

// insert akun bank
  $("#addAccountBtn").click(function() {
    var bank = prompt("Nama Bank:");
    var number = prompt("Nomor Rekening:");
    var holder = prompt("Atas Nama:");
    var status = "aktif";

    if (bank && number && holder) {
      $.post("ajax_accounts.php?action=insert", {
        bank: bank,
        account_number: number,
        account_holder: holder,
        status: status
      }, function() {
        loadAccounts();
      });
    }
  });

// update akun bank inline
  $(document).on("blur", ".editableAccount", function() {
    var id = $(this).data("id");
    var field = $(this).data("field");
    var value = $(this).text();

    $.post("ajax_accounts.php?action=update", { id: id, field: field, value: value }, function(res) {
      console.log("Update account:", res);
    });
  });

// update status dropdown
  $(document).on("change", ".statusAccountDropdown", function() {
    var id = $(this).data("id");
    var value = $(this).val();

    $.post("ajax_accounts.php?action=update", { id: id, field: "status", value: value }, function(res) {
      console.log("Update status:", res);
    });
  });

// delete akun bank
  $(document).on("click", ".deleteAccountBtn", function() {
    if (confirm("Hapus rekening ini?")) {
      var id = $(this).data("id");
      $.post("ajax_accounts.php?action=delete", { id: id }, function() {
        loadAccounts();
      });
    }
  });

// load on page ready
  $(document).ready(function() {
    loadAccounts();
  });

</script>


<?php include BASE_PATH . "includes/footer.php"; //  ?> 