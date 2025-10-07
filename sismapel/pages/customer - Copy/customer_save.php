<?php


session_start();
include __DIR__ . "../../../config.php";

$ppp_secret = $_POST['ppp_secret'];
$fullname = $_POST['fullname'];
$address = $_POST['address'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$paket_id = $_POST['paket_id'];
$keterangan = $_POST['keterangan'];
$lat = $_POST['latitude'];
$long = $_POST['longitude'];
$payment_status = '1';
$active_status = '1';
$odp_id = $_POST['odp_id'];

$branch_id = $_SESSION['branch_id'];

// id distribusi dari form

// cek available port
$cek = $conn->query("SELECT available_port FROM distribusi WHERE id = '$odp_id'")->fetch_assoc();

if ($cek['available_port'] > 0) {
    // insert customer
    $conn->query("INSERT INTO customers (ppp_secret, fullname, address, phone, email, paket_id, keterangan, latitude, longitude, payment_status, active_status, odp_id, branch_id)  
    	VALUES ('$ppp_secret', '$fullname', '$address', '$phone', '$email', '$paket_id', '$keterangan', '$lat', '$long', '$payment_status', '$active_status', '$odp_id', '$branch_id')");
    
    // kurangi available port
    $conn->query("UPDATE distribusi SET available_port = available_port - 1 WHERE id = '$odp_id'");

    header("Location: customer.php?msg=success");

} else {
    header("Location: customer.php?msg=odp_penuh");
    
}




?>
