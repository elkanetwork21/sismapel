<?php
include __DIR__ . "../../../../config.php";

if(isset($_POST['id'], $_POST['status'])){
    $id = intval($_POST['id']);
    $status = intval($_POST['status']);

    $sql = "UPDATE users SET status='$status' WHERE id='$id'";
    if($conn->query($sql)){
        echo "success";
    } else {
        echo "error";
    }
}
?>
