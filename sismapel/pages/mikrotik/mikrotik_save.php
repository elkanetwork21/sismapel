<?php


session_start();
include __DIR__ . "../../../config.php";
$server=$_POST['server'];
$ip_address = $_POST['ip_address'];
$port      = $_POST['port'];
$username  = $_POST['username'];
$password  = $_POST['password'];

$branch_id = $_SESSION['branch_id'];


// Ambil data mikrotik untuk branch
$sql = "SELECT * FROM mikrotik_settings WHERE branch_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();
$mikrotik = $result->fetch_assoc();

  if ($mikrotik) {
        // update
    $sql = "UPDATE mikrotik_settings SET nama=?, ip_address=?, port=?, username=?, password=? WHERE branch_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisss", $server, $ip_address, $port, $username, $password, $branch_id);
    if ($stmt->execute()) {
        header("Location: mikrotik.php?msg=update&token=" . urlencode($_SESSION['csrf_token'])); 
        exit;
    };
    
} else {
        // insert baru
    $sql = "INSERT INTO mikrotik_settings (nama, branch_id, ip_address, port, username, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisiss", $server, $branch_id, $ip_address, $port, $username, $password);
    if ($stmt->execute()){
        header("Location: mikrotik.php?msg=success&token=" . urlencode($_SESSION['csrf_token'])); 
        exit;
    };
    

}



?>
