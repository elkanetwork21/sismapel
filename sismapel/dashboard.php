<?php 
session_start();
?>


<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ISP Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- AOS Animation -->
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/poppins@5.0.14/index.css" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9f9f9;
    }
    .hero {
      background: linear-gradient(135deg, #0d6efd, #004aad);
      color: #fff;
      padding: 120px 0;
      text-align: center;
    }
    .hero h1 {
      font-size: 3.2rem;
      font-weight: 700;
      animation: fadeInDown 1.5s;
    }
    .hero p {
      font-size: 1.25rem;
      margin-top: 15px;
      animation: fadeInUp 2s;
    }
    .hero .btn {
      margin: 15px 10px;
      font-size: 1.1rem;
      padding: 12px 30px;
      border-radius: 30px;
      transition: all .3s;
    }
    .hero .btn:hover {
      transform: scale(1.05);
    }
    .features {
      padding: 80px 0;
    }
    .features .card {
      border: none;
      transition: all 0.4s ease-in-out;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
      border-radius: 18px;
    }
    .features .card:hover {
      transform: translateY(-12px) scale(1.02);
      box-shadow: 0 15px 30px rgba(0,0,0,0.18);
    }
    .cta {
      background: #0d6efd;
      color: #fff;
      text-align: center;
      padding: 80px 20px;
    }
    .footer {
      background: #002c6e;
      color: #fff;
      text-align: center;
      padding: 20px;
    }
    /* Keyframes */
    @keyframes fadeInDown {
      from {opacity: 0; transform: translateY(-40px);}
      to {opacity: 1; transform: translateY(0);}
    }
    @keyframes fadeInUp {
      from {opacity: 0; transform: translateY(40px);}
      to {opacity: 1; transform: translateY(0);}
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">ISP Management</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link active" href="#">Beranda</a></li>
          <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
          <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
          <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
          <li class="nav-item"><a class="btn btn-light text-primary ms-3" href="auth/login">Login</a></li>
          <li class="nav-item"><a class="btn btn-warning ms-2" href="auth/register.php">Daftar</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero">
    <div class="container" data-aos="zoom-in">
      <h1>Kelola Pelanggan Internet Lebih Mudah</h1>
      <p>Sistem manajemen terintegrasi dengan Mikrotik, keuangan, dan monitoring jaringan.</p>
      <a href="auth/register" class="btn btn-warning">Daftar Sekarang</a>
      <a href="auth/login" class="btn btn-light text-primary">Login</a>
    </div>
  </section>

  <!-- Fitur -->
  <section class="features" id="fitur">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <h2 class="fw-bold">Fitur Utama</h2>
        <p class="text-muted">Semua yang Anda butuhkan dalam satu sistem</p>
      </div>
      <div class="row g-4">
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
          <div class="card p-4 text-center">
            <div class="mb-3 fs-1 text-primary">👥</div>
            <h5>Manajemen Pelanggan</h5>
            <p class="text-muted">Data pelanggan tersimpan aman & mudah diakses.</p>
          </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
          <div class="card p-4 text-center">
            <div class="mb-3 fs-1 text-primary">🌐</div>
            <h5>Integrasi Mikrotik</h5>
            <p class="text-muted">Otomatisasi autentikasi & kontrol bandwidth.</p>
          </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
          <div class="card p-4 text-center">
            <div class="mb-3 fs-1 text-primary">📍</div>
            <h5>Mapping Jaringan</h5>
            <p class="text-muted">Monitoring ODP, ODC, dan perangkat jaringan.</p>
          </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
          <div class="card p-4 text-center">
            <div class="mb-3 fs-1 text-primary">💰</div>
            <h5>Keuangan & Tagihan</h5>
            <p class="text-muted">Laporan otomatis pendapatan & pengeluaran.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Tentang -->
  <section class="container my-5" id="tentang">
    <div class="row align-items-center">
      <div class="col-md-6" data-aos="fade-right">
        <h2 class="fw-bold">Tentang Sistem Kami</h2>
        <p>Kami menghadirkan solusi lengkap untuk ISP kecil hingga menengah dalam mengelola pelanggan, infrastruktur, serta keuangan dengan mudah, cepat, dan efisien.</p>
      </div>
      <div class="col-md-6 text-center" data-aos="fade-left">
        <img src="https://cdn-icons-png.flaticon.com/512/3208/3208753.png" width="300" alt="about">
      </div>
    </div>
  </section>


  <!-- Kontak -->
  <section class="container my-5" id="kontak">
    <div class="row g-4">
      <!-- Form Kontak -->
      <div class="col-md-6" data-aos="fade-right">
        <h2 class="fw-bold mb-4">Hubungi Kami</h2>
        <form action="auth/send_message.php" method="POST" class="p-4 shadow rounded bg-white">
          <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" placeholder="Nama Anda" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="email@domain.com" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Pesan</label>
            <textarea name="pesan" class="form-control" rows="4" placeholder="Tulis pesan Anda..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary px-4">Kirim Pesan</button>
        </form>
      </div>

      <!-- Maps -->
      <div class="col-md-6" data-aos="fade-left">
        <h2 class="fw-bold mb-4">Lokasi Kami</h2>
        <div class="ratio ratio-16x9 shadow rounded">
          <!-- Ganti koordinat dengan lokasi ISP Anda -->
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.2742609280194!2d107.1489638741139!3d-6.227525860987342!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6987fec3414401%3A0xd8ca272b90ed6eca!2sElka%20Net!5e0!3m2!1sid!2sid!4v1759384945745!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta" data-aos="zoom-in">
    <div class="container">
      <h2 class="fw-bold">Siap Meningkatkan Layanan Internet Anda?</h2>
      <p>Daftar sekarang dan nikmati kemudahan dalam mengelola ISP Anda.</p>
      <a href="auth/register" class="btn btn-warning btn-lg">Mulai Sekarang</a>
    </div>
  </section>




  <!-- Footer -->
  <footer class="footer">
    <p>© 2025 ISP Management System. All rights reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <script>
    AOS.init({
      duration: 1000,
      once: true
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php

if (!empty($_SESSION['alert'])) {
    $type = $_SESSION['alert']['type'];
    $msg  = $_SESSION['alert']['msg'];
    echo "<script>
        Swal.fire({
            icon: '$type',
            title: '".($type == "success" ? "Berhasil" : "Oops...")."',
            text: '$msg',
            showConfirmButton: true
        });
    </script>";
    unset($_SESSION['alert']);
}
?>
</body>
</html>
