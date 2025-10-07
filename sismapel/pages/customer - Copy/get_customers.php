<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = $_SESSION['branch_id'];
$API = getMikrotikConnection($branch_id);

$activeSecrets = [];
$comments = [];

// ambil data dari mikrotik (langsung via ajax, bukan di load awal)
if ($API) {
  $actives = $API->comm("/ppp/active/print");
  foreach ($actives as $a) $activeSecrets[] = $a['name'];

  $secrets = $API->comm("/ppp/secret/print");
  foreach ($secrets as $s) {
    $comments[$s['name']] = isset($s['profile']) ? $s['profile'] : "";
  }
  $API->disconnect();
}

// ambil customer dari DB
$result = $conn->query("SELECT * FROM customers WHERE branch_id=$branch_id");
$no = 0;
while($row = $result->fetch_assoc()){
  $no++;
  $status = in_array($row['ppp_secret'], $activeSecrets) ? "online" : "offline";
  $comment = $comments[$row['ppp_secret']] ?? "";

  echo "<tr>
    <td>{$no}</td>
    <td>".htmlspecialchars($row['fullname'])."</td>
    <td>".substr($row['address'],0,30)."</td>
    <td>
      <span id='tx-{$row['ppp_secret']}'>0 bps</span><br>
      <span id='rx-{$row['ppp_secret']}'>0 bps</span>
    </td>
    <td>
      ".($comment==="Isolir" ? "<span class='badge bg-danger'>Isolir</span>" : "<span class='badge bg-success'>Aktif</span>")."
      ".($status==="online" ? "<span class='badge bg-success'>Online</span>" : "<span class='badge bg-danger'>Offline</span>")."
    </td>
    <td>
      <label class='switch'>
        <input type='checkbox' class='toggleStatus' data-username='{$row['ppp_secret']}' ".($row['active_status']==1?'checked':'').">
        <span class='slider round'></span>
      </label>
    </td>
    <td>
      <a href='customer_detail.php?id={$row['id']}' class='btn btn-outline-primary btn-sm'><i class='bi bi-eye'></i></a>
    </td>
  </tr>";
}
?>
