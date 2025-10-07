<?php
// session_start();
// include __DIR__ . "../../config.php";
// cek akses halaman

$role_id = $_SESSION['role'];
$current_page = basename($_SERVER['PHP_SELF']);
$q = $conn->query("SELECT 1 FROM role_permissions WHERE role_id=$role_id AND page='$current_page'");
if ($q->num_rows == 0) {
    echo "<div class='alert alert-danger'>Anda tidak punya akses ke halaman ini.</div>";
    exit;
}

?>