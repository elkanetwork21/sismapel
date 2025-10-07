<?php
session_start();
include __DIR__ . "../../../config.php";

$branch_id  = $_POST['branch_id'] ?? 0;
$username   = $_POST['username'] ?? '';
$tanggal    = $_POST['tanggal'] ?? '';
$nominal    = $_POST['nominal'] ?? 0;
$kategori   = $_POST['kategori_id'] ?? 0;
$keterangan = $_POST['keterangan'] ?? '';
$lampiran   = "";

// Upload lampiran jika ada
if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == 0) {
    $targetDir = BASE_PATH . "uploads/pengeluaran/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . "_" . basename($_FILES['lampiran']['name']);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES['lampiran']['tmp_name'], $targetFile)) {
        $lampiran = $fileName;
    }
}

$sql = "INSERT INTO pengeluaran (branch_id, tanggal, nominal, kategori_id, keterangan, lampiran) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isdiss", $branch_id, $tanggal, $nominal, $kategori, $keterangan, $lampiran);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $conn->error;
}
