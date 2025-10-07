<?php
include __DIR__ . "../../../config.php";
// include __DIR__ . "/company_profile.php";
// require __DIR__ . '/vendor/autoload.php';
include BASE_PATH . "/vendor/dompdf/lib/Cpdf.php";


use Dompdf;

if (!isset($_GET['id'])) die("Invoice ID tidak ditemukan");
$id = intval($_GET['id']);

// Ambil invoice
$sql = "SELECT * FROM invoices WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

// Ambil item
$sql_items = "SELECT * FROM invoice_items WHERE invoice_id=?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $id);
$stmt_items->execute();
$items = $stmt_items->get_result();

// Buat HTML untuk PDF
ob_start();
?>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
h2, h3 { margin:0; padding:0; }
table { border-collapse: collapse; width: 100%; }
th, td { border:1px solid #000; padding:5px; text-align:left; }
.header { display:flex; align-items:center; border-bottom:2px solid #000; margin-bottom:10px; }
.header img { height:60px; margin-right:15px; }
.footer { position:fixed; bottom:0; left:0; right:0; font-size:10px; text-align:center; }
.sign { margin-top:50px; text-align:right; }
</style>

<div class="header">
    <img src="<?= $company['logo'] ?>" alt="Logo">
    <div>
        <h2><?= $company['name'] ?></h2>
        <p><?= $company['address'] ?><br>
        Tel: <?= $company['phone'] ?> | Email: <?= $company['email'] ?></p>
    </div>
</div>

<h2 style="text-align:center;">INVOICE</h2>
<p><b>No Invoice:</b> <?= $invoice['invoice_number'] ?><br>
<b>Tanggal:</b> <?= $invoice['invoice_date'] ?><br>
<b>Jatuh Tempo:</b> <?= $invoice['due_date'] ?><br>
<b>Customer:</b> <?= $invoice['customer_id'] ?><br>
<b>Metode Bayar:</b> <?= $invoice['payment_method'] ?></p>

<table>
    <thead>
        <tr>
            <th>Deskripsi</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Diskon</th>
            <th>Pajak</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
    <?php while($item = $items->fetch_assoc()): ?>
        <tr>
            <td><?= $item['item_description'] ?></td>
            <td><?= $item['qty'] ?></td>
            <td>Rp <?= number_format($item['price'],0,',','.') ?></td>
            <td>Rp <?= number_format($item['discount'],0,',','.') ?></td>
            <td>Rp <?= number_format($item['tax'],0,',','.') ?></td>
            <td>Rp <?= number_format($item['total'],0,',','.') ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<h3 style="text-align:right;">Grand Total: Rp <?= number_format($invoice['total'],0,',','.') ?></h3>

<div class="sign">
    <p>Hormat Kami,<br><br><br><br>
    ___________________________<br>
    <?= $company['name'] ?></p>
</div>

<div class="footer">
    Dicetak pada <?= date("d-m-Y H:i:s") ?> | <?= $company['name'] ?>
</div>
<?php
$html = ob_get_clean();

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("invoice_".$invoice['invoice_number'].".pdf", ["Attachment" => true]);
