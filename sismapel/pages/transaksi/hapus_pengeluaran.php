<?php
session_start();
include __DIR__ . "../../../config.php";

$id        = intval($_POST['id']);
$password  = $_POST['password'];
$branch_id = $_SESSION['branch_id'];

// 1. Cek password admin branch (JOIN ke roles)
$sql = "SELECT u.password 
        FROM users u
        INNER JOIN roles r ON u.role_id = r.id
        WHERE u.branch_id = ? 
          AND r.role_name = 'Admin' OR r.role_name = 'Administrator' 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$stmt->bind_result($hash);
$stmt->fetch();
$stmt->close();

if (!$hash) {
  echo json_encode(["status" => "error", "message" => "Admin tidak ditemukan"]);
  exit;
}

// 2. Verifikasi password
if (!password_verify($password, $hash)) {
  echo json_encode(["status" => "error", "message" => "Password salah"]);
  exit;
}

// 3. Hapus pengeluaran
$stmt = $conn->prepare("DELETE FROM pengeluaran WHERE id=? AND branch_id=?");
$stmt->bind_param("ii", $id, $branch_id);
if ($stmt->execute()) {
  echo json_encode(["status" => "success"]);
} else {
  echo json_encode(["status" => "error", "message" => "Gagal menghapus data"]);
}
$stmt->close();
