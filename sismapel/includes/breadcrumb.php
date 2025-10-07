<?php
// Ambil halaman aktif dari sidebar
$current_page = basename($_SERVER['PHP_SELF'], ".php");

$menu = [
  "index"     => ["title" => "Dashboard", "icon" => ""],
  "mikrotik"  => ["title" => "Router Management", "icon" => ""],
  "produk"   => ["title" => "Produk Management", "icon" => ""],
  "distribusi"    => ["title" => "Distribusi", "icon" => ""],
  "invoice"    => ["title" => "Invoice", "icon" => ""],
  "transaksi"    => ["title" => "Transaksi", "icon" => ""],
  "user"    => ["title" => "User Management", "icon" => ""],
  "general"    => ["title" => "General Setting", "icon" => ""],
  "payment"    => ["title" => "Payment Gateway", "icon" => ""],
  "otomatisasi"    => ["title" => "Automatisasi", "icon" => ""],
  "customer"    => ["title" => "Customer", "icon" => ""],
  "role_manage"    => ["title" => "Role Manage", "icon" => ""],
  "tambah_customer"    => ["title" => "Management Customer", "icon" => ""],
  "tambah_distribusi"    => ["title" => "Add Distribusi", "icon" => ""],
  "edit_distribusi"    => ["title" => "Edit Distribusi", "icon" => ""],
  "detail_mikrotik"    => ["title" => "Detail Mikrotik", "icon" => ""],
  "tambah_produk"    => ["title" => "Tambah Produk", "icon" => ""],
  "customer_detail"    => ["title" => "Detail Customer", "icon" => ""],
  "invoice_detail"    => ["title" => "Detail Invoice", "icon" => ""],
  "backup"    => ["title" => "Backup Mikrotik", "icon" => ""],
  "mapping"    => ["title" => "Mapping Customer", "icon" => ""],
  "invoice_new"    => ["title" => "Manual Invoice", "icon" => ""],
  "invoice_temp"    => ["title" => "Temporary Invoice", "icon" => ""]
];
?>

<div class="row">
  <div class="col-sm-6"></div>
  <div class="col-sm-6">
    <ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index"><span class="bi bi-house"></span> Home</a></li>
    <?php if (isset($menu[$current_page])): ?>
      <li class="breadcrumb-item active" aria-current="page">
        <i class="<?= $menu[$current_page]['icon'] ?>"></i>
        <?= $menu[$current_page]['title'] ?>
      </li>
    <?php endif; ?>
    </ol>
  </div>
</div> 