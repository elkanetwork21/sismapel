<?php
header('Content-Type: application/json');
session_start();
include __DIR__ . "../../../config.php";
include BASE_PATH . "/pages/mikrotik/mikrotik_connect.php";


$branch_id   = $_SESSION['branch_id'] ?? 0;
$customer_id = $_GET['customer_id'] ?? ($_POST['customer_id'] ?? 0);
$action      = $_GET['action'] ?? ($_POST['action'] ?? '');

$response = ["status" => "error", "message" => "Invalid action"];

switch ($action) {

    case "get_txrx_customer":
        $API = getMikrotikConnection($branch_id);
        $data = [];

        if ($API) {
            // Ambil data customer
            $result = $conn->query("SELECT id, fullname, ppp_secret FROM customers");
            $customers = [];
            while ($row = $result->fetch_assoc()) {
                $row['interface_name'] = "<pppoe-" . $row['ppp_secret'] . ">";
                $customers[$row['interface_name']] = $row;
            }

            // Ambil semua interface
            $API->write('/interface/print');
            $interfaces = $API->read();

            foreach ($interfaces as $iface) {
                $ifname = $iface['name'];
                if (isset($customers[$ifname])) {
                    $cust = $customers[$ifname];

                    // Ambil traffic
                    $API->write('/interface/monitor-traffic', false);
                    $API->write('=interface=' . $ifname, false);
                    $API->write('=once=');
                    $ARRAY = $API->parseResponse($API->read(false));

                    $rx = $ARRAY[0]['rx-bits-per-second'] ?? 0;
                    $tx = $ARRAY[0]['tx-bits-per-second'] ?? 0;

                    $data[] = [
                        "id"        => $cust['id'],
                        "nama"      => $cust['fullname'],
                        "ppp_secret"=> $cust['ppp_secret'],
                        "interface" => $ifname,
                        "tx"        => $tx,
                        "rx"        => $rx
                    ];
                }
            }
            $API->disconnect();
        }
        $response = ["status"=>"success","data"=>$data];
        break;

    case "update_status_customer":
        $username = $_POST['username'] ?? '';
        $status   = intval($_POST['status'] ?? 0);

        $sql = "SELECT c.paket_id, p.nama_paket 
                FROM customers c
                JOIN paket_langganan p ON c.paket_id = p.id
                WHERE c.ppp_secret = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $response = ["status"=>"error","message"=>"User tidak ditemukan di database"];
            break;
        }
        $row = $result->fetch_assoc();
        $PROFILE_NORMAL = $row['nama_paket'];
        $PROFILE_ISOLIR = "Isolir";

        $API = getMikrotikConnection($branch_id);
        if ($API) {
            $secret = $API->comm("/ppp/secret/print", ["?name"=>$username]);
            if (count($secret) > 0) {
                $id = $secret[0]['.id'];
                $API->comm("/ppp/secret/set", [
                    ".id"     => $id,
                    "profile" => ($status == 1 ? $PROFILE_NORMAL : $PROFILE_ISOLIR),
                    "comment" => ($status == 1 ? "" : "ISOLIR")
                ]);
            }

            // kill session aktif
            $active = $API->comm("/ppp/active/print", ["?name"=>$username]);
            if (count($active) > 0) {
                $API->comm("/ppp/active/remove", [".id"=>$active[0]['.id']]);
            }

            $API->disconnect();
        }

        // update DB
        $stmt = $conn->prepare("UPDATE customers SET active_status=? WHERE ppp_secret=?");
        $stmt->bind_param("is", $status, $username);
        if ($stmt->execute()) {
            $response = ["status"=>"success","message"=>"Status berhasil diperbarui"];
        } else {
            $response = ["status"=>"error","message"=>"Gagal update database"];
        }
        break;

    case "get_queue_customer":
        function formatBytes($bytes, $precision = 2) {
            $units = ['B','KB','MB','GB','TB'];
            $bytes = max($bytes, 0);
            $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow   = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);
            return round($bytes, $precision) . ' ' . $units[$pow];
        }

        $secret_asal = $_GET['secret'] ?? null;
        $secret      = $secret_asal ? "<pppoe-" . $secret_asal . ">" : null;

        $API = getMikrotikConnection($branch_id);
        $data = [];
        if ($API && $secret) {
            $API->write('/queue/simple/print', false);
            $API->write('?name=' . $secret, true);
            $queues = $API->read();

            if (count($queues) > 0) {
                $q = $queues[0];
                $bytes = $q['bytes'] ?? "0/0";

                if (is_string($bytes)) {
                    $parts = preg_split('/[\/,]/', $bytes);
                    $tx = intval($parts[0] ?? 0);
                    $rx = intval($parts[1] ?? 0);
                } elseif (is_array($bytes)) {
                    $tx = intval($bytes[0] ?? 0);
                    $rx = intval($bytes[1] ?? 0);
                } else {
                    $tx = $rx = 0;
                }

                // PPP Secret info
                $API->write('/ppp/secret/print', false);
                $API->write('?name=' . $secret_asal, true);
                $secrets = $API->read();

                // PPP Active info
                $API->write('/ppp/active/print', false);
                $API->write('?name=' . $secret_asal, true);
                $ppp_active = $API->read();

                $uptime     = "0s";
                $last_seen  = "-";
                $ip_address = "-";

                if (count($ppp_active) > 0) {
                    $uptime     = $ppp_active[0]['uptime'] ?? "0s";
                    $ip_address = $ppp_active[0]['address'] ?? "-";
                } else {
                    $last_seen = date("Y-m-d H:i:s");
                }

                if (!empty($secrets)) {
                    $last_seen = $secrets[0]['last-logged-out'] ?? $last_seen;
                }

                $data = [
                    'name'           => $q['name'],
                    'upload_bytes'   => $tx,
                    'download_bytes' => $rx,
                    'upload_human'   => formatBytes($tx),
                    'download_human' => formatBytes($rx),
                    'uptime'         => $uptime,
                    'last_seen'      => $last_seen,
                    'ip_address'     => $ip_address,
                    'active'         => (count($ppp_active) > 0)
                ];
            } else {
                $data = ['error' => 'Queue tidak ditemukan untuk ' . $secret];
            }
            $API->disconnect();
        } else {
            $data = ['error' => 'Gagal konek ke Mikrotik atau secret kosong'];
        }

        $response = ["status"=>"success","data"=>$data];
        break;

    case "get_ppp_trafic":
        $ppp_secret = $_GET['ppp_secret'] ?? '';
        if (!$ppp_secret) {
            $response = ["status"=>"error","message"=>"ppp_secret tidak ditemukan"];
            break;
        }

        $API = getMikrotikConnection($branch_id);
        if ($API) {
            $interface = "<pppoe-" . $ppp_secret . ">";
            $check = $API->comm("/interface/print", ["?name"=>$interface]);

            if (count($check) > 0) {
                $traffic = $API->comm("/interface/monitor-traffic", [
                    "interface"=>$interface,
                    "once"=>""
                ]);

                $response = [
                    "status"=>"success",
                    "ppp_secret"=>$ppp_secret,
                    "interface"=>$interface,
                    "rx"=> ($traffic[0]['rx-bits-per-second'] ?? 0) / 1024 / 1024,
                    "tx"=> ($traffic[0]['tx-bits-per-second'] ?? 0) / 1024 / 1024
                ];
            } else {
                $response = ["status"=>"error","message"=>"Interface tidak ditemukan: ".$interface];
            }
            $API->disconnect();
        } else {
            $response = ["status"=>"error","message"=>"Gagal koneksi ke Mikrotik"];
        }
        break;

    default:
        $response = ["status"=>"error","message"=>"Action not found"];
}

echo json_encode($response);
$conn->close();
