<?php
session_start();
include __DIR__ . "../../../config.php";

$branch_id = $_SESSION['branch_id'];

// Ambil parameter filter
$dari     = isset($_GET['dari']) ? $_GET['dari'] : '';
$sampai   = isset($_GET['sampai']) ? $_GET['sampai'] : '';
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Query dasar
$sql = "SELECT id, invoice_id, method_id, amount, note, created_at 
        FROM payments 
        WHERE branch_id='$branch_id' AND (invoice_id IS NULL OR invoice_id NOT LIKE 'INV%')";

// Filter tanggal
if(!empty($dari) && !empty($sampai)){
    $sql .= " AND DATE(created_at) BETWEEN '$dari' AND '$sampai'";
} elseif(!empty($dari)){
    $sql .= " AND DATE(created_at) >= '$dari'";
} elseif(!empty($sampai)){
    $sql .= " AND DATE(created_at) <= '$sampai'";
}

// Filter kategori
if(!empty($kategori)){
    $sql .= " AND method_id='$kategori'";
}

$sql .= " ORDER BY id DESC";
$query = $conn->query($sql);

$data = [];
$no = 1;
while($row = $query->fetch_assoc()){
    $data[] = [
        'no'        => $no++,
        'tanggal'   => date("Y-m-d", strtotime($row['created_at'])),
        'keterangan'=> $row['invoice_id'],
        'nominal'   => "Rp " . number_format($row['amount'],0,',','.'),
        'id'        => $row['id']
    ];
}

echo json_encode(["data"=>$data]);
