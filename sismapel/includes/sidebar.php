<?php
include_once __DIR__ . '../../config.php';   

$username  = $_SESSION['username'];
$email  = $_SESSION['email'];
$branch_id = $_SESSION['branch_id'];


$result = $conn->query("SELECT * FROM branches WHERE id =$branch_id");

while($row = $result->fetch_assoc()) {

  $logo      = $row['logo'] ?? "";
}


date_default_timezone_set("Asia/Jakarta"); // Sesuaikan timezone

$hour = date("H");

if ($hour >= 5 && $hour < 12) {
  $greeting = "Selamat pagi";
} elseif ($hour >= 12 && $hour < 15) {
  $greeting = "Selamat siang";
} elseif ($hour >= 15 && $hour < 18) {
  $greeting = "Selamat sore";
} else {
  $greeting = "Selamat malam";
}

?>

<style>
  /* Hapus garis pemisah antar menu */
  .sidebar .nav-link,
  .sidebar .accordion-button {
    border: none !important;
  }

/* Style link sidebar */
.sidebar .nav-link,
.sidebar .accordion-body a {
  color: #333 !important;        /* warna teks default */
  text-decoration: none !important; /* hilangkan underline */
  display: block;
  padding: 6px 12px;
  border-radius: 6px;
  transition: background 0.2s;
}

/* Hover effect */
.sidebar .nav-link:hover,
.sidebar .accordion-body a:hover {
  background: #f0f0f0;
  color: #000 !important;
  text-decoration: none;
}

/* Aktif (yang terbuka/terpilih) */
.sidebar .accordion-button:not(.collapsed),
.sidebar .nav-link.active {
  background: #007bff;
  color: #fff !important;
}

/* Pastikan sidebar selalu solid */
.app-sidebar {
  background-color: #fff !important;   /* warna putih solid */
  backdrop-filter: none !important;    /* hilangkan efek blur/transparan */
  -webkit-backdrop-filter: none !important;
}

</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="app-wrapper">
  <nav class="app-header navbar navbar-expand bg-body">
    <!--begin::Container-->
    <div class="container-fluid">
      <!--begin::Start Navbar Links-->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
            <i class="bi bi-list"></i>
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <span class="d-none d-md-inline"><?= $greeting?> <strong><?=$username ?></strong></span>
      </ul>
    </div>
  </nav>
  <aside class="app-sidebar bg-body-primary shadow" data-bs-theme="">




    <div class="sidebar-wrapper">
      <div class="sidebar-header d-flex justify-content-center align-items-center" style="height:80px;">
        <a href="<?php echo BASE_URL; ?>index"><img 
        src="<?php echo BASE_URL; ?>pages/setting/general/images/<?= $logo?>" 
        alt="Logo Preview" 
        style="max-width:130px; height:auto;"
        ></a>
      </div>

      <nav class="mt-2">
        <ul
        class="nav sidebar-menu flex-column"
        data-lte-toggle="treeview"
        role="navigation"
        aria-label="Main navigation"
        data-accordion="false"
        id="navigation"
        >
        <li class="nav-item">
          <a href="<?php echo BASE_URL; ?>index?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
            <i class="nav-icon bi bi-house"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-hdd-network"></i>
            <p>
              Mikrotik Server
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/mikrotik/mikrotik?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Setting</p>
              </a>
            </li>
          </ul>
          <!-- <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/mikrotik/PPP_secret.php" class="nav-link">
                <p>PPP</p>
              </a>
            </li>
          </ul> -->
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/mikrotik/backup?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Backup/Restore</p>
              </a>
            </li>
          </ul>
          
        </li>



        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-journals"></i>
            <p>Paket & Service
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/produk/produk?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Paket Langganan</p>
              </a>
            </li>
          </ul>
          <!-- <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/produk/produk.php" class="nav-link">
                <p>Service</p>
              </a>
            </li>
          </ul>
          -->

        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-map"></i>
            <p>
              Distribusi
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/distribusi/distribusi?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>POP, ODC, ODP</p>
              </a>
            </li>
          </ul>

          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/coverage/coverage?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Coverage Area</p>
              </a>
            </li>
          </ul>



        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-people"></i>
            <p>
              Customer
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/customer/customer?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Dashboard</p>
              </a>
            </li>
          </ul>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/customer/tambah_customer?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Tambah Customer</p>
              </a>
            </li>
          </ul>

          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/customer/mapping?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Mapping</p>
              </a>
            </li>
          </ul>
        </li>


        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-files"></i>
            <p>
              Invoice
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/invoice/invoice?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Dashboard</p>
              </a>
            </li>
          </ul>

          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/invoice/invoice_new?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Manual Invoice</p>
              </a>
            </li>
          </ul>

          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/invoice/invoice_temp?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Temp Invoice</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fa fa-line-chart"></i>
            <p>
              Transaksi
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/transaksi/rekapitulasi?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Rekapitulasi</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/transaksi/pengeluaran?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Pengeluaran</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/transaksi/pendapatan?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Pendapatan Lainnya</p>
              </a>
            </li>
          </ul>
        </li>


        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-gear"></i>
            <p>
              Setting
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/setting/general/general?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>General</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/setting/branches/branch?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Branch</p>
              </a>
            </li>


            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/setting/invoice/invoice?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Invoice</p>
              </a>
            </li>

           <!--  <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/setting/payment/payment.php" class="nav-link">
                <p>Payment</p>
              </a>
            </li> -->
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/setting/user/user?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>User Management</p>
              </a>
            </li>
          <!--   <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/setting/otomatisasi/otomatisasi.php" class="nav-link">
                <p>Otomatisasi</p>
              </a>
            </li> -->
            <li class="nav-item">
              <a href="<?php echo BASE_URL; ?>pages/setting/role/role_manage?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
                <p>Role</p>
              </a>
            </li>
           <!--  <li class="nav-item">
              <a href="#" class="nav-link">
                <p>WhatsApp Gateway</p>
              </a>
            </li> -->
          </ul>
        </li>

        


        <li class="nav-item">
          <a href="<?php echo BASE_URL; ?>auth/logout?token=<?php echo $_SESSION['csrf_token']?>" class="nav-link">
            <i class="nav-icon bi bi-box-arrow-right"></i>
            <p >Logout</p>
          </a>
        </li>


      </ul>

    </nav>
  </div>
</aside>
