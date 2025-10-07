<?php
include __DIR__ . "../../../../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $branch_id = $conn->real_escape_string($_POST['branch_id']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $conn->real_escape_string($_POST['role_id']);

    // Upload foto
    $foto = "";
    if (!empty($_FILES['foto']['name'])) {
        $foto = time() . "_" . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $foto);
    }

    $sql = "INSERT INTO users (username, email, password, role_id, foto, branch_id) 
    VALUES ('$username', '$email', '$password', '$role', '$foto', '$branch_id')";

    if ($conn->query($sql) === TRUE) {
        header("Location: branch.php?msg=success");
    } else {
        header("Location: branch.php?msg=error");
    }
}
?>
