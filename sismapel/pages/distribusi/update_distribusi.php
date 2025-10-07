<?php
include __DIR__ . "../../../config.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];
    $type = $_POST['type'];
    $name = $_POST['name'];
    $code = $_POST['code'];
    $port = $_POST['port'];
    $full_address = $_POST['full_address'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE distribusi SET latitude=?, longitude=?, type=?, name=?, code=?, port=?, full_address=?, description=? WHERE id=?");
    $stmt->bind_param("ddssssssi", $lat, $lng, $type, $name, $code, $port, $full_address, $description, $id);

    if ($stmt->execute()) {
        header("Location: distribusi?status=update&token=" . urlencode($_SESSION['csrf_token']));
    } else {
        header("Location: distribusi?status=error&token=" . urlencode($_SESSION['csrf_token']));
    }
}
?>
