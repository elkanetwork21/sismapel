<?php
session_start();
include __DIR__ . "/../config.php";

// Include PHPMailer manual
include BASE_PATH . "PHPMailer/PHPMailer.php";
include BASE_PATH . "PHPMailer/SMTP.php";
include BASE_PATH . "PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
          $token = bin2hex(random_bytes(32));
          $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

          $stmt = $conn->prepare(
            "INSERT INTO password_resets (user_id, token, expires_at) 
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))"
          );
          $stmt->bind_param("is", $user['id'], $token);
          $stmt->execute();

            $host = $_SERVER['HTTP_HOST'];
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $reset_link = $protocol . "://" . $host . "/sismapel/auth/reset_password.php?token=" . $token;

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'elkanetwork21@gmail.com';     // ganti
                $mail->Password   = 'kmufbwhyxsmrkuob';           // ganti dengan App Password Gmail
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('elkanetwork21@gmail.com', 'Sistmen Manajemen Pelanggan Tertintegrasi');
                $mail->addAddress($email, $user['username']);

                $mail->isHTML(true);
                $mail->Subject = 'Reset Password';
                $mail->Body    = "Halo {$user['username']},<br><br>
                                  Klik link berikut untuk reset password Anda:<br>
                                  <a href='$reset_link'>$reset_link</a><br><br>
                                  Link berlaku 1 jam.";

                $mail->send();
                $success = "Link reset password telah dikirim ke email Anda.";
            } catch (Exception $e) {
                $error = "Gagal mengirim email. Error: " . $mail->ErrorInfo;
                echo "<div style='color:blue'>Debug Link: <a href='$reset_link'>$reset_link</a></div>";
            }
        } else {
            $error = "Email tidak ditemukan.";
        }
    } else {
        $error = "Email harus diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
            max-width: 900px;
            width: 100%;
            border-radius: 15px;
            overflow: hidden;
            border: none;
            box-shadow: 0px 6px 20px rgba(0,0,0,0.15);
        }
        .image-section {
            background: #f8f9fc;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }
        .image-section img {
            max-width: 100%;
            height: auto;
            animation: fadeInLeft 1s ease;
        }
        .form-section {
            padding: 40px;
            background: #fff;
            animation: fadeInRight 1s ease;
        }
        .btn-primary {
            border-radius: 8px;
            background: #4e73df;
            border: none;
        }
        .btn-primary:hover {
            background: #2e59d9;
        }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="row g-0">
        <!-- Kolom Kiri: Gambar -->
        <div class="col-md-6 image-section">
            <img src="undraw_forgot-password_nttj.svg" alt="Illustrasi Lupa Password">
        </div>

        <!-- Kolom Kanan: Form -->
        <div class="col-md-6 form-section">
            <h3 class="text-center mb-4">Lupa Password</h3>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Alamat Email" required>
                    </div>
                </div>
                <button type="submit" id="submitBtn" class="btn btn-primary w-100">
    <span id="btnText">Kirim Link Reset</span>
    <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
</button>

                <p class="text-center mt-3">
                    <a href="login.php">Kembali ke Login</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelector("form").addEventListener("submit", function() {
    const btn = document.getElementById("submitBtn");
    const btnText = document.getElementById("btnText");
    const btnSpinner = document.getElementById("btnSpinner");

    btn.disabled = true; // disable tombol
    btnText.textContent = "Mengirim..."; // ubah teks
    btnSpinner.classList.remove("d-none"); // tampilkan spinner
});
</script>

</body>
</html>
