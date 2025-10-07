<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sukses Pendaftaran</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
  <style>
    body {
      background: linear-gradient(135deg, #0d6efd, #003366);
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      font-family: 'Poppins', sans-serif;
    }
    .success-card {
      background: #fff;
      color: #333;
      padding: 30px;
      border-radius: 20px;
      text-align: center;
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
      max-width: 500px;
      width: 100%;
      animation: fadeIn 0.8s ease-in-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .success-icon {
      font-size: 70px;
      color: #198754;
      margin-bottom: 20px;
    }
    .btn-primary {
      border-radius: 10px;
      padding: 10px 25px;
    }
  </style>
</head>
<body>
  <div class="success-card">
    <!-- Animasi Lottie -->
    <lottie-player 
      src="https://assets9.lottiefiles.com/packages/lf20_jbrw3hcz.json"  
      background="transparent"  
      speed="1"  
      style="width: 200px; height: 200px; margin:auto;"  
      loop autoplay>
    </lottie-player>

    <h2 class="mb-3 mt-2">Pendaftaran Berhasil!</h2>
    <p class="mb-4">
      Akun branch dan admin Anda berhasil dibuat.<br>
      <strong>Silakan hubungi administrator</strong> untuk proses verifikasi agar akun Anda dapat digunakan.
    </p>
    <a href="../dashboard" class="btn btn-primary">
      <i class="bi bi-box-arrow-in-right"></i> Kembali ke Dashboard
    </a>
  </div>
</body>
</html>
