<?php
include __DIR__ . "../../../../config.php";

if(isset($_POST['id'], $_POST['role'])){
    $id = intval($_POST['id']);
    $role = $conn->real_escape_string($_POST['role']);

    $sql = "UPDATE users SET role='$role' WHERE id='$id'";
    if($conn->query($sql)){
        echo "success";
    } else {
        echo "error";
    }
}
?>
