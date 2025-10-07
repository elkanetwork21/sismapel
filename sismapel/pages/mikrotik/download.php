<?php
$backup_dir = __DIR__ . "/backups/";

if (!isset($_GET['file'])) {
    die("❌ Tidak ada file yang dipilih.");
}

$file = basename($_GET['file']);
$path = $backup_dir . $file;

if (!file_exists($path)) {
    die("❌ File tidak ditemukan.");
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
