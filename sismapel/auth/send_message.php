<?php
session_start();

// config
include __DIR__ . "/../config.php";

// Load PHPMailer
require_once BASE_PATH . "PHPMailer/PHPMailer.php";
require_once BASE_PATH . "PHPMailer/SMTP.php";
require_once BASE_PATH . "PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $pesan  = trim($_POST['pesan'] ?? '');

    if ($nama && $email && $pesan) {
        $mail = new PHPMailer(true);
        try {
            // SMTP Settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'elkanetwork21@gmail.com';   // email admin
            $mail->Password   = 'kmufbwhyxsmrkuob';          // app password Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // Penerima
            $mail->setFrom('no-reply@elkanet.id', 'ISP Website');
            $mail->addAddress('elkanetwork21@gmail.com', 'Admin ISP');

            // Konten
            $mail->isHTML(true);
            $mail->Subject = "Pesan baru dari $nama";
            $mail->Body    = "
                <h3>Pesan Baru dari Website</h3>
                <p><b>Nama:</b> $nama</p>
                <p><b>Email:</b> $email</p>
                <p><b>Pesan:</b><br>" . nl2br(htmlspecialchars($pesan)) . "</p>
            ";

            $mail->send();

            $_SESSION['alert'] = [
                'type' => 'success',
                'msg'  => 'Pesan berhasil dikirim. Kami akan segera menghubungi Anda.'
            ];
        } catch (Exception $e) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'msg'  => 'Gagal mengirim pesan. Error: ' . $mail->ErrorInfo
            ];
        }
    } else {
        $_SESSION['alert'] = [
            'type' => 'warning',
            'msg'  => 'Semua field wajib diisi.'
        ];
    }

    // Redirect balik ke dashboard
    header("Location: ../dashboard#kontak");
    exit;
}
