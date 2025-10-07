<?php
// produk_new.php
session_start();

include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/sidebar.php";

// Pastikan user/autentikasi jika perlu
$branch_id = $_SESSION['branch_id'] ?? 0;

// Ambil paket lokal (DB) jika masih diperlukan
$paket = $conn->query("SELECT * FROM paket_langganan WHERE branch_id='". (int)$branch_id ."'");

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>New Profile | SisMaPel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/adminlte.css">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <style>
    body { font-family: 'Poppins', sans-serif; background:#f5f7fa; }
    /* small improvements */
    .card-header h5 { margin:0; }
    .spinner-border-sm { width: 1rem; height: 1rem; border-width: .15em; }
    .option-group { display:flex; gap:10px; }
    .option-group input[type="radio"]{ display:none; }
    .option-group label { padding:10px 18px; border:1px solid #ddd; border-radius:8px; cursor:pointer; color:#0d6efd; }
    .option-group input[type="radio"]:checked + label { background:#0d6efd; color:#fff; border-color:#0d6efd; }
    @media (max-width:768px) {
      .col-md-3, .col-md-4, .col-md-9 { width:100%; display:block; }
    }
  </style>
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<main class="app-main">
  <div class="container mt-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div><h5><i class="bi bi-journals"></i> New Profile</h5></div>
        <div id="mk-status" class="text-muted small text-end">Mikrotik: <span id="mk-status-text">idle</span></div>
      </div>

      <div class="card-body">
        <form id="profileForm" action="produk_save.php" method="post" novalidate>
          <div class="row mb-3">
            <div class="col-md-3"><label class="form-label">Profile Type</label></div>
            <div class="col-md-9">
              <div class="option-group" role="radiogroup" aria-label="Profile Type">
                <input type="radio" id="simple" name="profile_type" value="simple" checked>
                <label for="simple">Simple Queue</label>

                <input type="radio" id="ppp" name="profile_type" value="ppp">
                <label for="ppp">PPP Profile</label>

                <input type="radio" id="other" name="profile_type" value="other">
                <label for="other">Other</label>
              </div>
            </div>
          </div>

          <div class="row mb-3 align-items-start">
            <div class="col-md-3"><label class="form-label">Profile</label></div>
            <div class="col-md-9">
              <ul class="nav nav-pills mb-2" id="pills-tab" role="tablist">
                <li class="nav-item me-2" role="presentation">
                  <button class="btn btn-outline-primary active" id="pills-manual-tab" data-bs-toggle="pill" data-bs-target="#pills-manual" type="button" role="tab">Manual</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="btn btn-outline-primary" id="pills-auto-tab" data-bs-toggle="pill" data-bs-target="#pills-auto" type="button" role="tab">Auto</button>
                </li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane fade show active" id="pills-manual" role="tabpanel">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Nama</label>
                      <input type="text" class="form-control" name="manual_name" placeholder="ex : Paket 5Mbps" required minlength="3" maxlength="50">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Rate Limit</label>
                      <input type="text" class="form-control" name="manual_limit" placeholder="ex : Upload/Download (5M/5M)" 
                      required pattern="^[0-9]+[KkMmGg]?/[0-9]+[KkMmGg]?$"
                      title="Format harus: Upload/Download, contoh 5M/5M">
                    </div>
                  </div>
                </div>

                <div class="tab-pane fade" id="pills-auto" role="tabpanel">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label>Pick PPP Profile</label>
                      <select name="auto_name" id="profile" class="form-control" disabled>
                        <option value="">-- Memuat profile dari Mikrotik --</option>
                      </select>
                    </div>

                    <div class="col-md-6 mb-3">
                      <label>Rate Limit</label>
                      <input type="text" name="auto_limit" id="rate_limit" class="form-control" readonly>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-3"><label class="form-label">Harga</label></div>
            <div class="col-md-4">
              <input type="number" class="form-control" name="harga" min="1000" max="10000000" required>
            </div>
            <div class="col-md-5 d-flex align-items-center">
              <div class="form-check form-switch ms-auto">
                <input class="form-check-input" type="checkbox" id="pajakSwitch" name="pajak" value="1">
                <label class="form-check-label" for="pajakSwitch">Pajak (11%)</label>
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-3"><label>Keterangan</label></div>
            <div class="col-md-9">
              <textarea name="keterangan" class="form-control" rows="3"></textarea>
            </div>
          </div>

          <div class="text-end">
            <a href="produk?token=<?php echo $_SESSION['csrf_token']?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
          </div>
        </form>
      </div>
    </div><!-- card -->
  </div><!-- container -->
</main>

<!-- JS libs -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function(){
  const profileSelect = document.getElementById('profile');
  const rateInput = document.getElementById('rate_limit');
  const mkStatusText = document.getElementById('mk-status-text');

  // fetch PPP profiles via AJAX (separate endpoint)
  function loadProfiles(){
    mkStatusText.textContent = 'loading...';
    mkStatusText.className = 'text-muted';

    fetch('get_ppp_profile.php', { cache: 'no-store' })
      .then(res => res.json())
      .then(json => {
        if (!json || !json.success){
          throw new Error(json && json.message ? json.message : 'Gagal memuat profile dari Mikrotik');
        }
        // kosongkan dan isi select
        profileSelect.innerHTML = '<option value="">-- Pilih Profile --</option>';
        json.data.forEach(item => {
          const opt = document.createElement('option');
          opt.value = item.name;
          opt.textContent = item.name;
          if (item.rate_limit) opt.setAttribute('data-ratelimit', item.rate_limit);
          profileSelect.appendChild(opt);
        });

        profileSelect.disabled = false;
        mkStatusText.textContent = 'ready';
        mkStatusText.className = 'text-success';
      })
      .catch(err => {
        console.error(err);
        mkStatusText.textContent = 'error';
        mkStatusText.className = 'text-danger';
        profileSelect.innerHTML = '<option value="">(error load profiles)</option>';
        profileSelect.disabled = true;
      });
  }

  // on change copy rate-limit
  profileSelect.addEventListener('change', function(){
    let selected = this.options[this.selectedIndex];
    rateInput.value = selected ? (selected.dataset.ratelimit || '') : '';
  });

  // initial load (only when PPP tab clicked perhaps) — load immediately is fine
  loadProfiles();

  // Optionally: reload profiles every 60s in background (to keep fresh)
  setInterval(loadProfiles, 60000);

  // Optional: form submit validation (basic)
  document.getElementById('profileForm').addEventListener('submit', function(e){
    // if auto tab selected and no profile chosen => prevent submit
    const activeTab = document.querySelector('#pills-manual-tab').classList.contains('active') ? 'manual' : 'auto';
    if (!document.getElementById('pills-manual-tab').classList.contains('active')) {
      // auto tab active
      if (!profileSelect.value) {
        e.preventDefault();
        Swal.fire({ icon:'warning', title:'Pilih profile', text:'Silakan pilih profile PPP terlebih dahulu.' });
        return false;
      }
    }
    // otherwise submit normally
  });

})();
</script>

<?php if (isset($_SESSION['errors']) && count($_SESSION['errors']) > 0): ?>
<script>
Swal.fire({
  icon: 'error',
  title: 'Validasi Gagal',
  html: `
    <ul style="text-align:left;">
      <?php foreach ($_SESSION['errors'] as $err): ?>
        <li><?= addslashes($err) ?></li>
      <?php endforeach; ?>
    </ul>
  `,
  confirmButtonText: 'Perbaiki'
});
</script>
<?php unset($_SESSION['errors']); endif; ?>


<?php include BASE_PATH . "includes/footer.php"; ?>
