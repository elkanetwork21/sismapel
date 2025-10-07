<?php

$host = "localhost";
$user = "root";   // sesuaikan
$pass = "";       // sesuaikan
$db   = "dashboard"; // sesuaikan

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}



if (!defined('BASE_PATH')) define('BASE_PATH', __DIR__ . "/");
if (!defined('BASE_URL')) define('BASE_URL', "/sismapel/");
// root path project
// define("BASE_PATH", __DIR__ . "/");

// // url base project
// define("BASE_URL", "/sismapel/");

// define('APP_RUNNING', true);

// require_once __DIR__ . '/includes/security_helper.php';

?>
