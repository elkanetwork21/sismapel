<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();



}

include __DIR__ . "../../config.php";

// ✅ Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard");
    session_destroy();
    exit();
}



// ✅ Generate token sekali saat login
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ Cek token dari GET
if (!isset($_GET['token']) || $_GET['token'] !== $_SESSION['csrf_token']) {
    header("Location: " . BASE_URL . "dashboard");
    session_destroy();
    exit();
}

$branch_id = $_SESSION['branch_id'];
$role_id   = $_SESSION['role_id'];
$user_id = $_SESSION['user_id'];


$conn->query("UPDATE users SET last_activity = NOW() WHERE id = '$user_id'");


// Halaman sekarang
$current_page = basename($_SERVER['PHP_SELF']); 

// Ambil daftar izin role dari database
$allowed = [];
$res = $conn->query("SELECT page FROM role_permissions WHERE role_id=$role_id");
while($r = $res->fetch_assoc()){
    $allowed[] = $r['page'];
}

// Jika halaman tidak ada di izin → tolak
if (!in_array($current_page, $allowed)) {
    // Bisa diarahkan ke dashboard / error page
    header("Location: " . BASE_URL . "403");
    exit();
}
