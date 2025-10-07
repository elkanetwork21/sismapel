<?php
session_start();
include __DIR__ . "../../../config.php";

$branch_id = $_SESSION['branch_id'];

$total   = $conn->query("SELECT COUNT(*) as t FROM customers WHERE branch_id=$branch_id")->fetch_assoc()['t'];
$isolir  = $conn->query("SELECT COUNT(*) as i FROM customers WHERE active_status=0 AND branch_id=$branch_id")->fetch_assoc()['i'];
$offline = $conn->query("SELECT COUNT(*) as o FROM customers WHERE online_status=0 AND branch_id=$branch_id")->fetch_assoc()['o'] ?? 0;

echo json_encode([
  "total"   => $total,
  "isolir"  => $isolir,
  "offline" => $offline
]);
