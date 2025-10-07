<?php
session_start();
include "../config.php";

$token  = $_GET['token'] ?? '';
$error  = $success = "";

// cek token di DB
if ($token) {
    $stmt = mysqli_prepare($conn, "SELECT pr.user_id, u.username, pr.expires_at 
                                   FROM password_resets pr 
                                   JOIN users u ON pr.user_id = u.id 
                                   WHERE pr.token = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $user_id   = $row['user_id'];
        $username  = $row['username'];
        $expires   = $row['expires_at'];

        if (strtotime($expires) < time()) {
            $error = "Link reset password sudah kadaluarsa.";
        }
    } else {
        $error = "Token tidak valid.";
    }
} else {
    $error = "Token tidak ditemukan.";
}

// jika form submit
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['password']) && empty($error)) {
    $password        = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {
        $error = "Password dan konfirmasi tidak sama.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // update password user
        $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $hashed, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            // hapus token setelah digunakan
            mysqli_query($conn, "DELETE FROM password_resets WHERE token='$token'");

            $success = "Password berhasil direset. Silakan login.";
        } else {
            $error = "Terjadi kesalahan saat menyimpan password baru.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #4e73df, #1cc88a);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .card {
      max-width: 600px;
      width: 100%;
      border-radius: 15px;
      border: none;
      box-shadow: 0px 6px 20px rgba(0,0,0,0.15);
      padding: 30px;
      background: #fff;
    }
    .btn-primary {
      border-radius: 8px;
      background: #4e73df;
      border: none;
    }
    .btn-primary:hover {
      background: #2e59d9;
    }
  </style>
</head>
<body>

<div class="card">
  <h3 class="text-center mb-4">Reset Password</h3>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php elseif ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
    <p class="text-center mt-3"><a href="login.php" class="btn btn-success">Login Sekarang</a></p>
  <?php endif; ?>

  <?php if (empty($error) && empty($success)): ?>
  <form method="POST">
    <div class="mb-3">
      <label for="password" class="form-label">Password Baru</label>
      <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required>
    </div>
    <div class="mb-3">
      <label for="confirm_password" class="form-label">Konfirmasi Password</label>
      <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Simpan Password Baru</button>
  </form>
  <?php endif; ?>
</div>

</body>
</html>
