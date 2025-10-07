<?php
session_start();
include "../config.php";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Set last_activity ke NULL agar langsung dianggap offline
    $conn->query("UPDATE users SET last_activity = NULL WHERE id = '$user_id'");
}

session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Logout</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />

  <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
      </style>
</head>
<body>
<script>
  Swal.fire({
    title: 'Logout Berhasil',
    html: `
      <lottie-player 
        src="https://assets10.lottiefiles.com/packages/lf20_qp1q7mct.json"  
        background="transparent"  
        speed="1"  
        style="width: 200px; height: 200px; margin:auto;"  
        autoplay>
      </lottie-player>
      <p style="margin-top:10px;">Anda telah keluar dari sistem.</p>
    `,
    showConfirmButton: false,
    timer: 2000,
    willClose: () => {
      window.location.href = "../dashboard";
    }
  });
</script>
</body>
</html>