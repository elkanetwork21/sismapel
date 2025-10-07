<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";
include BASE_PATH . "includes/security_helper.php"; 

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Sistem Managemen Pelanggan Terintegrasi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap + Icons + AdminLTE -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <link href="<?php echo BASE_URL; ?>css/adminlte.css" rel="stylesheet">

  <style>
    body { font-family: 'Poppins', sans-serif; background:#f5f7fa; }
    .stat-card {
      padding:20px; border-radius:12px; color:#fff; display:flex; 
      align-items:center; justify-content:space-between;
      transition: transform .2s;
    }
    .stat-card:hover { transform: translateY(-4px); }
    .card-header { background: #f8f9fa; }

    .card-header {
      background: linear-gradient(45deg, #007bff, #00bcd4);
      color: #fff;
      border-radius: 16px 16px 0 0;

    /*Tombol Tambah*/
    .btn-tambah {
      position: fixed;
      bottom: 25px;
      right: 25px;
      border-radius: 50px;
      padding: 12px 28px;
      font-size: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 9999;

      /*Tombol Switch*/
      .switch { position: relative; display: inline-block; width: 50px; height: 24px; }
      .switch input {display:none;}
      .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #ccc; transition: .4s; border-radius: 24px; }
      .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; transition: .4s; border-radius: 50%; }
      input:checked + .slider { background: #4CAF50; }
      input:checked + .slider:before { transform: translateX(26px); }


    }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
  <main class="app-main mt-4">
    <div class="container-fluid">
      

      <!-- Statistik -->
      <div class="row mb-4">
        <div class="col-md-4 mt-2">
          <div class="stat-card" style="background:linear-gradient(135deg,#1abc9c,#16a085)">
            <div>
              <h4 class="mb-0"><strong class="total-customer">0</strong></h4>
              <small>Total Customer</small>
            </div>
            <i class="fas fa-users fa-3x"></i>
          </div>
        </div>
        <div class="col-md-4 mt-2">
          <div class="stat-card" style="background:linear-gradient(135deg,#e74c3c,#c0392b)">
            <div>
              <h4 class="mb-0"><strong class="total-isolir">0</strong></h4>
              <small>Isolir</small>
            </div>
            <i class="fas fa-ban fa-3x"></i>
          </div>
        </div>
        <div class="col-md-4 mt-2">
          <div class="stat-card" style="background:linear-gradient(135deg,#f39c12,#d35400)">
            <div>
              <h4 class="mb-0"><strong class="total-offline">0</strong></h4>
              <small>Offline</small>
            </div>
            <i class="fas fa-plug fa-3x"></i>
          </div>
        </div>
      </div>

      
      <div class="card shadow-sm mb-4">
        <div class="card-header">
          <div class="row">
            <div class="col">
              <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer </h5>
            </div>
            <div class="col text-end">
              <a href="tambah_customer?token=<?php echo $_SESSION['csrf_token']?>" class="btn btn-outline-light">
                <i class="bi bi-plus-circle"></i> Add
              </a>
            </div>
          </div>
          
          

        </div>
        <div class="card-body">
          <!-- Legend Badge -->
          <div class="mb-3">
            Legend :
            <span class="badge bg-success">Online</span>
            <span class="badge bg-secondary">Offline</span>
            <span class="badge bg-primary">Aktif</span>
            <span class="badge bg-danger">Isolir</span>
          </div>
          <div class="table-responsive">
            <table id="customerTable" class="table table-striped">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama</th>
                  <th>Alamat</th>
                  <th>Phone</th>
                  <th>Status</th> <!-- Online/Offline -->
                  <th>Isolir</th> <!-- Isolir/Aktif -->
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal -->
      <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog">
          <form id="customerForm" method="post" action="customer_api.php?action=update">>
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="id" id="id">
                <div class="mb-2">
                  <label>Nama</label>
                  <input type="text" class="form-control" name="fullname" id="fullname" required>
                </div>
                <div class="mb-2">
                  <label>No HP</label>
                  <input type="text" class="form-control" name="phone" id="phone">
                </div>
                <div class="mb-2">
                  <label>Email</label>
                  <input type="text" class="form-control" name="email" id="email" required>
                </div>
                <div class="mb-2">
                  <label>Paket</label>
                  <select class="form-control" name="paket" id="paket" required>
                    <option value="">-- Pilih Paket --</option>
                  </select>
                </div>
                <div class="mb-2">
                  <label>Alamat</label>
                  <textarea class="form-control" name="address" id="address"></textarea>
                </div>

              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary">Update</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
              </div>
            </div>
          </form>
        </div>
      </div>


    </div>
  </main>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  let table;
  $(document).ready(function(){

    table = $('#customerTable').DataTable({
      ajax: "customer_api.php?action=read",
      columns: [
        {
          data: null,
          render: function (data, type, row, meta) {
        return meta.row + 1; // nomor urut
      }
    },
    {data:"fullname"},
    {data:"address"},
    {data:"phone"},
    
    {
      data:"status",
      render:function(data){
        return data === "Online"
        ? `<span class="badge bg-success">Online</span>`
        : `<span class="badge bg-secondary">Offline</span>`;
      }
    },
    {
      data:"isolir",
      render:function(data){
        return data === "isolir"
        ? `<span class="badge bg-danger">Isolir</span>`
        : `<span class="badge bg-primary">Aktif</span>`;
      }
    },
    {data:null, render:function(data,type,row){
      return `
        <button class="btn btn-sm btn-warning"
      onclick='openEdit(${JSON.stringify(row)})'>Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteCustomer(${row.id},${row.odp_id})">Hapus</button>
        <a href="customer_detail?id=${row.id}&token=<?= $_SESSION['csrf_token']?>" class="btn btn-primary btn-sm">Detail</a>`;

      }}
    ]
  });

  });

  function loadPaketDropdown(selectedId = "", callback = null) {
    $.getJSON("customer_api.php?action=paket", function(res){
      if(res.success){
        let html = `<option value="">-- Pilih Paket --</option>`;
        res.data.forEach(p=>{
          html += `<option value="${p.id}" ${p.id == selectedId ? "selected" : ""}>${p.nama_paket}</option>`;
        });
        $("#paket").html(html);
      }
      if (typeof callback === "function") callback();
    });
  }

  function openEdit(row){
    $("#id").val(row.id);
    $("#fullname").val(row.fullname);
    $("#address").val(row.address);
    $("#phone").val(row.phone);
    $("#email").val(row.email);
    $("#paket_id").val(row.paket_id);
    $(".modal-title").text("Edit Customer")


  // load dropdown, lalu tampilkan modal setelah selesai
    loadPaketDropdown(row.paket_id, function(){
      let modal = new bootstrap.Modal(document.getElementById('customerModal'));
      modal.show();
    });
  }

  // Handle submit form edit / tambah
$("#customerForm").on("submit", function(e){
  e.preventDefault(); // cegah reload form

  $.ajax({
    url: $(this).attr("action"),  // customer_api.php?action=update
    type: "POST",
    data: $(this).serialize(),
    dataType: "json",
    success: function(resp){
      if(resp.success){
        // Tutup modal
        $("#customerModal").modal("hide");
        // Reload datatable
        table.ajax.reload();

        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: resp.message
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: resp.message
        });
      }
    },
    error: function(xhr, status, error){
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Terjadi kesalahan server: " + error
      });
    }
  });
});

  function deleteCustomer(id, odp_id){
    Swal.fire({
      title:"Hapus data?",
      text:"Data tidak bisa dikembalikan!",
      icon:"warning",
      showCancelButton:true,
      confirmButtonText:"Ya, hapus!"
    }).then(result=>{
      if(result.isConfirmed){
        $.post("customer_api.php?action=delete",{id:id, odp_id:odp_id},function(resp){
          if(resp.success){
            table.ajax.reload();
            Swal.fire("Terhapus", resp.message, "success");
          } else {
            Swal.fire("Error", resp.message, "error");
          }
        },"json");
      }
    });
  }

  function loadSummary(){
    $.getJSON("customer_api.php?action=summary", function(res){
      if(res.success){
        $(".total-customer").text(res.total);
        $(".total-isolir").text(res.isolir);
        $(".total-offline").text(res.offline);
      }
    });
  }

// panggil setiap reload halaman
  $(document).ready(function(){
    loadSummary();
  });

</script>

<script>
  <?php if ($_GET['msg'] ?? '' === 'success'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Simpan!',
      text: 'Data berhasil disimpan.',
      timer: 2000,
      showConfirmButton: false
    }).then(() => window.location.href = 'customer?token=<?php echo $_SESSION['csrf_token']?>');
  <?php elseif ($_GET['msg'] ?? '' === 'odp_penuh'): ?>
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'ODP Penuh.',
      timer: 2000,
      showConfirmButton: false
    }).then(() => window.location.href = 'customer?token=<?php echo $_SESSION['csrf_token']?>');
 
   <?php elseif ($_GET['msg'] ?? '' === 'deleted'): ?>
    Swal.fire({
      icon: 'success',
      title: 'Delete',
      text: 'Data berhasil dihapus.',
      timer: 2000,
      showConfirmButton: false
    }).then(() => window.location.href = 'customer?token=<?php echo $_SESSION['csrf_token']?>');
  <?php endif; ?>
</script>

<?php include BASE_PATH . "includes/footer.php"; ?>
