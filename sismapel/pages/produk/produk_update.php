<?php


session_start();
include __DIR__ . "../../../config.php";

$profile_type = $_POST['profile_type'];
$manual_name = $_POST['manual_name'];
$manual_limit = $_POST['manual_limit'];
$harga      = $_POST['harga'];
$keterangan = $_POST['keterangan'];
$id = $_POST['id_produk'];



$branch_id = $_SESSION['branch_id'];

// jika switch tidak dicentang, maka $_POST['pajak'] tidak ada → default 0
$pajak = isset($_POST['pajak']) ? 1 : 0;

// Hitung harga final
$harga_final = $harga;
if ($pajak == 1) {
    $pajak_persen = 11; // PPN 11%
    $harga_final = $harga + ($harga * $pajak_persen / 100);
}


$sql = "UPDATE paket_langganan SET 
          nama_paket=?, rate_limit=?, harga_asli=?, harga_final=?, pajak=?, description=?, profile_type=? 
        WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssddissi", $manual_name, $manual_limit, $harga, $harga_final, $pajak, $keterangan, $profile_type, $id);

if ($stmt->execute()) {
    header("Location: produk?msg=updated&token=" . $_SESSION['csrf_token']);
} else {
    echo "Error: " . $stmt->error;
}


?>
