<?php
session_start();
include __DIR__ . "../../../config.php";

$branch_id = $_SESSION['branch_id'] ?? 0;

$dari     = $_POST['dari'] ?? '';
$sampai   = $_POST['sampai'] ?? '';
$kategori = $_POST['kategori'] ?? '';

$where = "WHERE p.branch_id = '$branch_id'";

// Filter tanggal
if (!empty($dari) && !empty($sampai)) {
    $where .= " AND p.tanggal BETWEEN '$dari' AND '$sampai'";
}

// Filter kategori
if (!empty($kategori)) {
    $where .= " AND p.kategori_id = '$kategori'";
}

$sql = "
    SELECT p.id, p.tanggal, p.keterangan, p.nominal, p.lampiran, 
           k.nama_kategori
    FROM pengeluaran p
    LEFT JOIN kategori_pengeluaran k ON p.kategori_id = k.id
    $where
    ORDER BY p.tanggal DESC, p.id DESC
";

$query = $conn->query($sql);

$result = [];
$no = 1;
while ($row = $query->fetch_assoc()) {
    $result[] = [
        "no"         => $no++,
        "id"         => $row['id'],
        "tanggal"    => date("d/m/Y", strtotime($row['tanggal'])),
        "kategori"   => $row['nama_kategori'] ?? "-",
        "keterangan" => htmlspecialchars($row['keterangan']),
        "nominal"    => "Rp " . number_format($row['nominal'], 0, ',', '.'),
        "lampiran"   => $row['lampiran'] ? $row['lampiran'] : null
    ];
}

header('Content-Type: application/json');
echo json_encode(["data" => $result]);
