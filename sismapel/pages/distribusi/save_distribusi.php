<?php
session_start();

include __DIR__ . "../../../config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_id   = $_POST['branch_id']; 
    $type        = $_POST['type'];
    $name        = $_POST['name'];
    $code        = $_POST['code'];
    $port        = $_POST['port'];
    $full_address= $_POST['full_address'];
    $description = $_POST['description'];
    $from_id     = !empty($_POST['from_id']) ? $_POST['from_id'] : null;
    $latitude    = $_POST['latitude'];
    $longitude   = $_POST['longitude'];


    // cek available port
    $cek = $conn->query("SELECT available_port FROM distribusi WHERE id = '$from_id'")->fetch_assoc();

    // Query sesuai dengan jumlah kolom
    $sql = "INSERT INTO distribusi 
        (branch_id, type, name, code, port, full_address, description, from_id, latitude, longitude, available_port) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

    $stmt = $conn->prepare($sql);

    // Binding param: i = integer, s = string, d = double
    $stmt->bind_param(
        "issssssissi", 
        $branch_id, 
        $type, 
        $name, 
        $code, 
        $port, 
        $full_address, 
        $description, 
        $from_id, 
        $latitude, 
        $longitude,
        $port
    );

    if ($cek['available_port'] > 0) {
        if ($stmt->execute()) {

            $conn->query("UPDATE distribusi SET available_port = available_port - 1 WHERE id = '$from_id'");
            header("Location: distribusi?msg=success&token=" . $_SESSION['csrf_token']);
        }else{

            header("Location: distribusi?msg=error&token=" . $_SESSION['csrf_token']);
        }
    }elseif ($from_id='null'){
        if ($stmt->execute()) {
        $conn->query("UPDATE distribusi SET available_port = available_port - 1 WHERE id = '$from_id'");
            header("Location: distribusi?msg=success&token=" . $_SESSION['csrf_token']);
        }else{

            header("Location: distribusi?msg=error&token=" . $_SESSION['csrf_token']);
        }
    }else{
        header("Location: distribusi?msg=odp_penuh&token=" . $_SESSION['csrf_token']);
    }
}
?>