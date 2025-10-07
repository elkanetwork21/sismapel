<?php
session_start();
include __DIR__ . "../../../../config.php";

$action = $_GET['action'] ?? '';
$branch_id = $_SESSION['branch_id'] ?? 0;

if ($action == "read") {
    $result = $conn->query("SELECT * FROM bank_accounts WHERE branch_id=$branch_id ORDER BY id ASC");

    if ($result->num_rows == 0) {
        echo "<tr><td colspan='6'>Belum ada data rekening</td></tr>";
    } else {
        while ($row = $result->fetch_assoc()) {
            $statusOptions = "
                <select class='statusAccountDropdown form-control' data-id='{$row['id']}'>
                    <option value='aktif' " . ($row['status'] == 'aktif' ? 'selected' : '') . ">Aktif</option>
                    <option value='nonaktif' " . ($row['status'] == 'nonaktif' ? 'selected' : '') . ">Nonaktif</option>
                </select>
            ";

            echo "<tr>
                <td contenteditable='true' class='editableAccount' data-id='{$row['id']}' data-field='bank_name'>{$row['bank_name']}</td>
                <td contenteditable='true' class='editableAccount' data-id='{$row['id']}' data-field='account_number'>{$row['account_number']}</td>
                <td contenteditable='true' class='editableAccount' data-id='{$row['id']}' data-field='account_holder'>{$row['account_holder']}</td>
                <td>{$statusOptions}</td>
                <td><button class='deleteAccountBtn btn btn-outline-danger' data-id='{$row['id']}'><span class='bi bi-trash'></span></button></td>
            </tr>";
        }
    }
    exit;
}

if ($action == "insert") {
    $bank = $_POST['bank'] ?? '';
    $number = $_POST['account_number'] ?? '';
    $holder = $_POST['account_holder'] ?? '';
    $status = $_POST['status'] ?? 'aktif';

    $sql = "INSERT INTO bank_accounts (branch_id, bank_name, account_number, account_holder, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $branch_id, $bank, $number, $holder, $status);
    $stmt->execute();
    echo "ok";
    exit;
}

if ($action == "update") {
    $id = $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    $sql = "UPDATE bank_accounts SET $field=? WHERE id=? AND branch_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $value, $id, $branch_id);
    $stmt->execute();
    echo "ok";
    exit;
}

if ($action == "delete") {
    $id = $_POST['id'];
    $sql = "DELETE FROM bank_accounts WHERE id=? AND branch_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $branch_id);
    $stmt->execute();
    echo "ok";
    exit;
}
