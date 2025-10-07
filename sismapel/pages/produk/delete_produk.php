<?php
session_start();
include __DIR__ . "../../../config.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM paket_langganan WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: produk.php?msg=deleted&token=" . $_SESSION['csrf_token']);
    } else {
        header("Location: produk.php?msg=error&token=" . $_SESSION['csrf_token']);
    }
} else {
    header("Location: produk.php?msg=invalid&token=" . $_SESSION['csrf_token']);
}
?>
