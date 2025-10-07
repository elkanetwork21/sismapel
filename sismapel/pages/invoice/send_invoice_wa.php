<?php
session_start();
include __DIR__ . "../../../config.php";

$branch_id = $_SESSION['branch_id'];
$id        = $_GET['id'] ?? 0;

// Ambil data invoice & customer
$sql = "SELECT i.invoice_number, i.grand_total, i.due_date, c.fullname, c.phone 
        FROM invoices i
        JOIN customers c ON i.customer_id = c.id
        WHERE i.id=? AND i.branch_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $branch_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if(!$data){
    die("Invoice tidak ditemukan");
}

// Nomor WA harus dalam format internasional (62xxxx)
$phone = preg_replace('/^0/', '62', $data['phone']); 

// Pesan WA
$message = "
Halo {$data['fullname']},

Berikut detail tagihan Anda:

📄 *Invoice:* {$data['invoice_number']}
💰 *Jumlah:* Rp " . number_format($data['grand_total'],0,',','.') . "
📅 *Jatuh Tempo:* {$data['due_date']}

Silakan lakukan pembayaran sebelum jatuh tempo.
Terima kasih 🙏
";

// Kirim ke API Fonnte
$token = "D4irnuh5udDzEWvGfJcRbcowknxagqyW5F9WX7gKLp"; // Ganti dengan token Fonnte
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.fonnte.com/send",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => array(
    'target' => $phone,
    'message' => $message,
  ),
  CURLOPT_HTTPHEADER => array(
    "Authorization: $token"
  ),
));
$response = curl_exec($curl);
curl_close($curl);

// Feedback ke user
if(strpos($response, '"status":true') !== false){
    $_SESSION['status'] = "success";
} else {
    $_SESSION['status'] = "error";
}
header("Location: invoice.php");
exit;
?>
