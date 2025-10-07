<?php
session_start();

$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];

include __DIR__ . "../../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";

$result = $conn->query("SELECT * FROM setting_invoice WHERE branch_id='$branch_id'");
$row = $result->fetch_assoc();
$support  = $row['support'] ?? '';
$rekening = $row['rekening'] ?? '';
$syarat   = $row['syarat'] ?? '';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Setting Invoice</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f8fafc;
      animation: fadeIn 0.4s ease-in-out;
    }
    @keyframes fadeIn { from {opacity:0} to {opacity:1} }

    .card {
      border-radius: 16px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
      border: none;
    }
    .card-header {
      background: linear-gradient(45deg, #007bff, #00bcd4);
      color: #fff;
      border-radius: 16px 16px 0 0;
    }
    .form-label {
      font-weight: 600;
      margin-bottom: 6px;
    }
    .ck-editor__editable {
      min-height: 180px;
    }
    .btn-save {
      position: fixed;
      bottom: 25px;
      right: 25px;
      border-radius: 50px;
      padding: 12px 28px;
      font-size: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <main class="app-main mt-4">


    <div class="container-fluid">
      <!-- <?php include BASE_PATH . "includes/breadcrumb.php"; ?> -->
  
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="bi bi-journals me-2"></i> Setting Invoice</h5>
        </div>
        <div class="card-body">
          <form method="post" action="invoice_save.php" id="invoiceForm">
            <div class="mb-3">
              <label class="form-label">Supported</label>
              <textarea name="support" id="support"><?= $support;?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Rekening Pembayaran</label>
              <textarea name="rekening" id="rekening"><?= $rekening;?></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Default Syarat Faktur</label>
              <textarea name="snk" id="snk"><?= $syarat;?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-save">
              <i class="bi bi-save"></i> Simpan
            </button>
          </form>
        </div>
      </div>
    </div>
  </main>

<script>
  ['support','rekening','snk'].forEach(id=>{
    ClassicEditor.create(document.querySelector('#'+id)).catch(e=>console.error(e));
  });

  <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
    Swal.fire({ icon:'success', title:'Berhasil!', text:'Data berhasil disimpan', timer:2000, showConfirmButton:false });
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'update'): ?>
    Swal.fire({ icon:'success', title:'Update!', text:'Data berhasil diupdate', timer:2000, showConfirmButton:false });
  <?php endif; ?>
</script>

<?php include BASE_PATH . "includes/footer.php"; ?>
</body>
</html>
