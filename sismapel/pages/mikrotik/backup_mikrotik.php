<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mikrotik_id = intval($_POST['mikrotik_id']);

    // 🔹 Ambil data Mikrotik dari DB
    $sql  = "SELECT * FROM mikrotik_settings WHERE id=? AND branch_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $mikrotik_id, $branch_id);
    $stmt->execute();
    $result   = $stmt->get_result();
    $mikrotik = $result->fetch_assoc();

    if (!$mikrotik) {
        die("❌ Mikrotik tidak ditemukan!");
    }

    $API = getMikrotikConnection($branch_id);
    $API->debug = false;

    if ($API) {
        // 🔹 Nama file backup
        $backup_name = "backup_" . $mikrotik_id . "_" . date("Ymd_His");
        $backup_file = $backup_name . ".backup";

        // 🔹 1. Buat backup di Mikrotik
        $API->comm("/system/backup/save", [
            "name"         => $backup_name,
            "dont-encrypt" => "yes"
        ]);

        sleep(2); // tunggu sebentar biar file benar-benar tersimpan

        // 🔹 2. Upload backup ke server PHP
        $server_url = "http://localhost/sismapel/pages/mikrotik/upload.php"; 
        // ganti dengan URL server kamu
        $API->comm("/tool/fetch", [
            "url"      => $server_url,
            "upload"   => "yes",
            "src-path" => $backup_file
        ]);

        // 🔹 3. Simpan log ke DB
        $sql  = "INSERT INTO backup_logs (mikrotik_id, branch_id, filename, created_at) 
                 VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $mikrotik_id, $branch_id, $backup_file);
        $stmt->execute();

        $API->disconnect();

        // Redirect dengan pesan sukses
        header("Location: backup?msg=success&token=" . $_SESSION['csrf_token']);
        exit();
    } else {
        header("Location: backup.php?msg=error&token=" . $_SESSION['csrf_token']);
        exit();
    }
}
?>

<!-- 🔹 Animasi Spinner -->
<style>
#backup-loading {
  position: fixed;
  width: 100%;
  height: 100%;
  background: rgba(255,255,255,0.9);
  top: 0;
  left: 0;
  z-index: 9999;
  display: none;
  align-items: center;
  justify-content: center;
}
.spinner {
  width: 70px;
  height: 70px;
  border: 6px solid #f3f3f3;
  border-top: 6px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
@keyframes spin {
  0%   { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

<div id="backup-loading">
  <div class="spinner"></div>
  <p style="margin-top:15px;font-weight:bold;color:#333;">Sedang membuat backup, mohon tunggu...</p>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const form = document.querySelector("form[action='backup_mikrotik.php']");
  if(form){
    form.addEventListener("submit", function(){
      document.getElementById("backup-loading").style.display = "flex";
    });
  }
});
</script>
