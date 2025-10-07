<?php
session_start();
include __DIR__ . "../../../../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username  = trim($_POST['username']);
    $branch_id = intval($_POST['branch_id']);
    $email     = trim($_POST['email']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role      = intval($_POST['role_id']);

    // Upload foto
    $foto = "";
    if (!empty($_FILES['foto']['name'])) {
        $foto = time() . "_" . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . "/uploads/" . $foto);
    }

    // === Validasi username/email sudah ada ===
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Username atau email sudah digunakan
        header("Location: user?msg=duplicate&token=" . urlencode($_SESSION['csrf_token']));
        exit;
    }

    // === Insert user baru ===
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, foto, branch_id) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisi", $username, $email, $password, $role, $foto, $branch_id);

    if ($stmt->execute()) {
        header("Location: user?msg=success&token=" . urlencode($_SESSION['csrf_token']));
    } else {
        header("Location: user?msg=error&token=" . urlencode($_SESSION['csrf_token']));
    }
}
?>
