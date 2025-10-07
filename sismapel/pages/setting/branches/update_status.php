<?php
include __DIR__ . "../../../../config.php";

if(isset($_POST['id']) && isset($_POST['status'])){
    $id = $_POST['id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE users SET status=? WHERE id=?");
    $stmt->bind_param("ii", $status, $id);
    if($stmt->execute()){
        echo "success";
    } else {
        echo "error";
    }
}
?>
