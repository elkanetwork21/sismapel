<?php
session_start();
include __DIR__ . "../../../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile_type = $_POST['profile_type'] ?? '';
    $manual_name  = trim($_POST['manual_name'] ?? '');
    $manual_limit = trim($_POST['manual_limit'] ?? '');
    $auto_name    = trim($_POST['auto_name'] ?? '');
    $auto_limit   = trim($_POST['auto_limit'] ?? '');
    $harga        = (int) ($_POST['harga'] ?? 0);
    $pajak        = isset($_POST['pajak']) ? 1 : 0;
    $keterangan   = trim($_POST['keterangan'] ?? '');

    $harga_final = $harga;
    if ($pajak == 1) {
    $pajak_persen = 11; // PPN 11%
    $harga_final = $harga + ($harga * $pajak_persen / 100);
}
    // ✅ Validation
    $errors = [];

    if (!in_array($profile_type, ['simple','ppp','other'])) {
        $errors[] = "Tipe profile tidak valid.";
    }

    if ($profile_type === 'simple') {
        if ($manual_name === '' || strlen($manual_name) < 3) {
            $errors[] = "Nama profile simple wajib diisi (min 3 karakter).";
        }
        if (!preg_match('/^[0-9]+[KkMmGg]?\/[0-9]+[KkMmGg]?$/', $manual_limit)) {
            $errors[] = "Rate limit simple harus format contoh: 5M/5M.";
        }
    }

    if ($profile_type === 'ppp' && $auto_name === '') {
        $errors[] = "Profile PPP wajib dipilih.";
    }

    if ($harga <= 0) {
        $errors[] = "Harga harus lebih besar dari 0.";
    }

    // Jika error
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: tambah_produk.php"); // kembali ke form
        exit;
    }

    // ✅ Simpan data ke DB (gunakan prepared statement)
    $stmt = $conn->prepare("INSERT INTO paket_langganan 
        (branch_id, profile_type, nama_paket, harga_asli, harga_final, pajak, rate_limit, description) 
        VALUES (?, ?, ?, ?, ?, ?, ?,?)");
    $name = ($profile_type === 'simple') ? $manual_name : $auto_name;
    $limit = ($profile_type === 'simple') ? $manual_limit : $auto_limit;
    $branch_id = $_SESSION['branch_id'];

    $stmt->bind_param("issiiiss", $branch_id, $profile_type, $name, $harga, $harga_final, $pajak, $limit, $keterangan);
    $stmt->execute();

    header("Location: produk?msg=success&token=" . $_SESSION['csrf_token']);
    exit;
}
?>
