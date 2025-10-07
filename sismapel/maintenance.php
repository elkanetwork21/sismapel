<?php
http_response_code(503); // Service Unavailable
header("Retry-After: 3600"); // Browser retry after 1 jam
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Situs Sedang Maintenance</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #0f2027;
      background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
      color: #fff;
      text-align: center;
      padding: 50px;
    }
    .container {
      max-width: 600px;
      margin: auto;
    }
    h1 {
      font-size: 3rem;
      margin-bottom: 20px;
      color: #ff6b6b;
    }
    p {
      font-size: 1.2rem;
      margin-bottom: 30px;
    }
    .loader {
      border: 8px solid rgba(255,255,255,0.2);
      border-top: 8px solid #ff6b6b;
      border-radius: 50%;
      width: 80px;
      height: 80px;
      animation: spin 1.5s linear infinite;
      margin: 20px auto;
    }
    @keyframes spin {
      0%   { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    footer {
      margin-top: 40px;
      font-size: 0.9rem;
      opacity: 0.7;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>🚧 Maintenance</h1>
    <div class="loader"></div>
    <p>Situs kami sedang dalam perbaikan untuk meningkatkan layanan.<br>
    Silakan kembali lagi nanti.</p>
    <footer>&copy; <?= date("Y") ?> Perusahaan Anda</footer>
  </div>
</body>
</html>
