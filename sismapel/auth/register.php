<?php
include __DIR__ . "../../config.php";

$alert = "";

if (isset($_POST['register'])) {
    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $branch     = trim($_POST['branch']);
    $alamat     = trim($_POST['alamat']);
    $telepon    = trim($_POST['telepon']);

    // cek duplikat username / email
    $checkUser = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' OR email='$email' LIMIT 1");
    if (mysqli_num_rows($checkUser) > 0) {
        $alert = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Registrasi gagal',
            text: 'Username atau Email sudah terdaftar!'
        });
        </script>";
    } else {
        // cari role_id untuk branch_admin
        $roleRes = mysqli_query($conn, "SELECT id FROM roles WHERE role_name='Admin' LIMIT 1");
        $roleRow = mysqli_fetch_assoc($roleRes);
        $role_id = $roleRow['id'] ?? null;

        if (!$role_id) {
            $alert = "
            <script>
            Swal.fire({
                icon: 'error',
                title: 'Registrasi gagal',
                text: 'Role branch_admin belum ada di database!'
            });
            </script>";
        } else {
            // 1. Insert branch dulu (admin_id null sementara)
            $queryBranch = "INSERT INTO branches (nama_branch, address, phone, email) 
                            VALUES ('$branch','$alamat','$telepon','$email')";
            if (mysqli_query($conn, $queryBranch)) {
                $branch_id = mysqli_insert_id($conn);

                // 2. Insert user dengan branch_id + role_id
                $queryUser = "INSERT INTO users (username, email, password, branch_id, role_id,status) 
                              VALUES ('$username','$email','$password','$branch_id','$role_id',0)";
                if (mysqli_query($conn, $queryUser)) {
                    $user_id = mysqli_insert_id($conn);

                    

                    $alert = "
                    <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Registrasi berhasil',
                        text: 'Menunggu verifikasi Administrator',
                        showConfirmButton: true
                    }).then(() => {
                        window.location = '" . BASE_URL . "auth/login';
                    });
                    </script>";
                } else {
                    $alert = "
                    <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Registrasi gagal',
                        text: 'Gagal menyimpan user: " . mysqli_error($conn) . "'
                    });
                    </script>";
                }
            } else {
                $alert = "
                <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi gagal',
                    text: 'Gagal menyimpan branch: " . mysqli_error($conn) . "'
                });
                </script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Branch</title>
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
            max-width: 1100px;
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
            padding: 30px;
            background: #fff;
            animation: fadeInRight 1s ease;
        }
        h2 {
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-control {
            border-radius: 10px;
        }
        .btn-primary {
            border-radius: 10px;
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

    <?= $alert; ?> <!-- Swal Fire muncul di sini -->

    <div class="card">
        <div class="row g-0">
            <!-- Kolom Kiri: Gambar -->
            <div class="col-md-4 image-section">
                <img src="undraw_email-consent_j36b.svg" alt="Illustrasi Register">
            </div>

            <!-- Kolom Kanan: Form (2 kolom) -->
            <div class="col-md-8 form-section">
                <h2>REGISTER BRANCH</h2>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Username </label>
                            <!-- <div class="row g-2"> -->
                                <!-- Input user mengetik -->
                                <!-- <div class="col-12 col-md-6">
                                    <input type="text" id="usernameInput" class="form-control" placeholder="Username" required>
                                </div> -->

                                <!-- Input read-only untuk username final -->
                                <!-- <div class="col-12 col-md-6">
                                    <input type="text" id="usernameFinal" name="username" class="form-control" readonly required>
                                </div> -->

                                <div class="col-12 col-md-12">
                                    <input type="text" id="usernameFinal" name="username" class="form-control" placeholder="Username" required>
                                </div>

                            <!-- </div> -->
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Nama Branch</label>
                            <input type="text" name="branch" class="form-control" required placeholder="Nama Usaha">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Alamat Branch</label>
                            <textarea name="alamat" class="form-control" placeholder="Alamat" rows="1" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Nomor Telepon </label>
                            <input type="text" name="telepon" placeholder="Phone" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email </label>
                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                        </div>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
                    <p class="text-center mt-2">Sudah punya akun? <a href="login">Login</a></p>
                </form>
            </div>
        </div>
    </div>

    <a href="../dashboard" 
   class="btn btn-primary shadow-lg px-4 py-2 d-flex align-items-center gap-2"
   style="position:fixed; bottom:20px; right:20px; border-radius:50px; z-index:999;">
   <i class="bi bi-house"></i>
</a>

</body>


<script>
function generateUniqueCode(length = 4) {
    const chars = 'abcdefghijklmnopqrstuvwxyz123456789!@#$%^&*';
    let code = '';
    for(let i=0;i<length;i++){
        code += chars.charAt(Math.floor(Math.random()*chars.length));
    }
    return code;
}

// Update username final saat user mengetik
const usernameInput = document.getElementById('usernameInput');
const usernameFinal = document.getElementById('usernameFinal');

usernameInput.addEventListener('input', () => {
    let base = usernameInput.value.trim() || 'user';
    usernameFinal.value = base + '.' + generateUniqueCode();
});

// Inisialisasi pertama
usernameFinal.value = 'user_' + generateUniqueCode();
</script>
</html>
