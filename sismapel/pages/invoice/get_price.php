<?php
include __DIR__ . "../../../config.php";

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = "SELECT harga_final FROM paket_langganan WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    echo $result['harga_final'] ?? 0;
}
?>
