<?php
session_start();

// Halaman publik
$public_pages = ['login', 'register', 'logout'];

// Ambil nama file dari URL
$page = isset($_GET['file']) ? basename($_GET['file']) : 'index';

// Jika halaman butuh login dan user belum login → redirect
if (!in_array($page, $public_pages) && !isset($_SESSION['user_id'])) {
    header("Location: dashboard");
    exit;
}

// Jika file ada → load
if (file_exists($page . ".php")) {
    include $page . ".php";
} else {
    http_response_code(404);
    include echo BASE_URL; "pages/setting/otomatisasi.php";
}
