<?php
include __DIR__ . "../../../config.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $odp_id = intval($_GET['odp']);

    $sql = "DELETE FROM customers WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        $conn->query("UPDATE distribusi SET available_port = available_port + 1 WHERE id = '$odp_id'");
        header("Location: customer.php?msg=deleted");
    } else {
        header("Location: customer.php?msg=error");
    }
} else {
    header("Location: customer.php?msg=invalid");
}
?>
