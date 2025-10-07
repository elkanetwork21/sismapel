<?php
session_start();
include __DIR__ . "../../../config.php";

$branch_id = $_SESSION['branch_id'] ?? 0;

// Ambil 12 bulan terakhir
$labels = [];
$pemasukan = [];
$pengeluaran = [];

for ($i = 11; $i >= 0; $i--) {
    $month = date('m', strtotime("-$i month"));
    $year  = date('Y', strtotime("-$i month"));
    $labels[] = date('M Y', strtotime("-$i month"));

    // Total pemasukan
    $sql_pemasukan = "
        SELECT COALESCE(SUM(amount),0) as total
        FROM payments
        WHERE branch_id='$branch_id'
          AND MONTH(created_at)='$month'
          AND YEAR(created_at)='$year'
    ";
    $total_pemasukan = $conn->query($sql_pemasukan)->fetch_assoc()['total'];
    $pemasukan[] = (float)$total_pemasukan;

    // Total pengeluaran
    $sql_pengeluaran = "
        SELECT COALESCE(SUM(nominal),0) as total
        FROM pengeluaran
        WHERE branch_id='$branch_id'
          AND MONTH(tanggal)='$month'
          AND YEAR(tanggal)='$year'
    ";
    $total_pengeluaran = $conn->query($sql_pengeluaran)->fetch_assoc()['total'];
    $pengeluaran[] = (float)$total_pengeluaran;
}

echo json_encode([
    "labels" => $labels,
    "pemasukan" => $pemasukan,
    "pengeluaran" => $pengeluaran
]);
