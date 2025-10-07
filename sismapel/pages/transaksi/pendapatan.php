<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php";
include BASE_PATH . "includes/sidebar.php";

$branch_id = $_SESSION['branch_id'];
$username  = $_SESSION['username'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Sistem Managemen Pelanggan Terintegrasi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <style>
    body { font-family: 'Poppins', sans-serif; background:#f5f7fa; }
    .card-header {
      background: linear-gradient(45deg, #28a745, #20c997);
      color: #fff;
      border-radius: 16px 16px 0 0;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

    
<main class="app-main">
  <div class="container-fluid mt-4">

    <div class="card shadow-lg border-0 rounded-4">
      <div class="card-header">
        <div class="row align-items-center">
          <div class="col">
            <h4 class="mb-0"><i class="bi bi-cash-coin me-2"></i> Pendapatan Lainnya</h4>
          </div>
              <div class="col text-end">
                <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalPendapatan">
                  <i class="bi bi-plus-circle me-1"></i> Input
                </button>
              </div>
            </div>
          </div>
          <div class="card-body">

            <!-- Filter -->
            <form class="row g-3 mb-3" id="filterForm">
              <div class="col-md-4">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="dari" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control">
              </div>
              <div class="col-md-3">
                <label class="form-label">Kategori</label>
                <select name="kategori" class="form-select">
                  <option value="">Semua</option>
                  <?php 
                  $kategori2 = $conn->query("SELECT id, nama_kategori FROM kategori_pengeluaran WHERE branch_id='$branch_id'");
                  while($row = $kategori2->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nama_kategori']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i>Search</button>
              </div>
            </form>

            <div class="table-responsive">
              <table id="tabelPendapatan" class="table table-striped w-100">
                <thead class="table-light">
                  <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <!-- <th>Kategori</th> -->
                    <th>Keterangan</th>
                    <th>Nominal</th>
                    <!-- <th>Lampiran</th> -->
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

          </div>
        </div>

      </div>
    </main>


<!-- Modal Tambah Pendapatan -->
<div class="modal fade" id="modalPendapatan" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-wallet2 me-2"></i> Tambah Pendapatan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="simpan_pendapatan.php" method="POST" enctype="multipart/form-data" id="pendapatanForm">
        <input type="hidden" name="branch_id" value="<?= $branch_id ?>">
        <input type="hidden" name="username" value="<?= $username ?>">

        <div class="modal-body">

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Tanggal</label>
              <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nominal</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="nominal" class="form-control" placeholder="0" required>
              </div>
            </div>
          </div>


          <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Tulis keterangan..." required></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  $("#pendapatanForm").on("submit", function(e){
    e.preventDefault();
    let formData = new FormData(this);

    $.ajax({
      url: "simpan_pendapatan.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function(res){
        if(res.trim() === "success"){
          Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: "Pendapatan berhasil disimpan",
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location.href = "pendapatan?token=<?php echo $_SESSION['csrf_token']?>";
          });
          $('#pendapatanForm')[0].reset();
          table.ajax.reload(null, false);
        } else {
          Swal.fire("Error!", res, "error");
        }
      }
    });
  });
</script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
  let table = $('#tabelPendapatan').DataTable({
  ajax: {
    url: 'get_pendapatan.php',
    type: 'GET',
    data: function(d) {
      d.dari     = $('input[name="dari"]').val();
      d.sampai   = $('input[name="sampai"]').val();
      d.kategori = $('select[name="kategori"]').val();
    },
    dataSrc: 'data'
  },
  columns: [
    { data: 'no' },
    { data: 'tanggal' },
    { data: 'keterangan' },
    { data: 'nominal' },
    { 
      data: 'id',
      render: function(id){
        return `
          <button class="btn btn-sm btn-danger btn-hapus" data-id="${id}">
            <i class="bi bi-trash"></i> Hapus
          </button>
        `;
      }
    }
  ]
});

</script>

<script>
  // Hapus Pendapatan
  $(document).on('click', '.btn-hapus', function(){
    let id = $(this).data('id');

    Swal.fire({
      title: 'Otorisasi Diperlukan',
      input: 'password',
      inputLabel: 'Masukkan Password Admin',
      inputPlaceholder: 'Password admin branch',
      inputAttributes: {
        autocapitalize: 'off',
        required: true
      },
      showCancelButton: true,
      confirmButtonText: 'Konfirmasi Hapus',
      cancelButtonText: 'Batal',
      preConfirm: (password) => {
        return $.ajax({
          url: 'hapus_pendapatan.php',
          type: 'POST',
          data: { id: id, password: password },
          dataType: 'json'
        }).then(res => {
          if(res.status !== 'success'){
            throw new Error(res.message);
          }
          return res;
        }).catch(err => {
          Swal.showValidationMessage(`Gagal: ${err}`);
        });
      }
    }).then((result) => {
      if(result.isConfirmed){
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Pendapatan berhasil dihapus'
        });
        table.ajax.reload(null,false);
      }
    });
  });
</script>

<script>
  $('#filterForm').on('submit', function(e) {
  e.preventDefault();
  table.ajax.reload(); // reload tabel pakai data terbaru
});


</script>
<?php include BASE_PATH . "includes/footer.php"; ?>