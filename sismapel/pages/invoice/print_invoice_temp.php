<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "includes/auth_check.php"; 
include BASE_PATH . "includes/security_helper.php"; 

$username  = $_SESSION['username'];
$branch_id = $_SESSION['branch_id'];

$id = validate_secure_id($_GET['id']); // decode
if ($id === false) {
    die("Data tidak ditemukan / ID tidak valid");
}

// Ambil data invoice dengan customer
$sql = $conn->query("
    SELECT 
        c.id AS customer_id,
        c.fullname,
        c.address,
        c.email,
        c.phone,
        p.nama_paket,
        i.id AS invoice_id,
        i.invoice_number,
        i.invoice_date,
        i.notes,
        i.grand_total
    FROM customers c
    LEFT JOIN paket_langganan p ON c.paket_id = p.id
    LEFT JOIN invoices_temp i ON i.customer_id = c.id WHERE i.id=$id")->fetch_assoc();
$no_invoice = $sql['invoice_number']??'';
$tgl_invoice = $sql['invoice_date']??'';
$nama_customer = $sql['fullname']??'';
$alamat = $sql['address']??'';
$phone = $sql['phone']??'';
$email = $sql['email']??'';
$grand_total = $sql['grand_total']??'';
$nama_paket = $sql['nama_paket']??'';
$deskripsi = $sql['notes']??'';



// Ambil data branch
$branchQuery = $conn->prepare("SELECT * FROM branches WHERE id = ?");
$branchQuery->bind_param("i", $branch_id);
$branchQuery->execute();
$branch = $branchQuery->get_result()->fetch_assoc();

// Ambil setting invoice
$setting = $conn->query("SELECT * FROM setting_invoice LIMIT 1")->fetch_assoc();
$sett_rekening = $setting['rekening'] ?? '';
$sett_syarat   = $setting['syarat'] ?? '';
$sett_support  = $setting['support'] ?? '';


?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice <?= htmlspecialchars($invoice['invoice_number']) ?></title>

<!-- Bootstrap 5 & DataTables -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />
<style>
body { font-family: 'Poppins', sans-serif; background: #f5f7fa; }
.table th, .table td { vertical-align: middle; }
#loading { display: none; } /* Loader dinonaktifkan */
@media print {
  #printBtn, .card-footer { display: none !important; }
  #back, .card-footer { display: none !important; }
}
</style>
<style>
  .invoice-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 80px;
    font-weight: bold;
    color: rgba(0,0,0,0.1);
    pointer-events: none;
    white-space: nowrap;
    z-index: 0;
  }
  .card.invoice-card {
    position: relative; /* penting supaya watermark bisa absolute di dalam */
    overflow: hidden;
  }
</style>

</head>
<body>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Invoice: <?= htmlspecialchars($no_invoice) ?></h2>
        <img src="../setting/general/images/<?= $branch['logo'] ?>" alt="Logo" style="max-width:120px;">
    </div>

    <div class="row mb-2">
        <div class="col-md-6">
            <h5>Ditagihkan ke:</h5>
            <p>
                <?= htmlspecialchars($nama_customer) ?><br>
                <?= htmlspecialchars($alamat) ?><br>
                <strong>Phone:</strong> <?= htmlspecialchars($phone) ?><br>
                <strong>Email:</strong> <?= htmlspecialchars($email) ?>
            </p>
        </div>
        <div class="col-md-6 text-end">

            <p>
                <strong><?= $branch['nama_branch']?></strong><br>
                <?= $branch['address']?><br>
                <?= $branch['phone']?><br>
                <?= $branch['email']?><br> <br>
                <strong>Tanggal Faktur:</strong> <?= $tgl_invoice ?><br>
                
                
            </p>
            <h4>Rp <?= number_format($grand_total, 0, ',', '.') ?></h4>
        </div>
    </div>



    <div class="">
        
        <div class="">
            <table class="table table-bordered mb-0" style="line-height:1;">
                <thead class="table-light">
                    <tr>
                        <th>Deskripsi</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <tr>
                        <td><?= $deskripsi ?></td>
                        <td>1</td>
                        <td><?= $grand_total ?></td>
                        <td><?= $grand_total ?></td>
                    </tr>
                    
                    <tr>
                        <td colspan="3" class="text-end">Sub Total</td>
                        <td>Rp <?= number_format($grand_total,0,',','.') ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end">Discount</td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end">Pajak</td>
                        <td>0</td>
                    </tr>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Grand Total</td>
                        <td>Rp <?= number_format($grand_total,0,',','.') ?></td>
                    </tr>
                    
                </tbody>
            </table>
        </div>
   
        
    
    <!-- Rekening & Syarat -->
    <!-- <div class="card mb-2"> -->
        <br>
        <div class="card-body">
            <strong>Rekening Pembayaran:</strong><br><?= $sett_rekening ?>
            <strong>Syarat & Ketentuan:</strong><br><?= $sett_syarat ?>
        </div>
    </div>

    <div class="text-end mb-2">
        <a href="invoice_temp?token=<?= $_SESSION['csrf_token']?>" class="btn btn-outline-primary" id="back">
            <i class="bi bi-arrow-counterclockwise"></i> Kembali
        </a>
        <button class="btn btn-success" id="printBtn" onclick="window.print();"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function(){
    $('#paymentsTable').DataTable({
        pageLength: 5,
        lengthMenu: [5,10,25,50],
        searching: false,
        ordering: true
    });
});
</script>
</body>
</html>
