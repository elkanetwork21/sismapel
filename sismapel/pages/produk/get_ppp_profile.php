<?php
// api/get_ppp_profiles.php
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

require_once __DIR__ . "/../../config.php";
require_once BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";

// Basic security: pastikan user terautentikasi
if (!isset($_SESSION['branch_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    http_response_code(401);
    exit;
}

$branch_id = (int) $_SESSION['branch_id'];
$response = ['success' => false, 'data' => [], 'message' => ''];

// Connect to Mikrotik (isolated responsibility)
try {
    $API = getMikrotikConnection($branch_id);
    if (!$API) {
        throw new Exception('Gagal konek ke Mikrotik');
    }

    // Optional: set timeout on the API class (depends on routeros lib impl)
    // $API->setTimeout(5);

    $profiles = $API->comm("/ppp/profile/print");
    $API->disconnect();

    // Normalize response: return array of profiles with name + rate-limit
    $items = [];
    if (is_array($profiles)) {
        foreach ($profiles as $p) {
            $items[] = [
                'name' => $p['name'] ?? '',
                'rate_limit' => $p['rate-limit'] ?? '',
                // add other fields you need
            ];
        }
    }

    $response['success'] = true;
    $response['data'] = $items;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
