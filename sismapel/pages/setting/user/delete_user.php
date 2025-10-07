<?php
session_start();
include __DIR__ . "../../../../config.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM users WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: user?msg=deleted&token=" . urlencode($_SESSION['csrf_token']));
    } else {
        header("Location: user?msg=error&token=" . urlencode($_SESSION['csrf_token']));
    }
} else {
    header("Location: user?msg=invalid&token=" . urlencode($_SESSION['csrf_token']));
}
?>
