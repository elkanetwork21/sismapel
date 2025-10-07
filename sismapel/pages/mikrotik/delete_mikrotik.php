<?php

session_start();
include __DIR__ . "../../../config.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
 
    $sql = "DELETE FROM mikrotik_settings WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: mikrotik.php?msg=deleted&token=" . urlencode($_SESSION['csrf_token'])); 
    } else {
        header("Location: mikrotik.php?msg=error&token=" . urlencode($_SESSION['csrf_token'])); 
    }
} else {
    header("Location: mikrotik.php?msg=invalid&token=" . urlencode($_SESSION['csrf_token']));
}
?>
