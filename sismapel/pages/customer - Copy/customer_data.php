<?php
session_start();
include __DIR__ . "../../../config.php";

$branch_id = $_SESSION['branch_id'] ?? 0;

// ambil request datatables
$draw   = $_GET['draw'];
$start  = $_GET['start'];
$length = $_GET['length'];
$search = $_GET['search']['value'] ?? "";

// total data
$totalQuery = $conn->query("SELECT COUNT(*) as cnt FROM customers WHERE branch_id=$branch_id");
$totalData = $totalQuery->fetch_assoc()['cnt'];

// filter
$where = "branch_id=$branch_id";
if ($search != "") {
  $search = $conn->real_escape_string($search);
  $where .= " AND (fullname LIKE '%$search%' OR address LIKE '%$search%' OR phone LIKE '%$search%')";
}

$filterQuery = $conn->query("SELECT COUNT(*) as cnt FROM customers WHERE $where");
$filteredData = $filterQuery->fetch_assoc()['cnt'];

// ambil data
$sql = "SELECT * FROM customers WHERE $where LIMIT $start,$length";
$result = $conn->query($sql);

$data = [];
$no = $start+1;
while($row = $result->fetch_assoc()){
  $ppp = htmlspecialchars($row['ppp_secret']);
  $data[] = [
    "no" => $no++,
    "fullname" => htmlspecialchars($row['fullname']),
    "address"  => htmlspecialchars(substr($row['address'],0,30))."<br>Phone: ".htmlspecialchars($row['phone']),
    "traffic"  => "<span id='tx-$ppp'>0 bps</span> / <span id='rx-$ppp'>0 bps</span>",
    "status"   => "<span id='status-$ppp'>Loading...</span>",
    "isolir"   => ($row['active_status']==1) 
                    ? "<span class='badge bg-primary'>Aktif</span>" 
                    : "<span class='badge bg-danger'>Isolir</span>",
    "aksi"     => "
      <a href='customer_detail.php?id={$row['id']}' class='btn btn-outline-primary btn-sm'><i class='bi bi-eye'></i></a>
      <a href='customer_edit.php?id={$row['id']}' class='btn btn-outline-primary btn-sm'><i class='bi bi-pencil'></i></a>
      <a href='#' onclick='confirmDelete({$row['id']},{$row['odp_id']})' class='btn btn-outline-danger btn-sm'><i class='bi bi-trash'></i></a>
    ",
    "ppp_secret" => $ppp // untuk update status/traffic via ajax
  ];
}

echo json_encode([
  "draw" => intval($draw),
  "recordsTotal" => $totalData,
  "recordsFiltered" => $filteredData,
  "data" => $data
]);
