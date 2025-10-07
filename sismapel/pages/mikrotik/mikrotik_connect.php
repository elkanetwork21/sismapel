

<?php


// session_start();
// $branch_id = $_SESSION['branch_id'];
// include __DIR__ . "../../../config.php";
// include BASE_PATH . "/pages/mikrotik/routeros_api.class.php"; // library MikroTik
require_once "routeros_api.class.php";

// $host = "localhost";
// $user = "root";   // sesuaikan
// $pass = "";       // sesuaikan
// $db   = "dashboard"; // sesuaikan

// $conn = mysqli_connect($host, $user, $pass, $db);

// if (!$conn) {
//     die("Koneksi database gagal: " . mysqli_connect_error());
// }

function getMikrotikConnection($branch_id) {
    global $conn;
    $API = new RouterosAPI();

    // ambil data dari database
    $stmt = $conn->prepare("SELECT ip_address, port, username, password FROM mikrotik_settings WHERE branch_id=?");
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $stmt->bind_result($ip_address, $port, $username, $password);

    if ($stmt->fetch()) {
        $stmt->close();

        if ($API->connect($ip_address, $username, $password, $port)) {
            return $API; // berhasil login ke MikroTik
        } else {
            return false; // gagal konek
        }
    } else {
        $stmt->close();
        return false; // tidak ada setting mikrotik
    }
}
?>

