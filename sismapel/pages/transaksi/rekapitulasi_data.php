<?php
session_start();
include __DIR__ . "../../../config.php";

$branch_id = $_SESSION['branch_id'] ?? 0;

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Hitung bulan & tahun sebelumnya
$prev_bulan = $bulan - 1;
$prev_tahun = $tahun;
if ($prev_bulan == 0) {
    $prev_bulan = 12;
    $prev_tahun = $tahun - 1;
}

// Hitung saldo awal (saldo akhir bulan sebelumnya)
$sql_saldo_awal = "
    SELECT 
      COALESCE(SUM(amount),0) as total_pendapatan
    FROM payments
    WHERE branch_id='$branch_id'
      AND YEAR(created_at)='$prev_tahun'
      AND MONTH(created_at)='$prev_bulan'
";
$pendapatan_prev = $conn->query($sql_saldo_awal)->fetch_assoc()['total_pendapatan'];

$sql_pengeluaran_awal = "
    SELECT 
      COALESCE(SUM(nominal),0) as total_pengeluaran
    FROM pengeluaran
    WHERE branch_id='$branch_id'
      AND YEAR(tanggal)='$prev_tahun'
      AND MONTH(tanggal)='$prev_bulan'
";
$pengeluaran_prev = $conn->query($sql_pengeluaran_awal)->fetch_assoc()['total_pengeluaran'];

$saldo_awal = $pendapatan_prev - $pengeluaran_prev;

// Ambil transaksi bulan terpilih
$sql = "
  SELECT created_at AS tanggal, invoice_id AS keterangan, amount AS nominal, 'Credit' AS jenis
  FROM payments
  WHERE branch_id = '$branch_id'
    AND MONTH(created_at) = '$bulan'
    AND YEAR(created_at) = '$tahun'
  
  UNION ALL
  
  SELECT tanggal AS tanggal, keterangan AS keterangan, nominal AS nominal, 'Debit' AS jenis
  FROM pengeluaran
  WHERE branch_id = '$branch_id'
    AND MONTH(tanggal) = '$bulan'
    AND YEAR(tanggal) = '$tahun'
  
  ORDER BY tanggal ASC
";

$data = $conn->query($sql);

$result = [];
$no = 1;
$total_pemasukan = 0;
$total_pengeluaran = 0;

// Tambahkan saldo awal
$result[] = [
    $no++,
    date("01/m/Y", strtotime("$tahun-$bulan-01")),
    "Saldo Awal",
    "-",
    "-",
    $saldo_awal
];

// Proses transaksi bulan ini
$saldo = $saldo_awal;
$last_tanggal = null;
while ($row = $data->fetch_assoc()) {
    $last_tanggal = $row['tanggal'];

    if ($row['jenis'] == 'Credit') {
        $saldo += $row['nominal'];
        $total_pemasukan += $row['nominal'];
    } else {
        $saldo -= $row['nominal'];
        $total_pengeluaran += $row['nominal'];
    }

    $result[] = [
        $no++,
        date("d/m/Y", strtotime($row['tanggal'])),
        $row['jenis'],
        htmlspecialchars($row['keterangan']),
        $row['nominal'],
        $saldo
    ];
}

// Tambahkan saldo akhir
if ($last_tanggal) {
    $result[] = [
        $no++,
        date("d/m/Y", strtotime($last_tanggal)),
        "Saldo Akhir",
        "-",
        "-",
        $saldo
    ];
} else {
    $result[] = [
        $no++,
        date("01/m/Y", strtotime("$tahun-$bulan-01")),
        "Saldo Akhir",
        "-",
        "-",
        $saldo
    ];
}

// Kirim JSON termasuk total
echo json_encode([
    "data" => $result,
    "total_pemasukan" => $total_pemasukan,
    "total_pengeluaran" => $total_pengeluaran,
    "saldo_akhir" => $saldo
]);
