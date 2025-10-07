<?php
$upload_dir = __DIR__ . "/backups/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $filename = basename($_FILES['file']['name']);
    $target = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        http_response_code(200);
        echo "✅ File backup berhasil diupload: " . $filename;
    } else {
        http_response_code(500);
        echo "❌ Gagal simpan file di server";
    }
} else {
    http_response_code(400);
    echo "❌ Tidak ada file yang dikirim";
}
