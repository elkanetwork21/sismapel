<?php
session_start();
include __DIR__ . "../../../config.php";

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $branch_id = $_SESSION['branch_id'];

    // 1. cek apakah distribusi dipakai oleh customers
    $cekCust = $conn->prepare("SELECT COUNT(*) as jml FROM customers WHERE odp_id = ?");
    $cekCust->bind_param("i", $id);
    $cekCust->execute();
    $resultCust = $cekCust->get_result()->fetch_assoc();
    $cekCust->close();

    if($resultCust['jml'] > 0){
        header("Location: distribusi?msg=used_customer&token=" . urlencode($_SESSION['csrf_token']));
        exit;
    }

    // 2. cek apakah distribusi dipakai oleh distribusi lain (sebagai from_id)
    $cekDist = $conn->prepare("SELECT COUNT(*) as jml FROM distribusi WHERE from_id = ?");
    $cekDist->bind_param("i", $id);
    $cekDist->execute();
    $resultDist = $cekDist->get_result()->fetch_assoc();
    $cekDist->close();

    if($resultDist['jml'] > 0){
        header("Location: distribusi?msg=used_distribusi&token=" . urlencode($_SESSION['csrf_token']));
        exit;
    }

    // cek from_id sebelum hapus
    $cek = $conn->prepare("SELECT from_id FROM distribusi WHERE id = ? AND branch_id = ?");
    $cek->bind_param("ii", $id, $branch_id);
    $cek->execute();
    $result = $cek->get_result()->fetch_assoc();
    $from = $result['from_id'] ?? null;
    $cek->close();

    // hapus distribusi
    $stmt = $conn->prepare("DELETE FROM distribusi WHERE id=? AND branch_id=?");
    $stmt->bind_param("ii", $id, $branch_id);

    if($stmt->execute()){
        if($from){
            $conn->query("UPDATE distribusi SET available_port = available_port + 1 WHERE id = '$from'");
        }
        header("Location: distribusi?msg=deleted&token=" . urlencode($_SESSION['csrf_token'])); 
    } else {
        header("Location: distribusi?msg=error&token=" . urlencode($_SESSION['csrf_token'])); 
    }
}
?>
