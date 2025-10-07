<?php
session_start();
include __DIR__ . "../../../config.php";

$branch_id = $_POST['branch_id'];
$username  = $_POST['username'];
$tanggal   = $_POST['tanggal'];
$nominal   = $_POST['nominal'];
$keterangan= $_POST['keterangan'];

// Cek apakah metode 'Cash' sudah ada
$getMethod = $conn->query("SELECT id FROM payment_methods WHERE nama_metode='Cash' LIMIT 1");

if($getMethod && $getMethod->num_rows > 0){
    $row = $getMethod->fetch_assoc();
    $method_id = $row['id'];
} else {
    // Jika tidak ada, buat baru
    $stmt_method = $conn->prepare("INSERT INTO payment_methods (nama_metode, created_at) VALUES (?, NOW())");
    $nama = "Cash";
    $stmt_method->bind_param("s", $nama);
    if($stmt_method->execute()){
        $method_id = $stmt_method->insert_id;
    } else {
        die("Gagal membuat metode Cash: " . $conn->error);
    }
    $stmt_method->close();
}

// Simpan ke tabel payments
$stmt = $conn->prepare("INSERT INTO payments 
    (invoice_id, branch_id, method_id, account_id, amount, note, created_at, created_by) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$account_id = null; // default account_id
$invoice_id = $keterangan; // isi invoice_id dengan keterangan dari form
$now = date("Y-m-d H:i:s");

$stmt->bind_param("siiissss", 
    $invoice_id, 
    $branch_id, 
    $method_id,   // <-- selalu pakai Cash
    $account_id, 
    $nominal, 
    $keterangan, 
    $now, 
    $username
);

if($stmt->execute()){
    echo "success";
} else {
    echo "Gagal simpan: " . $stmt->error;
}
$stmt->close();
