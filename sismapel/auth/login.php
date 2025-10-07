<?php
session_start();
include "../config.php";

$remembered_user = $_COOKIE['remember_user'] ?? "";
$alert = ""; // untuk error

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' LIMIT 1");

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
    if ($row['status'] == 1) { // hanya user aktif yg boleh login
        session_regenerate_id(true);

        $_SESSION['username']  = $row['username'];
        $_SESSION['email']     = $row['email'];
        $_SESSION['branch_id'] = $row['branch_id'];
        $_SESSION['role_id']   = $row['role_id'];
        $_SESSION['user_id']   = $row['id'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        if ($remember) {
            setcookie("remember_user", $username, time() + (86400 * 7), "/");
        } else {
            setcookie("remember_user", "", time() - 3600, "/");
        }

        // flash message login sukses
        $_SESSION['login_success'] = "Selamat datang $username 🎉";

        header("Location: ../index?token=" . $_SESSION['csrf_token']);
        exit;
    } else {
        $alert = "inactive"; // status=0 → belum aktif
    }
} else {
    $alert = "wrong"; // password salah
}
    } else {
        $alert = "notfound"; // user tidak ditemukan
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <img src="undraw_login_weas.svg" alt="Illustrasi Login">
        </div>

        <!-- Kolom Kanan: Form -->
        <div class="col-md-6 form-section">
            <h3 class="text-center mb-4">LOGIN</h3>
            <form method="POST" action="">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Username" 
                               value="<?php echo htmlspecialchars($remembered_user); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" id="password" name="password" placeholder="Password" class="form-control" required>
                        <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                            <i id="toggleIcon" class="bi bi-eye-slash"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" <?php echo ($remembered_user != "") ? "checked" : ""; ?>>
                    <label class="form-check-label">Remember Me</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
                <p class="text-center mt-2">Belum punya akun? <a href="register">Register</a></p>
                <p class="text-center mt-2">
  <a href="forgot_password.php">Lupa Password?</a>
</p>
            </form>
        </div>

    </div>
</div>

<a href="../dashboard" 
   class="btn btn-primary shadow-lg px-4 py-2 d-flex align-items-center gap-2"
   style="position:fixed; bottom:20px; right:20px; border-radius:50px; z-index:999;">
   <i class="bi bi-house"></i> 
</a>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<?php if ($alert == "success"): ?>
    <script>
        Swal.fire({
            title: 'Login Berhasil',
            html: `
              <lottie-player 
                src="https://assets1.lottiefiles.com/packages/lf20_jbrw3hcz.json"  
                background="transparent"  
                speed="1"  
                style="width: 200px; height: 200px; margin:auto;"  
                autoplay>
              </lottie-player>
              <p style="margin-top:10px;">Selamat datang <?php echo $username; ?> 🎉</p>
            `,
            showConfirmButton: false,
            timer: 2000,
            willClose: () => {
              window.location = '../index?token=<?php echo $_SESSION['csrf_token']; ?>';
            }
        });
    </script>
<?php elseif ($alert == "wrong"): ?>
    <script>
        Swal.fire({
            title: 'Password Salah',
            html: `
              <lottie-player 
                src="https://assets10.lottiefiles.com/packages/lf20_qp1q7mct.json"  
                background="transparent"  
                speed="1"  
                style="width: 150px; height: 150px; margin:auto;"  
                autoplay>
              </lottie-player>
              <p style="margin-top:10px; color:#dc3545;">Silakan coba lagi!</p>
            `,
            showConfirmButton: false,
            timer: 2000
        });
    </script>
<?php elseif ($alert == "inactive"): ?>
    <script>
        Swal.fire({
            title: 'Akun Belum Aktif',
            html: `
              <lottie-player 
                src="https://assets2.lottiefiles.com/packages/lf20_t24tpvcu.json"  
                background="transparent"  
                speed="1"  
                style="width: 150px; height: 150px; margin:auto;"  
                autoplay>
              </lottie-player>
              <p style="margin-top:10px; color:#dc3545;">Akun Anda belum aktif.<br>Harap hubungi Administrator!</p>
            `,
            showConfirmButton: true
        });
    </script>

<?php elseif ($alert == "notfound"): ?>
    <script>
        Swal.fire({
            title: 'User Tidak Ditemukan',
            html: `
              <lottie-player 
                src="https://assets2.lottiefiles.com/packages/lf20_t24tpvcu.json"  
                background="transparent"  
                speed="1"  
                style="width: 150px; height: 150px; margin:auto;"  
                autoplay>
              </lottie-player>
              <p style="margin-top:10px; color:#dc3545;">Periksa kembali username Anda.</p>
            `,
            showConfirmButton: false,
            timer: 2000
        });
    </script>
<?php endif; ?>

<script>
    function togglePassword() {
        const passField = document.getElementById("password");
        const icon = document.getElementById("toggleIcon");
        if (passField.type === "password") {
            passField.type = "text";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        } else {
            passField.type = "password";
            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
        }
    }
</script>
</body>
</html>
