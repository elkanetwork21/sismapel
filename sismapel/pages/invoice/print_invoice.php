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
$invoiceQuery = $conn->prepare("
    SELECT i.*, c.fullname AS customer_name, c.address, c.phone, c.email
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    WHERE i.id = ?
");
$invoiceQuery->bind_param("i", $id);
$invoiceQuery->execute();
$invoice = $invoiceQuery->get_result()->fetch_assoc();

if (!$invoice) {
    die("Invoice tidak ditemukan.");
}

// Ambil item invoice
$itemsQuery = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$itemsQuery->bind_param("i", $id);
$itemsQuery->execute();
$items = $itemsQuery->get_result();

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

// Total pembayaran
$paymentStmt = $conn->prepare("SELECT SUM(amount) AS total_paid FROM payments WHERE invoice_id = ?");
$paymentStmt->bind_param("s", $invoice['invoice_number']);
$paymentStmt->execute();
$total_paid = $paymentStmt->get_result()->fetch_assoc()['total_paid'] ?? 0;
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
        <h2>Invoice: <?= htmlspecialchars($invoice['invoice_number']) ?></h2>
        <img src="../setting/general/images/<?= $branch['logo'] ?>" alt="Logo" style="max-width:120px;">
    </div>

    <div class="row mb-2">
        <div class="col-md-6">
            <h5>Ditagihkan ke:</h5>
            <p>
                <?= htmlspecialchars($invoice['customer_name']) ?><br>
                <?= htmlspecialchars($invoice['address']) ?><br>
                <strong>Phone:</strong> <?= htmlspecialchars($invoice['phone']) ?><br>
                <strong>Email:</strong> <?= htmlspecialchars($invoice['email']) ?>
            </p>
        </div>
        <div class="col-md-6 text-end">
            <p>
                <strong>Tanggal Faktur:</strong> <?= $invoice['invoice_date'] ?><br>
                <strong>Jatuh Tempo:</strong> <?= $invoice['due_date'] ?><br>
                <strong>Status:</strong> 
                <?php
                    $statusClass = match($invoice['status']) {
                        'unpaid' => 'primary',
                        'paid' => 'success',
                        'partial' => 'warning',
                        'overdue' => 'danger',
                        default => 'secondary',
                    };
                ?>
                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($invoice['status']) ?></span>
            </p>
            <h4>Rp <?= number_format($invoice['grand_total'] - $total_paid, 0, ',', '.') ?></h4>
        </div>
    </div>



    <div class="">
        <div class="invoice-watermark">
          <?= ($invoice['status'] === 'paid') ? 'LUNAS' : 'BELUM LUNAS'; ?>
      </div>

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
                    <?php while($item = $items->fetch_assoc()): 
                        $totalItem = $item['qty'] * $item['price'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['description']) ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td>Rp <?= number_format($item['price'],0,',','.') ?></td>
                        <td>Rp <?= number_format($totalItem,0,',','.') ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <tr>
                        <td colspan="3" class="text-end">Sub Total</td>
                        <td>Rp <?= number_format($invoice['subtotal'],0,',','.') ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end">Discount</td>
                        <td>Rp <?= number_format($invoice['total_discount'],0,',','.') ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end">Pajak</td>
                        <td>Rp <?= number_format($invoice['total_tax'],0,',','.') ?></td>
                    </tr>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Grand Total</td>
                        <td>Rp <?= number_format($invoice['grand_total'],0,',','.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
   

    <!-- Transaksi Terkait -->
   
        <div class="mt-4"><h5>Transaksi Terkait</h5></div>
        <div class="">
            <div class="table-responsive">
            <table class="table table-striped table-bordered" >
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal Bayar</th>
                        <th>Metode</th>
                        <th>Rekening / E-Wallet</th>
                        <th>Jumlah</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $paymentsStmt = $conn->prepare("
                        SELECT p.amount, p.note, p.created_at, pm.nama_metode, ba.bank_name
                        FROM payments p
                        INNER JOIN payment_methods pm ON p.method_id = pm.id
                        LEFT JOIN bank_accounts ba ON p.account_id = ba.id
                        WHERE p.invoice_id = ?
                        ORDER BY p.created_at ASC
                    ");
                    $paymentsStmt->bind_param("s", $invoice['invoice_number']);
                    $paymentsStmt->execute();
                    $payments = $paymentsStmt->get_result();

                    if ($payments->num_rows > 0) {
                        $no = 1;
                        while($p = $payments->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date("d-m-Y H:i", strtotime($p['created_at'])) ?></td>
                        <td><?= htmlspecialchars($p['nama_metode']) ?></td>
                        <td><?= htmlspecialchars($p['bank_name']) ?></td>
                        <td>Rp <?= number_format($p['amount'],0,',','.') ?></td>
                        <td><?= htmlspecialchars($p['note']) ?></td>
                    </tr>
                    <?php endwhile; } else { ?>
                    <tr><td colspan="6" class="text-center">Belum ada pembayaran</td></tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
    
    <!-- Rekening & Syarat -->
    <!-- <div class="card mb-2"> -->
        <div class="card-body">
            <strong>Rekening Pembayaran:</strong><br><?= $sett_rekening ?>
            <strong>Syarat & Ketentuan:</strong><br><?= $sett_syarat ?>
        </div>
    </div>

    <div class="text-end mb-2">
        <a href="invoice_detail.php?id=<?= secure_id ($id) ?>&token=<?= $_SESSION['csrf_token']?>" class="btn btn-outline-primary" id="back">
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
