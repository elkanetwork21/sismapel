<?php
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

$branch_id = intval($_POST['branch_id'] ?? 0);

$API   = getMikrotikConnection($branch_id);
$files = [];

if ($API) {
  $allFiles = $API->comm("/file/print");
  foreach ($allFiles as $f) {
    if (isset($f['name']) && str_ends_with($f['name'], ".backup")) {
      $files[] = [
        "name" => $f['name'],
        "date" => isset($f['creation-time']) ? date('d-m-Y H:i:s', strtotime($f['creation-time'])) : ''
      ];
    }
  }
  $API->disconnect();
}

header('Content-Type: application/json');
echo json_encode($files);
