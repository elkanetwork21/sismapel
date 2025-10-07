<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . "../../../../config.php";
$branch_id = $_SESSION['branch_id'];

$action = $_GET['action'] ?? '';
if ($action == "read") {
    $result = $conn->query("SELECT * FROM payment_methods WHERE branch_id=$branch_id ORDER BY id ASC");

    if ($result->num_rows == 0) {
        echo "<tr><td colspan='5'>Belum ada data</td></tr>";
    } else {
        while ($row = $result->fetch_assoc()) {
            // buat select status
            $statusOptions = "
            <select class='statusDropdown form-control' data-id='{$row['id']}'>
            <option value='aktif' " . ($row['status'] == 'aktif' ? 'selected' : '') . ">Aktif</option>
            <option value='nonaktif' " . ($row['status'] == 'nonaktif' ? 'selected' : '') . ">Nonaktif</option>
            </select>
            ";

            echo "<tr>
            <td contenteditable='true' class='editable' data-id='{$row['id']}' data-field='nama_metode'>{$row['nama_metode']}</td>
            <td contenteditable='true' class='editable' data-id='{$row['id']}' data-field='deskripsi'>{$row['deskripsi']}</td>
            <td>{$statusOptions}</td>
            <td><button class='deleteBtn btn btn-outline-danger' data-id='{$row['id']}'><span class='bi bi-trash'></span></button></td>
            </tr>";
        }
    }
    exit;
}



if ($action == "insert") {
    echo $nama = $_POST['nama'] ?? '';
    echo $deskripsi = $_POST['deskripsi'] ?? '';
    echo $status = $_POST['status'] ?? 'aktif';

    $sql = "INSERT INTO payment_methods (branch_id, nama_metode, deskripsi, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $branch_id, $nama, $deskripsi, $status);
    $stmt->execute();
    echo "ok";
    exit;
}


if ($action == "update") {
    $id = $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    $sql = "UPDATE payment_methods SET $field=? WHERE id=? AND branch_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $value, $id, $branch_id);
    $stmt->execute();
    echo "ok";
    exit;
}

if ($action == "delete") {
    $id = $_POST['id'];
    $sql = "DELETE FROM payment_methods WHERE id=? AND branch_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $branch_id);
    $stmt->execute();
    echo "ok";
    exit;
}
?>
